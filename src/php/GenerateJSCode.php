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

/**
 *
 */
class GenerateJSCode
{
    /**
     *
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: text/javascript;charset="UTF-8"');
        header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
        header('Expires: 0');
        $util = new IMUtil();
        $util->outputSecurityHeaders();
    }

    /**
     * @param string $variable
     * @param string $value1
     * @param string $value2
     * @param string $value3
     * @param string $value4
     * @param string $value5
     * @return void
     */
    public function generateAssignJS(string $variable, string $value1, string $value2 = '', string $value3 = '', string $value4 = '', string $value5 = ''): void
    {
        echo "{$variable}={$value1}{$value2}{$value3}{$value4}{$value5};\n";
    }

    /**
     * @param string $message
     * @return void
     */
    public function generateDebugMessageJS(string $message): void
    {
        $q = '"';
        echo "INTERMediatorLog.setDebugMessage({$q}"
            . str_replace("\n", " ", addslashes($message)) . "{$q});\n";
    }

    /**
     * @param string $message
     * @return void
     */
    public function generateErrorMessageJS(string $message): void
    {
        $q = '"';
        echo "INTERMediatorLog.setErrorMessage({$q}"
            . str_replace("\n", " ", addslashes($message)) . "{$q});";
    }

    /**
     * @param array|null $dataSource
     * @param array|null $options
     * @param array|null $dbSpecification
     * @param int $debug
     * @return void
     */
    public function generateInitialJSCode(?array $dataSource, ?array $options, ?array $dbSpecification, int $debug): void
    {
        $q = '"';
        $ds = DIRECTORY_SEPARATOR;

        $browserCompatibility = Params::getParameterValue("browserCompatibility", null);
        $callURL = Params::getParameterValue("callURL", null);
        $scriptPathPrefix = Params::getParameterValue("scriptPathPrefix", null);
        $scriptPathSuffix = Params::getParameterValue("scriptPathSuffix", null);
        $oAuthProvider = Params::getParameterValue("oAuthProvider", null);
        $oAuthClientID = Params::getParameterValue("oAuthClientID", null);
        $oAuthRedirect = Params::getParameterValue("oAuthRedirect", null);
        $passwordPolicy = Params::getParameterValue("passwordPolicy", null);
        $dbClass = Params::getParameterValue("dbClass", null);
        $dbDSN = Params::getParameterValue("dbDSN", '');
        $nonSupportMessageId = Params::getParameterValue("nonSupportMessageId", null);
        $valuesForLocalContext = Params::getParameterValue("valuesForLocalContext", null);
        $themeName = Params::getParameterValue("themeName", 'default');
        $appLocale = Params::getParameterValue("appLocale", 'ja_JP');
        $appCurrency = Params::getParameterValue("appCurrency", 'JP');
        $resetPage = Params::getParameterValue("resetPage", null);
        $enrollPage = Params::getParameterValue("enrollPage", null);
        $serviceServerPort = Params::getParameterValue("serviceServerPort", "11478");
        $serviceServerHost = Params::getParameterValue("serviceServerHost", null);
        $serviceServerProtocol = Params::getParameterValue("serviceServerProtocol", 'ws');
        $notUseServiceServer = Params::getParameterValue("notUseServiceServer", null);
        $activateClientService = Params::getParameterValue("activateClientService", null);
        $followingTimezones = Params::getParameterValue("followingTimezones", true);
        $passwordHash = Params::getParameterValue("passwordHash", 1);
        $alwaysGenSHA2 = Params::getParameterValue("alwaysGenSHA2", null);
        $isSAML = Params::getParameterValue("isSAML", null);
        $samlWithBuiltInAuth = Params::getParameterValue("samlWithBuiltInAuth", null);
        $credentialCookieDomain = Params::getParameterValue('credentialCookieDomain', NULL);
        $prohibitDebugMode = Params::getParameterValue('prohibitDebugMode', false);
        $resetPage = $options['authentication']['reset-page'] ?? $resetPage ?? null;
        $enrollPage = $options['authentication']['enroll-page'] ?? $enrollPage ?? null;
        $serviceServerHost = $serviceServerHost ?? $_SERVER['SERVER_ADDR'] ?? null;
        if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) {
            if (strpos($_SERVER['HTTP_HOST'], ':') === false) {
                $serviceServerHost = $serviceServerHost ?? $_SERVER['HTTP_HOST'] ?? null;
            } else {
                $serviceServerHost = $serviceServerHost ?? parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) ?? null;
            }
        }
        $serviceServerHost = $serviceServerHost ?? 'localhost';
        $passwordHash = ($passwordHash === '2m') ? 1.5 : floatval($passwordHash);
        $isSAML = $options['authentication']['is-saml'] ?? $isSAML ?? false;
        $samlWithBuiltInAuth = $options['authentication']['saml-builtin-auth'] ?? $samlWithBuiltInAuth ?? false;
        $activateGenerator = Params::getParameterValue("activateGenerator", false);
        $extraButtons = Params::getParameterValue("extraButtons", []);

        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'Not_on_web_server';

        $hasSyncControl = false;
        foreach ($dataSource as $contextDef) {
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
            $defFilePath = realpath($documentRoot . $_SERVER['SCRIPT_NAME']);
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
        $classBaseName = $dbSpecification['db-class'] ?? $dbClass ?? '';
        $dbClassName = 'INTERMediator\\DB\\' . $classBaseName;
        $dbInstance = new $dbClassName();
        $dbInstance->setupHandlers($dbDSN);
        if ($dbInstance->specHandler != null) {
            $defaultKey = $dbInstance->specHandler->getDefaultKey();
        }
        if ($defaultKey !== null) {
            $items = array();
            foreach ($dataSource as $context) {
                if (!array_key_exists('key', $context)) {
                    $context['key'] = $defaultKey;
                }
                $items[] = $context;
            }
            $dataSource = $items;
        }

        /*
         * Determine the uri of myself
         */
        if (isset($callURL)) {
            $pathToMySelf = $callURL;
        } else if (isset($scriptPathPrefix) || isset($scriptPathSuffix)) {
            $pathToMySelf = ($scriptPathPrefix ?? '') . ($_SERVER['SCRIPT_NAME'] ?? null) . ($scriptPathSuffix ?? '');
        } else {
            $pathToMySelf = IMUtil::relativePath(
                parse_url($_SERVER['HTTP_REFERER'] ?? null, PHP_URL_PATH), $_SERVER['SCRIPT_NAME'] ?? null);
        }
        $qStr = isset($_SERVER['QUERY_STRING']) ? "?{$_SERVER['QUERY_STRING']}" : '';

        if ($qStr == '?') {
            $qStr = '';
        }

        $this->generateAssignJS(
            "INTERMediatorOnPage.getEntryPath", "function(){return {$q}{$pathToMySelf}{$qStr}{$q};}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getTheme", "function(){return {$q}",
            $options['theme'] ?? $themeName, "{$q};}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getDataSources", "function(){return ",
            IMUtil::arrayToJSExcluding($dataSource, '', array('password')), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsAliases",
            "function(){return ", IMUtil::arrayToJS($options['aliases'] ?? array()), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsTransaction",
            "function(){return ", IMUtil::stringToJS($options['transaction'] ?? ''), ";}");
        $this->generateAssignJS("INTERMediatorOnPage.dbClassName", "{$q}{$dbClassName}{$q}");
        $this->generateAssignJS("INTERMediatorOnPage.defaultKeyName", "{$q}{$defaultKey}{$q}");

        $isEmailAsUsernae = isset($options['authentication']['email-as-username']) && $options['authentication']['email-as-username'] === true;
        $this->generateAssignJS(
            "INTERMediatorOnPage.isEmailAsUsername", $isEmailAsUsernae ? "true" : "false");

        $messageClass = IMUtil::getMessageClassInstance();
        $this->generateAssignJS(
            "INTERMediatorOnPage.getMessages",
            "function(){return ", IMUtil::arrayToJS($messageClass->getMessages()), ";}");
        $terms = $messageClass->getTerms($options);
        $this->generateAssignJS(
            "INTERMediatorOnPage.getTerms",
            "function(){return ", (count($terms) > 0) ? IMUtil::arrayToJS($terms) : "null", ";}");

        if (isset($options['browser-compatibility'])) {
            $browserCompatibility = $options['browser-compatibility'];
        }
        foreach ($browserCompatibility as $browser => $browserInfo) {
            if (strtolower($browser) !== $browser) {
                $browserCompatibility[strtolower($browser)] = $browserInfo;
                unset($browserCompatibility[$browser]);
            }
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.browserCompatibility",
            "function(){return ", IMUtil::arrayToJS($browserCompatibility), ";}");

        $remoteAddr = $_SERVER['REMOTE_ADDR'];
        if (is_null($remoteAddr) || $remoteAddr === FALSE) {
            $remoteAddr = '0.0.0.0';
        }
        $clientIdSeed = time() . $remoteAddr . mt_rand();
        $randomSecret = mt_rand();
        $clientId = hash_hmac('sha256', $clientIdSeed, $randomSecret);

        $this->generateAssignJS(
            "INTERMediatorOnPage.clientNotificationIdentifier",
            "function(){return ", IMUtil::stringToJS($clientId), ";}");

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
                "INTERMediatorLog.debugMode", !$debug ? "false" : $debug);
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
        foreach ($dataSource as $aContext) {
            if (isset($aContext['authentication'])) {
                $boolValue = "true";
                $requireAuthenticationContext[] = $aContext['name'];
            }
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.requireAuthentication", $boolValue);
        $this->generateAssignJS(
            "INTERMediatorOnPage.credentialCookieDomain", $q, ($credentialCookieDomain ?? ''), $q);
        $this->generateAssignJS(
            "INTERMediatorOnPage.authRequiredContext", IMUtil::arrayToJS($requireAuthenticationContext));
        if (!is_null($enrollPage)) {
            $this->generateAssignJS("INTERMediatorOnPage.enrollPageURL", $q, $enrollPage, $q);
        }
        if (!is_null($resetPage)) {
            $this->generateAssignJS("INTERMediatorOnPage.resetPageURL", $q, $resetPage, $q);
        }
        $this->generateAssignJS(
            "INTERMediatorOnPage.extraButtons", IMUtil::arrayToJS($extraButtons));

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
        };

        $authStoringValue = $options['authentication']['storing']
            ?? Params::getParameterValue("authStoring", 'credential');
        $this->generateAssignJS("INTERMediatorOnPage.authStoring", $q, $authStoringValue, $q);
        $authExpiredValue = $options['authentication']['authexpired']
            ?? Params::getParameterValue("authExpired", 3600);
        $this->generateAssignJS("INTERMediatorOnPage.authExpired", intval($authExpiredValue));
        $realmValue = $options['authentication']['realm']
            ?? Params::getParameterValue("authRealm", '');
        $this->generateAssignJS("INTERMediatorOnPage.realm", $q, $realmValue, $q);
        $req2FAValue = $options['authentication']['is-required-2FA']
            ?? Params::getParameterValue("isRequired2FA", '');
        $this->generateAssignJS("INTERMediatorOnPage.isRequired2FA", $req2FAValue ? "true" : "false");
        $digitsOf2FACodeValue = $options['authentication']['digits-of-2FA-Code']
            ?? Params::getParameterValue("digitsOf2FACode", 4);
        $this->generateAssignJS("INTERMediatorOnPage.digitsOf2FACode", intval($digitsOf2FACodeValue));

        if (isset($passwordPolicy)) {
            $this->generateAssignJS(
                "INTERMediatorOnPage.passwordPolicy", $q, $passwordPolicy, $q);
        } else if (isset($options["authentication"]["password-policy"])
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

        $this->generateAssignJS("INTERMediatorOnPage.activateClientService",
            ($activateClientService && $hasSyncControl && !$notUseServiceServer) ? "true" : "false");
        $this->generateAssignJS("INTERMediatorOnPage.useServiceServer",
            !$notUseServiceServer ? "true" : "false");
        $this->generateAssignJS("INTERMediatorOnPage.serviceServerURL",
            "{$q}{$serviceServerProtocol}://{$serviceServerHost}:{$serviceServerPort}{$q}");
        $this->generateAssignJS("INTERMediatorOnPage.serverDefaultTimezone", $q, date_default_timezone_get(), $q);
        $this->generateAssignJS("INTERMediatorOnPage.isFollowingTimezone", $followingTimezones ? "true" : "false");
        $this->generateAssignJS("INTERMediatorOnPage.passwordHash", $passwordHash);
        $this->generateAssignJS("INTERMediatorOnPage.alwaysGenSHA2", $alwaysGenSHA2 ? "true" : "false");
        $this->generateAssignJS("INTERMediatorOnPage.serverPHPVersionFull", $q, PHP_VERSION, $q);
        $this->generateAssignJS("INTERMediatorOnPage.serverPHPVersion", PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION);
        if ($activateGenerator) {
            $this->generateAssignJS("INTERMediatorOnPage.activateMaintenanceCall", "true");
        }

        $this->generateAssignJS("INTERMediatorOnPage.authPanelTitle",
            $q, Params::getParameterValue('authPanelTitle', ""), $q);
        $this->generateAssignJS("INTERMediatorOnPage.authPanelTitle2FA", $q,
            Params::getParameterValue('authPanelTitle2FA', ""), $q);
        $this->generateAssignJS("INTERMediatorOnPage.authPanelExp",
            $q, Params::getParameterValue('authPanelExp', ""), $q);
        $this->generateAssignJS("INTERMediatorOnPage.authPanelExp2FA",
            $q, Params::getParameterValue('authPanelExp2FA', ""), $q);
    }

    /**
     * @param bool $isSocketIO
     * @return string
     */
    private function combineScripts(bool $isSocketIO): string
    {
        $imPath = IMUtil::pathToINTERMediator();
        $jsCodeDir = $imPath . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
        $nodeModuleDir = $imPath . DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR;
        $content = '';
        // $content .= $this->readJSSource($nodeModuleDir . 'jsencrypt/bin/jsencrypt.js');
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

    /**
     * @param string $filename
     * @return string
     */
    private function readJSSource(string $filename): string
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
