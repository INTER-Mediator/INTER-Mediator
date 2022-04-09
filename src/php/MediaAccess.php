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

use Aws\Credentials\Credentials;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use phpseclib\Crypt\RSA;

class MediaAccess
{
    private $disposition = "inline";    // default disposition.
    private $targetKeyField;    // set with the analyzeTarget method.
    private $targetKeyValue;  // set with the analyzeTarget method.
    private $targetContextName = null;  // set with the analyzeTarget method.
    private $cookieUser;    // set with the checkAuthentication method.
    private $accessLogLevel = 0;
    private $outputMessage = ['apology' => 'Logging messages are not implemented so far.'];

    public function getResultForLog(): array
    {
        if ($this->accessLogLevel < 1) {
            return [];
        }
        return $this->outputMessage;
    }

    public function asAttachment()
    {
        $this->disposition = "attachment";
    }

    private function isPossibleSchema($file): bool
    {
        $schema = ["https:", "http:", "class:", "s3:"];
        foreach ($schema as $scheme) {
            if (strpos($file, $scheme) === 0) {
                return true;
            }
        }
        return false;
    }

    public function processing($dbProxyInstance, $options, $file)
    {
        $contextRecord = null;
        try {
            // It the $file ('media'parameter) isn't specified, it doesn't respond an error.
            if (strlen($file) === 0) {
                $erMessage = "[INTER-Mediator] The value of the 'media' key in url isn't specified.";
                echo $erMessage;
                error_log($erMessage);
                $this->exitAsError(200);
            }
            // If the media parameter is an URL, the variable isURL will be set to true.
            $isURL = $this->isPossibleSchema($file);
            if (!$isURL && !isset($options['media-root-dir'])) {
                $erMessage = "[INTER-Mediator] This MediaAccess operation requires the option value of the 'media-root-dir' key.";
                echo $erMessage;
                error_log($erMessage);
                $this->exitAsError(200); // The file accessing requires the media-root-dir keyed value.
            }
            /*
             * If the FileMaker's object field is storing a PDF, the $file could be "http://server:16000/...
             * style URL. In case of an image, $file is just the path info as like above.
             */
            list($file, $isURL) = $this->checkForFileMakerMedia($dbProxyInstance, $options, $file, $isURL);

            // Set the target variable
            $file = IMUtil::removeNull($file);
            if (strpos($file, '../') !== false) { // Stop for security reason.
                $erMessage = "[INTER-Mediator] The '..' path component isn't permitted.";
                echo $erMessage;
                error_log($erMessage);
                $this->exitAsError(200);
            }
            $target = $isURL ? $file : "{$options['media-root-dir']}/{$file}";
            // Analyze the target variable if it contains context name and key parameters.
            $analyzeResult = $this->analyzeTarget($target);  // Check the context name and key fields.
            if ($analyzeResult) {
                $dbProxyInstance->dbSettings->setDataSourceName($this->targetContextName);
//                $context = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
            }
            // Check the authentication and authorization
            //if (isset($options['media-context'])) { // media-context is removed. This comment is for my memo.
            $this->cookieUser = null;
            $authResult = $this->checkAuthentication($dbProxyInstance, $options);
            // Authentication error or authorization error rise an exception within checkAuthentication
            if ($analyzeResult) { // Get the relevant relation to the context.
                switch ($authResult) {
                    case  'field_user':
                    case  'field_group':
                        $authInfoField = $dbProxyInstance->dbClass->authHandler->getFieldForAuthorization("load");
                        $tableName = $dbProxyInstance->dbSettings->getEntityForRetrieve();
                        $contextRecord = $dbProxyInstance->dbClass->authHandler->authSupportCheckMediaPrivilege(
                            $tableName, $authResult, $authInfoField, $this->cookieUser, $this->targetKeyField, $this->targetKeyValue);
                        if ($contextRecord === false) {
                            $this->exitAsError(401);
                        }
                        $contextRecord = [$contextRecord];
                        break;
                    default: // 'context_auth' or 'no_auth'
                        if ($this->targetContextName) {
                            if ($this->targetKeyField && $this->targetKeyValue) {
                                $dbProxyInstance->dbSettings->addExtraCriteria($this->targetKeyField, "=", $this->targetKeyValue);
                            }
                            $dbProxyInstance->dbSettings->setCurrentUser($this->cookieUser);
                            $contextRecord = $dbProxyInstance->readFromDB();
                        }
                }
            }

            $isClass = (stripos($target, 'class://') === 0);
            $isNoRec = !is_array($contextRecord) || (count($contextRecord) === 0);
            $isOneRec = is_array($contextRecord) && (count($contextRecord) === 1);
            // $condition = !$isOneRec && (!$isClass || ($isClass && $isNoRec));
            // In case of the "class:" schema, the record set can have 1 or more than 1 records.
            // In case of non class: schema, the record set has to have just 1 record.
            $isNoTarget = !$this->targetContextName;
            $condition = ($isClass && !$isNoRec && !$isNoTarget)
                || (!$isClass && ((!$isNoRec && $isOneRec && !$isNoTarget) || ($isNoRec && !$isOneRec && $isNoTarget)));
//              if (!$isOneRec && (!$isClass || ($isClass && $isNoRec))) {
            if (!$condition) {
                $erMessage = "[INTER-Mediator] No record which is associated with the parameters in the url({$target}).";
                echo $erMessage;
                error_log($erMessage);
                $this->exitAsError(500);
            }

            // Responding the contents
            $result = null;
            $content = false;
            $dq = '"';

            if (!$isURL) { // File path.
                if (!empty($file) && !file_exists($target)) {
                    error_log("[INTER-Mediator] The file does't exist: {$target}.");
                    $this->exitAsError(500);
                }
                $content = file_get_contents($target);
                $fileName = basename($file);
                $qPos = strpos($fileName, "?");
                if ($qPos !== false) {
                    $fileName = substr($fileName, 0, $qPos);
                }
                header("Content-Type: " . IMUtil::getMimeType($fileName));
                header("Content-Length: " . strlen($content));
                header("Content-Disposition: {$this->disposition}; filename={$dq}" . urlencode($fileName) . $dq);
                $util = new IMUtil();
                $util->outputSecurityHeaders();
                $this->outputImage($content);
            } else if (stripos($target, 'http://') === 0 || stripos($target, 'https://') === 0) { // http or https
                $parsedUrl = parse_url($target);
                if (get_class($dbProxyInstance->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI' &&
                    isset($parsedUrl['host']) && $parsedUrl['host'] === 'localserver') {
                    // for FileMaker Data API
                    $target = 'http://' . $parsedUrl['user'] . ':' . $parsedUrl['pass'] . '@127.0.0.1:1895' . $parsedUrl['path'] . '?' . $parsedUrl['query'];
                    if (function_exists('curl_init')) {
                        $session = curl_init($target);
                        curl_setopt($session, CURLOPT_HEADER, true);
                        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($session);
                        $headerSize = curl_getinfo($session, CURLINFO_HEADER_SIZE);
                        $headers = substr($content, 0, $headerSize);
                        curl_close($session);
                        $sessionKey = '';
                        if ($header = explode("\r\n", $headers)) {
                            foreach ($header as $line) {
                                if ($line) {
                                    $h = explode(': ', $line);
                                    if (isset($h[0]) && isset($h[1]) && $h[0] == 'Set-Cookie') {
                                        $sessionKey = str_replace(
                                            '; HttpOnly', '', str_replace('X-FMS-Session-Key=', '', $h[1] ?? "")
                                        );
                                    }
                                }
                            }
                        }
                        $target = 'http://127.0.0.1:1895' . $parsedUrl['path'] . '?' . $parsedUrl['query'];
                        $headers = array('X-FMS-Session-Key: ' . $sessionKey);
                        $session = curl_init($target);
                        curl_setopt($session, CURLOPT_HEADER, false);
                        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($session);
                        curl_close($session);
                    } else {
                        $this->exitAsError(500);
                    }
                } else if (intval(get_cfg_var('allow_url_fopen')) === 1) {
                    $content = file_get_contents($target);
                } else {
                    if (function_exists('curl_init')) {
                        $session = curl_init($target);
                        curl_setopt($session, CURLOPT_HEADER, false);
                        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                        $content = curl_exec($session);
                        curl_close($session);
                    } else {
                        $this->exitAsError(500);
                    }
                }
                $fileName = basename($file);
                $qPos = strpos($fileName, "?");
                if ($qPos !== false) {
                    $fileName = str_replace("%20", " ", substr($fileName, 0, $qPos) ?? "");
                }
                header("Content-Type: " . IMUtil::getMimeType($fileName));
                header("Content-Length: " . strlen($content));
                header("Content-Disposition: {$this->disposition}; filename={$dq}"
                    . str_replace("+", "%20", urlencode($fileName) ?? "") . $dq);
                $util = new IMUtil();
                $util->outputSecurityHeaders();
                $this->outputImage($content);
            } else if (stripos($target, 'class://') === 0) { // class
                $noscheme = substr($target, 8);
                $className = substr($noscheme, 0, strpos($noscheme, "/"));
                $processingObject = new $className();
                $processingObject->processing($contextRecord, $options);
            } else if (stripos($target, 's3://') === 0) {
//                $params = IMUtil::getFromParamsPHPFile(["accessRegion", "rootBucket",
//                    "s3AccessKey", "s3AccessSecret", "s3AccessProfile"], true);
//                $accessRegion = $params["accessRegion"];
//                $rootBucket = $params["rootBucket"];
//                $s3AccessProfile = $params["s3AccessProfile"];
//                $s3AccessKey = $params["s3AccessKey"];
//                $s3AccessSecret = $params["s3AccessSecret"];

                [$accessRegion, $rootBucket, $s3AccessKey, $s3AccessSecret, $s3AccessProfile]
                    = Params::getParameterValue(
                    ["accessRegion", "rootBucket", "s3AccessKey", "s3AccessSecret", "s3AccessProfile"], null);
                $startOfPath = strpos($target, "/", 5);
                $urlPath = substr($target, $startOfPath + 1);
                $fileName = basename($urlPath);
                $clientArgs = ['version' => 'latest', 'region' => $accessRegion];
                if ($s3AccessProfile) {
                    $clientArgs['profile'] = $s3AccessProfile;
                } else if ($s3AccessKey && $s3AccessSecret) {
                    $clientArgs['credentials'] = new Credentials($s3AccessKey, $s3AccessSecret);
                }
                $s3 = new S3Client($clientArgs);
                $objectSpec = ['Bucket' => $rootBucket, 'Key' => $urlPath,];
                try {
                    $result = $s3->getObject($objectSpec);
                } catch (S3Exception $e) {
                    error_log($e->getMessage());
                    $this->exitAsError(500);
                }
                if (interface_exists($result['Body'], 'Psr\Http\Message\StreamInterface')) {
                    $content = $result['Body']->getContents();
                } else {
                    $content = $result['Body'];
                }

                header("Content-Type: " . IMUtil::getMimeType($fileName));
                header("Content-Length: " . strlen($content));
                header("Content-Disposition: {$this->disposition}; filename={$dq}"
                    . str_replace("+", "%20", urlencode($fileName) ?? "") . $dq);
                $util = new IMUtil();
                $util->outputSecurityHeaders();
                $this->outputImage($content);
            }
        } catch (Exception $ex) {
            // do nothing
        }
    }

    /**
     * @param $code int any error code, but supported just 204, 401 and 500.
     * @throws Exception happens anytime.
     */
    private function exitAsError($code)
    {
        switch ($code) {
            case 204:
                header("HTTP/1.1 204 No Content");
                break;
            case 401:
                header("HTTP/1.1 401 Unauthorized");
                break;
            case 500:
                header("HTTP/1.1 500 Internal Server Error");
                break;
            default: // for debug purpose mainly.
        }
        throw new Exception('Respond HTTP Error.');
    }

    /**
     * @param $dbProxyInstance
     * @param $options
     * @param $file
     * @param $isURL
     * @return array
     */
    public function checkForFileMakerMedia($dbProxyInstance, $options, $file, $isURL): array
    {
        if (strpos($file, '/fmi/xml/cnt/') === 0 ||
            strpos($file, '/Streaming_SSL/MainDB') === 0) {
            // FileMaker's container field storing an image.
//            if (isset($options['authentication']['user'][0])
//                && $options['authentication']['user'][0] == 'database_native'
//            ) {
//                $passPhrase = '';
//                $generatedPrivateKey = ''; // avoid errors for defined in params.php.
//
//                $imRootDir = IMUtil::pathToINTERMediator() . DIRECTORY_SEPARATOR;
//                $currentDirParam = $imRootDir . 'params.php';
//                $parentDirParam = dirname($imRootDir) . DIRECTORY_SEPARATOR . 'params.php';
//                if (file_exists($parentDirParam)) {
//                    include($parentDirParam);
//                } else if (file_exists($currentDirParam)) {
//                    include($currentDirParam);
//                }
//                $rsa = new RSA();
//                $rsa->setPassword($passPhrase);
//                $rsa->loadKey($generatedPrivateKey);
//                $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
//                $cookieNameUser = '_im_username';
//                $cookieNamePassword = '_im_crypted';
//                $credential = isset($_COOKIE[$cookieNameUser]) ? urlencode($_COOKIE[$cookieNameUser]) : '';
//                if (isset($_COOKIE[$cookieNamePassword]) && strlen($_COOKIE[$cookieNamePassword]) > 0) {
//                    $credential .= ':' . urlencode($rsa->decrypt(base64_decode($_COOKIE[$cookieNamePassword])));
//                }
//                $urlHost = $dbProxyInstance->dbSettings->getDbSpecProtocol() . '://' . $credential . '@'
//                    . $dbProxyInstance->dbSettings->getDbSpecServer() . ':'
//                    . $dbProxyInstance->dbSettings->getDbSpecPort();
//            } else {
            $urlHost = $dbProxyInstance->dbSettings->getDbSpecProtocol() . "://"
                . urlencode($dbProxyInstance->dbSettings->getDbSpecUser()) . ":"
                . urlencode($dbProxyInstance->dbSettings->getDbSpecPassword()) . "@"
                . $dbProxyInstance->dbSettings->getDbSpecServer() . ":"
                . $dbProxyInstance->dbSettings->getDbSpecPort();
//            }
            $file = $urlHost . $file;
            $oldLocale = setlocale(LC_CTYPE, 0);
            setlocale(LC_CTYPE, 'C');
            $path = parse_url($file, PHP_URL_PATH);
            $query = parse_url($file, PHP_URL_QUERY);
            setlocale(LC_CTYPE, $oldLocale);
            parse_str($query, $get_array);
            $get_array = $get_array + $_GET;
            foreach ($get_array as $key => $value) {
                if ($key !== 'media' && $key !== 'attach') {
                    if (strpos($path, '?') !== false) {
                        $path .= '&';
                    } else {
                        $path .= '?';
                    }
                    $path .= urlencode($key) . '=' . urlencode($value);
                }
            }
            $isURL = true;
            return array($urlHost . $path, $isURL);
        }
        return array($file, $isURL);
    }

    /**
     * @param $dbProxyInstance
     * @param $options
     * @param $target
     * @return ?string
     * 'context_auth'
     * 'no_auth'
     * 'field_user'
     * 'field_group'
     */
    private function checkAuthentication($dbProxyInstance, $options): ?string
    {
        $contextDef = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
        $isContextAuth = (isset($contextDef['authentication']) && (isset($contextDef['authentication']['all'])
                || isset($contextDef['authentication']['load']) || isset($contextDef['authentication']['read'])));
        $isOptionAuth = isset($options['authentication']);
        if (!$isContextAuth && !$isOptionAuth) { // No authentication
            return 'no_auth';
        }
        // Check the authentication credential on cookie
        $cookieNameUser = "_im_username";
        $cookieNameToken = "_im_mediatoken";
        if (isset($options['authentication']['realm'])) {
            $realm = str_replace(" ", "_",
                str_replace(".", "_", $options['authentication']['realm'] ?? ""));
            $cookieNameUser .= ('_' . $realm);
            $cookieNameToken .= ('_' . $realm);
        }
        $cValueUser = $_COOKIE[$cookieNameUser] ?? '';
        $this->cookieUser = $cValueUser;
        $cValueToken = $_COOKIE[$cookieNameToken] ?? '';
        if (!$dbProxyInstance->checkMediaToken($cValueUser, $cValueToken)) {
            $this->exitAsError(401);
        }
        if ($isContextAuth) { // If the context definition has authentication keyed value.
            $authInfoTarget = $dbProxyInstance->dbClass->authHandler->getTargetForAuthorization("read");
            if ($authInfoTarget == 'field-user') {
                if (!$this->targetContextName) {
                    $this->exitAsError(401);
                }
                return 'field_user';
            } else if ($authInfoTarget == 'field-group') {
                // unimplemented
                return 'field_group';
            } else {
                $isOptionAuth = true; // Follow the below process if the target isn't specified.
            }
        }
        if ($isOptionAuth) { // If the option setting has authentication keyed value.
            $authorizedUsers = $dbProxyInstance->dbClass->authHandler->getAuthorizedUsers("read");
            $authorizedGroups = $dbProxyInstance->dbClass->authHandler->getAuthorizedGroups("read");
            if (count($authorizedGroups) != 0 || count($authorizedUsers) != 0) {
                $belongGroups = $dbProxyInstance->dbClass->authHandler->authSupportGetGroupsOfUser($_COOKIE[$cookieNameUser]);
                if (!in_array($_COOKIE[$cookieNameUser], $authorizedUsers)
                    && count(array_intersect($belongGroups, $authorizedGroups)) == 0
                ) {
                    $this->exitAsError(400);
                }
            }
            return 'context_auth';
        }
//        file_put_contents('/tmp/2', var_export($cValueUser, true));
//        file_put_contents('/tmp/3', var_export($cValueToken, true));
//        file_put_contents('/tmp/4', var_export($cookieNameToken, true));
        return null;
    }

    private function analyzeTarget($target): bool
    {
        // The following properties are the results of this method.
        $this->targetKeyField = null;
        $this->targetKeyValue = null;
        $this->targetContextName = null;

        $result = false;
        $endOfPath = strpos($target, "?");
        $endOfPath = ($endOfPath === false) ? strlen($target) : $endOfPath;
        $pathComponents = explode('/', substr($target, 0, $endOfPath));
        $indexKeying = -1;
        foreach ($pathComponents as $index => $dname) {
            $decodedComponent = urldecode($dname);
            if (strpos($decodedComponent, '=') !== false) {
                $indexKeying = $index;
                $fieldComponents = explode('=', $decodedComponent);
                $this->targetKeyField = $fieldComponents[0];
                $this->targetKeyValue = $fieldComponents[1];
            }
        }
        if ($indexKeying > 0) {
            $this->targetContextName = urldecode($pathComponents[$indexKeying - 1]);
            $result = true;
        } else {
            if ($pathComponents[0] == 'class:' && isset($pathComponents[3])) {
                $this->targetContextName = urldecode($pathComponents[3]);
                $result = true;
            }
        }
        return $result;
    }

    private function outputImage($content)
    {
        $rotate = false;
        if (function_exists('exif_imagetype') && function_exists('imagejpeg') &&
            strlen($content) > 0
        ) {
            $tmpDir = ini_get('upload_tmp_dir');
            if ($tmpDir === '') {
                $tmpDir = sys_get_temp_dir();
            }
            $temp = 'IM_TEMP_' .
                str_replace(DIRECTORY_SEPARATOR, '-', base64_encode(IMUtil::randomString(12)) ?? "") .
                '.jpg';
            if (mb_substr($tmpDir, 1) === DIRECTORY_SEPARATOR) {
                $tempPath = $tmpDir . $temp;
            } else {
                $tempPath = $tmpDir . DIRECTORY_SEPARATOR . $temp;
            }
            $fp = fopen($tempPath, 'w');
            if ($fp !== false) {
                fwrite($fp, $content);
                fclose($fp);

                if (file_exists($tempPath)) {
                    $imageType = image_type_to_mime_type(exif_imagetype($tempPath));
                    if ($imageType === 'image/jpeg') {
                        $image = imagecreatefromstring($content);
                        if ($image !== false) {
                            try {
                                $exif = @exif_read_data($tempPath);
                            } catch (Exception $ex) {
                                $exif = false;
                            }
                            if ($exif !== false && !empty($exif['Orientation'])) {
                                switch ($exif['Orientation']) {
                                    case 3:
                                        $content = imagerotate($image, 180, 0);
                                        $rotate = true;
                                        break;
                                    case 6:
                                        $content = imagerotate($image, -90, 0);
                                        $rotate = true;
                                        break;
                                    case 8:
                                        $content = imagerotate($image, 90, 0);
                                        $rotate = true;
                                        break;
                                }
                            }
                        }
                        if ($rotate === true) {
                            header('Content-Type: image/jpeg');
                            ob_start();
                            imagejpeg($content);
                            $size = ob_get_length();
                            header('Content-Length: ' . $size);
                            $util = new IMUtil();
                            $util->outputSecurityHeaders();
                            ob_end_flush();
                        }
                        imagedestroy($image);
                    }
                    unlink($tempPath);
                }
            }
        }
        if ($rotate === false) {
            echo $content;
        }
    }
}
