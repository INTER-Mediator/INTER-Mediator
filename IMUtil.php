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
class IMUtil
{
    public static function currentDTString($addSeconds = 0)
    {
//        $currentDT = new DateTime();
//        $timeValue = $currentDT->format("U");
//        $currentDTStr = $this->link->quote($currentDT->format('Y-m-d H:i:s'));

        // For 5.2
        $timeValue = time();
        $currentDTStr = date('Y-m-d H:i:s', $timeValue - $addSeconds);
        // End of for 5.2
        return $currentDTStr;
    }

    public static function secondsFromNow($dtStr)
    {
//        $currentDT = new DateTime();
//        $anotherDT = new DateTime($dtStr);
//        $timeValue = $currentDT->format("U") - $anotherDT->format("U");

        // For 5.2
        $timeValue = time() - strtotime($dtStr);
        // End of for 5.2
        return $timeValue;
    }
    
    public static function removeNull($str)
    {
        return str_replace("\x00", '', $str);
    }

    public static function getMessageClassInstance()
    {
        // Message Class Detection
        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $messageClass = null;
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            $clientLangArray = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            foreach ($clientLangArray as $oneLanguage) {
                $langCountry = explode(';', $oneLanguage);
                if (strlen($langCountry[0]) > 0) {
                    $clientLang = explode('-', $langCountry[0]);
                    $messageClass = "MessageStrings_$clientLang[0]";
                    if (file_exists("{$currentDir}{$messageClass}.php")) {
                        $messageClass = new $messageClass();
                        break;
                    }
                }
                $messageClass = null;
            }
        }
        if ($messageClass == null) {
            $messageClass = new MessageStrings();
        }
        return $messageClass;
    }

    // Thanks for http://q.hatena.ne.jp/1193396523
    public static function guessFileUploadError()
    {
        $postMaxSize = self::return_bytes(ini_get('post_max_size'));

        if ($_SERVER['REQUEST_METHOD'] == 'POST'
        //    && count($_POST) == 0
            && $_SERVER['HTTP_CONTENT_LENGTH'] > $postMaxSize
            && strpos($_SERVER['HTTP_CONTENT_TYPE'], 'multipart/form-data') === 0
        ) {
            return true;
        }
        foreach ($_FILES as $fn => $fileInfo) {
            if (isset($fileInfo["error"]))  {
                $errInfo = $fileInfo["error"];
                if (is_array($errInfo)) {   // JQuery File Upload Style
                    foreach($errInfo as $index => $errCode) {
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
    public static function return_bytes($val)
    {
        $val = trim($val);
        switch (strtolower($val[strlen($val) - 1])) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }


    public static function getFromParamsPHPFile($vars, $permitUndef = false)
    {
        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }
        $result = array();
        foreach ($vars as $var) {
            if (isset($$var)) {
                $result[$var] = $$var;
            } else {
                if (!$permitUndef) {
                    return false;
                }
                $result[$var] = null;
            }
        }
        return $result;
    }

    public function protectCSRF()
    {
        /*
         * Prevent CSRF Attack with XMLHttpRequest
         * http://d.hatena.ne.jp/hasegawayosuke/20130302/p1
         */
        $params = IMUtil::getFromParamsPHPFile(array('webServerName'), true);
        $webServerName = $params['webServerName'];
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

    public function checkHost($host, $webServerName)
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
            strpos($webServerName, '.') !== FALSE &&
            !preg_match('/^[0-9\.]+$/', $webServerName)
        ) {
            return TRUE;
        }

        return FALSE;
    }

    public function outputSecurityHeaders($params = NULL)
    {
        if (is_null($params)) {
            $params = IMUtil::getFromParamsPHPFile(array('xFrameOptions', 'contentSecurityPolicy'), true);
        }
        $xFrameOptions = str_replace("\r", '', str_replace("\n", '', $params['xFrameOptions']));
        $contentSecurityPolicy = str_replace("\r", '', str_replace("\n", '', $params['contentSecurityPolicy']));

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
        header('X-XSS-Protection: 1; mode=block');
    }
}
