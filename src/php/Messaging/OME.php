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

/**
 * Class OME (Open Mail Environment)
 * Provides methods for composing and sending emails with Japanese language support.
 * Supports SMTP and mail() delivery, attachments, templates, and encoding handling.
 *
 * @package INTERMediator\Messaging
 */
class OME
{
    /**
     * @var int The byte width for automatic line breaks in the email body.
     */
    private int $bodyWidth = 74;
    /**
     * @var string The character encoding used for the email.
     */
    private string $mailEncoding = "UTF-8";
    /**
     * @var string The email body content.
     */
    private string $body = '';
    /**
     * @var string|null The MIME type of the email body (e.g., 'text/html').
     */
    private ?string $bodyType = '';
    /**
     * @var string The subject of the email.
     */
    private string $subject = '';
    /**
     * @var string The recipient(s) in the To field.
     */
    private string $toField = '';
    /**
     * @var string The recipient(s) in the Cc field.
     */
    private string $ccField = '';
    /**
     * @var string The recipient(s) in the Bcc field.
     */
    private string $bccField = '';
    /**
     * @var string The sender address in the From field.
     */
    private string $fromField = '';
    /**
     * @var string Additional email headers.
     */
    private string $extHeaders = '';
    /**
     * @var string Stores the latest error message.
     */
    private string $errorMessage = '';
    /**
     * @var string Additional parameters for the sendmail command.
     */
    private string $sendmailParam = '';
    /**
     * @var string Temporary storage for template contents.
     */
    private string $tmpContents = '';
    /**
     * @var array List of file paths for attachments.
     */
    private array $attachments = [];
    /**
     * @var array|null SMTP connection information.
     */
    private ?array $smtpInfo = null;
    /**
     * @var bool Whether to set the current date in the email header.
     */
    private bool $isSetCurrentDateToHead = false;
    /**
     * @var bool Whether to use the sendmail parameter.
     */
    private bool $isUseSendmailParam = false;
    /**
     * @var int Milliseconds to wait after sending mail.
     */
    private int $waitMS;

    /**
     * OME constructor.
     * Initializes encoding and wait time.
     */
    function __construct()
    {
        mb_internal_encoding('UTF-8');
        $this->waitMS = Params::getParameterValue("waitAfterMail", 20);
    }

    /**
     * Returns the latest error message.
     * @return string Error message in Japanese.
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Sets SMTP connection information.
     * @param array $info SMTP configuration array.
     */
    public function setSmtpInfo(array $info): void
    {
        $this->smtpInfo = $info;
    }

    /**
     * Sets the mail encoding.
     * @param string $info Encoding name.
     */
    public function setMailEncoding(string $info): void
    {
        $this->mailEncoding = $info;
    }

    /**
     * Enables setting the current date in the header.
     */
    public function setCurrentDateToHead(): void
    {
        $this->isSetCurrentDateToHead = true;
    }

    /**
     * Enables using the sendmail parameter.
     */
    public function useSendMailParam(): void
    {
        $this->isUseSendmailParam = true;
    }

    /**
     * Sets the email body, replacing any existing content.
     * @param string $str The body content.
     * @param ?string $type MIME type of the body (optional).
     */
    public function setBody(string $str, ?string $type = null): void
    {
        $this->body = $str;
        $this->bodyType = $type;
    }

    /**
     * Gets the email body content.
     * @return string The current body content.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Appends content to the email body.
     * @param string $str The string to append.
     */
    public function appendBody(string $str): void
    {
        $this->body .= $str;
    }

    /**
     * Sets the subject of the email.
     * @param string $str The subject string.
     */
    public function setSubject(string $str): void
    {
        $this->subject = $str;
    }

    /**
     * Gets the subject of the email.
     * @return string The subject string.
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Sets an extra header (other than Subject, To, From, Cc, Bcc).
     * @param string $field Header field name.
     * @param string $value Header value.
     */
    public function setExtraHeader(string $field, string $value): void
    {
        $this->extHeaders = "$field: $value\n";
    }

    /**
     * Sets additional parameters for the sendmail command.
     * @param string $param Parameter string for mb_send_mail.
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

    /**
     * Checks if the email address is valid.
     * @param ?string $address The email address to check.
     * @return bool True if the address is valid, false otherwise.
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

    /**
     * Sets the From field with the sender's address and name.
     * @param string $address The sender's email address.
     * @param ?string $name The sender's name (optional).
     * @param bool $isSetToParam Whether to set the sender's address as the Return-Path (optional).
     * @return bool True if the address is valid, false otherwise.
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

    /**
     * Gets the From field.
     * @return string The sender's email address and name.
     */
    public function getFromField(): string
    {
        return $this->fromField;
    }

    /**
     * Sets the To field with the recipient's address and name.
     * @param string $address The recipient's email address.
     * @param ?string $name The recipient's name (optional).
     * @return bool True if the address is valid, false otherwise.
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

    /**
     * Gets the To field.
     * @return string The recipient's email address and name.
     */
    public function getToField(): string
    {
        return $this->toField;
    }

    /**
     * Appends the To field with the recipient's address and name.
     * @param string $address The recipient's email address.
     * @param ?string $name The recipient's name (optional).
     * @return bool True if the address is valid, false otherwise.
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

    /**
     * Gets the Cc field.
     * @return string The recipient's email address and name.
     */
    public function getCcField(): string
    {
        return $this->ccField;
    }

    /**
     * Sets the Cc field with the recipient's address and name.
     * @param string $address The recipient's email address.
     * @param ?string $name The recipient's name (optional).
     * @return bool True if the address is valid, false otherwise.
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

    /**
     * Appends the Cc field with the recipient's address and name.
     * @param string $address The recipient's email address.
     * @param ?string $name The recipient's name (optional).
     * @return bool True if the address is valid, false otherwise.
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

    /**
     * Gets the Bcc field.
     * @return string The recipient's email address and name.
     */
    public function getBccField(): string
    {
        return $this->bccField;
    }

    /**
     * Sets the Bcc field with the recipient's address and name.
     * @param string $address The recipient's email address.
     * @param ?string $name The recipient's name (optional).
     * @return bool True if the address is valid, false otherwise.
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

    /**
     * Appends the Bcc field with the recipient's address and name.
     * @param string $address The recipient's email address.
     * @param ?string $name The recipient's name (optional).
     * @return bool True if the address is valid, false otherwise.
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

    /**
     * Sets a template file for the email body.
     * @param string $tfile The path to the template file.
     * @return bool True if the file is loaded successfully, false otherwise.
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

    /**
     * Sets a template string for the email body.
     * @param string $str The template string.
     */
    public function setTemplateAsString(string $str): void
    {
        $this->tmpContents = $str;
    }

    /**
     * Inserts data into the template and sets the email body.
     * @param array $ar The data to insert into the template.
     * @return bool True if the insertion is successful, false otherwise.
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

    /**
     * Sets the byte width for automatic line breaks in the email body.
     * @param int $bytes The byte width.
     */
    public function setBodyWidth(int $bytes): void
    {
        $this->bodyWidth = $bytes;
    }

    /**
     * Checks if a string contains control characters.
     * @param string $str The string to check.
     * @return bool True if the string contains control characters, false otherwise.
     */
    private function checkControlCodeNothing(string $str): bool
    {
        return mb_ereg_match("/[[:cntrl:]]/", $str);
    }

    /**
     * Adds an attachment to the email.
     * @param string $fpath The path to the attachment file.
     */
    public function addAttachment(string $fpath): void
    {
        $this->attachments[] = $fpath;
    }

    /**
     * Sends the email.
     * @return bool True if the email is sent successfully, false otherwise.
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

    /**
     * Divides a string into lines with a maximum byte width.
     * @param string $str The string to divide.
     * @return string The divided string.
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

    /**
     * Unifies CRLF to LF.
     * @param string $str The string to unify.
     * @return string The unified string.
     */
    private function unifyCRLF(string $str): string
    {
        $strUnifiedLF = str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
        return str_replace("\n", "\r\n", $strUnifiedLF);
    }

    /**
     * Checks if a character is a space.
     * @param string $str The character to check.
     * @return bool True if the character is a space, false otherwise.
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

    /**
     * Checks if a character is a word element.
     * @param string $str The character to check.
     * @return bool True if the character is a word element, false otherwise.
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

    /**
     * Checks if a character is Japanese.
     * @param string $str The character to check.
     * @return bool True if the character is Japanese, false otherwise.
     */
    private function isJapanese(string $str): bool
    {
        $cCode = ord($str);
        if ($cCode >= 0x80) return True;
        return False;
    } // End of function isJapanese()

    /**
     * Checks if a character is a Japanese line top inhibit character.
     * @param string $str The character to check.
     * @return bool True if the character is a Japanese line top inhibit character, false otherwise.
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

    /**
     * Checks if a character is a Japanese line end inhibit character.
     * @param string $str The character to check.
     * @return bool True if the character is a Japanese line end inhibit character, false otherwise.
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

    /**
     * Encodes a string for use in email headers.
     * @param string $str The string to encode.
     * @param bool $isSeparateLine Whether to separate lines.
     * @return string The encoded string.
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
