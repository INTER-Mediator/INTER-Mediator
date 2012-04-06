<?php

/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Tokyo');

require_once('operation_common.php');
require_once('MessageStrings.php');
require_once('MessageStrings_ja.php');
/*
 * GET
 * ?access=select
 * &name=<table name>
 * &start=<record number to start>
 * &records=<how many records should it return>
 * &field_<N>=<field name>
 * &value_<N>=<value of the field>
 * &condition<N>field=<Extra criteria's field name>
 * &condition<N>operator=<Extra criteria's operator>
 * &condition<N>value=<Extra criteria's value>
 * &parent_keyval=<value of the foreign key field>
 */

function IM_Entry($datasrc, $options, $dbspec, $debug = false)
{
    $q = '"';

    header('Content-Type: text/javascript; charset="UTF-8"');
    header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
    header('Expires: 0');

    $generatedPrivateKey = '';
    $passPhrase = '';
    $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    $currentDirParam = $currentDir . 'params.php';
    $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
    if (file_exists($parentDirParam)) {
        include($parentDirParam);
    } else if (file_exists($currentDirParam)) {
        include($currentDirParam);
    }

    if (!isset($_POST['access'])) {

        if (file_exists($currentDir . 'INTER-Mediator-Lib.js')) {
            $jsLibDir = $currentDir . 'js_lib' . DIRECTORY_SEPARATOR;
            $bi2phpDir = $currentDir . 'bi2php' . DIRECTORY_SEPARATOR;
            echo file_get_contents($currentDir . 'INTER-Mediator-Lib.js');
            echo file_get_contents($currentDir . 'INTER-Mediator-Page.js');
            echo file_get_contents($currentDir . 'INTER-Mediator.js');
            echo file_get_contents($jsLibDir . 'sha1.js');
            echo file_get_contents($bi2phpDir . 'biBigInt.js');
            echo file_get_contents($bi2phpDir . 'biMontgomery.js');
            echo file_get_contents($bi2phpDir . 'biRSA.js');
            echo file_get_contents($currentDir . 'Adapter_DBServer.js');
        } else {
            echo file_get_contents($currentDir . 'INTER-Mediator.js');
        }

        echo "INTERMediatorOnPage.getEntryPath = function(){return {$q}{$_SERVER['SCRIPT_NAME']}{$q};};";
        echo "INTERMediatorOnPage.getDataSources = function(){return ", arrayToJS($datasrc, ''), ";};";
        echo "INTERMediatorOnPage.getOptionsAliases = function(){return ",
        arrayToJS(isset($options['aliases']) ? $options['aliases'] : array(), ''), ";};";
        echo "INTERMediatorOnPage.getOptionsTransaction = function(){return ",
        arrayToJS(isset($options['transaction']) ? $options['transaction'] : '', ''), ";};";
        $clientLang = explode('-', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        $messageClass = "MessageStrings_{$clientLang[0]}";
        if (class_exists($messageClass)) {
            $messageClass = new $messageClass();
        } else {
            $messageClass = new MessageStrings();
        }
        echo "INTERMediatorOnPage.getMessages = function(){return ",
        arrayToJS($messageClass->getMessages(), ''), ";};";
        if (isset($options['browser-compatibility'])) {
            $browserCompatibility = $options['browser-compatibility'];
        }
        echo "INTERMediatorOnPage.browserCompatibility = function(){return ",
        arrayToJS($browserCompatibility, ''), ";};";
        if (isset($prohibitDebugMode) && $prohibitDebugMode) {
            echo "INTERMediator.debugMode=false;";
        } else {
            echo "INTERMediator.debugMode=", ($debug === false) ? "false" : $debug, ";";
        }

        // Check Authentication
        $boolValue = "false";
        $requireAuthenticationContext = array();
        if (isset($options['authentication'])) {
            $boolValue = "true";
        }
        foreach ($datasrc as $aContext) {
            if (isset($aContext['authentication'])) {
                $boolValue = "true";
                $requireAuthenticationContext[] = $aContext['name'];
            }
        }
        echo "INTERMediatorOnPage.requireAuthentication={$boolValue};";
        echo "INTERMediatorOnPage.authRequiredContext=", arrayToJS($requireAuthenticationContext, ''), ";";

        $nativeAuth = (isset($options['authentication']) && isset($options['authentication']['user'])
            && ($options['authentication']['user'] === 'database_native')) ? "true" : "false";
        echo "INTERMediatorOnPage.isNativeAuth={$nativeAuth};";
        $storing = (isset($options['authentication']) && isset($options['authentication']['storing'])) ?
            $options['authentication']['storing'] : 'cookie';
        echo "INTERMediatorOnPage.authStoring='$storing';";
        $expired = (isset($options['authentication']) && isset($options['authentication']['authexpired'])) ?
            $options['authentication']['storing'] : '3600';
        echo "INTERMediatorOnPage.authExpired='$expired';";
        $keyArray = openssl_pkey_get_details( openssl_pkey_get_private( $generatedPrivateKey, $passPhrase ));
        echo "INTERMediatorOnPage.publickey=new biRSAKeyPair('",
            bin2hex( $keyArray['rsa']['e']),"','0','",bin2hex( $keyArray['rsa']['n']),"');";


    } else {

        $dbClassName = 'DB_' . (isset($dbspec['db-class']) ? $dbspec['db-class'] : (isset ($dbClass) ? $dbClass : ''));
        require_once("{$dbClassName}.php");
        $dbInstance = null;
        $dbInstance = new $dbClassName();
        if ($dbInstance == null) {
            $dbInstance->errorMessage[] = "The database class [{$dbClassName}] that you specify is not valid.";
            echo implode('', $dbInstance->getMessagesForJS());
            return;
        }
        if ((!isset($prohibitDebugMode) || !$prohibitDebugMode) && $debug) {
            $dbInstance->setDebugMode($debug);
        }

        $dbInstance->initialize($datasrc, $options, $dbspec);
        $tableInfo = $dbInstance->getDataSourceTargetArray();
        $access = $_POST['access'];
        $clientId = isset($_POST['clientid']) ? $_POST['clientid'] : $_SERVER['REMOTE_ADDR'];
        $paramAuthUser = isset($_POST['authuser']) ? $_POST['authuser'] : "";
        $paramResponse = isset($_POST['response']) ? $_POST['response'] : "";

        $requireAuthentication = false;
        $requireAuthorization = false;
        $isDBNative = false;
        if (   isset($options['authentication'] )
               && (  isset($options['authentication']['user'])
                  || isset($options['authentication']['group']) )
            || $access == 'challenge'
            || (isset($tableInfo['authentication'])
                && ( isset($tableInfo['authentication']['all'])
                    || isset($tableInfo['authentication'][$access])))
        ) {
            $requireAuthorization = true;
            $isDBNative = ($options['authentication']['user'] == 'database_native');
        }

        if ($requireAuthorization) { // Authentication required
            if ( strlen($paramAuthUser) == 0  || strlen($paramResponse) == 0 ) {
             // No username or password
                $access = "do nothing";
                $requireAuthentication = true;
            }
            // User and Password are suppried but...
            if ( $access != 'challenge') { // Not accessing getting a challenge.

                if ( $isDBNative ) {

                    $keyArray = openssl_pkey_get_details( openssl_pkey_get_private( $generatedPrivateKey, $passPhrase ));
                    require_once( 'bi2php/biRSA.php' );
                    $keyDecrypt = new biRSAKeyPair( '0', bin2hex( $keyArray['rsa']['d']), bin2hex( $keyArray['rsa']['n']));
                    $decrypted = $keyDecrypt->biDecryptedString( $paramResponse );
                    if ( $decrypted !== false ) {
                        $nlPos = strpos( $decrypted, "\n" );
                        $nlPos = ($nlPos === false) ? strlen($decrypted) : $nlPos;
                        $password = substr( $decrypted, 0, $nlPos );
                        $challenge = substr( $decrypted, $nlPos + 1 );
                        if ( ! $dbInstance->checkChallenge( $challenge, $clientId ) ) {
                            $access = "do nothing";
                            $requireAuthentication = true;
                        } else {
                            $dbInstance->setUserAndPaswordForAccess( $paramAuthUser, $password );
                        }
                    } else {
                        $dbInstance->setDebugMessage("Can't decrypt.");
                        $access = "do nothing";
                        $requireAuthentication = true;
                    }

                } else {
                    $noAuthorization = true;
                    $authorizedUsers = $dbInstance->getAuthorizedUsers($access);
                    $authorizedGroups = $dbInstance->getAuthorizedGroups($access);
                    if ( (count($authorizedUsers) == 0 && count($authorizedGroups) == 0 )) {
                        $noAuthorization = false;
                    } else {
                        if (in_array($dbInstance->currentUser, $authorizedUsers)) {
                            $noAuthorization = false;
                        } else {
                            if (count($authorizedGroups) > 0) {
                                $belongGroups = $dbInstance->getGroupsOfUser($dbInstance->currentUser);
                                if (count(array_intersect($belongGroups, $authorizedGroups)) != 0) {
                                    $noAuthorization = false;
                                }
                            }
                        }
                    }
                    if ($noAuthorization) {
                        $access = "do nothing";
                        $requireAuthentication = true;
                    }
                    if (!$dbInstance->checkAuthorization($paramAuthUser, $paramResponse, $clientId)) {
                        // Not Authenticated!
                        $access = "do nothing";
                        $requireAuthentication = true;
                    }
                }
            }
        }
        // Come here access=challenge or authenticated access

        switch ($access) {
            case 'select':
                $result = $dbInstance->getFromDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS()),
                    'dbresult=' . arrayToJS($result, ''), ';',
                "resultCount='{$dbInstance->mainTableCount}';";
                break;
            case 'update':
                $dbInstance->setToDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS());
                break;
            case 'new':
                $result = $dbInstance->newToDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS()), "newRecordKeyValue='{$result}';";
                break;
            case 'delete':
                $dbInstance->deleteFromDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS());
                break;
            case 'challenge':
                echo implode('', $dbInstance->getMessagesForJS());
                break;
        }
        if ($requireAuthorization) {
            $generatedChallenge = $dbInstance->generateChallenge();
            $generatedUID = $dbInstance->generateClientId('');
            $userSalt = $dbInstance->saveChallenge(
                $isDBNative ? 0 : $paramAuthUser, $generatedChallenge, $generatedUID);
            echo "challenge='{$generatedChallenge}{$userSalt}';";
            echo "clientid='{$generatedUID}';";
            if ($requireAuthentication) {
                echo "requireAuth=true;"; // Force authentication to client
            }
        }
    }
}

?>