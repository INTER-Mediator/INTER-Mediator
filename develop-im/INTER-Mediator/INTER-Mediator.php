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
    $LF = "\n";
    $q = '"';

    header('Content-Type: text/javascript; charset="UTF-8"');
    header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
    header('Expires: 0');

    $currentDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
    $paramsPath = dirname( $currentDir  ). DIRECTORY_SEPARATOR . 'params.php';
    if ( file_exists( $paramsPath )) {
        include('params.php');
    } else if ( file_exists( $currentDir . 'params.php' )) {
        $paramsPath = $currentDir . 'params.php';
    }
    include( $paramsPath );

    if (!isset($_GET['access'])) {

        if ( file_exists( $currentDir . 'INTER-Mediator-Lib.js' ))  {
            $jsLibDir = $currentDir . 'js_lib'. DIRECTORY_SEPARATOR;
            echo file_get_contents($currentDir . 'INTER-Mediator-Lib.js');
            echo file_get_contents($currentDir . 'INTER-Mediator-Page.js');
            echo file_get_contents($currentDir . 'INTER-Mediator.js');
            echo file_get_contents($jsLibDir . 'sha1.js');
            echo file_get_contents($jsLibDir . 'rsa.js');
            echo file_get_contents($currentDir . 'Adapter_DBServer.js');
        } else {
            echo file_get_contents($currentDir . 'INTER-Mediator.js');
        }

        echo "INTERMediatorOnPage.getEntryPath = function(){return {$q}{$_SERVER['SCRIPT_NAME']}{$q};};";
        echo "INTERMediatorOnPage.getDataSources = function(){return ", arrayToJS( $datasrc, ''), ";};";
        echo "INTERMediatorOnPage.getOptionsAliases = function(){return ", arrayToJS($options['aliases'], ''), ";};";
        echo "INTERMediatorOnPage.getOptionsTransaction = function(){return ",  arrayToJS($options['transaction'], ''), ";};";
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
        if ( isset( $prohibitDebugMode ) && $prohibitDebugMode )    {
            echo "INTERMediator.debugMode=false;";
        } else {
            echo "INTERMediator.debugMode=", ($debug === false) ? "false" : $debug, ";";
        }

        // Check Authentication
        $boolValue = "false";
        $requireAuthContext = array();
        if ( isset( $options['authentication'] ))  {
            $boolValue = "true";
        }
        foreach ( $datasrc as $aContext )   {
            if ( $aContext['authentication'])   {
                $boolValue = "true";
                $requireAuthContext[] = $aContext['name'];
            }
        }
        echo "INTERMediatorOnPage.requreAuthentication={$boolValue};";
        echo "INTERMediatorOnPage.authRequiredContext=", arrayToJS($requireAuthContext, ''), ";";
        echo "INTERMediatorOnPage.authStoring='", $options['authentication']['storing'], "';";
        echo "INTERMediatorOnPage.authExpired='", $options['authentication']['authexpired'], "';";

    } else {

        $dbClassName = 'DB_' . (isset($dbspec['db-class']) ? $dbspec['db-class'] : (isset ($dbClass) ? $dbClass : ''));
        require_once("{$dbClassName}.php");
        $dbInstance = null;
        $dbInstance = new $dbClassName();
        if ( $dbInstance == null )  {
            $dbInstance->errorMessage[] = "The database class [{$dbClassName}] that you specify is not valid.";
            echo implode('', $dbInstance->getMessagesForJS());
            return;
        }
        if (( ! isset( $prohibitDebugMode ) || ! $prohibitDebugMode ) && $debug)    {
            $dbInstance->setDebugMode( $debug );
        }
        $dbInstance->initialize( $datasrc, $options, $dbspec );
        $access = $_GET['access'];
        $requireAuth = false;
        $tableInfo = $dbInstance->getDataSourceTargetArray();
        $clientId = isset( $_GET['clientid'] ) ? $_GET['clientid'] : $_SERVER['REMOTE_ADDR'];
        $authentication = ( isset( $tableInfo['authentication'] ) ? $tableInfo['authentication'] :
            ( isset( $options['authentication'] )   ? $options['authentication']   : null ));
        if ( $authentication != null )  {   // Authentication required
            if ( ! isset( $_GET['authuser'] ) || ! isset( $_GET['response'] )
                || strlen( $_GET['authuser'] ) == 0 || strlen( $_GET['response'] ) == 0 )  {   // No username or password
                $access = "do nothing";
                $requireAuth = true;
            }
            // User and Password are suppried but...
            if ( $access != 'challenge' ) {       // Not accessing getting a challenge.
                $noAuthorization = true;
                $authorizedUsers = $dbInstance->getAuthorizedUsers( );
                if ( count( $authorizedUsers ) > 0 && in_array( $dbInstance->currentUser, $authorizedUsers )) {
                    $noAuthorization = false;
                }
                $authorizedGroups = $dbInstance->getAuthorizedGroups( );
                if ( count( $authorizedGroups ) > 0 ) {
                    $belongGroups = $dbInstance->getGroupsOfUser( $dbInstance->currentUser );
                    if ( count( array_intersect( $belongGroups, $authorizedGroups )) != 0 )  {
                        $noAuthorization = false;
                    }
                }
                if ( $noAuthorization ) {
                    $access = "do nothing";
                    $requireAuth = true;
                }
                if ( ! $dbInstance->checkChallenge( $_GET['authuser'], $_GET['response'], $clientId ))  {
                    // Not Authenticated!
                    $access = "do nothing";
                    $requireAuth = true;
                }
            }
        }
        // Come here access=challenge or authenticated access

        switch ($access)    {
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
            case 'insert':
                $result = $dbInstance->newToDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS()), "newRecordKeyValue='{$result}';";
                break;
            case 'delete':
                $dbInstance->deleteFromDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS());
                break;
            case 'challenge':
                break;
        }
        if ( $authentication != null )  {
            $generatedChallenge = $dbInstance->generateChallenge();
            $generatedUID = $dbInstance->generateClientId( '' );
            $userSalt = $dbInstance->saveChallenge( $_GET['authuser'], $generatedChallenge, $generatedUID );
            echo "challenge='{$generatedChallenge}{$userSalt}';";
            echo "clientid='{$generatedUID}';";
            if ( $requireAuth ) {
                echo "requireAuth=true;";     // Force authentication to client
            }
        }
    }
}
?>