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

use INTERMediator\Auth\OAuthAuth;
use INTERMediator\DB\DBClass;
use Exception;

/**
 * Class GenerateJSCode
 * Generates JavaScript code for INTER-Mediator front-end initialization, error/debug messages, and configuration.
 */
class GenerateJSCode
{
    /** GenerateJSCode constructor.
     * Starts a session if not started, sets JS headers, and outputs security headers.
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            session_write_close();
        }
        header('Content-Type: text/javascript;charset="UTF-8"');
        header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
        header('Expires: 0');
        $util = new IMUtil();
        $util->outputSecurityHeaders();
    }

    /** Outputs a JavaScript assignment statement.
     * @param string $variable The JavaScript variable name.
     * @param string|int|float $value1 The first value to assign.
     * @param string|int|float $value2 Optional additional value.
     * @param string|int|float $value3 Optional additional value.
     * @param string|int|float $value4 Optional additional value.
     * @param string|int|float $value5 Optional additional value.
     * @return void
     */
    public function generateAssignJS(string           $variable, string|int|float $value1,
                                     string|int|float $value2 = '', string|int|float $value3 = '',
                                     string|int|float $value4 = '', string|int|float $value5 = ''): void
    {
        echo "{$variable}={$value1}{$value2}{$value3}{$value4}{$value5};\n";
    }

    /** Returns the JavaScript string literal (with double quotes) of the value.
     * @param mixed $value The value to convert.
     * @return string JavaScript string literal.
     */
    private function jsStr(mixed $value): string
    {
        return strval(json_encode(is_scalar($value) ? strval($value) : '',
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
            | JSON_INVALID_UTF8_SUBSTITUTE));
    }

    /** Returns the message string escaped for a JavaScript string literal.
     * @param string $message The message.
     * @return string Escaped message.
     */
    private function escapeMessage(string $message): string
    {
        return IMUtil::valueForJSInsert(str_replace(["\r\n", "\n", "\r"], " ", $message));
    }

    /** Outputs a JavaScript debug message using INTERMediatorLog.setDebugMessage().
     * @param string $message The debug message to output.
     * @return void
     */
    public function generateDebugMessageJS(string $message): void
    {
        $q = '"';
        echo "INTERMediatorLog.setDebugMessage({$q}" . $this->escapeMessage($message) . "{$q});\n";
    }

    /** Outputs a JavaScript error message using INTERMediatorLog.setErrorMessage().
     * @param string $message The error message to output.
     * @return void
     */
    public function generateErrorMessageJS(string $message): void
    {
        $q = '"';
        echo "INTERMediatorLog.setErrorMessage({$q}" . $this->escapeMessage($message) . "{$q});\n";
    }

    /**
     * Generates the initial JavaScript code for INTER-Mediator, including configuration, OAuth, theme, authentication, etc.
     * @param array<array-key, mixed>|null $dataSource Data source definitions for contexts.
     * @param array<array-key, mixed>|null $options Options for INTER-Mediator.
     * @param array<array-key, mixed>|null $dbSpecification Database specification.
     * @param int|false $debug Debug mode level.
     * @return void
     * @throws Exception
     */
    public function generateInitialJSCode(?array $dataSource, ?array $options, ?array $dbSpecification, int|false $debug): void
    {
        $ds = DIRECTORY_SEPARATOR;
        $dataSource = $dataSource ?? [];
        $options = $options ?? [];

        $browserCompatibility = Params::getParameterValue("browserCompatibility", "*");
        $callURL = Params::getParameterValue("callURL", null);
        $scriptPathPrefix = Params::getParameterValue("scriptPathPrefix", null);
        $scriptPathSuffix = Params::getParameterValue("scriptPathSuffix", null);
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
        $samlWithBuiltInAuth = Params::getParameterValue("samlWithBuiltInAuth", null);
        $credentialCookieDomain = Params::getParameterValue('credentialCookieDomain', NULL);
        $prohibitDebugMode = Params::getParameterValue('prohibitDebugMode', false);
        $resetPage = $options['authentication']['reset-page'] ?? $resetPage ?? null;
        $enrollPage = $options['authentication']['enroll-page'] ?? $enrollPage ?? null;
        $serviceServerHost = $serviceServerHost ?? $_SERVER['SERVER_ADDR'] ?? null;
        if (!empty($_SERVER['HTTP_HOST'])) {
            if (!str_contains($_SERVER['HTTP_HOST'], ':')) {
                $serviceServerHost = $serviceServerHost ?? $_SERVER['HTTP_HOST'];
            } else {
                $serviceServerHost = $serviceServerHost ?? parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST) ?? null;
            }
        }
        $serviceServerHost = $serviceServerHost ?? 'localhost';
        $passwordHash = ($passwordHash === '2m') ? 1.5 : floatval($passwordHash);
        $samlWithBuiltInAuth = $options['authentication']['saml-builtin-auth'] ?? $samlWithBuiltInAuth ?? false;
        $activateGenerator = Params::getParameterValue("activateGenerator", false);
        $extraButtons = Params::getParameterValue("extraButtons", []);

        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'Not_on_web_server';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        $hasSyncControl = false;
        foreach ($dataSource as $contextDef) {
            if (isset($contextDef['sync-control'])) {
                $hasSyncControl = true;
                break;
            }
        }

        $pathToIM = IMUtil::pathToINTERMediator();
        // Read the JS programs regarding by the developing or deployed.
        $currentDir = "{$pathToIM}{$ds}src{$ds}js{$ds}";
        if (!file_exists($currentDir . 'INTER-Mediator.min.js')) {
            echo $this->combineScripts($activateClientService && $hasSyncControl);
        } else {
            readfile($currentDir . 'INTER-Mediator.min.js');
        }

        // OAuth Provider Information
        $oAuth = Params::getParameterValue("oAuth", null);
        $clientOAuthParams = [];
        $isOAuthAvailable = false;
        if (is_array($oAuth)) {
            foreach ($oAuth as $provider => $info) {
                if ($provider) {
                    $isOAuthAvailable = true;
                }
                $clientOAuthParams[$provider] = ['AuthButton' => $info['AuthButton'] ?? ''];
                if (isset($info['Behavior'])) {
                    $clientOAuthParams[$provider]['Behavior'] = $info['Behavior'];
                }
                $authObj = new OAuthAuth(strval($provider), true);
                $clientOAuthParams[$provider]['AuthURL'] = $authObj->getAuthRequestURL();
                $errorMessage = $authObj->errorMessages();
                if (!empty($errorMessage)) {
                    unset($clientOAuthParams[$provider]);
                    $this->generateErrorMessageJS($errorMessage);
                }
            }
        }
        if (is_array($oAuth)) {
            $this->generateAssignJS(
                "IMLibAuthenticationUI.isOAuthAvailable", $isOAuthAvailable ? "true" : "false");
            $this->generateAssignJS("IMLibAuthentication.oAuthParams", "[]");
            foreach ($clientOAuthParams as $provider => $info) {
                $providerStr = $this->jsStr($provider);
                $this->generateAssignJS("IMLibAuthentication.oAuthParams[{$providerStr}]", "[]");
                $this->generateAssignJS("IMLibAuthentication.oAuthParams[{$providerStr}][\"AuthButton\"]",
                    $this->jsStr($info['AuthButton']));
                $this->generateAssignJS("IMLibAuthentication.oAuthParams[{$providerStr}][\"AuthURL\"]",
                    $this->jsStr($info['AuthURL']));
                if (isset($info['Behavior'])) {
                    $this->generateAssignJS("IMLibAuthentication.oAuthParams[{$providerStr}][\"Behavior\"]",
                        $this->jsStr($info['Behavior']));
                }
            }
        }

        // Generate the link to the definition file editor
        $relativeToDefFile = '';
        $editorPath = realpath($pathToIM . $ds . 'editors');
        if ($editorPath) { // In the case of core-only build.
            $defFilePath = strval(realpath($documentRoot . $scriptName));
            while (!str_starts_with($defFilePath, rtrim($editorPath, $ds) . $ds)
                && dirname($editorPath) !== $editorPath) {
                $editorPath = dirname($editorPath);
                $relativeToDefFile .= '..' . $ds;
            }
            $relativeToDefFile .= substr($defFilePath, strlen(rtrim($editorPath, $ds)) + 1);
            $editorPath = $pathToIM . $ds . 'editors' . $ds . 'defedit.html';
        } else {
            $editorPath = "Editors don't exist.";
        }
        if (file_exists($editorPath)) {
            $relativeToEditor = substr($editorPath, strlen($documentRoot));
            $this->generateAssignJS("INTERMediatorOnPage.getEditorPath",
                "function(){return " . $this->jsStr("{$relativeToEditor}?target={$relativeToDefFile}") . ";}");
        } else {
            $this->generateAssignJS("INTERMediatorOnPage.getEditorPath",
                "function(){return '';}");
        }
        $relativeToIM = substr($pathToIM, strlen($documentRoot));
        $this->generateAssignJS("INTERMediatorOnPage.getPathToIMRoot",
            "function(){return " . $this->jsStr($relativeToIM) . ";}");

        // from db-class, determine the default key field string
        $defaultKey = null;
        $classBaseName = $dbSpecification['db-class'] ?? $dbClass ?? '';
        $dbClassName = 'INTERMediator\\DB\\' . $classBaseName;
        if (is_subclass_of($dbClassName, DBClass::class)) {
            $dbInstance = new $dbClassName();
            $dbInstance->setupHandlers($dbDSN);
            if ($dbInstance->specHandler != null) {
                $defaultKey = $dbInstance->specHandler->getDefaultKey();
            }
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

        // Determine the uri of myself
        if (isset($callURL)) {
            $pathToMySelf = $callURL;
        } else if (isset($scriptPathPrefix) || isset($scriptPathSuffix)) {
            $pathToMySelf = ($scriptPathPrefix ?? '') . $scriptName . ($scriptPathSuffix ?? '');
        } else {
            $theURL = strval(parse_url(strval($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_PATH));
            $pathToMySelf = IMUtil::relativePath($theURL, $scriptName);
        }
        $qStr = isset($_SERVER['QUERY_STRING']) ? "?{$_SERVER['QUERY_STRING']}" : '';

        if ($qStr == '?') {
            $qStr = '';
        }

        $this->generateAssignJS(
            "INTERMediatorOnPage.getEntryPath", "function(){return " . $this->jsStr("{$pathToMySelf}{$qStr}") . ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getTheme", "function(){return ",
            $this->jsStr($options['theme'] ?? $themeName), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getDataSources", "function(){return ",
            IMUtil::arrayToJSExcluding($dataSource, '', array('password')), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsAliases",
            "function(){return ", IMUtil::arrayToJS($options['aliases'] ?? array()), ";}");
        $this->generateAssignJS(
            "INTERMediatorOnPage.getOptionsTransaction",
            "function(){return ", IMUtil::stringToJS($options['transaction'] ?? ''), ";}");
        $this->generateAssignJS("INTERMediatorOnPage.dbClassName", $this->jsStr($dbClassName));
        $this->generateAssignJS("INTERMediatorOnPage.defaultKeyName", $this->jsStr($defaultKey));

        $isEmailAsUsername = isset($options['authentication']['email-as-username']) && $options['authentication']['email-as-username'] === true;
        $this->generateAssignJS(
            "IMLibAuthenticationUI.isEmailAsUsername", $isEmailAsUsername ? "true" : "false");

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
        if (/* $browserCompatibility === "*" || */ !is_array($browserCompatibility)) {
            $this->generateAssignJS("INTERMediatorOnPage.browserCompatibility", "function(){return '*';}");
        } else {
            foreach ($browserCompatibility as $browser => $browserInfo) {
                if (strtolower($browser) !== $browser) {
                    $browserCompatibility[strtolower($browser)] = $browserInfo;
                    unset($browserCompatibility[$browser]);
                }
            }
            $this->generateAssignJS(
                "INTERMediatorOnPage.browserCompatibility",
                "function(){return ", IMUtil::arrayToJS($browserCompatibility), ";}");
        }
        $clientId = bin2hex(random_bytes(32));

        $this->generateAssignJS(
            "INTERMediatorOnPage.clientNotificationIdentifier",
            "function(){return ", IMUtil::stringToJS($clientId), ";}");

        if ($nonSupportMessageId != "") {
            $this->generateAssignJS(
                "INTERMediatorOnPage.nonSupportMessageId",
                $this->jsStr($nonSupportMessageId));
        }
        $composerFile = $pathToIM . $ds . "composer.json";
        $fileContents = file_exists($composerFile) ? file_get_contents($composerFile) : false;
        $metadata = $fileContents ? json_decode($fileContents, true) : null;
        $this->generateAssignJS("INTERMediatorOnPage.metadata",
            "{version:" . $this->jsStr($metadata['version'] ?? '')
            . ",releasedate:" . $this->jsStr($metadata['time'] ?? '') . "}");

        $isDebugMode = !(isset($prohibitDebugMode) && $prohibitDebugMode) && $debug;
        $this->generateAssignJS("INTERMediatorLog.debugMode", $isDebugMode ? intval($debug) : "false");

        if (!is_null($appLocale)) {
            $this->generateAssignJS("INTERMediatorOnPage.appLocale", $this->jsStr($appLocale));
            $this->generateAssignJS("INTERMediatorLocale",
                strval(json_encode(Locale\IMLocaleFormatTable::getCurrentLocaleFormat(),
                    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE)));
        }
        if (!is_null($appCurrency)) {
            $this->generateAssignJS("INTERMediatorOnPage.appCurrency", $this->jsStr($appCurrency));
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
            "IMLibAuthentication.requireAuthentication", $boolValue);
        $this->generateAssignJS(
            "IMLibAuthentication.credentialCookieDomain", $this->jsStr($credentialCookieDomain ?? ''));
//        $this->generateAssignJS(
//            "INTERMediatorOnPage.authRequiredContext", IMUtil::arrayToJS($requireAuthenticationContext));
        if (!is_null($enrollPage)) {
            $this->generateAssignJS("IMLibAuthenticationUI.enrollPageURL", $this->jsStr($enrollPage));
        }
        if (!is_null($resetPage)) {
            $this->generateAssignJS("IMLibAuthenticationUI.resetPageURL", $this->jsStr($resetPage));
        }
        $this->generateAssignJS(
            "IMLibAuthenticationUI.extraButtons", IMUtil::arrayToJS($extraButtons));

        $authStoringValue = $options['authentication']['storing']
            ?? Params::getParameterValue("authStoring", 'credential');
        if (trim(strtolower($authStoringValue)) === 'passkey') {
            $authStoringValue = 'credential';
            $this->generateAssignJS("IMLibAuthenticationUI.isPasskey", "true");
        }
        $isPasskeyOnlyOnAuth = $options['authentication']['passkey-only-on-auth']
            ?? Params::getParameterValue("isPasskeyOnlyOnAuth", false);
        $this->generateAssignJS("IMLibAuthenticationUI.isPasskeyOnlyOnAuth",
            $isPasskeyOnlyOnAuth ? "true" : "false");
        $isAddClassAuthn = $options['authentication']['add-class-authn']
            ?? Params::getParameterValue("isAddClassAuthn", false);
        $this->generateAssignJS("IMLibAuthenticationUI.isAddClassAuthn",
            $isAddClassAuthn ? "true" : "false");
        $isPasskeyErrorAlerting = $options['authentication']['passkey-error-alerting']
            ?? Params::getParameterValue("isPasskeyErrorAlerting", false);
        $this->generateAssignJS("IMLibAuthenticationUI.isPasskeyErrorAlerting",
            $isPasskeyErrorAlerting ? "true" : "false");

        $this->generateAssignJS("IMLibAuthentication.authStoring", $this->jsStr($authStoringValue));
        $this->generateAssignJS("IMLibAuthentication.authExpired",
            intval($options['authentication']['authexpired']
                ?? Params::getParameterValue("authExpired", 3600)));
        $this->generateAssignJS("IMLibAuthentication.realm", $this->jsStr(
            $options['authentication']['realm']
            ?? Params::getParameterValue("authRealm", '')));
        $this->generateAssignJS("IMLibAuthenticationUI.isRequired2FA",
            ($options['authentication']['is-required-2FA']
                ?? Params::getParameterValue("isRequired2FA", false)) ? "true" : "false");
        $is2FATesting = !!Params::getParameterValue("fixed2FACode", '');
        $method2FA = $is2FATesting ? 'testing' : strtolower($options['authentication']['method-2FA']
            ?? Params::getParameterValue("method2FA", 'authenticator'));
        $this->generateAssignJS("IMLibAuthenticationUI.method2FA", $this->jsStr($method2FA));
        $this->generateAssignJS("IMLibAuthenticationUI.isPassThrough2FA",
            ($options['authentication']['is-pass-through-2FA']
                ?? Params::getParameterValue("isPassThrough2FA", true)) ? "true" : "false");
        $digitsOf2FACodeValue = $options['authentication']['digits-of-2FA-Code']
            ?? Params::getParameterValue("digitsOf2FACode", ($method2FA === 'email') ? 4 : 6);
        $isFix2FACodeLen = strlen(Params::getParameterValue("fixed2FACode", ''));
        $this->generateAssignJS("IMLibAuthenticationUI.digitsOf2FACode", intval(
            $is2FATesting ? $isFix2FACodeLen : $digitsOf2FACodeValue));

        if (isset($passwordPolicy)) {
            $this->generateAssignJS(
                "IMLibAuthenticationUI.passwordPolicy", $this->jsStr($passwordPolicy));
        } else if (isset($options["authentication"]["password-policy"])
        ) {
            $this->generateAssignJS(
                "IMLibAuthenticationUI.passwordPolicy", $this->jsStr($options["authentication"]["password-policy"]));
        }
        if (isset($options['credit-including'])) {
            $this->generateAssignJS(
                "INTERMediatorOnPage.creditIncluding", $this->jsStr($options['credit-including']));
        }
        $isSAML = $options['authentication']['is-saml'] ?? Params::getParameterValue("isSAML", null) ?? false;
        $this->generateAssignJS(
            "IMLibAuthentication.isSAML", $isSAML ? 'true' : 'false');
        $this->generateAssignJS(
            "IMLibAuthenticationUI.samlWithBuiltInAuth", $samlWithBuiltInAuth ? 'true' : 'false');

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
            $this->jsStr("{$serviceServerProtocol}://{$serviceServerHost}:{$serviceServerPort}"));
        $this->generateAssignJS("INTERMediatorOnPage.serverDefaultTimezone", $this->jsStr(date_default_timezone_get()));
        $this->generateAssignJS("INTERMediatorOnPage.isFollowingTimezone", $followingTimezones ? "true" : "false");
        $this->generateAssignJS("IMLibAuthentication.passwordHash", floatval($passwordHash));
        $this->generateAssignJS("IMLibAuthentication.alwaysGenSHA2", $alwaysGenSHA2 ? "true" : "false");
        if ($isDebugMode) {
            $this->generateAssignJS("INTERMediatorOnPage.serverPHPVersionFull", $this->jsStr(PHP_VERSION));
        }
        $this->generateAssignJS("INTERMediatorOnPage.serverPHPVersion", PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION);
        if ($activateGenerator) {
            $this->generateAssignJS("INTERMediatorOnPage.activateMaintenanceCall", "true");
        }

        $this->generateAssignJS("IMLibAuthenticationUI.authPanelTitle",
            $this->jsStr(Params::getParameterValue('authPanelTitle', "")));
        $this->generateAssignJS("IMLibAuthenticationUI.authPanelTitle2FA",
            $this->jsStr(Params::getParameterValue('authPanelTitle2FA', "")));
        $this->generateAssignJS("IMLibAuthenticationUI.authPanelExp",
            $this->jsStr(Params::getParameterValue('authPanelExp', "")));
        $this->generateAssignJS("IMLibAuthenticationUI.authPanelExp2FA",
            $this->jsStr(Params::getParameterValue('authPanelExp2FA', "")));
    }

    /** Combines and returns the contents of required JavaScript files as a string.
     * @param bool $isSocketIO Whether to include Socket.IO script.
     * @return string The combined JavaScript code.
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
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-Auth.js');
        $content .= $this->readJSSource($jsCodeDir . 'INTER-Mediator-AuthUI.js');
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

    /** Reads a JavaScript source file, optionally ignoring lines or the rest of the file based on special markers.
     * @param string $filename The JavaScript file path.
     * @return string The file contents, possibly truncated or with lines removed.
     */
    private function readJSSource(string $filename): string
    {
        $content = strval(file_get_contents($filename));
        $pos = strpos($content, "@@IM@@IgnoringRestOfFile");
        if ($pos !== false) {
            $content = substr($content, 0, $pos) . "\n";
        }
        if (!str_contains($content, "@@IM@@IgnoringNextLine")) {
            return $content;
        }
        $result = [];
        $skipNext = false;
        foreach (explode("\n", $content) as $line) {
            if ($skipNext) {
                $skipNext = false;
                continue;
            }
            if (str_contains($line, "@@IM@@IgnoringNextLine")) {
                $skipNext = true;
                continue;
            }
            $result[] = $line;
        }
        return implode("\n", $result);
    }
}
