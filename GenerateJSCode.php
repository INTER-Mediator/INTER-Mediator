<?php
/**
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

class GenerateJSCode
{
    public function __construct()
    {
        header('Content-Type: text/javascript;charset="UTF-8"');
        header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
        header('Expires: 0');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Frame-Options: SAMEORIGIN');
    }

    public function generateAssignJS($variable, $value1, $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        echo "{$variable}={$value1}{$value2}{$value3}{$value4}{$value5};\n";
    }

    public function generateErrorMessageJS($message)
    {
        $q = '"';
        echo "INTERMediator.setErrorMessage({$q}"
            . str_replace("\n", " ", addslashes($message)) . "{$q});";
    }

    private $defaultsArray;

    public function generateInitialJSCode($datasource, $options, $dbspecification, $debug)
    {
        $q = '"';
        $po = '{';
        $pc = '}';
        $generatedPrivateKey = null;
        $passPhrase = null;

        /*
         * Decide the params.php file and load it.
         */
        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }

        /*
         * Read the JS programs regarding by the developing or deployed.
         */
        if (file_exists($currentDir . 'INTER-Mediator-Lib.js')) {
            echo $this->combineScripts($currentDir);
        } else {
            readfile($currentDir . 'INTER-Mediator.js');
        }

        /*
         * Generate the link to the definition file editor
         */
        $relativeToDefFile = '';
        $editorPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'INTER-Mediator-Support';
        $defFilePath = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'];
        while (strpos($defFilePath, $editorPath) !== 0 && strlen($editorPath) > 1) {
            $editorPath = dirname($editorPath);
            $relativeToDefFile .= '..' . DIRECTORY_SEPARATOR;
        }
        $relativeToDefFile .= substr($defFilePath, strlen($editorPath) + 1);
        $editorPath = dirname(__FILE__) . DIRECTORY_SEPARATOR
            . 'INTER-Mediator-Support' . DIRECTORY_SEPARATOR . 'defedit.html';
        if (file_exists($editorPath)) {
            $relativeToEditor = substr($editorPath, strlen($_SERVER['DOCUMENT_ROOT']));
            $this->generateAssignJS("INTERMediatorOnPage.getEditorPath",
                "function(){return {$q}{$relativeToEditor}?target=$relativeToDefFile{$q};}");
        } else {
            $this->generateAssignJS("INTERMediatorOnPage.getEditorPath",
                "function(){return '';}");
        }

        /*
         * from db-class, determine the default key field string
         */
        $defaultKey = null;
        $dbClassName = 'DB_' .
            (isset($dbspecification['db-class']) ? $dbspecification['db-class'] :
                (isset ($dbClass) ? $dbClass : ''));
        require_once("{$dbClassName}.php");
        if (((float)phpversion()) < 5.3) {
            $dbInstance = new $dbClassName();
            if ($dbInstance != null) {
                $defaultKey = $dbInstance->getDefaultKey();
            }
        } else {
            $defaultKey = call_user_func(array($dbClassName, 'defaultKey'));
        }
        if ($defaultKey !== null) {
            $items = array();
            foreach ($datasource as $context) {
                if (!array_key_exists('key', $context)) {
                    $context['key'] = $defaultKey;
                }
                $items[] = $context;
            }
            $datasource = $items;
        }

        /*
         * Determine the uri of myself
         */
        if (isset($callURL)) {
            $pathToMySelf = $callURL;
        } else if (isset($scriptPathPrefix) || isset($scriptPathSuffix)) {
            $pathToMySelf = (isset($scriptPathPrefix) ? $scriptPathPrefix : '')
                . $_SERVER['SCRIPT_NAME'] . (isset($scriptPathSufix) ? $scriptPathSuffix : '');
        } else {
            $pathToMySelf = $_SERVER['SCRIPT_NAME'];
        }

        $this->generateAssignJS(
            "INTERMediatorOnPage.getEntryPath", "function(){return {$q}{$pathToMySelf}{$q};}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getDataSources", "function(){return ",
            arrayToJSExcluding($datasource, '', array('password')), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsAliases",
            "function(){return ", arrayToJS(isset($options['aliases']) ? $options['aliases'] : array(), ''), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsTransaction",
            "function(){return ", arrayToJS(isset($options['transaction']) ? $options['transaction'] : '', ''), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getDBSpecification", "function(){return ",
            arrayToJSExcluding($dbspecification, '',
                array('dsn', 'option', 'database', 'user', 'password', 'server', 'port', 'protocol', 'datatype')), ";}");
        $isEmailAsUsernae = isset($options['authentication'])
            && isset($options['authentication']['email-as-username'])
            && $options['authentication']['email-as-username'] === true;
        $this->generateAssignJS(
            "INTERMediatorOnPage.isEmailAsUsername", $isEmailAsUsernae ? "true" : "false");

        $messageClass = null;
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
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

        $clientIdSeed = time() + $_SERVER['REMOTE_ADDR'] + mt_rand();
        $randomSecret = mt_rand();
        $clientId = hash_hmac('sha256', $clientIdSeed, $randomSecret);

        $this->generateAssignJS(
            "INTERMediatorOnPage.clientNotificationIdentifier",
            "function(){return ", arrayToJS($clientId, ''), ";}");

        $pusherParams = null;
        if (isset($pusherParameters)) {
            $pusherParams = $pusherParameters;
        } else if (isset($options['pusher'])) {
            $pusherParams = $options['pusher'];
        }
        if (!is_null($pusherParams)) {
            $appKey = isset($pusherParams['key']) ? $pusherParams['key'] : "_im_key_isnt_supplied";
            $chName = isset($pusherParams['channel']) ? $pusherParams['channel'] : "_im_pusher_default_channel";
            $this->generateAssignJS(
                "INTERMediatorOnPage.clientNotificationKey",
                "function(){return ", arrayToJS($appKey, ''), ";}");
            $this->generateAssignJS(
                "INTERMediatorOnPage.clientNotificationChannel",
                "function(){return ", arrayToJS($chName, ''), ";}");
        }

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
                && ($options['authentication']['user'][0] === 'database_native')) ? "true" : "false");
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
            $rsa = new Crypt_RSA();
            $rsa->setPassword($passPhrase);
            $rsa->loadKey($generatedPrivateKey);
            $rsa->setPassword();
            $publickey = $rsa->getPublicKey(CRYPT_RSA_PUBLIC_FORMAT_RAW);
            $this->generateAssignJS(
                "INTERMediatorOnPage.publickey",
                "new biRSAKeyPair('", $publickey['e']->toHex(), "','0','", $publickey['n']->toHex(), "')");
        }
        $localeSign = isset($appLocale) ? $appLocale : ini_get("intl.default_locale");
        setlocale(LC_ALL, $localeSign);
        $localInfo = localeconv();
        if ($localInfo['thousands_sep'] === '') {
            $localInfo['thousands_sep'] = ',';
        }
        $this->generateAssignJS("INTERMediatorOnPage.localeInfo", arrayToJS($localInfo,""));
    }
    
    private function combineScripts($currentDir)
    {
        $jsLibDir = $currentDir . 'lib' . DIRECTORY_SEPARATOR . 'js_lib' . DIRECTORY_SEPARATOR;
        $bi2phpDir = $currentDir . 'lib' . DIRECTORY_SEPARATOR . 'bi2php' . DIRECTORY_SEPARATOR;
        $content = '';
        $content .= file_get_contents($currentDir . 'INTER-Mediator.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-Element.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-Context.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-Lib.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-Calc.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-Page.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-Parts.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-Navi.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-UI.js');
        $content .= ';' . file_get_contents($jsLibDir . 'tinySHA1.js');
        $content .= file_get_contents($jsLibDir . 'sha256.js');
        $content .= file_get_contents($bi2phpDir . 'biBigInt.js');
        $content .= file_get_contents($bi2phpDir . 'biMontgomery.js');
        $content .= file_get_contents($bi2phpDir . 'biRSA.js');
        $content .= file_get_contents($currentDir . 'Adapter_DBServer.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-Events.js');
        $content .= file_get_contents($currentDir . 'INTER-Mediator-DoOnStart.js');
        
        return $content;
    }
}
