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

require_once('DB_Proxy.php');
//require_once('MessageStrings.php');
//require_once('MessageStrings_ja.php');
/*
 * GET
 * ?access=select
 * &name=<table name>
 * &start=<record number to start>
 * &records=<how many records should it return>
 * &field_<N>=<field name>
 * &value_<N>=<value of the field>
 * &condition<N>field=<Extra criteria's field name>
 * &condition<N>operator=<Extra criteria's operator>
 * &condition<N>value=<Extra criteria's value>
 * &parent_keyval=<value of the foreign key field>
 */

function IM_Entry($datasource, $options, $dbspecification, $debug = false)
{

    header('Content-Type: text/javascript; charset="UTF-8"');
    header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
    header('Expires: 0');

    spl_autoload_register('loadClass');

    if (!isset($_POST['access'])) {
        $generator = new GenerateInitialJSCode();
        $generator->generate($datasource, $options, $dbspecification, $debug);
    } else {
        $dbInstance = new DB_Proxy();
        $dbInstance->initialize($datasource, $options, $dbspecification, $debug);
        $dbInstance->processingRequest($options);
    }
}

class GenerateInitialJSCode
{
    function generateAssignJS($variable, $value1, $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        echo "{$variable}={$value1}{$value2}{$value3}{$value4}{$value5};\n";
    }

    function generate($datasource, $options, $dbspecification, $debug)
    {
        $q = '"';
        $generatedPrivateKey = null;
        $passPhrase = null;
        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }

        if (file_exists($currentDir . 'INTER-Mediator-Lib.js')) {
            $jsLibDir = $currentDir . 'js_lib' . DIRECTORY_SEPARATOR;
            $bi2phpDir = $currentDir . 'bi2php' . DIRECTORY_SEPARATOR;
            echo file_get_contents($currentDir . 'INTER-Mediator-Lib.js');
            echo file_get_contents($currentDir . 'INTER-Mediator-Page.js');
            echo file_get_contents($currentDir . 'INTER-Mediator.js');
            echo file_get_contents($jsLibDir . 'sha1.js');
            echo file_get_contents($bi2phpDir . 'biBigInt.js');
            echo file_get_contents($bi2phpDir . 'biMontgomery.js');
            echo file_get_contents($bi2phpDir . 'biRSA.js');
            echo file_get_contents($currentDir . 'Adapter_DBServer.js');
        } else {
            echo file_get_contents($currentDir . 'INTER-Mediator.js');
        }

        $this->generateAssignJS(
            "INTERMediatorOnPage.getEntryPath", "function(){return {$q}{$_SERVER['SCRIPT_NAME']}{$q};}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getDataSources", "function(){return ", arrayToJS($datasource, ''), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsAliases",
            "function(){return ", arrayToJS(isset($options['aliases']) ? $options['aliases'] : array(), ''), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsTransaction",
            "function(){return ", arrayToJS(isset($options['transaction']) ? $options['transaction'] : '', ''), ";}");

        $messageClass = null;
        $clientLangArray = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        foreach ($clientLangArray as $oneLanguage) {
            $langCountry = explode(';', $oneLanguage);
            $clientLang = explode('-', $langCountry[0]);
            $messageClass = "MessageStrings_{$clientLang[0]}";
            if (class_exists($messageClass)) {
                $messageClass = new $messageClass();
                break;
            }
        }
        if ($messageClass == null) {
            $messageClass = new MessageStrings();
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.getMessages",
            "function(){return ", arrayToJS($messageClass->getMessages(), ''), ";}");
        if (isset($options['browser-compatibility'])) {
            $browserCompatibility = $options['browser-compatibility'];
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.browserCompatibility",
            "function(){return ", arrayToJS($browserCompatibility, ''), ";}");
        if (isset($prohibitDebugMode) && $prohibitDebugMode) {
            $this->generateAssignJS(
                "INTERMediator.debugMode",
                "false");
        } else {
            $this->generateAssignJS(
                "INTERMediator.debugMode", ($debug === false) ? "false" : $debug);
        }

        // Check Authentication
        $boolValue = "false";
        $requireAuthenticationContext = array();
        if (isset($options['authentication'])) {
            $boolValue = "true";
        }
        foreach ($datasource as $aContext) {
            if (isset($aContext['authentication'])) {
                $boolValue = "true";
                $requireAuthenticationContext[] = $aContext['name'];
            }
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.requireAuthentication", $boolValue);
        $this->generateAssignJS(
            "INTERMediatorOnPage.authRequiredContext", arrayToJS($requireAuthenticationContext, ''));

        $this->generateAssignJS(
            "INTERMediatorOnPage.isNativeAuth",
            (isset($options['authentication']) && isset($options['authentication']['user'])
                && ($options['authentication']['user'] === 'database_native')) ? "true" : "false");
        $this->generateAssignJS(
            "INTERMediatorOnPage.authStoring",
            $q, (isset($options['authentication']) && isset($options['authentication']['storing'])) ?
                $options['authentication']['storing'] : 'cookie', $q);
        $this->generateAssignJS(
            "INTERMediatorOnPage.authExpired",
            (isset($options['authentication']) && isset($options['authentication']['authexpired'])) ?
                $options['authentication']['authexpired'] : '3600');
        $keyArray = openssl_pkey_get_details(openssl_pkey_get_private($generatedPrivateKey, $passPhrase));
        $this->generateAssignJS(
            "INTERMediatorOnPage.publickey",
            "new biRSAKeyPair('", bin2hex($keyArray['rsa']['e']), "','0','", bin2hex($keyArray['rsa']['n']), "')");
    }
}

/**
 * Dynamic class loader
 * @param $className
 */
function loadClass($className)
{
    require_once($className . '.php');
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
        }
        else {
            setlocale($locType, 'ja_JP');
        }
    } else if ($lstr == 'ja') {
        $useMbstring = true;
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        }
        else {
            setlocale($locType, 'ja_JP');
        }
    } else if ($lstr == 'en_US') {
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        }
        else {
            setlocale($locType, 'en_US');
        }
    } else if ($lstr == 'en') {
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        }
        else {
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
