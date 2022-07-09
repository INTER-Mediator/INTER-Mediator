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

namespace INTERMediator;

use DateInterval;
use DateTime;
use Exception;

class IMUtil
{
    public static function currentDTString($subSeconds = 0): string
    {
        $currentDT = new DateTime();
        try {
            if ($subSeconds >= 0) {
                $currentDT->sub(new DateInterval("PT" . intval($subSeconds) . "S"));
            } else {
                $currentDT->add(new DateInterval("PT" . -intval($subSeconds) . "S"));
            }
        } catch (Exception $e) {
        }
        return $currentDT->format('Y-m-d H:i:s');
    }

    public static function currentDTStringFMS($subSeconds = 0): string
    {
        $currentDT = new DateTime();
        try {
            if ($subSeconds >= 0) {
                $currentDT->sub(new DateInterval("PT" . intval($subSeconds) . "S"));
            } else {
                $currentDT->add(new DateInterval("PT" . -intval($subSeconds) . "S"));
            }
        } catch (Exception $e) {
        }
        return $currentDT->format('m/d/Y H:i:s');
    }

    public static function secondsFromNow($dtStr): int
    {
        $currentDT = new DateTime();
        try {
            $anotherDT = new DateTime($dtStr);
            return $currentDT->format("U") - $anotherDT->format("U");
        } catch (Exception $e) {
        }
        return 0;
    }

    public static function phpVersion($verStr = '')
    {
        $vString = explode('.', $verStr == '' ? phpversion() : $verStr);
        $vNum = 0;
        if (isset($vString[0])) {
            $vNum += intval($vString[0]);
        }
        if (isset($vString[1])) {
            $vNum += intval($vString[1]) / 10;
        }
        if (isset($vString[2])) {
            $vNum += intval(substr($vString[2], 0, 1)) / 100;
        }
        return $vNum;
    }

    public static function pathToINTERMediator(): string
    {
        return dirname(dirname(dirname(__FILE__)));
    }

    public static function getMIMEType($path): string
    {
        $type = "application/octet-stream";
        switch (strtolower(substr($path, strrpos($path, '.') + 1))) {
            case 'doc':
                $type = 'application/msword';
                break;
            case 'docx':
                $type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                break;
            case 'xls':
                $type = 'applicsation/vnd.ms-excel';
                break;
            case 'xlsx':
                $type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'ppt':
                $type = 'application/vnd.ms-powerpoint';
                break;
            case 'pptx':
                $type = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
                break;
            case 'jpeg':
            case 'jpg':
                $type = 'image/jpeg';
                break;
            case 'css':
                $type = 'text/css';
                break;
            case 'png':
                $type = 'image/png';
                break;
            case 'html':
                $type = 'text/html';
                break;
            case 'txt':
                $type = 'text/plain';
                break;
            case 'gif':
                $type = 'image/gif';
                break;
            case 'bmp':
                $type = 'image/bmp';
                break;
            case 'tiff':
            case 'tif':
                $type = 'image/tiff';
                break;
            case 'pdf':
                $type = 'application/pdf';
                break;
            case 'svg':
                $type = 'image/svg+xml';
                break;
        }
        return $type;
    }

    public static function combinePathComponents($ar): string
    {
        $path = "";
        $isFirstItem = true;
        foreach ($ar as $item) {
            $isSepTerminate = (substr($path, -1) == DIRECTORY_SEPARATOR);
            $isSepStart = (substr($item, 0, 1) == DIRECTORY_SEPARATOR);
            if (($isSepTerminate && !$isSepStart) || (!$isSepTerminate && $isSepStart)) {
                $path .= $item;
            } elseif ($isSepTerminate && $isSepStart) {
                $path .= substr($item, 1);
            } else {
                if (!$isFirstItem || !self::isPHPExecutingWindows()) {
                    $path .= DIRECTORY_SEPARATOR;
                }
                $path .= $item;
            }
            $isFirstItem = false;
        }
        return $path;
    }

    public static function isPHPExecutingWindows(): bool
    {
        $osName = php_uname("s");
        return $osName == "Windows NT";
    }

    public static function getServerUserHome(): string
    {
        if (IMUtil::isPHPExecutingWindows()) {
            $homeDir = getenv("USERPROFILE");
        } else {
            $homeDir = posix_getpwuid(posix_geteuid())["dir"];
        }
        return $homeDir;
    }

    public static function getServerUserName(): string
    {
        if (IMUtil::isPHPExecutingWindows()) {
            $homeDir = get_current_user();
        } else {
            // https://stackoverflow.com/questions/7771586/how-to-check-what-user-php-is-running-as
            // get_current_user doen't work on the ubuntu 18 of EC2. It returns the user logs in with ssh.
            $homeDir = posix_getpwuid(posix_geteuid())["name"];
        }
        return $homeDir;
    }

    public static function isPHPExecutingUNIX(): bool
    {
        $osName = php_uname("s");
        return $osName == "Linux" || $osName == "FreeBSD";
    }

    public static function removeNull($str)
    {
        return str_replace("\x00", '', $str ?? "");
    }

    // Message Class Detection
    public static function getMessageClassInstance(): ?Message\MessageStrings
    {
        $messageClass = null;
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            $clientLangArray = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            foreach ($clientLangArray as $oneLanguage) {
                $langCountry = explode(';', $oneLanguage);
                if (strlen($langCountry[0]) > 0) {
                    $clientLang = explode('-', $langCountry[0]);
                    if ($clientLang[0] === 'en') {
                        $messageClass = "INTERMediator\Message\MessageStrings";
                    } else {
                        $messageClass = "INTERMediator\Message\MessageStrings_{$clientLang[0]}";
                    }
                    try {
                        $messageClass = new $messageClass();
                        break;
                    } catch (Exception $ex) {
                        $messageClass = null;
                    }
                }
                $messageClass = null;
            }
        }
        if ($messageClass == null) {
            $messageClass = new Message\MessageStrings();
        }
        return $messageClass;
    }

// Thanks for http://q.hatena.ne.jp/1193396523
    public static function guessFileUploadError(): bool
    {
        $postMaxSize = self::return_bytes(ini_get('post_max_size'));
        if ($_SERVER['REQUEST_METHOD'] == 'POST'
            && $_SERVER['CONTENT_LENGTH'] > $postMaxSize
            && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') === 0
        ) {
            return true;
        }
        foreach ($_FILES as $fn => $fileInfo) {
            if (isset($fileInfo["error"])) {
                $errInfo = $fileInfo["error"];
                if (is_array($errInfo)) {   // JQuery File Upload Style
                    foreach ($errInfo as $index => $errCode) {
                        if ($errCode != UPLOAD_ERR_OK) {
                            return true;
                        }
                    }
                } else if ($fileInfo["error"] != UPLOAD_ERR_OK) {
                    return true;
                }
            }
        }
        return false;
    }

// Example in http://php.net/manual/ja/function.ini-get.php.
    public static function return_bytes($val): int
    {
        $val = trim($val);
        switch (strtolower($val[strlen($val) - 1])) {
            case 'g':
                $val = intval($val) * (1024 * 1024 * 1024);
                break;
            case 'm':
                $val = intval($val) * (1024 * 1024);
                break;
            case 'k':
                $val = intval($val) * 1024;
                break;
        }
        return $val;
    }

    public function protectCSRF(): bool
    {
        /*
         * Prevent CSRF Attack with XMLHttpRequest
         * http://d.hatena.ne.jp/hasegawayosuke/20130302/p1
         */
        $webServerName = Params::getParameterValue('webServerName', null);
        if ($webServerName === '' ||
            $webServerName === array() || $webServerName === array('')
        ) {
            $webServerName = NULL;
        }

        if (isset($_SERVER['HTTP_X_FROM'])) {
            $from = parse_url($_SERVER['HTTP_X_FROM']);
            $fromPort = isset($from['port']) ? ':' . $from['port'] : '';
            if ($fromPort === '' && $from['scheme'] === 'http') {
                $fromPort = ':80';
            } else if ($fromPort === '' && $from['scheme'] === 'https') {
                $fromPort = ':443';
            }
        }
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            $origin = parse_url($_SERVER['HTTP_ORIGIN']);
            $originPort = isset($origin['port']) ? ':' . $origin['port'] : '';
            if ($originPort === '' && $origin['scheme'] === 'http') {
                $originPort = ':80';
            } else if ($originPort === '' && $origin['scheme'] === 'https') {
                $originPort = ':443';
            }
        }

        if (isset($_SERVER['HTTP_HOST']) &&
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' &&
            isset($_SERVER['HTTP_X_FROM']) &&
            (!isset($_SERVER['HTTP_ORIGIN']) ||
                $from['scheme'] . '://' . $from['host'] . $fromPort ===
                $origin['scheme'] . '://' . $origin['host'] . $originPort)
        ) {
            $host = $_SERVER['HTTP_HOST'];
            if (is_null($webServerName)) {
                return TRUE;
            }
            if (is_array($webServerName)) {
                foreach ($webServerName as $name) {
                    if ($this->checkHost($host, $name) === TRUE) {
                        return TRUE;
                    }
                }
            } else {
                if ($this->checkHost($host, $webServerName) === TRUE) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    public function checkHost($host, $webServerName): bool
    {
        $host = strtolower($host);
        $webServerName = strtolower($webServerName);
        $length = strlen($webServerName);

        if ($length === 0) {
            return FALSE;
        }

        if ($host === $webServerName) {
            return TRUE;
        }

        if (isset($_SERVER['SERVER_ADDR']) &&
            $host === $_SERVER['SERVER_ADDR']
        ) {
            return TRUE;
        }

        if (!is_null($webServerName) && substr($host, -($length + 1)) === '.' . $webServerName &&
            strpos($webServerName, '.') !== FALSE && !preg_match('/^[0-9.]+$/', $webServerName)
        ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param array for testing only
     */
    public function outputSecurityHeaders($params = NULL)
    {
        if (is_null($params)) {
            [$xFrameOptions, $contentSecurityPolicy, $accessControlAllowOrigin]
                = Params::getParameterValue(['xFrameOptions', 'contentSecurityPolicy', 'accessControlAllowOrigin'], "");
        } else {
            $xFrameOptions = $params['xFrameOptions'];
            $contentSecurityPolicy = $params['contentSecurityPolicy'];
            $accessControlAllowOrigin = $params['accessControlAllowOrigin'];
        }
        $xFrameOptions = str_replace("\r", '', str_replace("\n", '', $xFrameOptions));
        $contentSecurityPolicy = str_replace("\r", '', str_replace("\n", '', $contentSecurityPolicy));
        $accessControlAllowOrigin = str_replace("\r", '', str_replace("\n", '', $accessControlAllowOrigin));

        if (is_null($xFrameOptions) || empty($xFrameOptions)) {
            $xFrameOptions = 'SAMEORIGIN';
        }
        if ($xFrameOptions !== '') {
            header("X-Frame-Options: {$xFrameOptions}");
        }
        if (is_null($contentSecurityPolicy) || empty($contentSecurityPolicy)) {
            $contentSecurityPolicy = '';
        }
        if ($contentSecurityPolicy !== '') {
            header("Content-Security-Policy: {$contentSecurityPolicy}");
        }
        if ($accessControlAllowOrigin !== '') {
            header("Access-Control-Allow-Origin: {$accessControlAllowOrigin}");
        }
        header('X-XSS-Protection: 1; mode=block');
    }


    /**
     * Convert strings to JavaScript friendly strings.
     * Contributed by Atsushi Matsuo at Jan 17, 2010
     * @return string strings for JavaScript
     */
    public static function valueForJSInsert($str): string
    {
        if (is_null($str)) {
            return "";
        }
        return str_replace("'", "\\'",
            str_replace('"', '\\"',
                str_replace("/", "\\/",
                    str_replace(">", "\\x3e",
                        str_replace("<", "\\x3c",
                            str_replace("\n", "\\n",
                                str_replace("\r", "\\r",
                                    str_replace("\xe2\x80\xa8", "\\n",      // U+2028
                                        str_replace("\xe2\x80\xa9", "\\n",  // U+2029
                                            str_replace("\\", "\\\\", $str ?? ""))))))))));
    }

    /**
     * Create JavaScript source from array
     * @param array ar parameter array
     * @param string prefix strings for the prefix for key
     * @return string JavaScript source
     */
    public static function arrayToJS($ar, $prefix = ""): string
    {
        if (is_array($ar)) {
            $items = array();
            foreach ($ar as $key => $value) {
                $items[] = IMUtil::arrayToJS($value, $key);
            }
            $currentKey = (string)$prefix;
            if ($currentKey == '')
                $returnStr = "{" . implode(',', $items) . '}';
            else
                $returnStr = "'{$currentKey}':{" . implode(',', $items) . '}';
        } else {
            $currentKey = (string)$prefix;
            if ($currentKey == '') {
                $returnStr = "'" . IMUtil::valueForJSInsert($ar) . "'";
            } else {
                $returnStr = "'{$prefix}':'" . IMUtil::valueForJSInsert($ar) . "'";
            }
        }
        return $returnStr;
    }

    /**
     * Create JavaScript source from array
     * @param array ar parameter array
     * @param string prefix strings for the prefix for key
     * @param array exarray array containing excluding keys
     * @return string JavaScript source
     */
    public static function arrayToJSExcluding($ar, $prefix, $exarray): string
    {
        $returnStr = '';

        if (is_array($ar)) {
            $items = array();
            foreach ($ar as $key => $value) {
                $items[] = IMUtil::arrayToJSExcluding($value, $key, $exarray);
            }
            $currentKey = (string)$prefix;
            foreach ($items as $item) {
                if (!in_array($currentKey, $exarray) && $item != '') {
                    if ($returnStr == '') {
                        $returnStr .= $item;
                    } else {
                        $returnStr .= ',' . $item;
                    }
                }
            }
            if ($currentKey == '') {
                $returnStr = '{' . $returnStr . '}';
            } else {
                $returnStr = "'{$currentKey}':{" . $returnStr . '}';
            }
        } else {
            $currentKey = (string)$prefix;
            if ($currentKey == '') {
                $returnStr = "'" . IMUtil::valueForJSInsert($ar) . "'";
            } else if (!in_array($currentKey, $exarray)) {
                $returnStr = "'{$prefix}':'" . IMUtil::valueForJSInsert($ar) . "'";
            }
        }
        return $returnStr;
    }

    public static function randomString($digit): string
    {
        $resultStr = '';
        for ($i = 0; $i < $digit; $i++) {
            try{
                $code = random_int(33, 126);
            } catch (\Exception $ex) {
                $code = rand(33, 126);
            }
            $resultStr .= chr($code);
        }
        return $resultStr;
    }

    /**
     * @param $prefix
     * @return string
     */
    public static function generateClientId($prefix, $passwordHash)
    {
        if ($passwordHash == "1") {
            return sha1(uniqid($prefix, true));
        }
        return hash("sha256", uniqid($prefix, true));
    }

    /**
     * @return string
     */
    public static function generateChallenge()
    {
        $str = '';
        for ($i = 0; $i < 24; $i++) {
            $n = rand(1, 255);
            $str .= ($n < 16 ? '0' : '') . dechex($n);
        }
        return $str;
    }

    /**
     * @return string
     */
    public static function generateSalt()
    {
        $str = '';
        for ($i = 0; $i < 4; $i++) {
            $n = rand(33, 126); // They should be an ASCII character for JS SHA1 lib.
            $str .= chr($n);
        }
        return $str;
    }

    public static function convertHashedPassword($pw, $passwordHash, $alwaysGenSHA2, $salt = false)
    {
        if ($salt === false) {
            $salt = IMUtil::generateSalt();
        }
        if ($passwordHash == "1" && !$alwaysGenSHA2) {
            return sha1($pw . $salt) . bin2hex($salt);
        }
        $value = $pw . $salt;
        for ($i = 0; $i < 4999; $i++) {
            $value = hash("sha256", $value, true);
        }
        return hash("sha256", $value, false) . bin2hex($salt);
    }

    public static function generateCredential($digit, $passwordHash, $alwaysGenSHA2)
    {
        $password = '';
        for ($i = 0; $i < $digit; $i++) {
            $password .= chr(rand(32, 127));
        }
        return IMUtil::convertHashedPassword($password, $passwordHash, $alwaysGenSHA2);
    }

    public static function generateRandomPW()
    {
        $str = '';
        try{
            $limit = random_int(15, 20);
        } catch (\Exception $ex) {
            $limit = rand(15, 20);
        }
        for ($i = 0; $i < $limit; $i++) {
            try{
                $n = random_int(33, 126); // They should be an ASCII character for JS SHA1 lib.
            } catch (\Exception $ex) {
                $n = rand(33, 126); // They should be an ASCII character for JS SHA1 lib.
            }
            $str .= chr($n);
        }
        return $str;
    }

}
