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
     * The default value is false.
     * @var bool Holds the debug mode status. true=enabled, false=disabled
     */
    public bool $debugMode = false;
    /**
     * @var array<string> Stores error messages that occur during the OAuth process
     *           Each array element contains a specific error message as string
     */
    private array $errorMessage = array();
    /**
     * @var string JavaScript code to be executed after authentication
     *            Contains redirection code or other client-side operations
     */
    private string $jsCode = '';
    /**
     * @var string
     */
    private string $provider;
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
    private ?ProviderAdapter $providerObj;
    /**
     * @var null|array<string, string> User information retrieved from the provider
     */
    private ?array $userInfo = null;
    /**
     * The generated password for OAuth user
     * @var string|null The password string in plain text format
     */
    private ?string $generatedPassword = null;

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
     * @return array<string, string>|null The user information array with the following keys:
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

    public function getGeneratedPassword(): ?string
    {
        return $this->generatedPassword;
    }

    /**
     * Constructor
     *
     *  Initializes the provider adapter. This method creates an instance of the provider adapter and sets the necessary parameters from the configuration.
     *  If the adapter couldn't be created or the parameters are invalid, the method sets the error message and sets the
     *  `isActive` flag to false.
     *
     * @param string $providerOrState The name of the provider or the state value.
     * @param bool $isProviderName If it's true, $providerOrState is the provider name. The default is false.
     */
    public function __construct(string $providerOrState, bool $isProviderName = false)
    {
        $this->isActive = false;
        $this->providerObj = $isProviderName
            ? ProviderAdapter::createAdapter($providerOrState)
            : ProviderAdapter::createAdapterFromState($providerOrState);
        if (!$this->providerObj) {
            $this->errorMessage[] = "Provider Adapter for {$providerOrState} couldn't create.";
            return;
        }
        $this->provider = $this->providerObj->getProviderName();
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
     * If the authentication is successful, it sets the `userInfo` property with the user's information.
     * If the authentication fails, it sets the `errorMessage` property with the error message.
     *
     * @return bool True if the authentication is successful, false otherwise.
     */
    public function afterAuth(): bool
    {
        if (!$this->isActive) {
            $this->errorMessage[] = "OAuthAuth object is not active.";
            return false;
        }
        try {
            $this->errorMessage = [];
            $this->isCreate = false;
            $this->userInfo = $this->providerObj->getUserInfo();
            if ($this->debugMode) {
                $this->errorMessage[] = "UserInfo = " . var_export($this->userInfo, true);
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
     * @param string|null $currentUser expecting username in the authuser table.
     * @param string|null $password The password to set for the user. If null, a random password will be generated.
     * @param bool $isSetPassword Whether to set the generated password as initialPassword.
     * @param bool $isSetLogin Whether to automatically login the user after creation.
     */
    public function userInfoToLogin(?string $currentUser = null, ?string $password = null, bool $isSetPassword = false, bool $isSetLogin = true): void
    {
        try {
            $oAuthRealm = Params::getParameterValue("authRealm", "");
            // Generate the new local user relevant to the OAuth user
            $dbProxy = new Proxy(true);
            $dbProxy->initialize(null, null, ['db-class' => 'PDO'], $this->debugMode ? 2 : 0);
            $username = is_null($currentUser) ? $this->userInfo["username"] : $currentUser;
            $param = [
                "username" => $username,
                "realname" => $this->userInfo["realname"] ?? "",
            ];
            $passwordHash = Params::getParameterValue("passwordHash", 1);
            $alwaysGenSHA2 = Params::getParameterValue("alwaysGenSHA2", false);
            $this->generatedPassword = is_null($password) ? IMUtil::randomString(30) : $password;
            $credential = IMUtil::convertHashedPassword($this->generatedPassword, $passwordHash, $alwaysGenSHA2);
            $param["hashedpasswd"] = $credential;

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
            if ($isSetPassword) {
                $param["initialPassword"] = $this->generatedPassword;
            }
            $this->isCreate = $dbProxy->dbClass->authHandler->authSupportOAuthUserHandling($param);

            if ($isSetLogin) {
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
                $backURL = $this->providerObj->getBackURL();
                $this->jsCode = $backURL ? ("location.href = '{$backURL}';") : "";
            }
        } catch (Exception $e) {
            $this->errorMessage[] = $e->getMessage();
        }
    }
}

