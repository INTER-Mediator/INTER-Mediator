<?php

namespace INTERMediator\Auth\OAuth;

use Exception;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;

/*
 * https://developers.google.com/identity/openid-connect/openid-connect?hl=ja
 *
 * https://accounts.google.com/.well-known/openid-configuration
 * returns
 * {
 "issuer": "https://accounts.google.com",
 "authorization_endpoint": "https://accounts.google.com/o/oauth2/v2/auth",
 "device_authorization_endpoint": "https://oauth2.googleapis.com/device/code",
 "token_endpoint": "https://oauth2.googleapis.com/token",
 "userinfo_endpoint": "https://openidconnect.googleapis.com/v1/userinfo",
 "revocation_endpoint": "https://oauth2.googleapis.com/revoke",
 "jwks_uri": "https://www.googleapis.com/oauth2/v3/certs",
 "response_types_supported": [
  "code",
  "token",
  "id_token",
  "code token",
  "code id_token",
  "token id_token",
  "code token id_token",
  "none"
 ],
 "subject_types_supported": [
  "public"
 ],
 "id_token_signing_alg_values_supported": [
  "RS256"
 ],
 "scopes_supported": [
  "openid",
  "email",
  "profile"
 ],
 "token_endpoint_auth_methods_supported": [
  "client_secret_post",
  "client_secret_basic"
 ],
 "claims_supported": [
  "aud",
  "email",
  "email_verified",
  "exp",
  "family_name",
  "given_name",
  "iat",
  "iss",
  "name",
  "picture",
  "sub"
 ],
 "code_challenge_methods_supported": [
  "plain",
  "S256"
 ],
 "grant_types_supported": [
  "authorization_code",
  "refresh_token",
  "urn:ietf:params:oauth:grant-type:device_code",
  "urn:ietf:params:oauth:grant-type:jwt-bearer"
 ]
}
 */

/**
 * クライアントサイド ウェブ アプリケーション用の OAuth 2.0
 * https://developers.google.com/identity/protocols/oauth2/javascript-implicit-flow?hl=ja
 *「Google でログイン」のブランドの取り扱いガイドライン
 * https://developers.google.com/identity/branding-guidelines?hl=ja
 */
class GoogleAdapter extends ProviderAdapter
{

    /**
     * Initializes the Google OAuth adapter with default endpoint configurations
     * Sets up essential URLs for OAuth flow including authorization, token, and user info endpoints
     */
    function __construct()
    {
//        $this->providerName = 'Google';
        $this->baseURL = "https://accounts.google.com/o/oauth2/v2/auth";// 'https://accounts.google.com/o/oauth2/auth';
        $this->getTokenURL = "https://oauth2.googleapis.com/token";
        $this->getInfoURL = "https://openidconnect.googleapis.com/v1/userinfo"; //'https://www.googleapis.com/oauth2/v3/userinfo';
        $this->issuer = 'https://accounts.google.com';
        $this->jwksURL = 'https://www.googleapis.com/oauth2/v3/certs';
    }

    /**
     * Sets the adapter to test mode for development and testing purposes
     *
     * @return ProviderAdapter The current adapter instance configured for testing
     */
    public function setTestMode(): ProviderAdapter //MyNumberCardAdapter
    {
        return $this;
    }

    /**
     * Validates the OAuth configuration and settings
     * Ensures all required parameters and endpoints are properly configured
     *
     * @return bool True if the configuration is valid, false otherwise
     */
    public function validate(): bool
    {
        return $this->validate_impl(true);
    }

    /**
     * Generates the Google OAuth authorization request URL
     * Creates a state parameter for CSRF protection and includes all required OAuth parameters
     *
     * @return string The complete authorization URL for Google OAuth
     * @throws Exception If unable to generate the authorization URL
     */
    public function getAuthRequestURL(): string
    {
        if (!$this->infoScope) {
            $this->infoScope = 'openid profile email'; // Default scope string
        }
        $state = IMUtil::randomString(32);
        $this->storeCode($state, "@G:state@");
        $this->storeProviderName($state);
        $this->storeBackURL($_SERVER['HTTP_REFERER'], $state);
        return $this->baseURL . '?response_type=code'
            . '&scope=' . urlencode($this->infoScope)
            . '&redirect_uri=' . urlencode($this->redirectURL)
            . '&client_id=' . urlencode($this->clientId)
            . '&state=' . urlencode($state);
    }

    /**
     * Retrieves authenticated user information from Google
     * Handles the OAuth callback, validates the state parameter, exchanges the code for tokens,
     * and fetches user information from Google's userinfo endpoint
     *
     * @return array Array containing user information (realname, username, email)
     * @throws Exception When authentication fails, tokens are invalid, or user info is incomplete
     */
    public function getUserInfo(): array
    {
        if (!isset($_GET["state"])) {
            throw new Exception("Failed with security issue. The state parameter doesn't exist in the request.");
        }
        $this->stateValue = $_GET["state"];
        if (!$this->checkCode($this->stateValue, "@G:state@")) {
            throw new Exception("Failed with security issue. The state parameter isn't same as the stored one.");
        }
        if (!isset($_GET['code'])) {
            throw new Exception("This isn't redirected from the providers site.  The code parameter doesn't exist in the request.");
        }
        $tokenparams = array(
            'code' => $_GET['code'],
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectURL,
        );
        $response = $this->communication($this->getTokenURL, true, $tokenparams);
        if (strlen($response->access_token) < 1) {
            throw new Exception("Error: Access token didn't get from: {$this->getTokenURL}.");
        }
        $id_token = $response->id_token;
        $access_token = $response->access_token;
        $payloadIDToken = $this->checkIDToken($id_token, $access_token);
        $userInfo = [
            "realname" => $payloadIDToken->name,
            "username" => "{$payloadIDToken->sub}@{$this->providerName}",
            "email" => $payloadIDToken->email,
        ];
        return $userInfo;
    }
}

/* The Responses from Google

** https://accounts.google.com/o/oauth2/auth
(object) array(
    'access_token' => 'ya29.....',
    'expires_in' => 3599,
    'scope' => 'https://www.googleapis.com/auth/userinfo.profile openid https://www.googleapis.com/auth/userinfo.email',
    'token_type' => 'Bearer',
    'id_token' => 'eyJhbGciO.....',
)

** https://oauth2.googleapis.com/token
First two elements of $jWebToken
array (
    0 => (object) array(
    'alg' => 'RS256',
    'kid' => '93b495162af0c87....',
    'typ' => 'JWT', ),
    1 => (object) array(
    'iss' => 'https://accounts.google.com',
    'azp' => '2829817.....',
    'aud' => '2829817.....',
    'sub' => '1131609828.....',
    'email' => 'xxxx...xxxx@gmail.com',
    'email_verified' => true,
    'at_hash' => 'ixQDR3JF.....',
    'name' => 'ABCD',
    'picture' => 'https://lh3.googleusercontent.com/a/.....',
    'given_name' => 'CD',
    'family_name' => 'AB',
    'iat' => 17126....,
    'exp' => 171265..., ),
    2 => NULL,
)

** https://www.googleapis.com/oauth2/v3/userinfo
(object) array(
    'id' => '113160982.....',
    'email' => 'xxxx...xxxx@gmail.com',
    'verified_email' => true,
    'name' => 'AB CD',
    'given_name' => 'CD',
    'family_name' => 'AB',
    'picture' => 'https://lh3.googleusercontent.com/a/ACg8oc....',
    'locale' => 'ja',
)
*/
/*
 *
(object) array(
  'access_token' => 'EAAJLw1YLmZC8BOZCYTRI1xJHmlI5KZCqVyXjHJmUis5ihh4jxlZBNxfCwTjY....',
  'token_type' => 'bearer',
  'expires_in' => 5182481,
)
(object) array(
  'name' => 'AB CD',
  'id' => '10161084674082992', ),
 */

