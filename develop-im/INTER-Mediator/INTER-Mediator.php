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
 * &table=<table name>
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
        echo "function IM_getEntryPath(){return {$q}{$_SERVER['SCRIPT_NAME']}{$q};}{$LF}";
    //    echo "function IM_getMyPath(){return {$q}", getRelativePath(), "/INTER-Mediator.php{$q};}{$LF}";
        echo "function IM_getDataSources(){return ", arrayToJS($datasrc, ''), ";}{$LF}";
        echo "function IM_getOptions(){return ", arrayToJS($options, ''), ";}{$LF}";
        $clientLang = explode('-', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        $messageClass = "MessageStrings_{$clientLang[0]}";
        if (class_exists($messageClass)) {
            $messageClass = new $messageClass();
        } else {
            $messageClass = new MessageStrings();
        }
        echo "function IM_getMessages(){return ", arrayToJS($messageClass->getMessages(), ''), ";}{$LF}";
        if (isset($options['browser-compatibility'])) {
            $browserCompatibility = $options['browser-compatibility'];
        }
        echo "function IM_browserCompatibility(){return ", arrayToJS($browserCompatibility, ''), ";}{$LF}";
        echo "INTERMediator.debugMode=", $debug ? "true" : "false", ";{$LF}";
    } else {
        $dbClassName = isset($dbspec['db-class']) ? $dbspec['db-class'] : (isset ($dbClass) ? $dbClass : '');
        $dbClassName = "DB_{$dbClassName}";
        require_once("{$dbClassName}.php");
        eval("\$dbInstance = new {$dbClassName}();");
        if ($debug) {
            $dbInstance->setDebugMode();
        }
        $dbInstance->setDbSpecServer(
            isset($dbspec['server']) ? $dbspec['server'] : (isset ($dbServer) ? $dbServer : ''));
        $dbInstance->setDbSpecPort(
            isset($dbspec['port']) ? $dbspec['port'] : (isset ($dbPort) ? $dbPort : ''));
        $dbInstance->setDbSpecUser(
            isset($dbspec['user']) ? $dbspec['user'] : (isset ($dbUser) ? $dbUser : ''));
        $dbInstance->setDbSpecPassword(
            isset($dbspec['password']) ? $dbspec['password'] : (isset ($dbPassword) ? $dbPassword : ''));
        $dbInstance->setDbSpecDataType(
            isset($dbspec['datatype']) ? $dbspec['datatype'] : (isset ($dbDataType) ? $dbDataType : ''));
        $dbInstance->setDbSpecDatabase(
            isset($dbspec['database']) ? $dbspec['database'] : (isset ($dbDatabase) ? $dbDatabase : ''));
        $dbInstance->setDbSpecProtocol(
            isset($dbspec['protocol']) ? $dbspec['protocol'] : (isset ($dbProtocol) ? $dbProtocol : ''));
        $dbInstance->setDbSpecOption(
            isset($dbspec['option']) ? $dbspec['option'] : (isset ($dbOption) ? $dbOption : ''));
        $dbInstance->setDbSpecDSN(
            isset($dbspec['dsn']) ? $dbspec['dsn'] : (isset ($dbDSN) ? $dbDSN : ''));

        $dbInstance->setSeparator(isset($options['separator']) ? $options['separator'] : '@');
        $dbInstance->setDataSource($datasrc);
        if (isset($options['formatter'])) {
            $dbInstance->setFormatter($options['formatter']);
        }
        $dbInstance->setTargetName($_GET['name']);
        if (isset($_GET['start'])) {
            $dbInstance->setStart($_GET['start']);
        }
        if (isset($_GET['records'])) {
            $dbInstance->setRecordCount($_GET['records']);
        }
        for ($count = 0; $count < 10000; $count++) {
            if (isset($_GET["condition{$count}field"])) {
                $dbInstance->setExtraCriteria(
                    $_GET["condition{$count}field"],
                    isset($_GET["condition{$count}operator"]) ? $_GET["condition{$count}operator"] : '=',
                    isset($_GET["condition{$count}value"]) ? $_GET["condition{$count}value"] : '');
            } else {
                break;
            }
        }
        for ($count = 0; $count < 10000; $count++) {
            if (isset($_GET["sortkey{$count}field"])) {
                $dbInstance->setExtraSortKey($_GET["sortkey{$count}field"], $_GET["sortkey{$count}direction"]);
            } else {
                break;
            }
        }
        for ($count = 0; $count < 10000; $count++) {
            if (!isset($_GET["foreign{$count}field"])) {
                break;
            }
            $dbInstance->setForeignValue($_GET["foreign{$count}field"], $_GET["foreign{$count}value"]);
        }

        for ($i = 0; $i < 1000; $i++) {
            if (!isset($_GET["field_{$i}"])) {
                break;
            }
            $dbInstance->setTargetFields($_GET["field_{$i}"]);
        }
        for ($i = 0; $i < 1000; $i++) {
            if (!isset($_GET["value_{$i}"])) {
                break;
            }
            $dbInstance->setValues(get_magic_quotes_gpc() ? stripslashes($_GET["value_{$i}"]) : $_GET["value_{$i}"]);
        }
        //		if ( isset( $_GET['parent_keyval'] ))	{
        //			$dbInstance->setParentKeyValue( $_GET['parent_keyval'] );
        //		}
        switch ($_GET['access']) {
            case 'select':
                $result = $dbInstance->getFromDB($dbInstance->getTargetName());
                break;
            case 'update':
                $result = $dbInstance->setToDB($dbInstance->getTargetName());
                break;
            case 'insert':
                $result = $dbInstance->newToDB($dbInstance->getTargetName());
                break;
            case 'delete':
                $result = $dbInstance->deleteFromDB($dbInstance->getTargetName());
                break;
        }
        $returnData = array();
        foreach ($dbInstance->getErrorMessages() as $oneError) {
            $returnData[] = "INTERMediator.errorMessages.push({$q}" . addslashes($oneError) . "{$q});";
        }
        foreach ($dbInstance->getDebugMessages() as $oneError) {
            $returnData[] = "INTERMediator.debugMessages.push({$q}" . addslashes($oneError) . "{$q});";
        }
        switch ($_GET['access']) {
            case 'select':
                echo implode('', $returnData),
                    'var dbresult=' . arrayToJS($result, ''), ';',
                'var resultCount=', $dbInstance->mainTableCount, ';';
                break;
            case 'insert':
                echo implode('', $returnData), 'var newRecordKeyValue=', $result, ';';
                break;
            default:
                echo implode('', $returnData);
                break;
        }

    }
}

?>