<?php

namespace INTERMediator\Auth\OAuth;

use DateInterval;
use DateTime;
use Exception;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;

/**
 *
 */
class MyNumberCardAdapter extends ProviderAdapter
{
    /**
     * @var bool
     */
    private bool $isTest = true;

    /**
     * API Reference
     * https://developers.digital.go.jp/documents/auth-and-sign/authserver/
     */
    function __construct()
    {
        $this->providerName = 'MyNumberCard';
        $this->baseURL = 'https://auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/auth';
        $this->getTokenURL = "https://auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/token";
        $this->getInfoURL = 'https://auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/userinfo';
        $this->issuer = "https://auth-and-sign.go.jp/realms/main/";
        $this->jwksURL = "https://auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/certs";
    }

    /**
     * @return $this
     */
    public function setTestMode(): ProviderAdapter //MyNumberCardAdapter
    {
        $this->isTest = true;
        $this->providerName = 'MyNumberCard-Sandbox';
        $this->baseURL = 'https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/auth';
        $this->getTokenURL = "https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/token";
        $this->getInfoURL = 'https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/userinfo';
        $this->issuer = "https://auth-and-sign.go.jp/realms/main/";
        $this->jwksURL = "https://auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/certs";
        return $this;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if (parent::validate()) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getAuthRequestURL(): string
    {
        if (!$this->infoScope) {
            $this->infoScope = 'openid name address birthdate gender'; // Default scope string
        }
        $state = IMUtil::randomString(32);
        $nonce = IMUtil::randomString(32);
        $verifier = IMUtil::challengeString(64);
        $challenge = $this->base64url_encode(hash('sha256', $verifier, true));
        $dbProxy = new Proxy(true);
        $dbProxy->initialize(null, null, ['db-class' => 'PDO'], $this->debugMode ? 2 : false);
        $dbProxy->authDbClass->authHandler->authSupportStoreChallenge(
            0, $state, substr($this->clientId, 0, 64), "@M:state@", true);
        $dbProxy->authDbClass->authHandler->authSupportStoreChallenge(
            0, $verifier, substr($this->clientId, 0, 64), "@M:verifier@", true);
        return $this->baseURL . '?response_type=code&scope=' . urlencode($this->infoScope)
            . '&client_id=' . urlencode($this->clientId)
            . '&redirect_uri=' . urlencode($this->redirectURL)
            . '&state=' . urlencode($state)
            . '&nonce=' . urlencode($nonce)
            . '&code_challenge=' . urlencode($challenge)
            . '&code_challenge_method=S256&acr_values=aal3 crl';
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getUserInfo(): array
    {
        if (isset($_GET['code']) && isset($_GET['state']) && isset($_GET['session_state'])) { // Success
            $code = $_GET['code'];
            $state = $_GET['state'];
            $session_state = ['session_state'];
            $dbProxy = new Proxy(true);
            $dbProxy->initialize(null, null, ['db-class' => 'PDO'], $this->debugMode ? 2 : false);
            $storedStates = $dbProxy->authDbClass->authHandler->authSupportRetrieveChallenge(
                0, substr($this->clientId, 0, 64), false, "@M:state@", true);
            if (!in_array($state, explode("\n", $storedStates))) {
                throw new Exception("Failed with security issue.");
            }
            $storedVerifiers = explode("\n", $dbProxy->authDbClass->authHandler->authSupportRetrieveChallenge(
                0, substr($this->clientId, 0, 64), false, "@M:verifier@", true));
            if (count($storedVerifiers) < 1) {
                throw new Exception("Verifier value isn't stored.");
            }
            $verifier = $storedVerifiers[0];
        } else if (isset($_GET['error']) && isset($_GET['error_description']) && isset($_GET['state'])) { // Error
            throw new Exception("Error: [{$_GET['error']}] {$_GET['error_description']}");
        } else {
            throw new Exception("Error: This isn't a valid access");
        }

        $tokenparams = array(
            'code' => (string)$code,
            'client_id' => $this->clientId,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectURL,
            'code_verifier' => $verifier,
            'client_assertion_type' => "urn:ietf:params:oauth:client-assertion-type:jwt-bearer",
            'client_assertion' => $this->createJWT(json_encode([
                'iss' => (string)$this->clientId,
                'sub' => (string)$this->clientId,
                'aud' => $this->getTokenURL,
                'jti' => uniqid(),
                'exp' => (new DateTime())->add(DateInterval::createFromDateString('1 day'))->format("U"),
            ])),
        );
        $response = $this->communication($this->getTokenURL, true, $tokenparams);
        $access_token = $response->access_token ?? "";
        $promisedScope = $response->scope ?? "";
        if (strlen($access_token) < 1) {
            throw new Exception("Error: Access token didn't get from: {$this->getTokenURL}.");
        }
        $id_token = $response->id_token;
        $access_token = $response->access_token;
        $this->checkIDToken($id_token, $access_token);
        $userInfoResult = $this->communication($this->getInfoURL, false, null, $access_token);
        $userInfo = [
            "sub" => $userInfoResult->sub,
            "username" => "{$userInfoResult->sub}@{$this->providerName}",
        ];
        if (str_contains($promisedScope, "name")) {
            $userInfo["realname"] = $userInfoResult->name ?? "";
        }
        if (str_contains($promisedScope, "address")) {
            $userInfo["address"] = $userInfoResult->address ?? "";
        }
        if (str_contains($promisedScope, "birthdate")) {
            $userInfo["birthdate"] = $userInfoResult->birthdate ?? "";
        }
        if (str_contains($promisedScope, "gender")) {
            $userInfo["gender"] = $userInfoResult->gender ?? "";
        }
        if (strlen($userInfo["username"]) < 1) {
            throw new Exception("Nothing to get from the authenticating server."
                . var_export($userInfoResult, true));
        }
        return $userInfo;
    }
}