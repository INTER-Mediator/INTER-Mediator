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
class OME
{
    private $bodyWidth = 74;
    private $mailEncoding = "UTF-8";

    private $body = '';
    private $subject = '';
    private $toField = '';
    private $ccField = '';
    private $bccField = '';
    private $fromField = '';
    private $extHeaders = '';
    private $errorMessage = '';
    private $sendmailParam = '';
    private $tmpContents = '';

    private $senderAddress = null;
    private $smtpInfo = null;
    private $isSetCurrentDateToHead = false;
    private $isUseSendmailParam = false;

    function __construct()
    {
        mb_internal_encoding('UTF-8');
    }

    /**    エラーメッセージを取得する。
     *
     *    このクラスの多くの関数は、戻り値がbooleanとなっていて、それをもとにエラーかどうかを判別すできる。
     *    戻り値がfalseである場合、この関数を使ってエラーメッセージを取得できる。
     *
     * @return string 日本語のエラーメッセージの文字列
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function setSmtpInfo($info)
    {
        $this->smtpInfo = $info;
    }

    public function setMailEncoding($info)
    {
        $this->mailEncoding = $info;
    }

    public function setCurrentDateToHead()
    {
        $this->isSetCurrentDateToHead = true;
    }

    public function useSendMailParam()
    {
        $this->isUseSendmailParam = true;
    }

    /**    メールの本文を設定する。既存の本文は置き換えられる。
     *
     * @param string メールの本文に設定する文字列
     */
    public function setBody($str)
    {
        $this->body = $str;
    }

    /**    メールの本文を追加する。既存の本文の後に追加する。
     *
     * @param string メールの本文に追加する文字列
     */
    public function appendBody($str)
    {
        $this->body .= $str;
    }

    /**    メールの件名を設定する。
     *
     * @param string メールの件名に設定する文字列
     */
    public function setSubject($str)
    {
        $this->subject = $str;
    }

    /**    追加のヘッダを1つ設定する。ただし、Subject、To、From、Cc、Bccは該当するメソッドを使う
     *
     * @param string    追加するヘッダのフィールド
     * @param string    フィールドの値。日本語を含める場合は自分でエンコードを行う
     */
    public function setExtraHeader($field, $value)
    {
        $this->extHeaders = "$field: $value\n";
    }

    /**    sendmailコマンドに与える追加のパラメータを指定する
     *
     * @param string    追加のパラメータ。この文字列がそのままmb_send_mail関数の5つ目の引数となる
     */
    public function setSendMailParam($param)
    {
        $this->sendmailParam = $param;
        $this->isUseSendmailParam = true;
    }

    private function divideMailAddress($addr)
    {
        if(preg_match(
                "/(.*)<(([a-zA-Z0-9])+([a-zA-Z0-9_\.-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+))+>/",
                $addr, $matches)===1)   {
            return array('name' => trim($matches[1]), 'address' => $matches[2]);
        }
        return array('name' => '', 'address' => trim($addr));
    }

    /**    メールアドレスが正しい形式かどうかを判断する。
     *
     *    判断に使う正規表現は「^([a-z0-9_]|\-|\.)+@(([a-z0-9_]|\-)+\.)+[a-z]+$」なので、完全ではないが概ねOKかと。
     *
     * @return    boolean    正しい形式ならTRUE、そうではないときはFALSE
     * @param    string    チェックするメールアドレス。
     */
    public function checkEmail($address)
    {
        if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9_\.\+-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $address)) {
            if (isset($address)) {
                $this->errorMessage = "アドレス“{$address}”は正しくないメールアドレスです。";
            }
            return false;
        } else {
            return true;
        }
    }

    /**    Fromフィールドを設定する。
     * @return    boolean    与えたメールアドレスが正しく、引数が適切に利用されればTRUEを返す。メールアドレスが正しくないとFALSEを戻し、内部変数等には与えた引数のデータは記録されない
     * @param    string    送信者のアドレスで、アドレスとして正しいかどうかがチェックされる
     * @param    string    送信者名（日本語の文字列はそのまま指定可能）で、省略しても良い
     * @param    boolean    送信者アドレスを自動的にsendmailの-fパラメータとして与えて、Return-Pathのアドレスとして使用する場合はTRUE。既定値はFALSE
     */
    public function setFromField($address, $name = false, $isSetToParam = FALSE)
    {
        if ($name === false)    {
            $divided = $this->divideMailAddress($address);
            $address = $divided['address'];
            $name = $divided['name'];
        }
        if ($this->checkEmail($address)) {
            if ($name == '') {
                $this->fromField = $address;
                if ($isSetToParam || $this->isUseSendmailParam)
                    $this->sendmailParam = "-f $address";
            } else {
                $this->fromField = "$name <$address>";
                if ($isSetToParam || $this->isUseSendmailParam)
                    $this->sendmailParam = "-f $address";
            }
            $this->senderAddress = $address;
            return true;
        }
        return false;
    }

    /**    Toフィールドを設定する。すでに設定されていれば上書きされ、この引数の定義だけが残る
     *
     * @return    boolean    与えたメールアドレスが正しく、引数が適切に利用されればTRUEを返す。メールアドレスが正しくないとFALSEを戻し、内部変数等には与えた引数のデータは記録されない
     *
     * @param string 送信者のアドレス
     * @param string 送信者名
     */
    public function setToField($address, $name = false)
    {
        if ($name === false)    {
            $divided = $this->divideMailAddress($address);
            $address = $divided['address'];
            $name = $divided['name'];
        }
        if ($this->checkEmail($address)) {
            if ($name == '' || $name === false)
                $this->toField = "$address";
            else
                $this->toField = "$name <$address>";
            return true;
        }
        return false;
    }

    // This method for unit testing.
    public function getToField()    {
        return $this->toField;
    }

    /**    Toフィールドに追加する。
     *
     * @return boolean メールアドレスを調べて不正ならfalse（アドレスは追加されない）、そうでなければtrue
     * @param string    送信者のアドレス
     * @param string    送信者名。日本語の指定も可能
     */
    public function appendToField($address, $name = false)
    {
        if ($name === false)    {
            $divided = $this->divideMailAddress($address);
            $address = $divided['address'];
            $name = $divided['name'];
        }
        if ($this->checkEmail($address)) {
            if ($name == '' || $name === false)
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

    /**    Ccフィールドを設定する。すでに設定されていれば上書きされ、この引数の定義だけが残る
     *
     * @param string    送信者のアドレス
     * @param string    送信者名
     * @return boolean    メールアドレスを調べて不正ならfalse（アドレスは設定されない）、そうでなければtrue
     */
    public function setCcField($address, $name = false)
    {
        if ($name === false)    {
            $divided = $this->divideMailAddress($address);
            $address = $divided['address'];
            $name = $divided['name'];
        }
        if ($this->checkEmail($address)) {
            if ($name == '' || $name === false)
                $this->ccField = "$address";
            else
                $this->ccField = "$name <$address>";
            return true;
        }
        return false;
    }

    /**    Ccフィールドに追加する。
     *
     * @param string 送信者のアドレス
     * @param string 送信者名
     * @return boolean    メールアドレスを調べて不正ならfalse（アドレスは追加されない）、そうでなければtrue
     */
    public function appendCcField($address, $name = false)
    {
        if ($name === false)    {
            $divided = $this->divideMailAddress($address);
            $address = $divided['address'];
            $name = $divided['name'];
        }
        if ($this->checkEmail($address)) {
            if ($name == '' || $name === false)
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

    /**    Bccフィールドを設定する。すでに設定されていれば上書きされ、この引数の定義だけが残る
     *
     * @param string 送信者のアドレス
     * @param string 送信者名
     * @return boolean メールアドレスを調べて不正ならfalse（アドレスは設定されない）、そうでなければtrue
     */
    public function setBccField($address, $name = false)
    {
        if ($name === false)    {
            $divided = $this->divideMailAddress($address);
            $address = $divided['address'];
            $name = $divided['name'];
        }
        if ($this->checkEmail($address)) {
            if ($name == '' || $name === false)
                $this->bccField = "$address";
            else
                $this->bccField = "$name <$address>";
            return true;
        }
        return false;
    }

    /**    Bccフィールドに追加する。
     *
     * @param string 送信者のアドレス
     * @param string 送信者名
     * @return string メールアドレスを調べて不正ならfalse（アドレスは追加されない）、そうでなければtrue
     */
    public function appendBccField($address, $name = false)
    {
        if ($name === false)    {
            $divided = $this->divideMailAddress($address);
            $address = $divided['address'];
            $name = $divided['name'];
        }
        if ($this->checkEmail($address)) {
            if ($name == '' || $name === false)
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
     * @param string テンプレートファイル。たとえば、同一のディレクトリにあるファイルなら、ファイル名だけを記述すればよい。
     * @return boolean ファイルの中身を読み込めた場合true、ファイルがないなどのエラーの場合はfalse
     */
    public function setTemplateAsFile($tfile)
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
     * @param string    テンプレートとして利用する文字列
     */
    public function setTemplateAsString($str)
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
     * @param array テンプレートに差し込むデータが入っている配列
     * @return boolean 差し込み処理が問題なく終わればtrue、
     * そうでなければfalse（たとえばテンプレートに "@@x@@" などの置き換え文字列が残っている場合。
     * それでも可能な限り置き換えを行い、置き換え文字列は削除される）
     */
    public function insertToTemplate($ar)
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
     * @param integer 改行を行うバイト数。0を指定すると自動改行しない。
     */
    public function setBodyWidth($bytes)
    {
        $this->bodyWidth = $bytes;
    }

    /**    文字列中にコントロールコードが含まれているかを調べる
     *
     * @param string 調べる文字列
     * @return boolean 含まれていたらTRUEを返す
     */
    private function checkControlCodeNothing($str)
    {
        return mb_ereg_match("/[[:cntrl:]]/", $str);
    }

    /**    メールを送信する。
     *
     *    念のため、To、Cc、Bccのデータにコントロールコードが入っているかどうかをチェックしている。
     *    コントロールコードが見つかればfalseを返し送信はしないものとする。
     *
     * @return boolean メールが送信できればtrue、送信できなければFALSE
     */
    public function send()
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
        $headerField = "X-Mailer: Open Mail Envrionment for PHP on INTER-Mediator(http://inter-mediator.org)\n";
        $headerField .= "Content-Type: text/plain; charset={$this->mailEncoding}\n";
        if ($this->fromField != '')
            $headerField .= "From: {$this->fromField}\n";
        if ($this->ccField != '')
            $headerField .= "Cc: {$this->ccField}\n";
        if ($this->smtpInfo === null) {
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

        $bodyString = $this->devideWithLimitingWidth($this->body);
        if ($this->mailEncoding != 'UTF-8') {
            $bodyString = mb_convert_encoding($bodyString, $this->mailEncoding);
        }

        if ($this->smtpInfo === null) {
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
            if ($this->toField != '')
                $headerField .= $this->unifyCRLF("To: {$this->toField}\n");
            $headerField .= 'Subject: '
                . $this->unifyCRLF(rtrim($this->header_base64_encode($this->subject, true)))
                . "\n";
            if ($this->senderAddress != null) {
                $this->smtpInfo['from'] = $this->senderAddress;
            }
            $smtp = new QdSmtp($this->smtpInfo);
            $recipients = array();
            $headerValues = array($this->toField, $this->ccField, $this->bccField);
            foreach ($headerValues as $headerValue) {
                $temp = array();
                $value = explode(',', $this->unifyCRLF(rtrim($headerValue, False)));
                foreach ($value as $valueItem) {
                    $divided = $this->divideMailAddress($valueItem);
                    $array = array($divided['address']);
                    $temp = array_merge($temp, $array);
                }
                if ($temp !== array() && $temp !== array('')) {
                    $recipients = array_merge($recipients , $temp);
                }
            }
            $recipients = array_unique($recipients);
            $smtp->to($recipients);
            $smtp->data($this->unifyCRLF($this->header_base64_encode($headerField, True))
                . $this->unifyCRLF($bodyString));
            $resultMail = $smtp->send();
        }
        return $resultMail;
    }

    /**    文字列を別メソッドで決められたバイト数ごとに分割する。ワードラップ、禁則を考慮する。（内部利用メソッド）
     *
     * @param string 処理対象の文字列
     * @return string 分割された文字列
     */
    private function devideWithLimitingWidth($str)
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

    private function unifyCRLF($str)
    {
        $strUnifiedLF = str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
        return str_replace("\n", "\r\n", $strUnifiedLF);
    }

    /**    引数の文字が空白かどうかのチェックを行う。ただ、これは標準の関数を利用すべきかもしれない（内部利用メソッド／devideWithLimitingWidth関数で利用）
     *
     * @param string 処理対象の文字
     * @return boolean 空白ならTRUE
     */
    private function isSpace($str)
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
     * @param string 処理対象の文字
     * @return boolean 単語を構成する文字ならTRUE
     */
    private function isWordElement($str)
    {
        if ($this->isSpace($str)) return False;
        $cCode = ord($str);
        if (($cCode >= 0x30) && ($cCode <= 0x39)) return True;
        if (($cCode >= 0x41) && ($cCode <= 0x5A)) return True;
        if (($cCode >= 0x61) && ($cCode <= 0x7A)) return True;
        switch ($str) {
            case "'":
                return True;
        } // Endo of switch
        return False;
    } // End of function isWordElement()

    /**    引数が日本語の文字列かどうかを判断する（内部利用メソッド／devideWithLimitingWidth関数で利用）
     *
     * @param string 処理対象の文字
     * @return boolean 日本語ならTRUE
     */
    private function isJapanese($str)
    {
        $cCode = ord($str);
        if ($cCode >= 0x80) return True;
        return False;
    } // End of function isJapanese()

    /**    引数が日本語の行頭禁則文字かどうかを判断する（内部利用メソッド／devideWithLimitingWidth関数で利用）
     *
     * @param string 処理対象の文字
     * @return boolean 行頭禁則文字ならTRUE
     */
    private function isInhibitLineTopChar($str)
    {
        switch ($str) {
            case ')':
            case ']':
            case '}':
            case '）':
            case '】':
            case '”':
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
     * @param string 処理対象の文字
     * @return boolean 行末禁則文字ならTRUE
     */
    private function isInhibitLineEndChar($str)
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
     * @param string 処理対象の文字列
     * @param boolean  日本語と英語の境目を改行する
     * @return string MIMEエンコードした文字列
     */
    private function header_base64_encode($str, $isSeparateLine)
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
