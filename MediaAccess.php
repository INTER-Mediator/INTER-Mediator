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
                header("Content-Type: " . $this->getMimeType($fileName));
                header("Content-Length: " . strlen($content));
                header("Content-Disposition: {$this->disposition}; filename={$dq}" . urlencode($fileName) . $dq);
                $util = new IMUtil();
                $util->outputSecurityHeaders();

                $this->outputImage($content);
            } else if (stripos($target, 'http://') === 0 || stripos($target, 'https://') === 0) { // http or https
                if (intval(get_cfg_var('allow_url_fopen')) === 1) {
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
                header("Content-Type: " . $this->getMimeType($fileName));
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
        if (strpos($file, "/fmi/xml/cnt/") === 0) { // FileMaker's container field storing an image.
            if (isset($options['authentication']['user'][0])
                && $options['authentication']['user'][0] == 'database_native'
            ) {
                $passPhrase = '';
                $generatedPrivateKey = ''; // avoid errors for defined in params.php.
                $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
                $currentDirParam = $currentDir . 'params.php';
                $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
                if (file_exists($parentDirParam)) {
                    include($parentDirParam);
                } else if (file_exists($currentDirParam)) {
                    include($currentDirParam);
                }

                $rsaClass = IMUtil::phpSecLibClass('phpseclib\Crypt\RSA');
                $rsa = new $rsaClass;
                $rsa->setPassword($passPhrase);
                $rsa->loadKey($generatedPrivateKey);
                $rsa->setPassword();
                $privatekey = $rsa->getPrivateKey();
                if (IMUtil::phpVersion() < 6) {
                    $priv = $rsa->_parseKey($privatekey, CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
                } else {
                    $priv = $rsa->_parseKey($privatekey, constant('phpseclib\Crypt\RSA::PRIVATE_FORMAT_PKCS1'));
                }

                require_once('lib/bi2php/biRSA.php');
                $keyDecrypt = new biRSAKeyPair('0', $priv['privateExponent']->toHex(), $priv['modulus']->toHex());

                $cookieNameUser = '_im_username';
                $cookieNamePassword = '_im_crypted';
                $credential = isset($_COOKIE[$cookieNameUser]) ? urlencode($_COOKIE[$cookieNameUser]) : '';
                if (isset($_COOKIE[$cookieNamePassword])) {
                    $credential .= ':' . urlencode($keyDecrypt->biDecryptedString($_COOKIE[$cookieNamePassword]));
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
            $file = $urlHost . str_replace(" ", "%20", $file);
            foreach ($_GET as $key => $value) {
                if ($key !== 'media' && $key !== 'attach') {
                    $file .= "&" . urlencode($key) . "=" . urlencode($value);
                }
            }
            $isURL = true;
            return array($file, $isURL);
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
        $dbProxyInstance->dbSettings->setDataSourceName($options['media-context']);
        $context = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
        if (isset($context['authentication'])
            && (isset($context['authentication']['all'])
                || isset($context['authentication']['load'])
                || isset($context['authentication']['read']))
        ) {
            $realm = isset($context['authentication']['realm']) ? "_{$context['authentication']['realm']}" : '';
            $cookieNameUser = "_im_username{$realm}";
            $cookieNameToken = "_im_mediatoken{$realm}";
            if (isset($options['authentication']['realm'])) {
                $cookieNameUser .= '_' . str_replace(".", "_", $options['authentication']['realm']);
                $cookieNameToken .= '_' . str_replace(".", "_", $options['authentication']['realm']);
            }
            if (!$dbProxyInstance->checkMediaToken($_COOKIE[$cookieNameUser], $_COOKIE[$cookieNameToken])) {
                $this->exitAsError(401);
            }
            if (isset($context['authentication']['load'])) {
                $authInfoField = $dbProxyInstance->dbClass->getFieldForAuthorization("load");
                $authInfoTarget = $dbProxyInstance->dbClass->getTargetForAuthorization("load");
            } else if (isset($context['authentication']['read'])) {
                $authInfoField = $dbProxyInstance->dbClass->getFieldForAuthorization("read");
                $authInfoTarget = $dbProxyInstance->dbClass->getTargetForAuthorization("read");
            } else if (isset($context['authentication']['all'])) {
                $authInfoField = $dbProxyInstance->dbClass->getFieldForAuthorization("all");
                $authInfoTarget = $dbProxyInstance->dbClass->getTargetForAuthorization("all");
            }
            if ($authInfoTarget == 'field-user') {
                $endOfPath = strpos($target, "?");
                $endOfPath = ($endOfPath === false) ? strlen($target) : $endOfPath;
                $pathComponents = explode('/', substr($target, 0, $endOfPath));
                $indexKeying = -1;
                foreach ($pathComponents as $index => $dname) {
                    $decodedComponent = urldecode($dname);
                    if (strpos($decodedComponent, '=') !== false) {
                        $indexKeying = $index;
                        $fieldComponents = explode('=', $decodedComponent);
                        $keyField = $fieldComponents[0];
                        $keyValue = $fieldComponents[1];
                    }
                }
                if ($indexKeying == -1) {
                    $this->exitAsError(401);
                }
                $contextName = $pathComponents[$indexKeying - 1];
                if ($contextName != $options['media-context']) {
                    $this->exitAsError(401);
                }
                $dbProxyInstance->dbSettings->setDataSourceName($contextName);
                $tableName = $dbProxyInstance->dbSettings->getEntityForRetrieve();
                $this->contextRecord = $dbProxyInstance->dbClass->authSupportCheckMediaPrivilege(
                    $tableName, $authInfoField, $_COOKIE[$cookieNameUser], $keyField, $keyValue);
                if ($this->contextRecord === false) {
                    $this->exitAsError(401);
                }
            } else if ($authInfoTarget == 'field-group') {
                //
            } else {
                if (isset($context['authentication']['load'])) {
                    $authorizedUsers = $dbProxyInstance->dbClass->getAuthorizedUsers("load");
                    $authorizedGroups = $dbProxyInstance->dbClass->getAuthorizedGroups("load");
                } else if (isset($context['authentication']['read'])) {
                    $authorizedUsers = $dbProxyInstance->dbClass->getAuthorizedUsers("read");
                    $authorizedGroups = $dbProxyInstance->dbClass->getAuthorizedGroups("read");
                } else if (isset($context['authentication']['all'])) {
                    $authorizedUsers = $dbProxyInstance->dbClass->getAuthorizedUsers("all");
                    $authorizedGroups = $dbProxyInstance->dbClass->getAuthorizedGroups("all");
                }
                if (count($authorizedGroups) == 0 && count($authorizedUsers) == 0) {
                    return;
                }
//                if (in_array($_COOKIE[$cookieNameUser], $authorizedUsers)) {
//                    return;
//                }
                $belongGroups = $dbProxyInstance->dbClass->authSupportGetGroupsOfUser($_COOKIE[$cookieNameUser]);
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
                } else {
                    $contextName = $pathComponents[$index];
                }
            }
            if ($indexKeying == -1) {
                //    $this->exitAsError(401);
            }
            $dbProxyInstance->dbSettings->setDataSourceName($contextName);
            $this->contextRecord = $dbProxyInstance->readFromDB();
        }
    }

    private function getMimeType($path)
    {
        $type = "application/octet-stream";
        switch (strtolower(substr($path, strrpos($path, '.') + 1))) {
            case 'jpg':
                $type = 'image/jpeg';
                break;
            case 'jpeg':
                $type = 'image/jpeg';
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
            case 'tif':
                $type = 'image/tiff';
                break;
            case 'tiff':
                $type = 'image/tiff';
                break;
            case 'pdf':
                $type = 'application/pdf';
                break;
        }
        return $type;
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
                str_replace(base64_encode(randomString(12)), DIRECTORY_SEPARATOR, '-') .
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

                $imageType = image_type_to_mime_type(exif_imagetype($tempPath));
                if ($imageType === 'image/jpeg') {
                    $image = imagecreatefromstring($content);
                    if ($image !== false) {
                        $exif = exif_read_data($tempPath);
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

        if ($rotate === false) {
            echo $content;
        }
    }

}
