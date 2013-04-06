<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Tokyo');

require_once('DB_Interfaces.php');
require_once('DB_Logger.php');
require_once('DB_Settings.php');
require_once('DB_UseSharedObjects.php');
require_once('DB_Proxy.php');

function IM_Entry($datasource, $options, $dbspecification, $debug = false)
{
    spl_autoload_register('loadClass');

    if ($debug) {
        $dc = new DefinitionChecker();
        $defErrorMessage = $dc->checkDefinitions($datasource, $options, $dbspecification);
        if (strlen($defErrorMessage) > 0) {
            $generator = new GenerateJSCode();
            $generator->generateInitialJSCode($datasource, $options, $dbspecification, $debug);
            $generator->generateErrorMessageJS($defErrorMessage);
            return;
        }
    }

    if (!isset($_POST['access']) && !isset($_GET['media'])) {
        $generator = new GenerateJSCode();
        $generator->generateInitialJSCode($datasource, $options, $dbspecification, $debug);
    } else if (isset($_POST['access']) && $_POST['access'] == 'uploadfile') {
        /*
                array(6) { ["_im_redirect"]=> string(54) "http://localhost/im/Sample_webpage/messages_MySQL.html" ["_im_contextname"]=> string(4) "chat" ["_im_field"]=> string(7) "message" ["_im_keyfield"]=> string(2) "id" ["_im_keyvalue"]=> string(2) "38" ["access"]=> string(10) "uploadfile" } array(1) { ["_im_uploadfile"]=> array(5) { ["name"]=> string(16) "ac0600_aoiro.pdf" ["type"]=> string(15) "application/pdf" ["tmp_name"]=> string(26) "/private/var/tmp/phpkk9RXn" ["error"]=> int(0) ["size"]=> int(77732) } }

        */
//        var_export($_POST);
        foreach($_FILES as $fn=>$fileInfo)  {
        }

        $dbKeyValue = $_POST["_im_keyvalue"];
        $dbProxyInstance = new DB_Proxy();
        $dbProxyInstance->initialize($datasource, $options, $dbspecification, $debug, $_POST["_im_contextname"]);
        $dbProxyInstance->dbSettings->setExtraCriteria($_POST["_im_keyfield"], "=", $dbKeyValue);
        $dbProxyInstance->dbSettings->setTargetFields(array($_POST["_im_field"]));
        $dbProxyInstance->dbSettings->setValues(array($fileInfo["name"]));

        if (!isset($options['media-root-dir'])) {
            $dbProxyInstance->logger->setErrorMessage("'media-root-dir' isn't specified");
            $dbProxyInstance->processingRequest($options, "noop");
            $dbProxyInstance->finishCommunication();
            return;
        }
        $fileRoot = $options['media-root-dir'];
        if ( substr($fileRoot, strlen($fileRoot)-1, 1) != '/' )    {
            $fileRoot .= '/';
        }
        $filePathInfo = pathinfo($fileInfo["name"]);
        $dirPath  = $fileRoot . $_POST["_im_contextname"] . '/'
            . $_POST["_im_keyfield"] . "=". $_POST["_im_keyvalue"] . '/' . $_POST["_im_field"];
        $filePath  = $dirPath . '/' . $filePathInfo['filename'] . '_'
            . rand (1000 , 9999 ). '.' . $filePathInfo['extension'];
        if ( ! file_exists($dirPath))   {
            mkdir($dirPath, 0744, true);
        }
        $result = move_uploaded_file($fileInfo["tmp_name"], $filePath);
        if (!$result) {
            $dbProxyInstance->logger->setErrorMessage("Fail to move the uploaded file in the media folder.");
            $dbProxyInstance->processingRequest($options, "noop");
            $dbProxyInstance->finishCommunication();
            return;
        }

        $dbProxyInstance->processingRequest($options, "update");

        $relatedContext = null;
        $dbProxyContext = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
        if( isset($dbProxyContext['file-upload']))   {
            $relatedContexName = $dbProxyContext['file-upload'];
            $relatedContext = new DB_Proxy();
            $relatedContext->initialize($datasource, $options, $dbspecification, $debug, $relatedContexName);
            $relatedContextInfo = $relatedContext->dbSettings->getDataSourceTargetArray();
            $relatedContext->dbSettings->setTargetFields(
                array($relatedContextInfo["relation"][0]["foreign-key"], "path"));
            $relatedContext->dbSettings->setValues(
                array($dbKeyValue, $filePath));
            $relatedContext->processingRequest($options, "new");
        }

        if ( isset( $_POST["_im_redirect"] ))   {
            header("Location: {$_POST["_im_redirect"]}");
        }
        if ( ! is_null( $relatedContext ))    {
            $relatedContext->finishCommunication(true);
        }
        $dbProxyInstance->finishCommunication();


    } else if (!isset($_POST['access']) && isset($_GET['media'])) {
        $dbProxyInstance = new DB_Proxy();
        $dbProxyInstance->initialize($datasource, $options, $dbspecification, $debug);
        $mediaHandler = new MediaAccess();
        $mediaHandler->processing($dbProxyInstance, $options, $_GET['media']);
    } else {
        $dbInstance = new DB_Proxy();
        $dbInstance->initialize($datasource, $options, $dbspecification, $debug);
        $dbInstance->processingRequest($options);
        $dbInstance->finishCommunication();
    }
}



/**
 * Dynamic class loader
 * @param $className
 */
function loadClass($className)
{
    if ((include_once $className . '.php') === false) {
        $errorGenerator = new GenerateJSCode();
        if (strpos($className, "MessageStrings_") !== 0) {
            $errorGenerator->generateErrorMessageJS("The class '{$className}' is not defined.");
        }
    }

}

/**
 * Convert strings to JavaScript friendly strings.
 * Contributed by Atsushi Matsuo at Jan 17, 2010
 * @return string strings for JavaScript
 */
function valueForJSInsert($str)
{
    return str_replace("'", "\\'",
        str_replace('"', '\\"',
            str_replace("/", "\\/",
                str_replace(">", "\\x3e",
                    str_replace("<", "\\x3c",
                        str_replace("\n", "\\n",
                            str_replace("\r", "\\r",
                                str_replace("\\", "\\\\", $str))))))));
}

/**
 * Create JavaScript source from array
 * @param array ar parameter array
 * @param string prefix strings for the prefix for key
 * @return string JavaScript source
 */
function arrayToJS($ar, $prefix)
{
    if (is_array($ar)) {
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = arrayToJS($value, $key);
        }
        $currentKey = (string)$prefix;
        if ($currentKey == '')
            $returnStr = "{" . implode(',', $items) . '}';
        else
            $returnStr = "'{$currentKey}':{" . implode(',', $items) . '}';
    } else {
        $currentKey = (string)$prefix;
        if ($currentKey == '') {
            $returnStr = "'" . valueForJSInsert($ar) . "'";
        } else {
            $returnStr = "'{$prefix}':'" . valueForJSInsert($ar) . "'";
        }
    }
    return $returnStr;
}

/**
 * Create parameter strng from array
 * @param array ar parameter array
 * @param string prefix strings for the prefix for key
 * @return string parameter string
 */
function arrayToQuery($ar, $prefix)
{
    if (is_array($ar)) {
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = arrayToQuery($value, "{$prefix}_{$key}");
        }
        $returnStr = implode('', $items);
    } else {
        $returnStr = "&{$prefix}=" . urlencode($ar);
    }
    return $returnStr;
}

/**
 * Get the relative path from the caller to the directory of 'INTER-Mediator.php'.
 * @return Relative path as a part of URL.
 */
function getRelativePath()
{
    $caller = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_FILENAME']));
    $imDirectory = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
    $commonPath = '';
    $shorterLength = min(count($caller), count($imDirectory));
    for ($i = 0; $i < $shorterLength; $i++) {
        if ($caller[$i] != $imDirectory[$i]) {
            break;
        }
    }
    $relPath = str_repeat('../', count($caller) - $i)
        . implode('/', array_slice($imDirectory, $i));
    return $relPath;
}

/**
 * Generate the instance of the message class associated with browser's language.
 * @return object Generated instance of the message class.
 */
//function getErrorMessageClass()
//{
//    $currentDir = dirname(__FILE__);
//    $lang = getLocaleFromBrowser();
//    $candClassName = 'MessageStrings_' . $lang;
//    if (!file_exists($currentDir . DIRECTORY_SEPARATOR . $candClassName . '.php')) {
//        if (strpos($lang, '_') !== false) {
//            $lang = substr($lang, 0, strpos($lang, '_'));
//            $candClassName = 'MessageStrings_' . $lang;
//            if (!file_exists($currentDir . DIRECTORY_SEPARATOR . $candClassName . '.php')) {
//                $candClassName = 'MessageStrings';
//            }
//        }
//    }
//    $c = null;
//    require_once($candClassName . '.php');
//    eval("\$c = new {$candClassName}();");
//    return $c->getMessages();
//}

/**
 * Set the locale with parameter, for UNIX and Windows OS.
 * @param string locType locale identifier string.
 * @return boolean If true, strings with locale are possibly multi-byte string.
 */
function setLocaleAsBrowser($locType)
{
    $lstr = getLocaleFromBrowser();

    // Detect server platform, Windows or Unix
    $isWindows = false;
    $uname = php_uname();
    if (strpos($uname, 'Windows')) {
        $isWindows = true;
    }

    $useMbstring = false;
    if ($lstr == 'ja_JP') {
        $useMbstring = true;
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        } else {
            setlocale($locType, 'ja_JP');
        }
    } else if ($lstr == 'ja') {
        $useMbstring = true;
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        } else {
            setlocale($locType, 'ja_JP');
        }
    } else if ($lstr == 'en_US') {
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        } else {
            setlocale($locType, 'en_US');
        }
    } else if ($lstr == 'en') {
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        } else {
            setlocale($locType, 'en_US');
        }
    }
    return $useMbstring;
}

/**
 * Get the locale string (ex. 'ja_JP') from HTTP header from a browser.
 * @return string Most prior locale identifier
 */
function getLocaleFromBrowser()
{
    $lstr = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    // Extracting first item and cutting the priority infos.
    if (strpos($lstr, ',') !== false) $lstr = substr($lstr, 0, strpos($lstr, ','));
    if (strpos($lstr, ';') !== false) $lstr = substr($lstr, 0, strpos($lstr, ';'));

    // Convert to the right locale identifier.
    if (strpos($lstr, '-') !== false) {
        $lstr = explode('-', $lstr);
    } else if (strpos($lstr, '_') !== false) {
        $lstr = explode('_', $lstr);
    } else {
        $lstr = array($lstr);
    }
    if (count($lstr) == 1)
        $lstr = $lstr[0];
    else
        $lstr = strtolower($lstr[0]) . '_' . strtoupper($lstr[1]);
    return $lstr;
}

function hex2bin_for53($str)
{
    return pack("H*", $str);
}

function randomString($digit)
{
    $resultStr = '';
    for ($i = 0; $i < $digit; $i++) {
        $resultStr .= chr(rand(20, 126));
    }
    return $resultStr;
}

?>
