<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
/**
 * ###########OME.php/The character set of this file is UTF-8################
 * #
 * #  OME( Open Mail Envrionment ) for PHP   http://mac-ome.jp
 * #  by Masayuki Nii ( msyk@msyk.net )
 * @package OME
 */

/**
 *    このクラスは、日本語での正しいメール送信を行うために作ったものです。
 *    解説は、http://mac-ome.jp/site/php.html を参照してください。
 *
 *    history
 *    <ul>
 *    <li>2003/7/23 「メール送信システムの作り方大全」のサンプルとして制作</li>
 *    <li>2003/9/13 OMEのフリーメール用に少しバージョンアップ</li>
 *    <li>2004/3/26 クラス化した。OMEとして公開する事にした。</li>
 *    <li>2004/4/18 バグフィックス</li>
 *    <li>2004/4/27 バグフィックス（BccやCcができなかったのを修正）</li>
 *    <li>2008/6/6 phpdocumentor向けにコメントを整理、パラメータ設定のメソッドを追加、ファイルをUTF-8にした</li>
 *    <li>2014/2/10 INTER-Mediatorに統合</li>
 *    </ul>
 * @package OME
 * @author Masayuki Nii <msyk@msyk.net>
 * @since PHP 4.0
 * @version
 */

namespace INTERMediator\Messaging;

use Exception;
use INTERMediator\IMUtil;
use INTERMediator\Params;

use Symfony\Component\Mailer\Exception\ExceptionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class OME
{
    private int $bodyWidth = 74;
    private string $mailEncoding = "UTF-8";
    private string $body = '';
    private ?string $bodyType = '';
    private string $subject = '';
    private string $toField = '';
    private string $ccField = '';
    private string $bccField = '';
    private string $fromField = '';
    private string $extHeaders = '';
    private string $errorMessage = '';
    private string $sendmailParam = '';
    private string $tmpContents = '';
    private array $attachments = [];
    private ?array $smtpInfo = null;
    private bool $isSetCurrentDateToHead = false;
    private bool $isUseSendmailParam = false;

    private int $waitMS;

    function __construct()
    {
        mb_internal_encoding('UTF-8');
        $this->waitMS = Params::getParameterValue("waitAfterMail", 20);
    }

    /**    エラーメッセージを取得する。
     *
     *    このクラスの多くの関数は、戻り値がbooleanとなっていて、それをもとにエラーかどうかを判別すできる。
     *    戻り値がfalseである場合、この関数を使ってエラーメッセージを取得できる。
     *
     * @return string 日本語のエラーメッセージの文字列
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function setSmtpInfo(array $info): void
    {
        $this->smtpInfo = $info;
    }

    public function setMailEncoding(string $info): void
    {
        $this->mailEncoding = $info;
    }

    public function setCurrentDateToHead(): void
    {
        $this->isSetCurrentDateToHead = true;
    }

    public function useSendMailParam(): void
    {
        $this->isUseSendmailParam = true;
    }

    /**    メールの本文を設定する。既存の本文は置き換えられる。
     *
     * @param string $str メールの本文に設定する文字列
     * @param ?string $type
     */
    public function setBody(string $str, ?string $type = null): void
    {
        $this->body = $str;
        $this->bodyType = $type;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**    メールの本文を追加する。既存の本文の後に追加する。
     *
     * @param string $str メールの本文に追加する文字列
     */
    public function appendBody(string $str): void
    {
        $this->body .= $str;
    }

    /**    メールの件名を設定する。
     *
     * @param string $str メールの件名に設定する文字列
     */
    public function setSubject(string $str): void
    {
        $this->subject = $str;
    }

    /**    メールの件名を取得するする。
     *
     * @return string メールの件名に設定する文字列
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**    追加のヘッダを1つ設定する。ただし、Subject、To、From、Cc、Bccは該当するメソッドを使う
     *
     * @param string $field 追加するヘッダのフィールド
     * @param string $value フィールドの値。日本語を含める場合は自分でエンコードを行う
     */
    public function setExtraHeader(string $field, string $value): void
    {
        $this->extHeaders = "$field: $value\n";
    }

    /**    sendmailコマンドに与える追加のパラメータを指定する
     *
     * @param string $param 追加のパラメータ。この文字列がそのままmb_send_mail関数の5つ目の引数となる
     */
    public function setSendMailParam(string $param): void
    {
        $this->sendmailParam = $param;
        $this->isUseSendmailParam = true;
    }

    private function divideMailAddress(string $addr): array
    {
        if (strlen($addr) > 1) {
            $lpos = mb_strpos($addr, '<');
            $rpos = mb_strpos($addr, '>', $lpos);
            if ($lpos !== false && $rpos !== false) {
                $name = trim(mb_substr($addr, 0, $lpos)) . trim(mb_substr($addr, $rpos + 1));
                $addr = trim(mb_substr($addr, $lpos + 1, $rpos - $lpos - 1));
                return array($addr, $name);
            }
        }
        return array(trim($addr), '');
    }

    /**    メールアドレスが正しい形式かどうかを判断する。
     *
     *    判断に使う正規表現は「^([a-z0-9_]|\-|\.)+@(([a-z0-9_]|\-)+\.)+[a-z]+$」なので、完全ではないが概ねOKかと。
     *
     * @param ?string $address チェックするメールアドレス。
     * @return    boolean    正しい形式ならTRUE、そうではないときはFALSE
     */
    public function checkEmail(?string $address): bool
    {
        if (is_null($address)) {
            $this->errorMessage = "アドレス“{$address}”は空です。";
        }
        if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9_.+-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9._-]+)+$/", $address)) {
            if (isset($address)) {
                $this->errorMessage = "アドレス“{$address}”は正しくないメールアドレスです。";
            }
            return false;
        } else {
            return true;
        }
    }

    /**    Fromフィールドを設定する。
     * @param string $address 送信者のアドレスで、アドレスとして正しいかどうかがチェックされる
     * @param ?string $name 送信者名（日本語の文字列はそのまま指定可能）で、省略しても良い
     * @param bool $isSetToParam 送信者アドレスを自動的にsendmailの-fパラメータとして与えて、Return-Pathのアドレスとして使用する場合はTRUE。既定値はFALSE
     * @return    bool    与えたメールアドレスが正しく、引数が適切に利用されればTRUEを返す。メールアドレスが正しくないとFALSEを戻し、内部変数等には与えた引数のデータは記録されない
     */
    public function setFromField(string $address, ?string $name = null, bool $isSetToParam = FALSE): bool
    {
        if (is_null($name)) {
            [$address, $name] = $this->divideMailAddress($address);
        }
        if ($this->checkEmail($address)) {
            if ($name == '') {
                $this->fromField = $address;
            } else {
                $this->fromField = "$name <$address>";
            }
            if ($isSetToParam || $this->isUseSendmailParam) {
                $this->sendmailParam = "-f $address";
            }
            return true;
        }
        return false;
    }

    public function getFromField(): string
    {
        return $this->fromField;
    }

    /**    Toフィールドを設定する。すでに設定されていれば上書きされ、この引数の定義だけが残る
     *
     * @param string $address 送信者のアドレス
     * @param ?string $name 送信者名
     * @return    bool    与えたメールアドレスが正しく、引数が適切に利用されればTRUEを返す。メールアドレスが正しくないとFALSEを戻し、内部変数等には与えた引数のデータは記録されない
     *
     */
    public function setToField(string $address, ?string $name = null): bool
    {
        if (is_null($name)) {
            [$address, $name] = $this->divideMailAddress($address);
        }
        if ($this->checkEmail($address)) {
            if ($name == '') {
                $this->toField = "$address";
            } else {
                $this->toField = "$name <$address>";
            }
            return true;
        }
        return false;
    }

    // Getter of the To field.
    public function getToField(): string
    {
        return $this->toField;
    }

    /**    Toフィールドに追加する。
     *
     * @param string $address 送信者のアドレス
     * @param ?string $name 送信者名。日本語の指定も可能
     * @return bool メールアドレスを調べて不正ならfalse（アドレスは追加されない）、そうでなければtrue
     */
    public function appendToField(string $address, ?string $name = null): bool
    {
        if (is_null($name)) {
            [$address, $name] = $this->divideMailAddress($address);
        }
        if ($this->checkEmail($address)) {
            if ($name == '')
                $appendString = "$address";
            else
                $appendString = "$name <$address>";
            if ($this->toField == '')
                $this->toField = $appendString;
            else
                $this->toField .= ", $appendString";
            return true;
        }
        return false;
    }

    // Getter of the Cc field.
    public function getCcField(): string
    {
        return $this->ccField;
    }

    /**    Ccフィールドを設定する。すでに設定されていれば上書きされ、この引数の定義だけが残る
     *
     * @param string $address 送信者のアドレス
     * @param ?string $name 送信者名
     * @return bool    メールアドレスを調べて不正ならfalse（アドレスは設定されない）、そうでなければtrue
     */
    public function setCcField(string $address, ?string $name = null): bool
    {
        if (is_null($name)) {
            [$address, $name] = $this->divideMailAddress($address);
        }
        if ($this->checkEmail($address)) {
            if ($name == '')
                $this->ccField = "$address";
            else
                $this->ccField = "$name <$address>";
            return true;
        }
        return false;
    }

    /**    Ccフィールドに追加する。
     *
     * @param string $address 送信者のアドレス
     * @param ?string $name 送信者名
     * @return bool    メールアドレスを調べて不正ならfalse（アドレスは追加されない）、そうでなければtrue
     */
    public function appendCcField(string $address, ?string $name = null): bool
    {
        if (is_null($name)) {
            [$address, $name] = $this->divideMailAddress($address);
        }
        if ($this->checkEmail($address)) {
            if ($name == '')
                $appendString = "$address";
            else
                $appendString = "$name <$address>";
            if ($this->ccField == '')
                $this->ccField = $appendString;
            else
                $this->ccField .= ", $appendString";
            return true;
        }
        return false;
    }

    // Getter of the Bcc field.
    public function getBccField(): string
    {
        return $this->bccField;
    }

    /**    Bccフィールドを設定する。すでに設定されていれば上書きされ、この引数の定義だけが残る
     *
     * @param string $address 送信者のアドレス
     * @param ?string $name = null 送信者名
     * @return bool メールアドレスを調べて不正ならfalse（アドレスは設定されない）、そうでなければtrue
     */
    public function setBccField(string $address, ?string $name = null): bool
    {
        if (is_null($name)) {
            [$address, $name] = $this->divideMailAddress($address);
        }
        if ($this->checkEmail($address)) {
            if ($name == '')
                $this->bccField = "$address";
            else
                $this->bccField = "$name <$address>";
            return true;
        }
        return false;
    }

    /**    Bccフィールドに追加する。
     *
     * @param string $address 送信者のアドレス
     * @param ?string $name 送信者名
     * @return bool メールアドレスを調べて不正ならfalse（アドレスは追加されない）、そうでなければtrue
     */
    public function appendBccField(string $address, ?string $name = null): bool
    {
        if (is_null($name)) {
            [$address, $name] = $this->divideMailAddress($address);
        }
        if ($this->checkEmail($address)) {
            if ($name == '')
                $appendString = "$address";
            else
                $appendString = "$name <$address>";
            if ($this->bccField == '')
                $this->bccField = $appendString;
            else
                $this->bccField .= ", $appendString";
            return true;
        }
        return false;
    }

    /**    指定したファイルをテンプレートとして読み込む。
     *
     * @param string $tfile テンプレートファイル。たとえば、同一のディレクトリにあるファイルなら、ファイル名だけを記述すればよい。
     * @return bool ファイルの中身を読み込めた場合true、ファイルがないなどのエラーの場合はfalse
     */
    public function setTemplateAsFile(string $tfile): bool
    {
        $fileContensArray = file($tfile);
        if ($fileContensArray) {
            $this->tmpContents = implode('', $fileContensArray);
            return true;
        }
        $this->errorMessage = "テンプレートファイルが存在しません。指定パス={$tfile}";
        return false;
    }

    /**    文字列そのものをテンプレートして設定する。
     *
     * @param string $str    テンプレートとして利用する文字列
     */
    public function setTemplateAsString(string $str): void
    {
        $this->tmpContents = $str;
    }

    /**    テンプレートに引数の配列の内容を差し込み、それをメールの本文とする。既存の本文は上書きされる。
     *
     *    テンプレート中の「@@1@@」が、$ar[0]の文字列と置き換わる。
     *    テンプレート中の「@@2@@」が、$ar[1]の文字列と置き換わる。といった具合に置換する。
     *
     *    たとえば、配列の要素が5の場合、「@@6@@」や「@@7@@」などがテンプレート中に残るが、
     *    これらは差し込みをしてから強制的に削除される。強制削除があった場合にはfalseを戻すが、
     *    それでも差し込み自体は行われている。
     *
     * @param array $ar テンプレートに差し込むデータが入っている配列
     * @return bool 差し込み処理が問題なく終わればtrue、
     * そうでなければfalse（たとえばテンプレートに "@@x@@" などの置き換え文字列が残っている場合。
     * それでも可能な限り置き換えを行い、置き換え文字列は削除される）
     */
    public function insertToTemplate(array $ar): bool
    {
        $tempBody = $this->tmpContents;
        $returnValue = TRUE;
        $counter = 1;
        foreach ($ar as $aItem) {
            $tempBody = str_replace("@@$counter@@", $aItem, $tempBody);
            $counter += 1;
        }
//	if ( preg_match( '@@[0-9]*@@', $tempBody ) )	{
//		$tempBody = @reg_replace('@@[0-9]*@@', '', $tempBody );
//		$this->errorMessage = '差し込みテンプレートに余分が置き換え文字列（@@数字@@）がありましたが、削除しました。';
//		$returnValue = FALSE;
//	}
        $this->body = $tempBody;
        return $returnValue;
    }

    /**    本文の自動改行のバイト数を設定する。初期値は74になっている。
     *
     * @param int $bytes 改行を行うバイト数。0を指定すると自動改行しない。
     */
    public function setBodyWidth(int $bytes): void
    {
        $this->bodyWidth = $bytes;
    }

    /**    文字列中にコントロールコードが含まれているかを調べる
     *
     * @param string $str 調べる文字列
     * @return bool 含まれていたらTRUEを返す
     */
    private function checkControlCodeNothing(string $str): bool
    {
        return mb_ereg_match("/[[:cntrl:]]/", $str);
    }

    /**    添付ファイルを指定する
     * @param string $fpath 添付するファイルへのパス
     */
    public function addAttachment(string $fpath): void
    {
        $this->attachments[] = $fpath;
    }

    /**    メールを送信する。
     *
     *    念のため、To、Cc、Bccのデータにコントロールコードが入っているかどうかをチェックしている。
     *    コントロールコードが見つかればfalseを返し送信はしないものとする。
     *
     * @return bool メールが送信できればtrue、送信できなければFALSE
     * @throws TransportExceptionInterface
     */
    public function send(): bool
    {
        if ($this->checkControlCodeNothing($this->toField)) {
            $this->errorMessage = '宛先の情報にコントロールコードが含まれています。';
            return false;
        }
        if ($this->checkControlCodeNothing($this->ccField)) {
            $this->errorMessage = '宛先の情報にコントロールコードが含まれています。';
            return false;
        }
        if ($this->checkControlCodeNothing($this->bccField)) {
            $this->errorMessage = '宛先の情報にコントロールコードが含まれています。';
            return false;
        }
        $headerField = "X-Mailer: Open Mail Envrionment for PHP on INTER-Mediator(https://inter-mediator.org)\n";
        $headerField .= "Content-Type: text/plain; charset={$this->mailEncoding}\n";
        if ($this->fromField != '')
            $headerField .= "From: {$this->fromField}\n";
        if ($this->ccField != '')
            $headerField .= "Cc: {$this->ccField}\n";
        if ($this->toField != '')
            $headerField .= "To: {$this->toField}\n";
        if (is_null($this->smtpInfo)) {
            if ($this->bccField != '')
                $headerField .= "Bcc: {$this->bccField}\n";
        }
        if ($this->isSetCurrentDateToHead) {
            $formatString = 'r'; //"D, d M Y H:i:s O (T)";
            $headerField .= "Date: " . date($formatString) . "\n";
            // Mon, 10 Feb 2014 19:36:36 +0900 (JST)
        }
        if ($this->extHeaders != '')
            $headerField .= $this->extHeaders;

        $bodyString = '';
        if (is_null($this->smtpInfo)) {
            $bodyString = $this->devideWithLimitingWidth($this->body);
            if ($this->mailEncoding != 'UTF-8') {
                $bodyString = mb_convert_encoding($bodyString, $this->mailEncoding);
            }
            if ($this->isUseSendmailParam) {
                $resultMail = mail(
                    rtrim($this->header_base64_encode($this->toField, False)),
                    rtrim($this->header_base64_encode($this->subject, true)),
                    $bodyString,
                    $this->header_base64_encode($headerField, True),
                    $this->sendmailParam);
            } else {
                $resultMail = mail(
                    rtrim($this->header_base64_encode($this->toField, False)),
                    rtrim($this->header_base64_encode($this->subject, true)),
                    $bodyString,
                    $this->header_base64_encode($headerField, True));
            }
        } else {
            $port = (isset($this->smtpInfo['port']) && strlen($this->smtpInfo['port']) > 0) ? $this->smtpInfo['port'] : 25;
            $portPart = ":{$port}";
            $host = (isset($this->smtpInfo['host']) && strlen($this->smtpInfo['host']) > 0) ? $this->smtpInfo['host'] : 'default';
            $protocol = (isset($this->smtpInfo['protocol']) && strlen($this->smtpInfo['protocol']) > 0) ? $this->smtpInfo['protocol'] : 'native';
            $user = (isset($this->smtpInfo['user']) && strlen($this->smtpInfo['user']) > 0) ? $this->smtpInfo['user'] : '';
            $pass = (isset($this->smtpInfo['pass']) && strlen($this->smtpInfo['pass']) > 0) ? $this->smtpInfo['pass'] : '';
            $userPart = urlencode($user) . ":" . urlencode($pass) . "@";
            if ($user == '' || $pass == '') {
                $userPart = "";
            }
            if ($host == 'default' || strpos($host, 'default?') === 0) {
                $portPart = "";
            }
            $url = "{$protocol}://{$userPart}{$host}{$portPart}";

            $email = new Email();
            $email->subject($this->subject);
            if ($this->fromField != null) {
                $addArray = $this->recepientsAddressArray(explode(',', $this->fromField));
                if (strlen($this->fromField) > 0 && count($addArray) > 0) {
                    $email->from($addArray[0]);
                }
            }
            $recipientsInfo = '';
            $addArray = $this->recepientsAddressArray(explode(',', $this->toField));
            if (strlen($this->toField) > 0 && count($addArray) > 0) {
                foreach ($addArray as $address) {
                    $email->addTo($address);
                }
                $recipientsInfo .= "[To]{$this->toField}";
            }
            $addArray = $this->recepientsAddressArray(explode(',', $this->ccField));
            if (strlen($this->ccField) > 0 && count($addArray) > 0) {
                foreach ($addArray as $address) {
                    $email->addCc($address);
                }
                $recipientsInfo .= "[CC]{$this->ccField}";
            }
            $addArray = $this->recepientsAddressArray(explode(',', $this->bccField));
            if (strlen($this->bccField) > 0 && strlen($this->toField) > 0 && count($addArray) > 0) {
                foreach ($addArray as $address) {
                    $email->addBcc($address);
                }
                $recipientsInfo .= "[BCC]{$this->bccField}";
            }
            $this->bodyType = ($this->bodyType === false) ? 'text/plain' : $this->bodyType;
            $targetTerm = "##image##";
            if (strpos($this->body, $targetTerm) !== false && $this->bodyType == 'text/html') {
                $imagePos = strpos($this->body, $targetTerm);
                $counter = 1;
                foreach ($this->attachments as $path) {
                    $cid = "embedded-image-{$counter}";
                    $counter += 1;
                    $email->embedFromPath($path, $cid/*, IMUtil::getMIMEType(pathinfo($path, PATHINFO_EXTENSION))*/);
                    $bodyString = substr($this->body, 0, $imagePos) . "cid:{$cid}"
                        . substr($this->body, $imagePos + strlen($targetTerm));
                }
            } else {
                $bodyString = ($this->bodyType == 'text/html') ? $this->body : $this->devideWithLimitingWidth($this->body);
                if ($this->mailEncoding != 'UTF-8') {
                    $bodyString = mb_convert_encoding($bodyString, $this->mailEncoding);
                }
                foreach ($this->attachments as $path) {
                    $email->attachFromPath($path);
                }
            }
            if ($this->bodyType == 'text/html') {
                $email->html($bodyString);
            } else {
                $email->text($bodyString);
            }
            $resultMail = true;
            try {
                (new Mailer(Transport::fromDsn($url)))->send($email);
            } catch (Exception $e) {
                $headMsg = (IMUtil::getMessageClassInstance())->getMessageAs(1050);
                $exceptionMessage = $e->getMessage();
                if (strlen($exceptionMessage) > 0) {
                    $this->errorMessage = $headMsg . '"' . $exceptionMessage . "\"\n";
                } else {
                    $this->errorMessage = "{$headMsg}{$recipientsInfo}\n";
                }
                $resultMail = false;
            }
            usleep($this->waitMS * 1000);
        }
        return $resultMail;
    }

    private function recepientsArray(array $ar): array
    {
        mb_regex_encoding('UTF-8');
        $result = [];
        foreach ($ar as $item) {
            $str = trim($item);
            if (strlen($str) > 1) {
                $r = $this->divideMailAddress($str);
                if (strlen($r[1]) > 0) {
                    $result[$r[0]] = $r[1];
                } else {
                    $result[] = $r[0];
                }
            } else {
                $result[] = $str;
            }
        }
        return $result;
    }

    private function recepientsAddressArray(array $ar): array
    {
        mb_regex_encoding('UTF-8');
        $result = [];
        foreach ($ar as $item) {
            $str = trim($item);
            if (strlen($str) > 1) {
                $r = $this->divideMailAddress($str);
                if ($r[0]) {
                    $result[] = $r[1] ? new Address($r[0], $r[1]) : new Address($r[0]);
                }
            }
        }
        return $result;
    }

    /**    文字列を別メソッドで決められたバイト数ごとに分割する。ワードラップ、禁則を考慮する。（内部利用メソッド）
     *
     * @param string $str 処理対象の文字列
     * @return string 分割された文字列
     */
    private function devideWithLimitingWidth(string $str): string
    {
        $maxByteCount = 2;
        if ($this->bodyWidth == 0)
            return $str;
        $newLine = "\n";
        $strLength = mb_strlen($str);
        $devidedStr = mb_substr($str, 0, 1);
        $beforeChar = $devidedStr;
        if ($devidedStr == $newLine)
            $byteLength = 0;
        else
            $byteLength = strlen($devidedStr);
        $lineByteCounter = 0;
        for ($pos = 1; $pos < $strLength; $pos++) {
            $posChar = mb_substr($str, $pos, 1);
            if ($posChar == $newLine) {
                $byteLength = 0;
                $lineByteCounter = 0;
            } else {
                if (($lineByteCounter >= $this->bodyWidth)
                    && !$this->isInhibitLineTopChar($posChar)
                    && !$this->isInhibitLineEndChar($beforeChar)
                ) {
                    if (($this->isJapanese($posChar)
                            && !$this->isSpace($posChar))
                        || ($this->isJapanese($beforeChar)
                            && $this->isWordElement($posChar))
                        || (!$this->isWordElement($beforeChar)
                            && $this->isWordElement($posChar))
                    ) {
                        $devidedStr .= $newLine;
                        $byteLength = 0;
                        $lineByteCounter = 0;
                    } // Endo of if
                }
                $byteLength += strlen($posChar);
                $lineByteCounter += min($maxByteCount, strlen($posChar));
            }
            $devidedStr .= $posChar;
            $beforeChar = $posChar;
        }
        return $devidedStr;
    } // End of function devideWithLimitingWidth()

    private function unifyCRLF(string $str): string
    {
        $strUnifiedLF = str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
        return str_replace("\n", "\r\n", $strUnifiedLF);
    }

    /**    引数の文字が空白かどうかのチェックを行う。ただ、これは標準の関数を利用すべきかもしれない（内部利用メソッド／devideWithLimitingWidth関数で利用）
     *
     * @param string $str 処理対象の文字
     * @return bool 空白ならTRUE
     */
    private function isSpace(string $str): bool
    {
        switch ($str) {
            case " ":
            case "｡｡":
                return True;
        } // Endo of switch
        return False;
    } // End of isSpace()

    /**    引数の文字が単語を構成する文字（アルファベット、あるいは数値）かどうかのチェックを行う（内部利用メソッド／devideWithLimitingWidth関数で利用）
     *
     * @param string $str 処理対象の文字
     * @return bool 単語を構成する文字ならTRUE
     */
    private function isWordElement(string $str): bool
    {
        if ($this->isSpace($str)) return False;
        $cCode = ord($str);
        if (($cCode >= 0x30) && ($cCode <= 0x39)) return True;
        if (($cCode >= 0x41) && ($cCode <= 0x5A)) return True;
        if (($cCode >= 0x61) && ($cCode <= 0x7A)) return True;
        if ($str == "'") {
            return True;
        } // Endo of switch
        return False;
    } // End of function isWordElement()

    /**    引数が日本語の文字列かどうかを判断する（内部利用メソッド／devideWithLimitingWidth関数で利用）
     *
     * @param string $str 処理対象の文字
     * @return bool 日本語ならTRUE
     */
    private function isJapanese(string $str): bool
    {
        $cCode = ord($str);
        if ($cCode >= 0x80) return True;
        return False;
    } // End of function isJapanese()

    /**    引数が日本語の行頭禁則文字かどうかを判断する（内部利用メソッド／devideWithLimitingWidth関数で利用）
     *
     * @param string $str 処理対象の文字
     * @return bool 行頭禁則文字ならTRUE
     */
    private function isInhibitLineTopChar(string $str): bool
    {
        switch ($str) {
            case ')':
            case ']':
            case '}':
            case '）':
            case '】':
            case '］':
            case '」':
            case '』':
            case '〕':
            case '｝':
            case '〉':
            case '》':
            case "’":
            case '”':
            case ':':
            case ';':
            case '!':
            case '.':
            case '?':
            case '。':
            case '、':
            case '，':
            case '…':
            case '‥':
            case '．':
            case '：':
            case '；':
            case '！':
            case '？':
                return True;
        } // Endo of switch
        return False;
    } // End of function isInhibitLineTopChar

    /**    引数が日本語の行末禁則文字かどうかを判断する（内部利用メソッド／devideWithLimitingWidth関数で利用）
     *
     * @param string $str 処理対象の文字
     * @return bool 行末禁則文字ならTRUE
     */
    private function isInhibitLineEndChar(string $str): bool
    {
        switch ($str) {
            case '(':
            case '[':
            case '{':
            case '（':
            case '“':
            case '【':
            case '［':
            case '『':
            case '「':
            case '〔':
            case '｛':
            case '〈':
            case '《':
            case "‘":
                return True;
        } // Endo of switch
        return False;
    } // End of function isInhibitLineEndChar

    /**    メールヘッダ用にMIMEに即した文字列に変換する（内部利用メソッド／devideWithLimitingWidth関数で利用）
     *
     *    ヘッダ文字列として利用できるように、文字列内の日本語の部分をMIMEエンコードする。
     *    文字列の中を日本語と英語に分けて、日本語の部分だけをISO-2022-JPでエンコードする。
     *
     * @param string $str 処理対象の文字列
     * @param bool $isSeparateLine  日本語と英語の境目を改行する
     * @return string MIMEエンコードした文字列
     */
    private function header_base64_encode(string $str, bool $isSeparateLine): string
    {
        $strLen = mb_strlen($str);
        $encodedString = '';
        $substring = '';
        $beforeIsMBChar = False;
        $isFirstLine = True;
        $ch = '';
        for ($i = 0; $i <= $strLen; $i++) {
            if ($i == $strLen) {
                $thisIsMBChar = !$beforeIsMBChar;
            } else {
                $ch = mb_substr($str, $i, 1);
                $thisIsMBChar = (ord($ch) > 127);
            } // Endo of else
            if (($thisIsMBChar != $beforeIsMBChar) && ($substring != '')) {
                if ($isSeparateLine && !$isFirstLine) {
                    $encodedString .= "\t";
                }
                if ($thisIsMBChar) {
                    $encodedString .= $substring;
                } else {
                    $jisSeq = mb_convert_encoding($substring, $this->mailEncoding, 'UTF-8');
                    if ($this->mailEncoding == "ISO-2022-JP") {
                        $jisSeq .= chr(27) . '(B';
                    }
                    $bEncoded = base64_encode($jisSeq);
                    $encodedString .= "=?{$this->mailEncoding}?B?{$bEncoded}?=";
                } // Endo of else
                if ($isSeparateLine && !$isFirstLine) {
                    $encodedString .= "\n";
                }
                $substring = '';
                $isFirstLine = False;
            } // Endo of if
            $substring .= $ch;
            $beforeIsMBChar = $thisIsMBChar;
        } // Endo of for
        return $encodedString;
    } // End of function header_base64_encode

} // End of class OME
