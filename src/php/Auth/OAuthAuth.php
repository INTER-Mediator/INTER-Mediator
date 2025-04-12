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
     * @var bool
     */
    public bool $isActive;

    /**
     * @var bool
     */
    public bool $debugMode = true;
    /**
     * @var array
     */
    private array $errorMessage = array();
    /**
     * @var string
     */
    private string $jsCode = '';
    /**
     * @var string|null
     */
    private ?string $provider;
    /**
     * @var bool
     */
    private bool $doRedirect = true;
    /**
     * @var bool|null
     */
    private ?bool $isCreate = null;
    private ?ProviderAdapter $providerObj = null;

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
     * @param bool $val
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
     * @param string $provider
     */
    public function __construct(string $provider)
    {
        $this->provider = $provider;
        $this->initiaizeAdapter();
        if (!$this->providerObj) {
            $this->errorMessage[] = "Provider Adapter for {$provider} couldn't create.";
            $this->isActive = false;
        }
    }

    private function initiaizeAdapter(): void
    {
        $this->providerObj = ProviderAdapter::createAdapter($this->provider);
        if (!$this->providerObj) {
            $this->errorMessage[] = "Provider Adapter for {$this->provider} couldn't create.";
            $this->isActive = false;
            return;
        }
        $oAuthInfo = Params::getParameterValue("oAuth", null);
        $this->isActive = true;
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
        if (!$this->providerObj->validate()) {
            $this->isActive = false;
            $this->errorMessage[] = "Wrong Paramters.";
            $this->provider = "unspecified";
            return;
        }
        $this->isActive = true;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getAuthRequestURL(): string
    {
        if($this->providerObj) {
            return $this->providerObj->getAuthRequestURL();
        }
        return "";
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function afterAuth(): bool
    {
        try {
            $this->errorMessage = array();
//            $this->providerObj = ProviderAdapter::createAdapter($this->provider);
            $this->initiaizeAdapter();
            $userInfo = $this->providerObj->getUserInfo();

            // Retrive the storing parameter.
            $oAuthStoring = $_COOKIE["_im_oauth_storing"] ?? "";
            if ($oAuthStoring !== "credential") {
                $this->errorMessage[] = "The 'storing' parameter has to be 'credential.";
                return false;
            }
            $oAuthRealm = $_COOKIE["_im_oauth_realm"] ?? "";

            // Generate the new local user relevant to the OAuth user
            $dbProxy = new Proxy(true);
            $dbProxy->initialize(null, null,
//            ['authentication' => ['authexpired' => 3600, 'storing' => $oAuthStoring]],
                ['db-class' => 'PDO'],
                $this->debugMode ? 2 : false);
            $authExpired = Params::getParameterValue("authExpired", 3600);
            $passwordHash = Params::getParameterValue("passwordHash", 1);
            $alwaysGenSHA2 = Params::getParameterValue("alwaysGenSHA2", false);
            $credential = IMUtil::convertHashedPassword(IMUtil::randomString(30), $passwordHash, $alwaysGenSHA2);
            $param = array(
                "username" => $userInfo["username"],
                "hashedpasswd" => $credential,
                "realname" => $userInfo["realname"] ?? "",
            );
            if (isset($userInfo["sub"])) {
                $param["sub"] = $userInfo["sub"];
            }
            if (isset($userInfo["email"])) {
                $param["email"] = $userInfo["email"];
            }
            if (isset($userInfo["address"])) {
                $param["address"] = $userInfo["address"];
            }
            if (isset($userInfo["birthdate"])) {
                $param["birthdate"] = $userInfo["birthdate"];
            }
            if (isset($userInfo["gender"])) {
                $param["gender"] = $userInfo["gender"];
            }
            $this->isCreate = $dbProxy->dbClass->authHandler->authSupportOAuthUserHandling($param);

            // Set the logging-in situation for local user to continue from log-in.
            $generatedClientID = IMUtil::generateClientId('', $credential);
            $challenge = IMUtil::generateChallenge();
            $dbProxy->saveChallenge($userInfo["username"], $challenge, $generatedClientID, "+");
            setcookie('_im_credential_token',
                $dbProxy->generateCredential($challenge, $generatedClientID, $credential),
                time() + $authExpired, '/', "", false, true);
            setcookie("_im_username_{$oAuthRealm}",
                $userInfo["username"], time() + $authExpired, '/', "", false, false);
            setcookie("_im_clientid_{$oAuthRealm}",
                $generatedClientID, time() + $authExpired, '/', "", false, false);

            if ($this->debugMode) {
                $this->errorMessage[] = "OAuthAuth::afterAuth calles authSupportOAuthUserHandling with "
                    . var_export($param, true) . ", returns {$this->isCreate}.";
                $this->errorMessage = array_merge($this->errorMessage, Logger::getInstance()->getDebugMessages());
            }
            if ($this->doRedirect && !$this->debugMode) {
                $this->jsCode = "location.href = '" . $_COOKIE["_im_oauth_backurl"] . "';";
            }
        } catch (Exception $e) {
            $this->errorMessage[] = $e->getMessage();
            return false;
        }
        return true;
    }

}

