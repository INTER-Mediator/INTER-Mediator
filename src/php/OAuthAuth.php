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

use INTERMediator\DB\Logger;
use INTERMediator\DB\Proxy;

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
     * @var string
     */
    private string $baseURL;
    /**
     * @var string
     */
    private string $getTokenURL;
    /**
     * @var string
     */
    private string $getInfoURL;
    /**
     * @var string|mixed|null
     */
    private ?string $clientId;
    /**
     * @var string|mixed|null
     */
    private ?string $clientSecret;
    /**
     * @var string|mixed|null
     */
    private ?string $redirectURL;
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
    /**
     * @var array|null
     */
    private ?array $userInfo = null;
    /**
     * @var array|string[]|null
     */
    private ?array $infoScope = null;

    /**
     * @var bool
     */
    public bool $debugMode = false;


    /**
     * @return string
     */
    public function oAuthBaseURL(): string
    {
        return $this->baseURL;
    }

    /**
     * @return string
     */
    public function oAuthProvider(): string
    {
        return $this->provider;
    }

    /**
     * @return array|string[]
     */
    public function infoScope(): array
    {
        return $this->infoScope;
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
        $oAuthIngo = Params::getParameterValue("oAuth", null);

        $this->isActive = false;
        $this->provider = $provider;
        $this->clientId = IMUtil::getFromProfileIfAvailable($oAuthIngo[$provider]["ClientID"] ?? null);
        $this->clientSecret = IMUtil::getFromProfileIfAvailable($oAuthIngo[$provider]["ClientSecret"] ?? null);
        $this->redirectURL = $oAuthIngo[$provider]["RedirectURL"] ?? null;

        switch (strtolower($this->provider)) {
            case "google":
                if (!($this->clientId && $this->clientSecret && $this->redirectURL)) {
                    $this->errorMessage[] = "Wrong Paramters";
                    $this->provider = "unspecified";
                    return;
                }

                $this->baseURL = 'https://accounts.google.com/o/oauth2/auth';
                $this->getTokenURL = "https://oauth2.googleapis.com/token";
                $this->getInfoURL = 'https://www.googleapis.com/oauth2/v3/userinfo';
                $this->infoScope = ['openid', 'profile', 'email'];

                /* Set up for Google
                 * 1. Go to https://console.developers.google.com.
                 * 2. Create a project.
                 */
                $this->isActive = true;
                $this->provider = "Google";
                break;
            case "mynumbercard-sandbox":
                if (!($this->clientId && $this->redirectURL)) {
                    $this->errorMessage[] = "Wrong Paramters";
                    $this->provider = "unspecified";
                    return;
                }
                $this->baseURL = 'https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/auth';
                $this->getTokenURL = "https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/token";
                $this->getInfoURL = 'https://sb-auth-and-sign.go.jp/api/realms/main/protocol/openid-connect/userinfo';
                $this->infoScope = ['openid', 'name', 'address', 'birthdate', 'gender' /*, 'sign'*/];
                $this->isActive = true;
                $this->provider = "MyNumberCard-Sandbox";
                break;
            case "facebook":
                if (!($this->clientId && $this->clientSecret && $this->redirectURL)) {
                    $this->errorMessage[] = "Wrong Paramters";
                    $this->provider = "unspecified";
                    return;
                }
                $this->baseURL = 'https://www.facebook.com/v21.0/dialog/oauth';
                $this->getTokenURL = "https://graph.facebook.com/v21.0/oauth/access_token";
                $this->getInfoURL = "https://graph.facebook.com/me";
                $this->infoScope = ['email', 'name', 'id', 'public_profile'];
                $this->isActive = true;
                $this->provider = "Facebook";
                break;
            default:
                break;
        }
    }

    /**
     * @param string $state
     * @return bool
     * @throws \Exception
     */
    private function isValidState(string $state): bool
    {
        if ($state === "") {
            return false;
        }
        $dbProxy = new Proxy();
        $dbProxy->initialize(null, null, ['db-class' => 'PDO'], $this->debugMode ? 2 : false);
        $challenges = $dbProxy->authDbClass->authHandler->authSupportRetrieveChallenge(
            0, substr($this->clientId, 0, 64), false, "@G:state@", true);
        return array_search($state, explode("\n", $challenges)) !== false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function afterAuth(): bool
    {
        $this->errorMessage = array();
        if (!$this->isValidState($_GET["state"] ?? "")) {
            $this->errorMessage[] = "Failed with security issue.";
            return false;
        }
        $this->userInfo = $this->getUserInfo();
        if (count($this->userInfo) == 0) {
            return false;
        }
        $oAuthStoring = $_COOKIE["_im_oauth_storing"] ?? "";
        if ($oAuthStoring !== "credential") {
            $this->errorMessage[] = "The 'storing' parameter has to be 'credential.";
            return false;
        }
        $oAuthRealm = $_COOKIE["_im_oauth_realm"] ?? "";

        $dbProxy = new Proxy();
        $dbProxy->initialize(null, null,
//            ['authentication' => ['authexpired' => 3600, 'storing' => $oAuthStoring]],
            ['db-class' => 'PDO'],
            $this->debugMode ? 2 : false);
        $passwordHash = Params::getParameterValue("passwordHash", 1);
        $alwaysGenSHA2 = Params::getParameterValue("alwaysGenSHA2", false);
        $credential = IMUtil::convertHashedPassword(IMUtil::randomString(30), $passwordHash, $alwaysGenSHA2);
        $param = array(
            "username" => $this->userInfo["username"],
            "hashedpasswd" => $credential,
            "realname" => $this->userInfo["realname"] ?? "",
            "email" => $this->userInfo["email"] ?? ""
        );
        $this->isCreate = $dbProxy->dbClass->authHandler->authSupportOAuthUserHandling($param);

        $generatedClientID = IMUtil::generateClientId('', $credential);
        $challenge = IMUtil::generateChallenge();
        $dbProxy->saveChallenge($this->userInfo["username"], $challenge, $generatedClientID, "+");
        setcookie('_im_credential_token',
            $dbProxy->generateCredential($challenge, $generatedClientID, $credential),
            time() + 3600, '/', "", false, true);
        setcookie("_im_username_{$oAuthRealm}",
            $this->userInfo["username"], time() + 3600, '/', "", false, false);
        setcookie("_im_clientid_{$oAuthRealm}",
            $generatedClientID, time() + 3600, '/', "", false, false);

        if ($this->debugMode) {
            $this->errorMessage[] = "authSupportOAuthUserHandling sends "
                . var_export($param, true) . ", returns {$this->isCreate}.";
            $this->errorMessage = array_merge($this->errorMessage, $dbProxy->logger->getDebugMessages());
        }
        $this->errorMessage = array_merge($this->errorMessage, $dbProxy->logger->getErrorMessages());
        if (count($this->errorMessage) < 1 && !(!$this->doRedirect && $this->isCreate)) {
            $this->jsCode = "location.href = '" . $_COOKIE["_im_oauth_backurl"] . "';";
            return true;
        }
        return true;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getAuthRequestURL(): string
    {
        $state = IMUtil::randomString(32);
        $dbProxy = new Proxy();
        $dbProxy->initialize(null, null, ['db-class' => 'PDO'], $this->debugMode ? 2 : false);
        $dbProxy->authDbClass->authHandler->authSupportStoreChallenge(
            0, $state, substr($this->clientId, 0, 64), "@G:state@", true);
        switch (strtolower($this->provider)) {
            case "google":
                return $this->baseURL . '?response_type=code&scope=' . urlencode(implode(" ", $this->infoScope))
                    . '&redirect_uri=' . urlencode($this->redirectURL)
                    . '&client_id=' . urlencode($this->clientId)
                    . '&state=' . urlencode($state);
                break;
            case "facebook":
                return $this->baseURL . '?redirect_uri=' . urlencode($this->redirectURL)
                    . '&client_id=' . urlencode($this->clientId)
                    . '&state=' . urlencode($state);
                break;
            case "mynumbercard-sandbox":
                $nonce = IMUtil::randomString(32);
                $challenge = base64_encode(hash('sha256', IMUtil::challengeString(64), true));
                return $this->baseURL . '?response_type=code&scope=' . urlencode(implode(" ", $this->infoScope))
                    . '&client_id=' . urlencode($this->clientId)
                    . '&redirect_uri=' . urlencode($this->redirectURL)
                    . '&state=' . urlencode($state)
                    . '&nonce=' . urlencode($nonce)
                    . '&code_challenge' . urlencode($nonce)
                    . '&code_challenge_method=S256&acr_values=aal3 crl';
        }
        return "";
    }

    /**
     * @return array
     */
    private function getUserInfo(): array
    {
        switch (strtolower($this->provider)) {
            case "google":
                if (!isset($_REQUEST['code'])) {
                    $this->errorMessage[] = "This isn't redirected from the providers site.";
                    return [];
                }
                $tokenparams = array(
                    'code' => $_REQUEST['code'],
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $this->redirectURL,
                );
                $response = $this->communication($this->getTokenURL, true, $tokenparams);
                if (!$response) {
                    return [];
                }
                if (strlen($response->access_token) < 1) {
                    $this->errorMessage[] = "Error: Access token didn't get from: {$this->getTokenURL}.";
                }
                $id_token = $response->id_token;
                $jWebToken = explode(".", $id_token);
                for ($i = 0; $i < count($jWebToken); $i++) {
                    $jWebToken[$i] = json_decode(base64_decode(strtr($jWebToken[$i], '-_', '+/')));
                }
                $username = $jWebToken[1]->sub ?? "";
                $email = $jWebToken[1]->email ?? "";
                $userInfo = $this->communication($this->getInfoURL, false, null, $response->access_token);
                $realname = $userInfo->name ?? "";
                if (strlen($username) < 2) {
                    $username = $userInfo->sub ?? "";
                    if (strlen($username) < 2) {
                        $this->errorMessage[] = "Error: User subject couldn't get from: {$this->getTokenURL}.";
                    }
                }
                if (strlen($email) < 1) {
                    $email = $userInfo->email ?? "";
                }
                $tokenID = array(
                    "realname" => $realname,
                    "username" => "{$username}@{$this->provider}",
                    "email" => $email,
                );
                if (is_null($tokenID) || strlen($tokenID["username"]) < 1 || strlen($tokenID["email"]) < 1) {
                    $this->errorMessage[] = "Nothing to get from the authenticating server. tokenID="
                        . var_export($tokenID, true);
                    return [];
                }
                return $tokenID;
                break;
            case "facebook":
                $input_token = $_REQUEST['code'] ?? "";
                if (!$input_token) {
                    $this->errorMessage[] = "This isn't redirected from the providers site.";
                    return [];
                }
                $tokenparams = array(
                    'code' => $input_token,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri' => $this->redirectURL,
                );
                $response = $this->communication($this->getTokenURL, false, $tokenparams);
                if (!$response) {
                    return [];
                }
                $access_token = $response->access_token ?? "";
                if (strlen($access_token) < 1) {
                    $this->errorMessage[] = "Error: Access token couldn't get from: {$this->getTokenURL}.";
                    return [];
                }
                $userInfo = $this->communication($this->getInfoURL, false,
                    [/*"fields" => "=name,email",*/ "access_token" => $access_token]);
                if (!$userInfo) {
                    return [];
                }
                $username = $userInfo->id;
                $realname = $userInfo->name;
                return ["username" => "{$username}@{$this->provider}", "realname" => $realname];
                break;
        }
        return [];
    }

    /**
     * @param string $url
     * @param bool $isPost
     * @param array|null $params
     * @param string|null $access_token
     * @return mixed
     */
    private function communication(string  $url,
                                   bool    $isPost = false,
                                   ?array  $params = null,
                                   ?string $access_token = null): mixed
    {
        $postParam = "";
        if ($params) {
            $isFirstTime = true;
            foreach ($params as $key => $value) {
                if (!$isFirstTime) {
                    $postParam .= "&";
                }
                $postParam .= "{$key}=" . urlencode($value);
                $isFirstTime = false;
            }
            if (!$isPost) {
                $url .= "?{$postParam}";
            }
        }
        if (function_exists('curl_init')) {
            $httpCode = 0;
            $session = curl_init($url);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            if ($access_token) {
                curl_setopt($session, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$access_token}"]);
            }
            if ($isPost) {
                curl_setopt($session, CURLOPT_POST, true);
                curl_setopt($session, CURLOPT_POSTFIELDS, $postParam);
            }
            $content = curl_exec($session);
            if (!curl_errno($session)) {
                $header = curl_getinfo($session);
                $httpCode = $header['http_code'];
            }
            curl_close($session);
        } else {
            $this->errorMessage[] = "Couldn't get call api (curl is NOT installed).";
            return false;
        }
        if ($httpCode != 200) {
            $this->errorMessage[] = "Error: {$url}<br/>Description: {$content}";
            return false;
        }
        $response = json_decode($content);
        if (isset($response->error)) {
            $this->errorMessage[] = "Error: Description: " . var_export($response, true);
            return false;
        }
        if ($this->debugMode) {
            $this->errorMessage[] = var_export($response, true);
        }
        return $response;
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
  'name' => '新居 雅行',
  'id' => '10161084674082992', ),
 */

