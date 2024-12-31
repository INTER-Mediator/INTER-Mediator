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

class OAuthAuth
{
    public bool $isActive;

    private string $baseURL;
    private string $getTokenURL;
    private string $getInfoURL;
    private ?string $clientId;
    private ?string $clientSecret;
    private ?string $redirectURL;
    private array $errorMessage = array();
    private string $jsCode = '';
    private string $id_token;
    private ?string $provider;
    private bool $doRedirect = true;
    private ?bool $isCreate = null;
    private ?array $userInfo = null;
    private ?array $infoScope = null;

    public bool $debugMode = false;


    public function oAuthBaseURL(): string
    {
        return $this->baseURL;
    }

    public function oAuthProvider(): string
    {
        return $this->provider;
    }

    public function infoScope(): array
    {
        return $this->infoScope;
    }

    public function javaScriptCode(): string
    {
        return $this->jsCode;
    }

    public function errorMessages(): string
    {
        return implode(", ", $this->errorMessage);
    }

    public function setDoRedirect(bool $val): void
    {
        $this->doRedirect = $val;
    }

    public function isCreate(): ?bool
    {
        return $this->isCreate;
    }

    public function __construct(string $provider)
    {
        $oAuthIngo = Params::getParameterValue("oAuth", null);

        $this->isActive = false;
        $this->provider = $provider;
        $this->clientId = $oAuthIngo[$provider]["ClientID"] ?? null;
        $this->clientSecret = $oAuthIngo[$provider]["ClientSecret"] ?? null;
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
                $this->infoScope = array('openid', 'profile', 'email');

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
                $this->infoScope = array('openid', 'name', 'address', 'birthdate', 'gender' /*, 'sign'*/);
                break;
            default:
                break;
        }
    }

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

    public function isValidState(string $state): bool
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

    public function afterAuth(): bool
    {
        $this->errorMessage = array();
        $this->userInfo = $this->getUserInfo();
        if (count($this->userInfo) == 0) {
            return false;
        }
        $oAuthStoring = $_COOKIE["_im_oauth_storing"] ?? "";
        $oAuthRealm = $_COOKIE["_im_oauth_realm"] ?? "";

        $dbProxy = new Proxy();
        $dbProxy->initialize(null,
            ['authentication' => ['authexpired' => 3600, 'storing' => $oAuthStoring]],
            ['db-class' => 'PDO'],
            $this->debugMode ? 2 : false);
        $passwordHash = Params::getParameterValue("passwordHash", 1);
        $alwaysGenSHA2 = Params::getParameterValue("alwaysGenSHA2", false);
        $credential = IMUtil::convertHashedPassword(IMUtil::randomString(30), $passwordHash, $alwaysGenSHA2);
        $param = array(
            "username" => $this->userInfo["username"],
            "hashedpasswd" => $credential,
            "realname" => $this->userInfo["realname"],
            "email" => $this->userInfo["email"]
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
    // Previously this class suported just storing=session-storage, and following JS code.
//        $this->jsCode .= 'function setAnyStore(key, val) {';
//        $this->jsCode .= "var isSession = {$oAuthStoring}, realm = '{$oAuthRealm}';";
//        $this->jsCode .= 'var d, isFinish = false, ex = 3600, authKey;';
//        $this->jsCode .= 'd = new Date();d.setTime(d.getTime() + ex * 1000);';
//        $this->jsCode .= 'authKey = key + ((realm.length > 0) ? ("_" + realm) : "");';
//        $this->jsCode .= 'try {if (isSession){sessionStorage.setItem(authKey, val);isFinish = true;}}';
//        $this->jsCode .= 'catch(ex){}';
//        $this->jsCode .= 'if (!isFinish) {document.cookie = authKey + "=" + encodeURIComponent(val)';
//        $this->jsCode .= '+ ";path=/;" + "max-age=" + ex + ";expires=" + d.toUTCString() + ";"';
//        $this->jsCode .= '+ ((document.URL.substring(0, 8) == "https://") ? "secure;" : "")}}';
//
//        $this->jsCode .= "setAnyStore('_im_username', '" . $this->userInfo["username"] . "');";
//        $this->jsCode .= "setAnyStore('_im_credential', '" . $credential . "');";
//        $this->jsCode .= "setAnyStore('_im_openidtoken', '" . $this->id_token . "');";
//        $this->jsCode .= "setAnyStore('_im_clientid', '');";

    private function getUserInfo(): array
    {
        switch (strtolower($this->provider)) {
            case "google":
                if (!isset($_REQUEST['code'])) {
                    $this->errorMessage[] = "This isn't redirected from the providers site.";
                    return [];
                }
                $tokenID = $this->decodeIDToken($_REQUEST['code']);
                //        var_export($tokenID);
                /*
                    array (
                        'realname' => 'ABCD',
                        'username' => '1131609....',
                        'email' => 'xxxx....xxxx3@gmail.com',
                    )
                 */
                if ($tokenID === false || strlen($tokenID["username"]) < 1 || strlen($tokenID["email"]) < 1) {
                    $this->errorMessage[] = "Nothing to get from the authenticating server. tokenID="
                        . var_export($tokenID, true);
                    return [];
                }

                return array(
                    "username" => $tokenID["username"],
                    "realname" => $tokenID["realname"],
                    "email" => $tokenID["email"]
                );
                break;
        }
        return [];
    }

    private function decodeIDToken(string $code): array
    {
        $tokenparams = array(
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectURL,
        );
        $postParam = "";
        $isFirstTime = true;
        foreach ($tokenparams as $key => $value) {
            if (!$isFirstTime) {
                $postParam .= "&";
            }
            $postParam .= "{$key}=" . urlencode($value);
            $isFirstTime = false;
        }
        if (function_exists('curl_init')) {
            $session = curl_init($this->getTokenURL);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_POST, true);
            curl_setopt($session, CURLOPT_POSTFIELDS, $postParam);
            //    curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
            $content = curl_exec($session);
            $header = [];
            if (!curl_errno($session)) {
                $header = curl_getinfo($session);
            }
            curl_close($session);
            $httpCode = $header['http_code'] ?? null;
        } else {
            $this->errorMessage[] = "Couldn't get information with the access token.";
            return false;
        }
        if ($httpCode != 200) {
            $this->errorMessage[] = "Error: {$this->getTokenURL}<br/>Description: {$content}";
            return false;
        }
        $response = json_decode($content);


        /* The example of Google in case of no error.
        echo "#### response ####";var_export($response);echo "<hr>";
    (object) array(
            'access_token' => 'ya29.....',
            'expires_in' => 3599,
            'scope' => 'https://www.googleapis.com/auth/userinfo.profile openid https://www.googleapis.com/auth/userinfo.email',
            'token_type' => 'Bearer',
            'id_token' => 'eyJhbGciO.....',
            }
        */

        if (isset($response->error)) {
            /* The example of Google in case of error
            { "error" : "invalid_grant", "error_description" : "Code was already redeemed." }
            */
            $this->errorMessage[] = "Error: {$response->error}<br/>Description: {$response->error_description}";
            return false;
        }
        if (strlen($response->access_token) < 1) {
            $this->errorMessage[] = "Error: Access token didn't get from: {$this->getTokenURL}.";
        }
        if ($this->debugMode) {
            $this->errorMessage[] = $content;
        }

        $this->id_token = $response->id_token;
        $jWebToken = explode(".", $response->id_token);
        for ($i = 0; $i < count($jWebToken); $i++) {
            $jWebToken[$i] = json_decode(base64_decode(strtr($jWebToken[$i], '-_', '+/')));
        }

        /* The example for Google: First two elements of $jWebToken
        var_export($jWebToken);echo "<hr>";
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
            'email' => 'msyk.nii83@gmail.com',
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
          */
        $username = $jWebToken[1]->sub;
        $email = $jWebToken[1]->email;

        if (function_exists('curl_init')) {
            $session = curl_init($this->getInfoURL);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$response->access_token}"]);
            $content = curl_exec($session);
            if (!curl_errno($session)) {
                $header = curl_getinfo($session);
            }
            curl_close($session);
            $httpCode = $header['http_code'];
        } else {
            $this->errorMessage[] = "Couldn't get information with the access token.";
            return false;
        }
        if ($httpCode != 200) {
            $this->errorMessage[] = "Error: {$this->getInfoURL}<br/>Description: {$content}";
            return false;
        }
        $userInfo = json_decode($content);
        if ($this->debugMode) {
            $this->errorMessage[] = var_export($userInfo, true);
        }

//        $userInfo = json_decode(
//            $userInfo = file_get_contents(
//                $this->getInfoURL . '?access_token=' . $response->access_token)
//        );
        /* The example of $userInfo about Google.
        var_export($userInfo);echo "<hr>";
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

        $realname = $userInfo->name;
        if (strlen($username) < 2) {
            $username = $userInfo->sub;
            if (strlen($username) < 2) {
                $this->errorMessage[] = "Error: User subject didn't get from: {$this->getTokenURL}.";
            }
        }
        if (strlen($email) < 1) {
            $email = $userInfo->email;
        }

        return array(
            "realname" => $realname,
            "username" => "{$username}@{$this->provider}",
            "email" => $email,
        );
    }
}

