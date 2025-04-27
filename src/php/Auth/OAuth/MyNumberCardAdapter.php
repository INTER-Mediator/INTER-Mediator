<?php

namespace INTERMediator\Auth\OAuth;

use DateInterval;
use DateTime;
use Exception;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;

/**
 * MyNumberCardAdapter Class for Digital Authentication Application Integration
 *
 * This class provides OAuth2.0 authentication functionality for the Japanese Digital Authentication
 * Application (MyNumber Card). It handles both production and sandbox environments
 * for authentication, token management, and user information retrieval.
 *
 * References:
 * デジタル認証アプリ
 * https://services.digital.go.jp/auth-and-sign/
 * 【民間事業者向け情報】マイナンバーカードで本人の確認を簡単に
 * https://services.digital.go.jp/auth-and-sign/business/
 * デジタル認証アプリ：行政機関等・民間事業者向け実装ガイドライン
 * https://developers.digital.go.jp/documents/auth-and-sign/implement-guideline/
 * お問い合わせ
 * https://support.aas.digital.go.jp/hc/ja/requests/new?ticket_form_id=33975504314777*
 * デジタル認証アプリサービスの申込書類一式について
 * https://support.aas.digital.go.jp/hc/ja/articles/41595092319641
 *
 * @package INTERMediator\Auth\OAuth
 */
class MyNumberCardAdapter extends ProviderAdapter
{
    /**
     * Flag to determine if the adapter is running in test/sandbox mode
     *
     * When true, all API endpoints will point to the sandbox environment
     * When false, production endpoints will be used
     *
     * @var bool Default is true for safety
     */
    private bool $isTest = true;

    /**
     * Initializes the MyNumberCard OAuth adapter with production endpoints
     *
     * Sets up the necessary OAuth endpoints for authentication flow:
     * - Authorization endpoint
     * - Token endpoint
     * - UserInfo endpoint
     * - JWKS endpoint
     *
     * API Reference:
     * https://developers.digital.go.jp/documents/auth-and-sign/authserver/
     */
    function __construct()
    {
        $this->providerName = 'MyNumberCard';
        $this->baseURL = 'https://auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/auth';
        $this->getTokenURL = "https://auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/token";
        $this->getInfoURL = 'https://auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/userinfo';
        $this->issuer = "https://auth-and-sign.go.jp/api/realms/main";
        $this->jwksURL = "https://auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/certs";
    }

    /**
     * Configures the adapter to use sandbox/test environment endpoints
     *
     * Updates all OAuth endpoints to use the sandbox URLs for testing purposes.
     * This should be used during development and testing phases.
     *
     * @return ProviderAdapter Returns this adapter instance for method chaining
     */
    public function setTestMode(): ProviderAdapter //MyNumberCardAdapter
    {
        $this->isTest = true;
        $this->providerName = 'MyNumberCard-Sandbox';
        $this->baseURL = 'https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/auth';
        $this->getTokenURL = "https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/token";
        $this->getInfoURL = 'https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/userinfo';
        $this->issuer = "https://sb-auth-and-sign.go.jp/api/realms/main";
        $this->jwksURL = "https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/certs";
        return $this;
    }

    /**
     * Validates the current OAuth configuration
     *
     * Verifies that all required OAuth parameters and endpoints are properly configured
     * for authentication flow.
     *
     * @return bool True if configuration is valid, false otherwise
     */
    public function validate(): bool
    {
        return $this->validate_impl(false);
    }

    /**
     * Generates the OAuth authorization request URL
     *
     * Creates a complete authorization URL with required OAuth parameters including:
     * - response_type
     * - scope (openid, name, address, birthdate, gender)
     * - state for CSRF protection
     * - nonce for replay protection
     * - PKCE challenge for enhanced security
     *
     * @return string Complete authorization URL for redirect
     * @throws Exception If security parameters cannot be generated
     */
    public function getAuthRequestURL(): string
    {
        if (!$this->infoScope) {
            $this->infoScope = 'openid name address birthdate gender'; // Default scope string
        }
        $state = strtr(IMUtil::randomString(32),";","S"); // Remove ';'. Semicolon doesn't include in redirect URI.
        $this->storeCode($state, "@M:state@");
        $nonce = IMUtil::randomString(32);
        $verifier = IMUtil::challengeString(64);
        $this->storeCode($verifier, "@M:verifier@", $state);
        return $this->baseURL . '?response_type=code&scope=' . urlencode($this->infoScope)
            . '&client_id=' . urlencode($this->clientId)
            . '&redirect_uri=' . urlencode($this->redirectURL)
            . '&state=' . urlencode($state)
            . '&nonce=' . urlencode($nonce)
            . '&code_challenge=' . $this->base64url_encode(hash('sha256', $verifier, true))
            . '&code_challenge_method=S256&acr_values=aal3 crl';
    }

    /**
     * Retrieves authenticated user information from the OAuth provider
     *
     * Processes the OAuth callback by:
     * 1. Validating the state parameter
     * 2. Exchanging authorization code for tokens
     * 3. Validating the received tokens
     * 4. Fetching user information from the userinfo endpoint
     *
     * @return array User information including:
     *               - sub (subject identifier)
     *               - username (formatted as sub@provider)
     *               - realname (if name scope granted)
     *               - address (if address scope granted)
     *               - birthdate (if birthdate scope granted)
     *               - gender (if gender scope granted)
     * @throws Exception On authentication failures, invalid tokens, or missing data
     */
    public function getUserInfo(): array
    {
        if (isset($_GET['code']) && isset($_GET['state']) && isset($_GET['session_state'])) { // Success
            $code = $_GET['code'];
            $state = $_GET['state'];
            if (!$this->checkCode($state, "@M:state@")) {
                throw new Exception("Failed with security issue. The state parameter isn't same as the stored one.");
            }
        } else if (isset($_GET['error']) && isset($_GET['error_description']) && isset($_GET['state'])) { // Error
            throw new Exception("Error: [{$_GET['error']}] {$_GET['error_description']}");
        } else {
            throw new Exception("Error: This isn't a valid access");
        }
        $storedVerifiers = $this->retrieveCode($state, "@M:verifier@");
        if (count($storedVerifiers) < 1) {
            throw new Exception("Verifier value isn't stored.");
        }
        $verifier = $storedVerifiers[0]; // Maybe 0 or 1 elements. The first element has to be latest one.

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
        if (strlen($userInfo["username"]) < 4) {
            throw new Exception("Nothing to get from the authenticating server."
                . var_export($userInfoResult, true));
        }
        return $userInfo;
    }
}