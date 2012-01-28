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

    include('params.php');

    if (!isset($_GET['access'])) {

        echo file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'INTER-Mediator.js');
        echo file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Adapter_DBServer.js');
        echo "INTERMediatorOnPage.getEntryPath = function(){return {$q}{$_SERVER['SCRIPT_NAME']}{$q};};{$LF}";
        //    echo "function IM_getMyPath(){return {$q}", getRelativePath(), "/INTER-Mediator.php{$q};}{$LF}";
        echo "INTERMediatorOnPage.getDataSources = function(){return ",
        arrayToJS( $datasrc, ''), ";};{$LF}";
        echo "INTERMediatorOnPage.getOptionsAliases = function(){return ",
        arrayToJS($options['aliases'], ''), ";};{$LF}";
        echo "INTERMediatorOnPage.getOptionsTransaction = function(){return ",
        arrayToJS($options['transaction'], ''), ";};{$LF}";
        $clientLang = explode('-', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        $messageClass = "MessageStrings_{$clientLang[0]}";
        if (class_exists($messageClass)) {
            $messageClass = new $messageClass();
        } else {
            $messageClass = new MessageStrings();
        }
        echo "INTERMediatorOnPage.getMessages = function(){return ",
        arrayToJS($messageClass->getMessages(), ''), ";};{$LF}";
        if (isset($options['browser-compatibility'])) {
            $browserCompatibility = $options['browser-compatibility'];
        }
        echo "INTERMediatorOnPage.browserCompatibility = function(){return ",
        arrayToJS($browserCompatibility, ''), ";};{$LF}";
        echo "INTERMediator.debugMode=", $debug ? "true" : "false", ";{$LF}";

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
        if ($debug) {
            $dbInstance->setDebugMode();
        }
        $dbInstance->initialize( $datasrc, $options, $dbspec );

        $authentication
            = ( isset( $datasrc['name']['authentication'] ) ? $datasrc['name']['authentication'] :
                ( isset( $options['authentication'] ) ? $options['authentication'] : null ));
        if ( $authentication != null )  {
            if ( ! isset( $_GET['user'] ) && ! isset( $_GET['response'] ))  {
                echo "challenge='{$dbInstance->generateChallenge()}';requireAuth=true;";
                return;
            }
        }

        switch ($_GET['access'])    {
            case 'select':
                $result = $dbInstance->getFromDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS()), 'var dbresult=' . arrayToJS($result, ''), ';',
                'var resultCount=', $dbInstance->mainTableCount, ';';
                break;
            case 'update':
                $dbInstance->setToDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS());
                break;
            case 'insert':
                $result = $dbInstance->newToDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS()), 'var newRecordKeyValue=', $result, ';';
                break;
            case 'delete':
                $dbInstance->deleteFromDB($dbInstance->getTargetName());
                echo implode('', $dbInstance->getMessagesForJS());
                break;
            case 'challenge':
                break;
            case 'authenticate':
                break;
        }
    }
}
?>