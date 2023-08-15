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
use Symfony\Component\Yaml\Yaml;

/**
 *
 */
class IMUtil
{
    /**
     * @param int $subSeconds
     * @return string
     */
    public static function currentDTString(int $subSeconds = 0): string
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

    /**
     * @param int $subSeconds
     * @return string
     */
    public static function currentDTStringFMS(int $subSeconds = 0): string
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

    /**
     * @param string $dtStr
     * @return int
     */
    public static function secondsFromNow(string $dtStr): int
    {
        $currentDT = new DateTime();
        try {
            $anotherDT = new DateTime($dtStr);
            return $currentDT->format("U") - $anotherDT->format("U");
        } catch (Exception $e) {
        }
        return 0;
    }

    /**
     * @param string $verStr
     * @return float|int
     */
    public static function phpVersion(string $verStr = '')
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

    /**
     * @return string
     */
    public static function pathToINTERMediator(): string
    {
        return dirname(__FILE__, 3);
    }

    /**
     * @param $path
     * @return string
     */
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

    /**
     * @param $ar
     * @return string
     */
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

    /**
     * @return bool
     */
    public static function isPHPExecutingWindows(): bool
    {
        $osName = php_uname("s");
        return $osName == "Windows NT";
    }

    /**
     * @return string
     */
    public static function getServerUserHome(): string
    {
        if (IMUtil::isPHPExecutingWindows()) {
            $homeDir = getenv("USERPROFILE");
        } else {
            $homeDir = posix_getpwuid(posix_geteuid())["dir"];
        }
        return $homeDir;
    }

    /**
     * @return string
     */
    public static function getServerUserName(): string
    {
        if (IMUtil::isPHPExecutingWindows()) {
            $homeDir = get_current_user();
        } else {
            // https://stackoverflow.com/questions/7771586/how-to-check-what-user-php-is-running-as
            // get_current_user doesn't work on the ubuntu 18 of EC2. It returns the user logs in with ssh.
            $homeDir = posix_getpwuid(posix_geteuid())["name"];
        }
        return $homeDir;
    }

    /**
     * @return bool
     */
    public static function isPHPExecutingUNIX(): bool
    {
        $osName = php_uname("s");
        return $osName == "Linux" || $osName == "FreeBSD";
    }

    /**
     * @param $str
     * @return array|string|string[]
     */
    public static function removeNull($str)
    {
        return str_replace("\x00", '', $str ?? "");
    }

    // Message Class Detection

    /**
     * @return Message\MessageStrings|null
     */
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

    /**
     * @return bool
     */
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

    /**
     * @param $val
     * @return int
     */
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

    /**
     * @return bool
     */
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

        $from = '';
        $fromPort = '';
        $origin = '';
        $originPort = '';
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

    /**
     * @param string $host
     * @param string $webServerName
     * @return bool
     */
    public function checkHost(string $host, string $webServerName): bool
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

        if (substr($host, -($length + 1)) === '.' . $webServerName &&
            strpos($webServerName, '.') !== FALSE && !preg_match('/^[0-9.]+$/', $webServerName)
        ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param array|null $params for testing only
     */
    public function outputSecurityHeaders(?array $params = NULL): void
    {
        $xFrameOptions = str_replace("\r", '', str_replace("\n", '',
            is_null($params)
                ? Params::getParameterValue('xFrameOptions', '')
                : $params['xFrameOptions']));
        $contentSecurityPolicy = str_replace("\r", '', str_replace("\n", '',
            is_null($params)
                ? Params::getParameterValue('contentSecurityPolicy', '')
                : $params['contentSecurityPolicy']));
        $accessControlAllowOrigin = str_replace("\r", '', str_replace("\n", '',
            is_null($params)
                ? Params::getParameterValue('accessControlAllowOrigin', '')
                : $params['accessControlAllowOrigin']));

        if (empty($xFrameOptions)) {
            $xFrameOptions = 'SAMEORIGIN';
        }
        if ($xFrameOptions !== '') {
            header("X-Frame-Options: {$xFrameOptions}");
        }
        if (empty($contentSecurityPolicy)) {
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
    public static function valueForJSInsert(?string $str): string
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
                                            str_replace("\\", "\\\\", $str))))))))));
    }

    /**
     * Create JavaScript source from array
     * @param array $ar ar parameter array
     * @param string $prefix prefix strings for the prefix for key
     * @return string JavaScript source
     */
    public static function arrayToJS(array $ar, string $prefix = ""): string
    {
        $returnStr = '';
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = is_array($value) ? IMUtil::arrayToJS($value, $key) : IMUtil::stringToJS($value, $key);
        }
        $currentKey = $prefix;
        if ($currentKey == '')
            $returnStr = "{" . implode(',', $items) . '}';
        else
            $returnStr = "'{$currentKey}':{" . implode(',', $items) . '}';
        return $returnStr;
    }

    /**
     * @param string $ar
     * @param string $prefix
     * @return string
     */
    public static function stringToJS(string $ar, string $prefix = ""): string
    {
        $returnStr = '';
        $currentKey = $prefix;
        if ($currentKey == '') {
            $returnStr = "'" . IMUtil::valueForJSInsert($ar) . "'";
        } else {
            $returnStr = "'{$prefix}':'" . IMUtil::valueForJSInsert($ar) . "'";
        }
        return $returnStr;
    }

    /**
     * Create JavaScript source from array
     * @param array $ar array
     * @param string $prefix prefix strings for the prefix for key
     * @param array|null $exarray exarray array containing excluding keys
     * @return string JavaScript source
     */
    public static function arrayToJSExcluding(array $ar, string $prefix, ?array $exarray): string
    {
        $returnStr = '';
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = is_array($value) ? IMUtil::arrayToJSExcluding($value, $key, $exarray)
                : IMUtil::stringToJSExcluding($value, $key, $exarray);
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
        return $returnStr;
    }

    /**
     * @param string $ar
     * @param string $prefix
     * @param array|null $exarray
     * @return string
     */
    public static function stringToJSExcluding(string $ar, string $prefix, ?array $exarray): string
    {
        $returnStr = '';
        $currentKey = (string)$prefix;
        if ($currentKey == '') {
            $returnStr = "'" . IMUtil::valueForJSInsert($ar) . "'";
        } else if (!in_array($currentKey, $exarray)) {
            $returnStr = "'{$prefix}':'" . IMUtil::valueForJSInsert($ar) . "'";
        }
        return $returnStr;
    }

    /**
     * @param int $digit
     * @return string
     */
    public static function randomString(int $digit): string
    {
        $resultStr = '';
        for ($i = 0; $i < $digit; $i++) {
            try {
                $code = random_int(33, 126);
            } catch (Exception $ex) {
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
    public static function generateClientId(string $prefix, string $passwordHash): string
    {
        if ($passwordHash == "1") {
            return sha1(uniqid($prefix, true));
        }
        return hash("sha256", uniqid($prefix, true));
    }

    /**
     * @return string
     */
    public static function generateChallenge(): string
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
    public static function generateSalt(): string
    {
        $str = '';
        for ($i = 0; $i < 4; $i++) {
            $n = rand(33, 126); // They should be an ASCII character for JS SHA1 lib.
            $str .= chr($n);
        }
        return $str;
    }

    /**
     * @param string $pw
     * @param string $passwordHash
     * @param bool $alwaysGenSHA2
     * @param string $salt
     * @return string
     */
    public static function convertHashedPassword(string $pw, string $passwordHash, bool $alwaysGenSHA2, string $salt = ''): string
    {
        if ($salt === '') {
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

    /**
     * @param int $digit
     * @param string $passwordHash
     * @param bool $alwaysGenSHA2
     * @return string
     */
    public static function generateCredential(int $digit, string $passwordHash, bool $alwaysGenSHA2): string
    {
        $password = '';
        for ($i = 0; $i < $digit; $i++) {
            $password .= chr(rand(32, 127));
        }
        return IMUtil::convertHashedPassword($password, $passwordHash, $alwaysGenSHA2);
    }

    /**
     * @return string
     */
    public static function generateRandomPW(): string
    {
        $str = '';
        try {
            $limit = random_int(15, 20);
        } catch (Exception $ex) {
            $limit = rand(15, 20);
        }
        for ($i = 0; $i < $limit; $i++) {
            try {
                $n = random_int(33, 126); // They should be an ASCII character for JS SHA1 lib.
            } catch (Exception $ex) {
                $n = rand(33, 126); // They should be an ASCII character for JS SHA1 lib.
            }
            $str .= chr($n);
        }
        return $str;
    }

    /**
     * @param string $fromPath
     * @param string $toPath
     * @return string|null
     */
    public static function relativePath(string $fromPath, string $toPath): ?string
    {
        if (!$fromPath || !$toPath) {
            return null;
        }
        $from = explode("/", $fromPath);
        $to = explode("/", $toPath);
        $commonRoot = 0;
        for ($i = 0; $i < min(count($from), count($to)); $i += 1) {
            if (!isset($from[$i]) || !isset($to[$i]) || ($from[$i] != $to[$i])) {
                $commonRoot = $i;
                break;
            }
        }
        $path = '';
        for ($index = count($from) - 2; $index >= $commonRoot; $index -= 1) {
            $path = "../{$path}";
        }
        for ($index = $commonRoot; $index < count($to); $index += 1) {
            $separator = (strlen($path) == 0) ? '' : '/';
            $path = "{$path}{$separator}{$to[$index]}";
        }
        return str_replace('//', '/', $path);
    }

    /**
     * @param string|null $checkPath
     * @param string|null $dir
     * @return bool
     */
    public static function isInsideOf(?string $checkPath, ?string $dir): bool
    {
        if (!$checkPath || !$dir) { // Both parameter have not to falsy.
            return false;
        }
        if (strlen($dir) > strlen($checkPath)) { // Apparently outside $dir.
            return false;
        }
        if (substr($checkPath, 0, strlen($dir)) == $dir) {
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function getYAMLDefContent(): array
    {
        $defPoolPath = Params::getParameterValue('yamlDefFilePool', false);
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        $ref = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_PATH);
        $possibleDirs = [$docRoot, $docRoot . dirname($ref), $defPoolPath,];
        if (isset($_GET['deffile'])) { // The yaml file path is set on the deffile parameter
            $filePath = $_GET['deffile'];
            $possibleDirs[] = $docRoot;
            $possibleExts = [''];
        } else { // The yaml file has the same name of the page file
            $filePath = basename($ref);
            $dotPos = strrpos($filePath, '.');
            if ($dotPos !== false) {
                $filePath = substr($filePath, 0, $dotPos);
            }
            $possibleExts = ['.yml', '.yaml', '.json'];
        }
        $yamlFilePath = '';
        $searchResult = [];
        foreach ($possibleDirs as $dir) {
            if ($dir) {
                foreach ($possibleExts as $extension) {
                    $yamlFilePath = $dir . '/' . $filePath . $extension;
                    if (file_exists($yamlFilePath)) {
                        break 2;
                    }
                    $searchResult[] = $yamlFilePath;
                }
            }
        }
        if (!file_exists($yamlFilePath)) {
            throw new Exception(
                "The yaml format definition file does not exist on following paths:\\n"
                . implode("\\n", $searchResult)
            );
        }
        $realPath = realpath($yamlFilePath);
        if (!(IMUtil::isInsideOf($realPath, $docRoot) || ($defPoolPath && IMUtil::isInsideOf($realPath, $defPoolPath)))) {
            throw new Exception("The yaml file exists outside of any permitted paths: {$realPath}");
        }
        return [Yaml::parse(file_get_contents($realPath)), $yamlFilePath];
        // OMG! Yaml parser can parse JSON data!! Really??
    }

    /**
     * @param string $yaml
     * @return array|null
     */
    public static function getDefinitionFromYAML(string $yaml): ?array
    {
        return Yaml::parse($yaml);
    }
}
