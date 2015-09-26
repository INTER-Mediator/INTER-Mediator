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

// Accept redirect all after confirmation.

OAuthAuth::afterAuth();

class OAuthAuth
{
    public static function afterAuth()
    {
        $oAuthClientID = null;
        $oAuthClientSecret = null;
        $oAuthBaseURL = null;
        $oAuthTokenURL = null;
        $oAuthRedirect = null;
        $oAuthScope = array();
        /*
         * Decide the params.php file and load it.
         */
        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }

        $code = $_REQUEST['code'];
        $params = array(
            'code' => $code,
            'client_id' => $oAuthClientID,
            'client_secret' => $oAuthClientSecret,
            'redirect_uri' => $oAuthRedirect,
            'grant_type' => 'authorization_code'
        );

        if (function_exists('curl_init')) {
            $session = curl_init($oAuthTokenURL);
            curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($session, CURLOPT_HTTPHEADER, array(
//                'Content-length: ' + strlen($params)
//            ));
            curl_setopt($session, CURLOPT_POST, true);
            curl_setopt($session, CURLOPT_POSTFIELDS, $params);
            $content = curl_exec($session);
            curl_close($session);
        } else {
            echo "error";
            exit;
        }
        $response = json_decode($content);
        /* $response: {
        "access_token" : "ya29.9AG4QGFUvt7Daoys1BHPszeXqrw3zPLMmYIdCxQ7eS3fGhOSE3LwZAUOskW-eowbDZIA",
        "token_type" : "Bearer",
        "expires_in" : 3599,
        "id_token" : "eyJhbGciOiJSUzI1.....OZm9XnugiIg" }
         */

        if (isset($response->error)) {
            /* for example
            { "error" : "invalid_grant", "error_description" : "Code was already redeemed." }
            */
            echo "Error: {$response->error}<br/>Description: {$response->error_description}";
            exit;
        }
        $jWebToken = explode(".", $response->id_token);
        for ($i = 0 ; $i < count($jWebToken) ; $i++ ) {
            var_dump($i);
            var_dump(base64_decode($jWebToken[$i]));
        }
        /*
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
         * "exp":1442765677}
         */

        $userInfo = json_decode(
            file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?' .
                'access_token=' . $response->access_token)
        );
        /* var_dump($userInfo);
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
    }

}