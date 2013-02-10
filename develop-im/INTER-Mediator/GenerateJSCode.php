<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2012 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2012/10/10
 * Time: 20:07
 * To change this template use File | Settings | File Templates.
 */
class GenerateJSCode
{
    function __construct()
    {
        header('Content-Type: text/javascript; charset="UTF-8"');
        header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
        header('Expires: 0');
    }

    function generateAssignJS($variable, $value1, $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        echo "{$variable}={$value1}{$value2}{$value3}{$value4}{$value5};\n";
    }

    function generateErrorMessageJS($message)
    {
        $q = '"';
        echo "INTERMediator.errorMessages.push({$q}"
            . str_replace("\n", " ", addslashes($message)) . "{$q});";
    }

    function generateInitialJSCode($datasource, $options, $dbspecification, $debug)
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
            echo file_get_contents($currentDir . 'INTER-Mediator-Parts.js');
            echo file_get_contents($currentDir . 'INTER-Mediator.js');
            echo file_get_contents($jsLibDir . 'sha1.js');
            echo file_get_contents($jsLibDir . 'sha256.js');
            echo file_get_contents($bi2phpDir . 'biBigInt.js');
            echo file_get_contents($bi2phpDir . 'biMontgomery.js');
            echo file_get_contents($bi2phpDir . 'biRSA.js');
            echo file_get_contents($currentDir . 'Adapter_DBServer.js');
        } else {
            echo file_get_contents($currentDir . 'INTER-Mediator.js');
        }

        $pathToMySelf = (isset($scriptPathPrefix) ? $scriptPathPrefix : '')
            . $_SERVER['SCRIPT_NAME'] . (isset($scriptPathSufix) ? $scriptPathSufix : '');
        $this->generateAssignJS(
            "INTERMediatorOnPage.getEntryPath", "function(){return {$q}{$pathToMySelf}{$q};}");
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
            if (strlen($langCountry[0]) > 0) {
                $clientLang = explode('-', $langCountry[0]);
                $messageClass = "MessageStrings_$clientLang[0]";
                if (file_exists("$currentDir$messageClass.php")) {
                    $messageClass = new $messageClass();
                    break;
                }
            }
            $messageClass = null;
        }
        if ($messageClass == null) {
            require_once('MessageStrings.php');
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
            $this->generateAssignJS("INTERMediator.debugMode", "false");
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
        $this->generateAssignJS(
            "INTERMediatorOnPage.realm", $q,
            (isset($options['authentication']) && isset($options['authentication']['realm'])) ?
                $options['authentication']['realm'] : '', $q);
        if (isset($generatedPrivateKey)) {
            $keyArray = openssl_pkey_get_details(openssl_pkey_get_private($generatedPrivateKey, $passPhrase));
            if (isset($keyArray['rsa'])) {
                $this->generateAssignJS(
                    "INTERMediatorOnPage.publickey",
                    "new biRSAKeyPair('", bin2hex($keyArray['rsa']['e']), "','0','", bin2hex($keyArray['rsa']['n']), "')");
            }
        }
    }
}
