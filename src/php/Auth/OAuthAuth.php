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

namespace INTERMediator\Auth;

use Exception;
use INTERMediator\Auth\OAuth\ProviderAdapter;
use INTERMediator\DB\Logger;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;
use INTERMediator\Params;

/**
 *
 */
class OAuthAuth
{
    /**
     * Indicates whether the OAuth authentication object is active and properly configured.
     * When true, the OAuth adapter is initialized and ready to handle authentication.
     * When false, there was an error in initialization or the adapter is not properly configured.
     *
     * @var bool Status of OAuth authentication process
     */
    public bool $isActive;

    /**
     * When debug mode is enabled, more detailed messages will be output.
     * When true, detailed information about the authentication process and error messages will be displayed.
     * Default value is false.
     * @var bool Holds the debug mode status. true=enabled, false=disabled
     */
    public bool $debugMode = false;
    /**
     * @var array Stores error messages that occur during the OAuth process
     *           Each array element contains a specific error message as string
     */
    private array $errorMessage = array();
    /**
     * @var string JavaScript code to be executed after authentication
     *            Contains redirection code or other client-side operations
     */
    private string $jsCode = '';
    /**
     * @var string|null
     */
    private ?string $provider;
    /**
     * @var bool Controls whether automatic redirection occurs after authentication
     *          When true, redirects to the original page after successful authentication
     */
    private bool $doRedirect = true;
    /**
     * @var bool|null
     */
    private ?bool $isCreate = null;
    /**
     * @var null|ProviderAdapter The OAuth provider adapter instance
     *                          Handles provider-specific authentication operations
     */
    private ?ProviderAdapter $providerObj = null;
    /**
     * @var null|array
     */
    private ?array $userInfo = null;
    /**
     * @var bool Flag to indicate confirmation-only mode
     *          When true, only verifies authentication without creating new user records
     */
    private bool $confirmOnly = false;

    /**
     * @return string
     */
    public function oAuthProvider(): string
    {
        return $this->provider;
    }

    /**
     * @return string
     */
    public function javaScriptCode(): string
    {
        return $this->jsCode;
    }

    /**
     * @return string
     */
    public function errorMessages(): string
    {
        return implode(", ", $this->errorMessage);
    }

    /**
     * Sets whether automatic redirection should occur after authentication. The default value is true.
     *
     * @param bool $val True to enable automatic redirection, false to disable
     * @return void
     */
    public function setDoRedirect(bool $val): void
    {
        $this->doRedirect = $val;
    }

    /**
     * @return bool|null
     */
    public function isCreate(): ?bool
    {
        return $this->isCreate;
    }

    /**
     * Get user information retrieved from the provider
     *
     * @return array|null The user information array with the following keys:
     *                    - "username": The username of the user. The value is a string.
     *                    - "realname": The real name of the user. The value is a string.
     *                    - "email": The E-mail address of the user. The value is a string.
     *                    - "birthdate": The birthday of the user. The value is a string.
     *                    - "gender": The gender of the user. The value is a string.
     *                    Or null if the user information couldn't be retrieved.
     */
    public function getUserInfo(): ?array
    {
        return $this->userInfo;
    }

    /**
     * Sets the flag to only confirm the user's authentication without creating a new user record.
     *
     * If this flag is set to true, the authentication process will only confirm the user's
     * authentication without creating a new user record. The default value is false.
     */
    public function setConfirmOnly(): void
    {
        $this->confirmOnly = true;
    }

    /**
     * Constructor
     *
     * @param string $provider The name of the provider.
     */
    public function __construct(string $provider)
    {
        $this->provider = $provider;
        $this->initializeAdapter();
        if (!$this->providerObj) {
            $this->errorMessage[] = "Provider Adapter for {$provider} couldn't create.";
            $this->isActive = false;
        }
    }

    /**
     * Initializes the provider adapter.
     *
     * This method creates an instance of the provider adapter and sets the necessary parameters from the configuration.
     * If the adapter couldn't be created or the parameters are invalid, the method sets the error message and sets the
     * `isActive` flag to false.
     *
     * @return void
     */
    private function initializeAdapter(): void
    {
        $this->providerObj = ProviderAdapter::createAdapter($this->provider);
        if (!$this->providerObj) {
            $this->errorMessage[] = "Provider Adapter for {$this->provider} couldn't create.";
            $this->isActive = false;
            return;
        }
        $oAuthInfo = Params::getParameterValue("oAuth", null);
        $this->providerObj->setDebugMode($this->debugMode);
        $this->provider = $this->providerObj->getProviderName();
        if (isset($oAuthInfo[$this->provider]["ClientID"])) {
            $this->providerObj->setClientId(IMUtil::getFromProfileIfAvailable($oAuthInfo[$this->provider]["ClientID"]) ?? null);
        }
        if (isset($oAuthInfo[$this->provider]["ClientSecret"])) {
            $this->providerObj->setClientSecret(IMUtil::getFromProfileIfAvailable($oAuthInfo[$this->provider]["ClientSecret"]) ?? null);
        }
        if (isset($oAuthInfo[$this->provider]["Scope"])) {
            $this->providerObj->setInfoScope(IMUtil::getFromProfileIfAvailable($oAuthInfo[$this->provider]["Scope"]) ?? null);
        }
        if (isset($oAuthInfo[$this->provider]["RedirectURL"])) {
            $this->providerObj->setRedirectURL($oAuthInfo[$this->provider]["RedirectURL"] ?? null);
        }
        if (isset($oAuthInfo[$this->provider]["KeyFilePath"])) {
            $this->providerObj->setKeyFilePath($oAuthInfo[$this->provider]["KeyFilePath"] ?? null);
        }
        if (!$this->providerObj->validate()) {
            $this->isActive = false;
            $this->errorMessage[] = "Wrong Paramters.";
            $this->provider = "unspecified";
            return;
        }
        $this->isActive = true;
    }

    /**
     * @return string The URL to request the authentication of the user.
     *
     * Returns the URL to request the authentication of the user. The returned URL is a string.
     */
    public function getAuthRequestURL(): string
    {
        if ($this->providerObj && $this->isActive) {
            return $this->providerObj->getAuthRequestURL();
        }
        return "";
    }

    /**
     * Processes the authentication flow after the user has authorized the client.
     *
     * This method is called after the user has authorized the client and the client has received the authorization code.
     * It processes the authentication flow and sets the necessary parameters of the class.
     * If the authentication is successful, it sets the `isActive` flag to true and sets the `userInfo` property with the user's information.
     * If the authentication fails, it sets the `errorMessage` property with the error message.
     *
     * @param bool $loginStart Whether to start the login process after the authentication is successful.
     * @return bool True if the authentication is successful, false otherwise.
     * @throws Exception
     */
    public function afterAuth(bool $loginStart = true): bool
    {
        if (!$this->isActive){
            $this->errorMessage[] = "OAuthAuth object is not active.";
            return false;
        }
        try {
            $this->errorMessage = array();
            $this->isCreate = false;
            $this->userInfo = $this->providerObj->getUserInfo();
            if ($this->debugMode) {
                $this->errorMessage[] = "UserInfo = " . var_export($this->userInfo, true);
            }
            if ($loginStart) {
                $this->userInfoToLogin();
            }
        } catch (Exception $e) {
            $this->errorMessage[] = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * Handling the OAuth user information to create a local user.
     *
     * @return
     * @throws Exception When the storing parameter is not "credential".
     */
    private function userInfoToLogin($currentUser = null): void
    {
        // Retrive the storing parameter.
        $oAuthStoring = $_COOKIE["_im_oauth_storing"] ?? "";
        if ($oAuthStoring !== "credential") {
            throw new Exception("The 'storing' parameter has to be 'credential.'");
        }
        $oAuthRealm = $_COOKIE["_im_oauth_realm"] ?? "";
        // Generate the new local user relevant to the OAuth user
        $dbProxy = new Proxy(true);
        $dbProxy->initialize(null, null, ['db-class' => 'PDO'], $this->debugMode ? 2 : false);
        $username = $this->userInfo["username"];
        if ($this->confirmOnly) {
            if (is_null($currentUser)) {
                $username = $dbProxy->dbSettings->getCurrentUser();
            } else {
                $username = $currentUser;
            }
        }
        $param = array(
            "username" => $username,
            "realname" => $this->userInfo["realname"] ?? "",
        );
        if (!$this->confirmOnly) {
            $passwordHash = Params::getParameterValue("passwordHash", 1);
            $alwaysGenSHA2 = Params::getParameterValue("alwaysGenSHA2", false);
            $credential = IMUtil::convertHashedPassword(IMUtil::randomString(30), $passwordHash, $alwaysGenSHA2);
            $param["hashedpasswd"] = $credential;
        }

        if (isset($this->userInfo["sub"])) {
            $param["sub"] = $this->userInfo["sub"];
        }
        if (isset($this->userInfo["email"])) {
            $param["email"] = $this->userInfo["email"];
        }
        if (isset($this->userInfo["address"])) {
            $param["address"] = $this->userInfo["address"];
        }
        if (isset($this->userInfo["birthdate"])) {
            $param["birthdate"] = $this->userInfo["birthdate"];
        }
        if (isset($this->userInfo["gender"])) {
            $param["gender"] = $this->userInfo["gender"];
        }
        $this->isCreate = $dbProxy->dbClass->authHandler->authSupportOAuthUserHandling($param);

        if (!$this->confirmOnly) {
            $authExpired = Params::getParameterValue("authExpired", 3600);
            // Set the logging-in situation for the local user to continue from log-in.
            $generatedClientID = IMUtil::generateClientId('', $credential);
            $challenge = IMUtil::generateChallenge();
            $dbProxy->saveChallenge($this->userInfo["username"], $challenge, $generatedClientID, "+");
            setcookie('_im_credential_token',
                $dbProxy->generateCredential($challenge, $generatedClientID, $credential),
                time() + $authExpired, '/', "", false, true);
            setcookie("_im_username_{$oAuthRealm}",
                $this->userInfo["username"], time() + $authExpired, '/', "", false, false);
            setcookie("_im_clientid_{$oAuthRealm}",
                $generatedClientID, time() + $authExpired, '/', "", false, false);
        }

        if ($this->debugMode) {
            $this->errorMessage[] = "OAuthAuth::afterAuth calles authSupportOAuthUserHandling with "
                . var_export($param, true) . ", returns {$this->isCreate}.";
            $this->errorMessage = array_merge($this->errorMessage, Logger::getInstance()->getDebugMessages());
        }
        if ($this->doRedirect && !$this->debugMode) {
            $this->jsCode = "location.href = '" . $_COOKIE["_im_oauth_backurl"] . "';";
        }
    }
}

