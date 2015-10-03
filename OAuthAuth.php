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

require_once("IMUtil.php");

class OAuthAuth
{
    private $baseURL;
    private $getTokenURL;
    private $getInfoURL;
    private $clientId;
    private $clientSecret;
    private $redirectURL;
    private $infoScope;
    private $errorMessage = array();
    private $jsCode = '';
    private $id_token;

    public function __construct($service)
    {
        switch (strtolower($service)) {
            case "google":
                $this->baseURL = 'https://accounts.google.com/o/oauth2/auth';
                $this->getTokenURL = 'https://accounts.google.com/o/oauth2/token';
                $this->getInfoURL = 'https://www.googleapis.com/oauth2/v1/userinfo';
                $this->infoScope = array('openid', 'profile', 'email');

                /* Set up for Google
                 * 1. Go to https://console.developers.google.com.
                 * 2. Create a project.
                 */
                break;
            default:
                break;
        }
    }

    public function oAuthBaseURL()
    {
        return $this->baseURL;
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

    public function afterAuth()
    {
        // The following variables come from param.php file.
        $oAuthClientID = null;
        $oAuthClientSecret = null;
        $oAuthRedirect = null;

        $params = IMUtil::getFromParamsPHPFile(
            array("oAuthClientID", "oAuthClientSecret", "oAuthRedirect",), true);
        if ($params === false) {
            $this->errorMessage[] = "Wrong Paramters";
            return false;
        }
        $this->clientId = $params["oAuthClientID"];
        $this->clientSecret = $params["oAuthClientSecret"];
        $this->redirectURL = $params["oAuthRedirect"];

        if (!isset($_REQUEST['code'])) {
            $this->errorMessage[] = "This isn't redirected from the providers site.";
            return false;
        }
        $tokenID = $this->decodeIDToken($_REQUEST['code']);
        if ($tokenID === false) {
            return false;
        }

        $dbProxy = new DB_Proxy();
        $dbProxy->initialize(null, null, null, false);
        $dbProxy->dbSettings->setLDAPExpiringSeconds(3600 * 24);
        $credential = $dbProxy->generateCredential(30);
        $dbProxy->dbClass->authSupportOAuthUserHandling($tokenID["username"], $credential);
        $this->errorMessage = array_merge($this->errorMessage, $dbProxy->logger->getErrorMessages());

        $oAuthStoring = isset($_COOKIE["_im_oauth_storing"]) ? $_COOKIE["_im_oauth_storing"] : "";
        $oAuthStoring = $oAuthStoring == 'session-storage' ? "true" : "false";
        $oAuthRealm = isset($_COOKIE["_im_oauth_realm"]) ? $_COOKIE["_im_oauth_realm"] : "";

        $this->jsCode = '';
        $this->jsCode .= 'function setAnyStore(key, val) {';
        $this->jsCode .= "var isSession = {$oAuthStoring}, realm = '{$oAuthRealm}';";
        $this->jsCode .= 'var d, isFinish = false, ex = 3600, authKey;';
        $this->jsCode .= 'd = new Date();d.setTime(d.getTime() + ex * 1000);';
        $this->jsCode .= 'authKey = key + ((realm.length > 0) ? ("_" + realm) : "");';
        $this->jsCode .= 'try {if (isSession){sessionStorage.setItem(authKey, val);isFinish = true;}}';
        $this->jsCode .= 'catch(ex){}';
        $this->jsCode .= 'if (!isFinish) {document.cookie = authKey + "=" + encodeURIComponent(val)';
        $this->jsCode .= '+ ";path=/;" + "max-age=" + ex + ";expires=" + d.toUTCString() + ";"';
        $this->jsCode .= '+ ((document.URL.substring(0, 8) == "https://") ? "secure;" : "")}}';

        $this->jsCode .= "setAnyStore('_im_username', '" . $tokenID["username"] . "');";
        $this->jsCode .= "setAnyStore('_im_credential', '" . $credential . "');";
        $this->jsCode .= "setAnyStore('_im_openidtoken', '" . $this->id_token . "');";
        if (count($this->errorMessage) < 1) {
            $this->jsCode .= "location.href = '" . $_COOKIE["_im_oauth_backurl"] . "';";
            return true;
        }

        return false;
    }

    private function decodeIDToken($code)
    {
        $tokenparams = array(
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectURL,
            'grant_type' => 'authorization_code'
        );

        if (function_exists('curl_init')) {
            $session = curl_init($this->getTokenURL);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($session, CURLOPT_POST, true);
            curl_setopt($session, CURLOPT_POSTFIELDS, $tokenparams);
            $content = curl_exec($session);
            curl_close($session);
        } else {
            $this->errorMessage[] = "Coundn't get information with the access token.";
            return false;
        }
        $response = json_decode($content);
        /* The example of Google in case of no error.
        $response: {
        "access_token" : "ya29.9AG4QGFUvt7Daoys1BHPszeXqrw3zPLMmYIdCxQ7eS3fGhOSE3LwZAUOskW-eowbDZIA",
        "token_type" : "Bearer",
        "expires_in" : 3599,
        "id_token" : "eyJhbGciOiJSUzI1.....OZm9XnugiIg" } */

        if (isset($response->error)) {
            /* The example of Google in case of error
            { "error" : "invalid_grant", "error_description" : "Code was already redeemed." }
            */
            $this->errorMessage[] = "Error: {$response->error}<br/>Description: {$response->error_description}";
            return false;
        }
        $this->id_token = $response->id_token;
        $jWebToken = explode(".", $response->id_token);
        for ($i = 0; $i < count($jWebToken); $i++) {
            $jWebToken[$i] = json_decode(base64_decode($jWebToken[$i]));
        }
        /* The example for Google: First two elements of $jWebToken
         * {
         * "alg":"RS256",
         * "kid":"0352564c1a4ac6c5097d4c5dee238b6de2cdf16e"}
         * "{
         * "iss":"accounts.google.com",
         * "at_hash":"4aebaXtoEE_o-eq23l2tug",
         * "aud":"1044341943970-3q053ucl9i8882m56fpm6dqg93julckv.apps.googleusercontent.com",
         * "sub":"113160982833865516666",
         * "email_verified":true,
         * "azp":"1044341943970-3q053ucl9i8882m56fpm6dqg93julckv.apps.googleusercontent.com",
         * "email":"msyk.nii83@gmail.com",
         * "iat":1442762077,
         * "exp":1442765677} */
        $username = $jWebToken[1]->sub . "@" . $jWebToken[1]->iss;
        $email = $jWebToken[1]->email;

        $userInfo = json_decode(
            $userInfo = file_get_contents(
                $this->getInfoURL . '?access_token=' . $response->access_token)
        );
        /* The example of $userInfo about Google.
         object(stdClass)#2 (10) {
        ["id"]=> string(21) "113160982833865516666"
        ["email"]=> string(20) "msyk.nii83@gmail.com"
        ["verified_email"]=> bool(true)
        ["name"]=> string(12) "新居雅行"
        ["given_name"]=> string(6) "雅行"
        ["family_name"]=> string(6) "新居"
        ["link"]=> string(45) "https://plus.google.com/113160982833865516666"
        ["picture"]=> string(92) "https://lh5.googleusercontent.com/-pVUMKEVd13Y/AAAAAAAAAAI/AAAAAAAAAD8/cn7jkZa6adc/photo.jpg"
        ["gender"]=> string(4) "male"
        ["locale"]=> string(2) "ja" } */

        $realname = $userInfo->name;

        return array(
            "realname" => $realname,
            "username" => $username,
            "email" => $email,
        );
    }
}