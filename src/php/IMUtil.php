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
 * Utility class for various helper functions used in INTER-Mediator.
 */
class IMUtil
{
    /**
     * Returns the current date and time string in 'Y-m-d H:i:s' format.
     *
     * @param int $subSeconds Number of seconds to subtract (or add if negative).
     * @return string Current date and time as string.
     */
    public static function currentDTString(int $subSeconds = 0): string
    {
        $currentDT = new DateTime();
        try {
            if ($subSeconds >= 0) {
                $currentDT->sub(new DateInterval("PT" . $subSeconds . "S"));
            } else {
                $currentDT->add(new DateInterval("PT" . -$subSeconds . "S"));
            }
        } catch (Exception $e) {
        }
        return $currentDT->format('Y-m-d H:i:s');
    }

    /**
     * Returns the current date and time string in FileMaker format ('m/d/Y H:i:s').
     *
     * @param int $subSeconds Number of seconds to subtract (or add if negative).
     * @return string Current date and time as string in FileMaker format.
     */
    public static function currentDTStringFMS(int $subSeconds = 0): string
    {
        $currentDT = new DateTime();
        try {
            if ($subSeconds >= 0) {
                $currentDT->sub(new DateInterval("PT" . $subSeconds . "S"));
            } else {
                $currentDT->add(new DateInterval("PT" . -$subSeconds . "S"));
            }
        } catch (Exception $e) {
        }
        return $currentDT->format('m/d/Y H:i:s');
    }

    /**
     * Calculates the difference in seconds from the given date/time string to now.
     *
     * @param string $dtStr Date/time string.
     * @return int Seconds from now.
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
     * Returns the PHP version as a float or int.
     *
     * @param string $verStr Optional version string to parse. If empty, uses current PHP version.
     * @return int|float PHP version as number.
     */
    public static function phpVersion(string $verStr = ''): int|float
    {
        $vString = explode('.', $verStr == '' ? phpversion() : $verStr);
        $vNum = intval($vString[0]);
        if (isset($vString[1])) {
            $vNum += intval($vString[1]) / 10;
        }
        if (isset($vString[2])) {
            $vNum += intval(substr($vString[2], 0, 1)) / 100;
        }
        return $vNum;
    }

    /**
     * Returns the path to the INTER-Mediator root directory.
     *
     * @return string Path to INTER-Mediator root.
     */
    public static function pathToINTERMediator(): string
    {
        return dirname(__FILE__, 3);
    }

    /**
     * Returns the MIME type for the given file path.
     *
     * @param $path string File path.
     * @return string MIME type.
     */
    public static function getMIMEType(string $path): string
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
     * Combines an array of path components into a single path string.
     *
     * @param $ar array Array of path components.
     * @return string Combined path.
     */
    public static function combinePathComponents(array $ar): string
    {
        $path = "";
        $isFirstItem = true;
        foreach ($ar as $item) {
            $isSepTerminate = (substr($path, -1) == DIRECTORY_SEPARATOR);
            $isSepStart = (substr($item, 0, 1) == DIRECTORY_SEPARATOR);
            if (($isSepTerminate && !$isSepStart) || (!$isSepTerminate && $isSepStart)) {
                $path .= $item;
            } elseif ($isSepTerminate) {
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
     * Checks if PHP is running on Windows.
     *
     * @return bool True if running on Windows, false otherwise.
     */
    public static function isPHPExecutingWindows(): bool
    {
        $osName = php_uname("s");
        return $osName == "Windows NT";
    }

    /**
     * Returns the home directory of the server user.
     *
     * @return string Home directory path.
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
     * Returns the username of the server user.
     *
     * @return string Server username.
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
     * Checks if PHP is running on a UNIX-like OS (Linux or FreeBSD).
     *
     * @return bool True if running on UNIX, false otherwise.
     */
    public static function isPHPExecutingUNIX(): bool
    {
        $osName = php_uname("s");
        return $osName == "Linux" || $osName == "FreeBSD";
    }

    /**
     * Removes null bytes from a string.
     *
     * @param $str string Input string.
     * @return array|string String with null bytes removed.
     */
    public static function removeNull(string $str): array|string
    {
        return str_replace("\x00", '', $str);
    }

    // Message Class Detection

    /**
     * Returns an instance of the appropriate MessageStrings class based on client language.
     *
     * @return Message\MessageStrings|null MessageStrings instance or null.
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
     * Checks for file upload errors based on POST size and $_FILES.
     *
     * @return bool True if there was an upload error, false otherwise.
     */
    public static function guessFileUploadError(): bool
    {
        $postMaxSize = self::return_bytes(ini_get('post_max_size'));
        if ($_SERVER['REQUEST_METHOD'] == 'POST'
            && $_SERVER['CONTENT_LENGTH'] > $postMaxSize
            && str_starts_with($_SERVER['CONTENT_TYPE'], 'multipart/form-data')
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
     * Converts a PHP ini size string (e.g., '2M') to bytes.
     *
     * @param $val mixed PHP ini size string.
     * @return int Size in bytes.
     */
    public static function return_bytes(mixed $val): int
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
     * Protects against CSRF attacks for XMLHttpRequest or fetch requests.
     *
     * @return bool True if CSRF check passes, false otherwise.
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
            ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ||
                $_SERVER['HTTP_X_REQUESTED_WITH'] === 'fetch') &&
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
     * Checks if the given host matches the web server name.
     *
     * @param string $host Host to check.
     * @param string $webServerName Expected web server name.
     * @return bool True if host matches, false otherwise.
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
            str_contains($webServerName, '.') && !preg_match('/^[0-9.]+$/', $webServerName)
        ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Outputs security-related HTTP headers.
     *
     * @param array|null $params Optional parameters for headers (for testing).
     * @return void
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
        header("X-Frame-Options: " . (empty($xFrameOptions) ? "SAMEORIGIN" : $xFrameOptions));
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
     * Converts a string to a JavaScript-friendly string.
     *
     * @param string|null $str Input string.
     * @return string JavaScript-safe string.
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
     * Converts an array to a JavaScript object string.
     *
     * @param array $ar Input array.
     * @param string $prefix Prefix for keys.
     * @return string JavaScript object as string.
     */
    public static function arrayToJS(array $ar, string $prefix = ""): string
    {
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
     * Converts a string to a JavaScript key-value string.
     *
     * @param string $ar Input string.
     * @param string $prefix Prefix for the key.
     * @return string JavaScript key-value string.
     */
    public static function stringToJS(string $ar, string $prefix = ""): string
    {
        $currentKey = $prefix;
        if ($currentKey == '') {
            $returnStr = "'" . IMUtil::valueForJSInsert($ar) . "'";
        } else {
            $returnStr = "'{$prefix}':'" . IMUtil::valueForJSInsert($ar) . "'";
        }
        return $returnStr;
    }

    /**
     * Converts an array to a JavaScript object string, excluding specified keys.
     *
     * @param array $ar Input array.
     * @param string $prefix Prefix for keys.
     * @param array|null $exarray Keys to exclude.
     * @return string JavaScript object as string.
     */
    public static function arrayToJSExcluding(array $ar, string $prefix, ?array $exarray): string
    {
        $returnStr = '';
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = is_array($value) ? IMUtil::arrayToJSExcluding($value, $key, $exarray)
                : IMUtil::stringToJSExcluding($value, $key, $exarray);
        }
        $currentKey = $prefix;
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
     * Converts a string to a JavaScript key-value string, excluding specified keys.
     *
     * @param string $ar Input string.
     * @param string $prefix Prefix for the key.
     * @param array|null $exarray Keys to exclude.
     * @return string JavaScript key-value string.
     */
    public static function stringToJSExcluding(string $ar, string $prefix, ?array $exarray): string
    {
        $returnStr = '';
        $currentKey = $prefix;
        if ($currentKey == '') {
            $returnStr = "'" . IMUtil::valueForJSInsert($ar) . "'";
        } else if (!in_array($currentKey, $exarray)) {
            $returnStr = "'{$prefix}':'" . IMUtil::valueForJSInsert($ar) . "'";
        }
        return $returnStr;
    }

    /**
     * Generates a random digit string of specified length.
     *
     * @param int $digit Number of digits.
     * @return string Random digit string.
     */
    public static function randomDigit(int $digit): string
    {
        $resultStr = '';
        for ($i = 0; $i < $digit; $i++) {
            try {
                $code = random_int(0, 9);
            } catch (Exception $ex) {
                $code = rand(0, 10);
            }
            $resultStr .= $code;
        }
        return $resultStr;
    }

    /**
     * Generates a random ASCII string of specified length.
     *
     * @param int $digit Length of the string.
     * @return string Random ASCII string.
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
     * Generates a challenge string with allowed characters.
     *
     * @param int $digit Length of the string.
     * @return string Challenge string.
     */
    public static function challengeString(int $digit): string
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-.~";
        $len = strlen($chars);
        $resultStr = '';
        for ($i = 0; $i < $digit; $i++) {
            try {
                $code = random_int(0, $len - 1);
            } catch (Exception $ex) {
                $code = rand(0, $len - 1);
            }
            $resultStr .= $chars[$code];
        }
        return $resultStr;
    }

    /**
     * Generates a client ID using a prefix and password hash.
     *
     * @param string $prefix Prefix for client ID.
     * @param string $passwordHash Password hash type.
     * @return string Client ID.
     */
    public static function generateClientId(string $prefix, string $passwordHash): string
    {
        if ($passwordHash == "1") {
            return sha1(uniqid($prefix, true));
        }
        return hash("sha256", uniqid($prefix, true));
    }

    /**
     * Generates a random challenge string (hexadecimal).
     *
     * @return string Challenge string.
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
     * Generates a random salt for password hashing.
     *
     * @return string Salt string.
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
     * Generates a random password string of specified length.
     * The password consists of alphanumeric characters and ends with a punctuation character.
     *
     * @param int $digit Length of the password to generate.
     * @return string Generated password.
     */
    public static function generatePassword(int $digit): string
    {
        $seed = "2345678abcdefghijkmnoprstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ";
        $seedPunctuation = "#$%&";
        $str = '';
        for ($i = 0; $i < $digit - 1; $i++) {
            $n = rand(0, strlen($seed) - 1);
            $str .= substr($seed, $n, 1);
        }
        $n = rand(0, strlen($seedPunctuation) - 1);
        $str .= substr($seedPunctuation, $n, 1);
        return $str;
    }

    /**
     * Converts a password to a hashed password string.
     *
     * @param string $pw Plain password.
     * @param string $passwordHash Password hash type.
     * @param bool $alwaysGenSHA2 Whether to always use SHA256.
     * @param string $salt Optional salt.
     * @return string Hashed password.
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
     * Generates a credential using a random password.
     *
     * @param int $digit Length of the password.
     * @param string $passwordHash Password hash type.
     * @param bool $alwaysGenSHA2 Whether to always use SHA256.
     * @return string Credential string.
     */
    public static function generateCredentialWithRandomPW(int $digit, string $passwordHash, bool $alwaysGenSHA2): string
    {
        $password = '';
        for ($i = 0; $i < $digit; $i++) {
            $password .= chr(rand(32, 127));
        }
        return IMUtil::convertHashedPassword($password, $passwordHash, $alwaysGenSHA2);
    }

    /**
     * Generates a random password string.
     *
     * @return string Random password.
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
     * Returns the class name for a visitor based on access string.
     *
     * @param string $access Access type.
     * @return string Visitor class name.
     */
    public static function getVisitorClassName(string $access): string
    {
        return "INTERMediator\\DB\\Support\\ProxyVisitors\\"
            . strtoupper(substr($access, 0, 1)) . strtolower(substr($access, 1))
            . "Visitor";
    }

    /**
     * Returns the relative path from one path to another.
     *
     * @param string $fromPath Source path.
     * @param string $toPath Destination path.
     * @return string|null Relative path or null if invalid.
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
     * Checks if a path is inside a directory.
     *
     * @param string|null $checkPath Path to check.
     * @param string|null $dir Directory.
     * @return bool True if inside, false otherwise.
     */
    public static function isInsideOf(?string $checkPath, ?string $dir): bool
    {
        if (!$checkPath || !$dir) { // Both parameters have not to falsy.
            return false;
        }
        if (strlen($dir) > strlen($checkPath)) { // Apparently outside $dir.
            return false;
        }
        if (str_starts_with($checkPath, $dir)) {
            return true;
        }
        return false;
    }

    /**
     * Loads and parses YAML definition file content.
     *
     * @return array Parsed YAML content and file path.
     * @throws Exception If the YAML file does not exist or is outside permitted paths.
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
     * Parses a YAML string and returns the definition array.
     *
     * @param string $yaml YAML string.
     * @return array|null Parsed array or null on failure.
     */
    public static function getDefinitionFromYAML(string $yaml): ?array
    {
        return Yaml::parse($yaml);
    }

    /**
     * Checks if the application is running as a web app (not CLI).
     *
     * @return bool True if running as a web app, false otherwise.
     */
    public static function isRunAsWebApp(): bool
    {
        if (php_sapi_name() == 'cli') {
            return false;
        }
        return true;
    }

    /**
     * Gets a value from a profile file if available, otherwise returns the input string.
     *
     * @param string|null $str Input string or profile descriptor.
     * @return string|null Value from profile or original string/null.
     */
    public static function getFromProfileIfAvailable(?string $str): string|null
    {
        if (is_null($str)) {
            return null;
        }
        $comp = array_map(function ($elm) {
            return strtolower(trim($elm));
        }, explode('|', $str));
        if (count($comp) <= 3 || $comp[0] !== "profile") { // It's not profile description.
            return $str;
        }
        $category = $comp[1];
        $section = $comp[2];
        $key = $comp[3];

        $path = [
            Params::getParameterValue("profileRoot", null),
            posix_getpwuid(posix_geteuid())["dir"],
            posix_getpwnam(get_current_user())["dir"],
        ];
        $suffix = "credentials";
        if ($category === "aws") {
            $suffix = "/.aws/{$suffix}";
        } else if ($category === "im") {
            $suffix = "/.im/{$suffix}";
        }
        $targetFile = "";
        foreach ($path as $pathItem) {
            if (!is_null($pathItem)) {
                $candidatePath = "{$pathItem}{$suffix}";
                if (file_exists($candidatePath)) {
                    $targetFile = $candidatePath;
                    break;
                }
            }
        }
        if ($targetFile === "") {
            return $str;
        }
        $fileContents = explode("\n",
            str_replace("\r", "\n", file_get_contents($targetFile)));
        $targetValue = "";
        $sectionCandidate = strtolower("[{$section}]");
        $inTargetSection = false;
        foreach ($fileContents as $line) {
            if (preg_match("/^\[.+]$/", $line)) {
                $inTargetSection = false;
            }
            if (strtolower($line) === $sectionCandidate) {
                $inTargetSection = true;
            } else if ($inTargetSection) {
                $lineComps = array_values(array_filter(
                    explode(" ", str_replace("\t", " ", $line)),
                    function ($elm) {
                        return strlen($elm) > 0;
                    }));
                if (count($lineComps) == 3
                    && strtolower($lineComps[0]) === strtolower($key)
                    && $lineComps[1] === "=") {
                    $targetValue = $lineComps[2];
                    break;
                }
            }
        }
        if ($targetValue === "") {
            return $str;
        }
        return $targetValue;
    }
}
