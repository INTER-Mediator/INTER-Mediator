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

use Exception;

class MediaAccess
{
    private $contextRecord = null;
    private $disposition = "inline";

    function asAttachment()
    {
        $this->disposition = "attachment";
    }

    function processing($dbProxyInstance, $options, $file)
    {
        try {
            // It the $file ('media'parameter) isn't specified, it doesn't respond an error.
            if (strlen($file) === 0) {
                $this->exitAsError(204);
            }
            // If the media parameter is an URL, the variable isURL will be set to true.
            $schema = array("https:", "http:", "class:");
            $isURL = false;
            foreach ($schema as $scheme) {
                if (strpos($file, $scheme) === 0) {
                    $isURL = true;
                    break;
                }
            }
            list($file, $isURL) = $this->checkForFileMakerMedia($dbProxyInstance, $options, $file, $isURL);
            /*
                         * If the FileMaker's object field is storing a PDF, the $file could be "http://server:16000/...
                         * style URL. In case of an image, $file is just the path info as like above.
                         */
            $file = IMUtil::removeNull($file);
            if (strpos($file, '../') !== false) {
                return;
            }
            $target = $isURL ? $file : "{$options['media-root-dir']}/{$file}";
            if (isset($options['media-context'])) {
                $this->checkAuthentication($dbProxyInstance, $options, $target);
            }
            $content = false;
            $dq = '"';
            if (!$isURL) { // File path.
                if (!empty($file) && !file_exists($target)) {
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
                                            '; HttpOnly', '', str_replace('X-FMS-Session-Key=', '', $h[1])
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
                    $fileName = str_replace("%20", " ", substr($fileName, 0, $qPos));
                }
                header("Content-Type: " . IMUtil::getMimeType($fileName));
                header("Content-Length: " . strlen($content));
                header("Content-Disposition: {$this->disposition}; filename={$dq}"
                    . str_replace("+", "%20", urlencode($fileName)) . $dq);
                $util = new IMUtil();
                $util->outputSecurityHeaders();
                $this->outputImage($content);
            } else if (stripos($target, 'class://') === 0) { // class
                $noscheme = substr($target, 8);
                $className = substr($noscheme, 0, strpos($noscheme, "/"));
                $processingObject = new $className();
                $processingObject->processing($this->contextRecord, $options);
            }
        } catch (Exception $ex) {
            // do nothing
        }
    }

    /**
     * @param $code any error code, but supported just 204, 401 and 500.
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
    public function checkForFileMakerMedia($dbProxyInstance, $options, $file, $isURL)
    {
        if (strpos($file, '/fmi/xml/cnt/') === 0 ||
            strpos($file, '/Streaming_SSL/MainDB') === 0) {
            // FileMaker's container field storing an image.
            if (isset($options['authentication']['user'][0])
                && $options['authentication']['user'][0] == 'database_native'
            ) {
                $passPhrase = '';
                $generatedPrivateKey = ''; // avoid errors for defined in params.php.

                $imRootDir = IMUtil::pathToINTERMediator() . DIRECTORY_SEPARATOR;
                $currentDirParam = $imRootDir . 'params.php';
                $parentDirParam = dirname($imRootDir) . DIRECTORY_SEPARATOR . 'params.php';
                if (file_exists($parentDirParam)) {
                    include($parentDirParam);
                } else if (file_exists($currentDirParam)) {
                    include($currentDirParam);
                }
                $rsa = new \phpseclib\Crypt\RSA();
                $rsa->setPassword($passPhrase);
                $rsa->loadKey($generatedPrivateKey);
                $rsa->setEncryptionMode(\phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
                $cookieNameUser = '_im_username';
                $cookieNamePassword = '_im_crypted';
                $credential = isset($_COOKIE[$cookieNameUser]) ? urlencode($_COOKIE[$cookieNameUser]) : '';
                if (isset($_COOKIE[$cookieNamePassword]) && strlen($_COOKIE[$cookieNamePassword]) > 0) {
                    $credential .= ':' . urlencode($rsa->decrypt(base64_decode($_COOKIE[$cookieNamePassword])));
                }
                $urlHost = $dbProxyInstance->dbSettings->getDbSpecProtocol() . '://' . $credential . '@'
                    . $dbProxyInstance->dbSettings->getDbSpecServer() . ':'
                    . $dbProxyInstance->dbSettings->getDbSpecPort();
            } else {
                $urlHost = $dbProxyInstance->dbSettings->getDbSpecProtocol() . "://"
                    . urlencode($dbProxyInstance->dbSettings->getDbSpecUser()) . ":"
                    . urlencode($dbProxyInstance->dbSettings->getDbSpecPassword()) . "@"
                    . $dbProxyInstance->dbSettings->getDbSpecServer() . ":"
                    . $dbProxyInstance->dbSettings->getDbSpecPort();
            }
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
     */
    private function checkAuthentication($dbProxyInstance, $options, $target)
    {
        if ($this->analyzeTarget($target)) {
            $dbProxyInstance->dbSettings->setDataSourceName($this->targetContextName);
            $context = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
        }
        if (!$context) {
            $dbProxyInstance->dbSettings->setDataSourceName($options['media-context']);
            $context = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
        }
        if (isset($context['authentication'])
            && (isset($context['authentication']['all'])
                || isset($context['authentication']['load'])
                || isset($context['authentication']['read']))
        ) {
            $cookieNameUser = "_im_username";
            $cookieNameToken = "_im_mediatoken";
            if (isset($options['authentication']['realm'])) {
                $realm = str_replace(" ", "_",
                    str_replace(".", "_", $options['authentication']['realm']));
                $cookieNameUser .= ('_' . $realm);
                $cookieNameToken .= ('_' . $realm);
            }
            $cValueUser = isset($_COOKIE[$cookieNameUser]) ? $_COOKIE[$cookieNameUser] : '';
            $cValueToken = isset($_COOKIE[$cookieNameToken]) ? $_COOKIE[$cookieNameToken] : '';
            if (!$dbProxyInstance->checkMediaToken($cValueUser, $cValueToken)) {
                $this->exitAsError(401);
            }
            if (isset($context['authentication']['load'])) {
                $authInfoField = $dbProxyInstance->dbClass->authHandler->getFieldForAuthorization("load");
                $authInfoTarget = $dbProxyInstance->dbClass->authHandler->getTargetForAuthorization("load");
            } else if (isset($context['authentication']['read'])) {
                $authInfoField = $dbProxyInstance->dbClass->authHandler->getFieldForAuthorization("read");
                $authInfoTarget = $dbProxyInstance->dbClass->authHandler->getTargetForAuthorization("read");
            } else if (isset($context['authentication']['all'])) {
                $authInfoField = $dbProxyInstance->dbClass->authHandler->getFieldForAuthorization("all");
                $authInfoTarget = $dbProxyInstance->dbClass->authHandler->getTargetForAuthorization("all");
            }
            if ($authInfoTarget == 'field-user') {
                if (!$this->targetContextName) {
                    $this->exitAsError(401);
                }
                $dbProxyInstance->dbSettings->setDataSourceName($this->targetContextName);
                $tableName = $dbProxyInstance->dbSettings->getEntityForRetrieve();
                $this->contextRecord = $dbProxyInstance->dbClass->authHandler->authSupportCheckMediaPrivilege(
                    $tableName, $authInfoField, $_COOKIE[$cookieNameUser], $this->targetKeyField, $this->targetKeyValue);
                if ($this->contextRecord === false) {
                    $this->exitAsError(401);
                }
            } else if ($authInfoTarget == 'field-group') {
                //
            } else {
                if (isset($context['authentication']['load'])) {
                    $authorizedUsers = $dbProxyInstance->dbClass->authHandler->getAuthorizedUsers("load");
                    $authorizedGroups = $dbProxyInstance->dbClass->authHandler->getAuthorizedGroups("load");
                } else if (isset($context['authentication']['read'])) {
                    $authorizedUsers = $dbProxyInstance->dbClass->authHandler->getAuthorizedUsers("read");
                    $authorizedGroups = $dbProxyInstance->dbClass->authHandler->getAuthorizedGroups("read");
                } else if (isset($context['authentication']['all'])) {
                    $authorizedUsers = $dbProxyInstance->dbClass->authHandler->getAuthorizedUsers("all");
                    $authorizedGroups = $dbProxyInstance->dbClass->authHandler->getAuthorizedGroups("all");
                }
                if (count($authorizedGroups) == 0 && count($authorizedUsers) == 0) {
                    return;
                }
                $belongGroups = $dbProxyInstance->dbClass->authHandler->authSupportGetGroupsOfUser($_COOKIE[$cookieNameUser]);
                if (!in_array($_COOKIE[$cookieNameUser], $authorizedUsers)
                    && count(array_intersect($belongGroups, $authorizedGroups)) == 0
                ) {
                    $this->exitAsError(400);
                }
                $endOfPath = strpos($target, "?");
                $endOfPath = ($endOfPath === false) ? strlen($target) : $endOfPath;
                $pathComponents = explode('/', substr($target, 0, $endOfPath));
                $indexKeying = -1;
                $contextName = '';
                foreach ($pathComponents as $index => $dname) {
                    $decodedComponent = urldecode($dname);
                    if (strpos($decodedComponent, '=') !== false) {
                        $indexKeying = $index;
                        $fieldComponents = explode('=', $decodedComponent);
                        $keyField = $fieldComponents[0];
                        $keyValue = $fieldComponents[1];
                        $dbProxyInstance->dbSettings->addExtraCriteria($keyField, "=", $keyValue);
                    } else {
                        $contextName = $pathComponents[$index];
                    }
                }
                if ($indexKeying == -1) {
                    //    $this->exitAsError(401);
                }
                $dbProxyInstance->dbSettings->setCurrentUser($_COOKIE[$cookieNameUser]);
                $dbProxyInstance->dbSettings->setDataSourceName($contextName);
                $this->contextRecord = $dbProxyInstance->readFromDB();
            }
        } else {
            $endOfPath = strpos($target, "?");
            $endOfPath = ($endOfPath === false) ? strlen($target) : $endOfPath;
            $pathComponents = explode('/', substr($target, 0, $endOfPath));
            $indexKeying = -1;
            $contextName = '';
            foreach ($pathComponents as $index => $dname) {
                $decodedComponent = urldecode($dname);
                if (strpos($decodedComponent, '=') !== false) {
                    $indexKeying = $index;
                    $fieldComponents = explode('=', $decodedComponent);
                    $keyField = $fieldComponents[0];
                    $keyValue = $fieldComponents[1];
                    $dbProxyInstance->dbSettings->addExtraCriteria($keyField, "=", $keyValue);
                    $contextName = $pathComponents[$index - 1];
                }
            }
            if ($indexKeying == -1) {
                //    $this->exitAsError(401);
            }
            $dbProxyInstance->dbSettings->setDataSourceName($contextName);
            $this->contextRecord = $dbProxyInstance->readFromDB();
        }
    }

    private $targetKeyField;
    private $targetKeyValue;
    private $targetContextName;
    private $targetFieldName;

    private function analyzeTarget($target)
    {
        $this->targetKeyField = null;
        $this->targetKeyValue = null;
        $this->targetContextName = null;
        $this->targetFieldName = null;

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
            if (isset($pathComponents[$indexKeying + 1])) {
                $this->targetFieldName = urldecode($pathComponents[$indexKeying + 1]);
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
                str_replace(DIRECTORY_SEPARATOR, '-', base64_encode(IMUtil::randomString(12))) .
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
