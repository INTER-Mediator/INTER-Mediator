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

class GenerateJSCode
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        header('Content-Type: text/javascript;charset="UTF-8"');
        header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
        header('Expires: 0');
        $util = new IMUtil();
        $util->outputSecurityHeaders();
    }

    public function generateAssignJS($variable, $value1, $value2 = '', $value3 = '', $value4 = '', $value5 = '')
    {
        echo "{$variable}={$value1}{$value2}{$value3}{$value4}{$value5};\n";
    }

    public function generateDebugMessageJS($message)
    {
        $q = '"';
        echo "INTERMediatorLog.setDebugMessage({$q}"
            . str_replace("\n", " ", addslashes($message)) . "{$q});\n";
    }

    public function generateErrorMessageJS($message)
    {
        $q = '"';
        echo "INTERMediatorLog.setErrorMessage({$q}"
            . str_replace("\n", " ", addslashes($message)) . "{$q});";
    }

    public function generateInitialJSCode($datasource, $options, $dbspecification, $debug)
    {
        $q = '"';
        $ds = DIRECTORY_SEPARATOR;
        $generatedPrivateKey = null;
        $passPhrase = null;
        $browserCompatibility = null;
        $scriptPathPrefix = null;
        $scriptPathSuffix = null;
        $oAuthProvider = null;
        $oAuthClientID = null;
        $oAuthRedirect = null;
        $themeName = "default";
        $dbClass = null;
        $params = IMUtil::getFromParamsPHPFile(array(
            "generatedPrivateKey", "passPhrase", "browserCompatibility", "scriptPathPrefix", "scriptPathSuffix",
            "oAuthProvider", "oAuthClientID", "oAuthRedirect", "passwordPolicy", "documentRootPrefix", "dbClass",
            "dbDSN", "nonSupportMessageId", "valuesForLocalContext", "themeName", "appLocale", "appCurrency",
            "resetPage", "enrollPage", "serviceServerPort", "serviceServerHost", "activateClientService",
            "followingTimezones", "notUseServiceServer", "serviceServerProtocol", "passwordHash", "alwaysGenSHA2",
            "isSAML", "samlWithBuiltInAuth"
        ), true);
        $generatedPrivateKey = $params["generatedPrivateKey"];
        $passPhrase = $params["passPhrase"];
        $browserCompatibility = $params["browserCompatibility"];
        $scriptPathPrefix = $params["scriptPathPrefix"];
        $scriptPathSuffix = $params["scriptPathSuffix"];
        $oAuthProvider = $params["oAuthProvider"];
        $oAuthClientID = $params["oAuthClientID"];
        $oAuthRedirect = $params["oAuthRedirect"];
        $passwordPolicy = $params["passwordPolicy"];
        $dbClass = $params["dbClass"];
        $dbDSN = isset($options['dsn']) ? $options['dsn']
            : (isset($params['dbDSN']) ? $params["dbDSN"] : '');
        $nonSupportMessageId = $params["nonSupportMessageId"];
        $valuesForLocalContext = $params["valuesForLocalContext"];
        $themeName = is_null($params["themeName"]) ? $themeName : $params["themeName"];
        $appLocale = isset($options['app-locale']) ? $options['app-locale']
            : (isset($params['appLocale']) ? $params["appLocale"] : 'ja_JP');
        $appCurrency = isset($options['app-currency']) ? $options['app-currency']
            : (isset($params['appCurrency']) ? $params["appCurrency"] : 'JP');
        $resetPage = isset($options['authentication']['reset-page']) ? $options['authentication']['reset-page']
            : (isset($params['resetPage']) ? $params["resetPage"] : null);
        $enrollPage = isset($options['authentication']['enroll-page']) ? $options['authentication']['enroll-page']
            : (isset($params['enrollPage']) ? $params["enrollPage"] : null);
        $serviceServerPort = isset($params['serviceServerPort']) ? $params['serviceServerPort'] : "11479";
        $serviceServerHost = (isset($params['serviceServerHost']) && $params['serviceServerHost'])
            ? $params['serviceServerHost'] : false;
        $serviceServerHost = $serviceServerHost ? $serviceServerHost
            : (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : false);
        $serviceServerHost = $serviceServerHost ? $serviceServerHost
            : (isset($_SERVER['HTTP_HOST']) ? parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) : false);
        $serviceServerHost = $serviceServerHost ? $serviceServerHost : 'localhost';
        $serviceServerProtocol = isset($params['serviceServerProtocol']) ? $params['serviceServerProtocol'] : 'ws';
        $notUseServiceServer = (isset($params['notUseServiceServer']) ? boolval($params["notUseServiceServer"]) : false);

        $activateClientService = isset($params['activateClientService']) ? boolval($params['activateClientService']) : false;
        $followingTimezones = isset($params['followingTimezones']) ? boolval($params['followingTimezones']) : false;
        $passwordHash = isset($params['passwordHash']) ? $params['passwordHash'] : 1;
        $passwordHash = ($passwordHash === '2m') ? 1.5 : floatval($passwordHash);
        $alwaysGenSHA2 = isset($params['alwaysGenSHA2']) ? boolval($params['alwaysGenSHA2']) : false;
        $isSAML = isset($options['authentication']['is-saml']) ? $options['authentication']['is-saml']
            : (isset($params['isSAML']) ? boolval($params['isSAML']) : false);
        $samlWithBuiltInAuth = isset($options['authentication']['saml-builtin-auth']) ? $options['authentication']['saml-builtin-auth']
            : (isset($params['samlWithBuiltInAuth']) ? boolval($params['samlWithBuiltInAuth']) : false);

        $serverName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'Not_on_web_server';
        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'Not_on_web_server';
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'Not_on_web_server';

        $hasSyncControl = false;
        foreach ($datasource as $contextDef) {
            if (isset($contextDef['sync-control'])) {
                $hasSyncControl = true;
                break;
            }
        }

        $pathToIM = IMUtil::pathToINTERMediator();
        /*
              * Read the JS programs regarding by the developing or deployed.
              */
        $currentDir = "{$pathToIM}{$ds}src{$ds}js{$ds}";
        if (!file_exists($currentDir . 'INTER-Mediator.min.js')) {
            echo $this->combineScripts($activateClientService && $hasSyncControl);
        } else {
            readfile($currentDir . 'INTER-Mediator.min.js');
        }

        /*
         * Generate the link to the definition file editor
         */
        $relativeToDefFile = '';
        $editorPath = realpath($pathToIM . $ds . 'editors');
        if ($editorPath) { // In case of core only build.
            $defFilePath = realpath($documentRoot . $serverName);
            while (strpos($defFilePath, $editorPath) !== 0 && strlen($editorPath) > 1) {
                $editorPath = dirname($editorPath);
                $relativeToDefFile .= '..' . $ds;
            }
            $relativeToDefFile .= substr($defFilePath, strlen($editorPath) + 1);
            $editorPath = $pathToIM . $ds . 'editors' . $ds . 'defedit.html';
        } else {
            $editorPath = "Editors don't exist.";
        }
        if (file_exists($editorPath)) {
            $relativeToEditor = substr($editorPath, strlen($_SERVER['DOCUMENT_ROOT']));
            $this->generateAssignJS("INTERMediatorOnPage.getEditorPath",
                "function(){return {$q}{$relativeToEditor}?target=$relativeToDefFile{$q};}");
        } else {
            $this->generateAssignJS("INTERMediatorOnPage.getEditorPath",
                "function(){return '';}");
        }
        $relativeToIM = substr($pathToIM, strlen($_SERVER['DOCUMENT_ROOT']));
        $this->generateAssignJS("INTERMediatorOnPage.getPathToIMRoot",
            "function(){return {$q}{$relativeToIM}{$q};}");
        /*
         * from db-class, determine the default key field string
         */
        $defaultKey = null;
        $classBaseName = (isset($dbspecification['db-class']) ? $dbspecification['db-class'] :
            (!is_null($dbClass) ? $dbClass : ''));
        $dbClassName = 'INTERMediator\\DB\\' . $classBaseName;
        $dbInstance = new $dbClassName();
        $dbInstance->setupHandlers($dbDSN);
        if ($dbInstance != null && $dbInstance->specHandler != null) {
            $defaultKey = $dbInstance->specHandler->getDefaultKey();
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
                . filter_var($scriptName)
                . (isset($scriptPathSufix) ? $scriptPathSuffix : '');
        } else {
            $pathToMySelf = filter_var($scriptName);
        }

        $this->generateAssignJS(
            "INTERMediatorOnPage.getEntryPath", "function(){return {$q}{$pathToMySelf}{$q};}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getTheme", "function(){return {$q}",
            isset($options['theme']) ? $options['theme'] : $themeName, "{$q};}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getDataSources", "function(){return ",
            IMUtil::arrayToJSExcluding($datasource, '', array('password')), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsAliases",
            "function(){return ", IMUtil::arrayToJS(isset($options['aliases']) ? $options['aliases'] : array()), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsTransaction",
            "function(){return ", IMUtil::arrayToJS(isset($options['transaction']) ? $options['transaction'] : ''), ";}");
        $this->generateAssignJS("INTERMediatorOnPage.dbClassName", "{$q}{$dbClassName}{$q}");
        $this->generateAssignJS("INTERMediatorOnPage.defaultKeyName", "{$q}{$defaultKey}{$q}");

        $isEmailAsUsernae = isset($options['authentication'])
            && isset($options['authentication']['email-as-username'])
            && $options['authentication']['email-as-username'] === true;
        $this->generateAssignJS(
            "INTERMediatorOnPage.isEmailAsUsername", $isEmailAsUsernae ? "true" : "false");

        $messageClass = IMUtil::getMessageClassInstance();
        $this->generateAssignJS(
            "INTERMediatorOnPage.getMessages",
            "function(){return ", IMUtil::arrayToJS($messageClass->getMessages()), ";}");
        if (isset($options['browser-compatibility'])) {
            $browserCompatibility = $options['browser-compatibility'];
        }
        foreach ($browserCompatibility as $browser => $browserInfo) {
            if (strtolower($browser) !== $browser) {
                $browserCompatibility[strtolower($browser)] = $browserCompatibility[$browser];
                unset($browserCompatibility[$browser]);
            }
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.browserCompatibility",
            "function(){return ", IMUtil::arrayToJS($browserCompatibility), ";}");

        $remoteAddr = filter_var($_SERVER['REMOTE_ADDR']);
        if (is_null($remoteAddr) || $remoteAddr === FALSE) {
            $remoteAddr = '0.0.0.0';
        }
        $clientIdSeed = time() . $remoteAddr . mt_rand();
        $randomSecret = mt_rand();
        $clientId = hash_hmac('sha256', $clientIdSeed, $randomSecret);

        $this->generateAssignJS(
            "INTERMediatorOnPage.clientNotificationIdentifier",
            "function(){return ", IMUtil::arrayToJS($clientId), ";}");

        if ($nonSupportMessageId != "") {
            $this->generateAssignJS(
                "INTERMediatorOnPage.nonSupportMessageId",
                "{$q}{$nonSupportMessageId}{$q}");
        }
        $metadata = json_decode(file_get_contents($pathToIM . $ds . "composer.json"));
        $this->generateAssignJS("INTERMediatorOnPage.metadata",
            "{version:{$q}{$metadata->version}{$q},releasedate:{$q}{$metadata->time}{$q}}");

        if (isset($prohibitDebugMode) && $prohibitDebugMode) {
            $this->generateAssignJS("INTERMediatorLog.debugMode", "false");
        } else {
            $this->generateAssignJS(
                "INTERMediatorLog.debugMode", ($debug === false) ? "false" : $debug);
        }

        if (!is_null($appLocale)) {
            $this->generateAssignJS("INTERMediatorOnPage.appLocale", "{$q}{$appLocale}{$q}");
            $this->generateAssignJS("INTERMediatorLocale",
                "JSON.parse('" . json_encode(Locale\IMLocaleFormatTable::getCurrentLocaleFormat()) . "')");
        }
        if (!is_null($appCurrency)) {
            $this->generateAssignJS("INTERMediatorOnPage.appCurrency", "{$q}{$appCurrency}{$q}");
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
            "INTERMediatorOnPage.authRequiredContext", IMUtil::arrayToJS($requireAuthenticationContext));
        if (!is_null($enrollPage)) {
            $this->generateAssignJS("INTERMediatorOnPage.enrollPageURL", $q, $enrollPage, $q);
        }
        if (!is_null($resetPage)) {
            $this->generateAssignJS("INTERMediatorOnPage.resetPageURL", $q, $resetPage, $q);
        }

        $ldap = new LDAPAuth(); // for PHP 5.2, 5.3
        $this->generateAssignJS(
            "INTERMediatorOnPage.isLDAP", $ldap->isActive ? "true" : "false");
        $this->generateAssignJS(
            "INTERMediatorOnPage.isOAuthAvailable", isset($oAuthProvider) ? "true" : "false");
        $authObj = new OAuthAuth();
        if ($authObj->isActive) {
            $this->generateAssignJS("INTERMediatorOnPage.oAuthClientID",
                $q, $oAuthClientID, $q);
            $this->generateAssignJS("INTERMediatorOnPage.oAuthBaseURL",
                $q, $authObj->oAuthBaseURL(), $q);
            $this->generateAssignJS("INTERMediatorOnPage.oAuthRedirect",
                $q, $oAuthRedirect, $q);
            $this->generateAssignJS("INTERMediatorOnPage.oAuthScope",
                $q, implode(' ', $authObj->infoScope()), $q);
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.isNativeAuth",
            (isset($options['authentication'])
                && isset($options['authentication']['user'])
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
            $rsaClass = IMUtil::phpSecLibClass('phpseclib\Crypt\RSA');
            $rsa = new $rsaClass;
            $rsa->setPassword($passPhrase);
            $rsa->loadKey($generatedPrivateKey);
            $rsa->setPassword();
            $publickey = $rsa->getPublicKey();

            $this->generateAssignJS(
                "INTERMediatorOnPage.publickey",
                "'" . str_replace(array("\r\n", "\r", "\n"), '', $publickey) . "'");
            $this->generateAssignJS("INTERMediatorOnPage.publickeysize", $rsa->getSize());
            if (in_array(sha1($generatedPrivateKey), array(
                    '413351603fa756ecd8270147d1a84e9a2de2a3f9',  // Ver. 5.2
                    '094f61a9db51e0159fb0bf7d02a321d37f29a715',  // Ver. 5.3
                )) && isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '192.168.56.101'
            ) {
                $this->generateDebugMessageJS('Please change the value of $generatedPrivateKey in params.php.');
            }
        }
        if (isset($passwordPolicy)) {
            $this->generateAssignJS(
                "INTERMediatorOnPage.passwordPolicy", $q, $passwordPolicy, $q);
        } else if (isset($options["authentication"])
            && isset($options["authentication"]["password-policy"])
        ) {
            $this->generateAssignJS(
                "INTERMediatorOnPage.passwordPolicy", $q, $options["authentication"]["password-policy"], $q);
        }
        if (isset($options['credit-including'])) {
            $this->generateAssignJS(
                "INTERMediatorOnPage.creditIncluding", $q, $options['credit-including'], $q);
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.isSAML", $isSAML ? 'true' : 'false');
        $this->generateAssignJS(
            "INTERMediatorOnPage.samlWithBuiltInAuth", $samlWithBuiltInAuth ? 'true' : 'false');

        // Initial values for local context
        if (!isset($valuesForLocalContext)) {
            $valuesForLocalContext = array();
        }
        if (isset($options['local-context'])) {
            foreach ($options['local-context'] as $item) {
                $valuesForLocalContext[$item['key']] = $item['value'];
            }
        }
        if (isset($valuesForLocalContext) && is_array($valuesForLocalContext) && count($valuesForLocalContext) > 0) {
            $this->generateAssignJS("INTERMediatorOnPage.initLocalContext", IMUtil::arrayToJS($valuesForLocalContext));
        }
        $sss = ServiceServerProxy::instance()->isActive();
        $this->generateAssignJS("INTERMediatorOnPage.serviceServerStatus", $sss ? "true" : "false");

        $activateClientService = $activateClientService && $hasSyncControl;
        $this->generateAssignJS("INTERMediatorOnPage.activateClientService",
            ($activateClientService && !$notUseServiceServer) ? "true" : "false");
        $this->generateAssignJS("INTERMediatorOnPage.serviceServerURL",
            "{$q}{$serviceServerProtocol}://{$serviceServerHost}:{$serviceServerPort}{$q}");
        $this->generateAssignJS("INTERMediatorOnPage.serverDefaultTimezone", $q, date_default_timezone_get(), $q);
        $this->generateAssignJS("INTERMediatorOnPage.isFollowingTimezone", $followingTimezones ? "true" : "false");
        $this->generateAssignJS("INTERMediatorOnPage.passwordHash", $passwordHash);
        $this->generateAssignJS("INTERMediatorOnPage.alwaysGenSHA2", $alwaysGenSHA2 ? "true" : "false");
    }

    private function combineScripts($isSocketIO): string
    {
        $imPath = IMUtil::pathToINTERMediator();
        $jsCodeDir = $imPath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
        $nodeModuleDir = $imPath . DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR;
        $content = '';
        $content .= $this->readJSSource($nodeModuleDir . 'jsencrypt/bin/jsencrypt.js');
        $content .= $this->readJSSource($nodeModuleDir . 'jssha/dist/sha.js');
        if ($isSocketIO) {
            $content .= $this->readJSSource($nodeModuleDir . 'socket.io-client/dist/socket.io.js');
        }
        $content .= "\n";
        $content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-formatter/index.js');
        //$content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-locale/index.js');
        $content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-queue/index.js');
        $content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-nodegraph/index.js');
        $content .= $this->readJSSource($nodeModuleDir . 'inter-mediator-expressionparser/index.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Page.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-ContextPool.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Context.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-LocalContext.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Lib.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Element.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Calc.js');
        $content .= $this->readJSSource($jsCodeDir . 'Adapter_DBServer.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Navi.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-UI.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Log.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Events.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-DoOnStart.js');

        return $content;
    }

    private function readJSSource($filename)
    {
        $content = file_get_contents($filename);
        $pos = strpos($content, "@@IM@@IgnoringRestOfFile");
        if ($pos !== false) {
            $content = substr($content, 0, $pos) . "\n";
        }
        while (($pos = strpos($content, "@@IM@@IgnoringNextLine")) !== false) {
            $prePos = $pos;
            for ($i = $pos; $i > 0; $i--) {
                if (substr($content, $i, 1) === "\n") {
                    $prePos = $i;
                    break;
                }
            }
            $postPos = strpos($content, "\n", $pos);
            $postPos = strpos($content, "\n", $postPos + 1);
            if ($i >= 0) {
                $content = substr($content, 0, $prePos + 1) . substr($content, $postPos + 1);
            }
        }
        return $content;
    }
}
