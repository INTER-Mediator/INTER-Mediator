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

    public function __construct()
    {
        $this->isActive = false;
        $this->clientId = Params::getParameterValue("oAuthClientID", null);
        $this->clientSecret = Params::getParameterValue("oAuthClientSecret", null);
        $this->redirectURL = Params::getParameterValue("oAuthRedirect", null);
        $this->provider = Params::getParameterValue("oAuthProvider", null);

        if (!($this->clientId && $this->clientSecret && $this->redirectURL && $this->provider)) {
            $this->errorMessage[] = "Wrong Paramters";
            $this->provider = "unspecified";
            return;
        }

        switch (strtolower($this->provider)) {
            case "google":
                $this->baseURL = 'https://accounts.google.com/o/oauth2/auth';
                $this->getTokenURL = 'https://www.googleapis.com/oauth2/v4/token';
                $this->getInfoURL = 'https://www.googleapis.com/oauth2/v1/userinfo';
                $this->infoScope = array('openid', 'profile', 'email');

                /* Set up for Google
                 * 1. Go to https://console.developers.google.com.
                 * 2. Create a project.
                 */
                $this->isActive = true;
                $this->provider = "Google";

                break;
            default:
                break;
        }
    }

    public function oAuthBaseURL()
    {
        return $this->baseURL;
    }

    public function oAuthProvider()
    {
        return $this->provider;
    }

    public function infoScope()
    {
        return $this->infoScope;
    }

    public function javaScriptCode()
    {
        return $this->jsCode;
    }

    public function errorMessages()
    {
        return implode(", ", $this->errorMessage);
    }

    public function setDoRedirect($val)
    {
        $this->doRedirect = $val;
    }

    public function isCreate()
    {
        return $this->isCreate;
    }

    public function afterAuth()
    {
        $this->errorMessage = array();
        $this->userInfo = $this->getUserInfo();
        if (count($this->userInfo) == 0) {
            return false;
        }
        $oAuthStoring = isset($_COOKIE["_im_oauth_storing"]) ? $_COOKIE["_im_oauth_storing"] : "";
        $oAuthStoring = $oAuthStoring == 'session-storage' ? "true" : "false";
        $oAuthRealm = isset($_COOKIE["_im_oauth_realm"]) ? $_COOKIE["_im_oauth_realm"] : "";

        $dbProxy = new Proxy();
        $dbProxy->initialize(null,
            ['authentication' => ['authexpired' => 3600, 'storing' => $oAuthStoring]],
            ['db-class' => 'PDO'],
            $this->debugMode ? 2 : false);
        $credential = IMUtil::convertHashedPassword(IMUtil::randomString(30), "1", false);
        $param = array(
            "username" => $this->userInfo["username"],
            "hashedpasswd" => $credential,
            "realname" => $this->userInfo["realname"],
            "email" => $this->userInfo["email"]
        );
        $this->isCreate = $dbProxy->dbClass->authHandler->authSupportOAuthUserHandling($param);

        $generatedClientID = IMUtil::generateClientId('', $credential);
        $challenge = IMUtil::generateChallenge();
        $dbProxy->saveChallenge($this->userInfo["username"], $challenge, $generatedClientID,  "+");
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

    private function getUserInfo(): array {
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
                        'realname' => '新居雅行',
                        'username' => '113160982833865516666',
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
    private function decodeIDToken($code)
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
            'name' => '新居雅行',
            'picture' => 'https://lh3.googleusercontent.com/a/.....',
            'given_name' => '雅行',
            'family_name' => '新居',
            'iat' => 17126....,
            'exp' => 171265..., ),
            2 => NULL,
            )
          */
        $username = $jWebToken[1]->sub /*. "@" . $jWebToken[1]->iss*/
        ;
        $email = $jWebToken[1]->email;

        //$accessURL = $this->getInfoURL . '?access_token=' . $response->access_token;
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
            $username = $userInfo->sub /*. "@" . $userInfo->hd*/
            ;
            if (strlen($username) < 2) {
                $this->errorMessage[] = "Error: User subject didn't get from: {$this->getTokenURL}.";
            }
        }
        if (strlen($email) < 1) {
            $email = $userInfo->email;
        }

        return array(
            "realname" => $realname,
            "username" => $username,
            "email" => $email,
        );
    }
}

