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

require_once( 'operation_common.php' );
/*
 * GET
 * ?access=select
 * table=<table name>
 * &start=<record number to start>
 * &records=<how many records should it return>
 * &field_<N>=<field name>
 * &value_<N>=<value of the field>
 * &ext_cond<N>field=<Extra criteria's field name>
 * &ext_cond<N>operator=<Extra criteria's operator>
 * &ext_cond<N>value=<Extra criteria's value>
 * &parent_keyval=<value of the foreign key field>
 */

function IM_Entry( $datasrc, $options = null, $dbspec = null, $debug=false )	{
	$LF = "\n";	$q = '"';
	if ( ! isset( $_GET['access'] ) )	{
		header( 'Content-Type: text/javascript' );
        header( 'Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0' );
        header( 'Expires: 0' );
		echo file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'INTER-Mediator.js');
		echo "function IM_getEntryPath(){return {$q}{$_SERVER['SCRIPT_NAME']}{$q};}{$LF}";
		echo "function IM_getMyPath(){return {$q}", getRelativePath(), "/INTER-Mediator.php{$q};}{$LF}";
		echo "function IM_getDataSources(){return ", arrayToJS( $datasrc, '' ), ";}{$LF}";
		echo "function IM_getOptions(){return ", arrayToJS( $options, '' ), ";}{$LF}";
	} else {
		$fieldsRequired = array();
		for ( $i=0 ; $i< 1000 ; $i++ )	{
			if ( isset( $_GET["field_{$i}"] ))	{
				$fieldsRequired[] = $_GET["field_{$i}"];
			} else {
				break;
			}
		}
		$valuesRequired = array();
		for ( $i=0 ; $i< 1000 ; $i++ )	{
			if ( isset( $_GET["value_{$i}"] ))	{
				$valuesRequired[] = $_GET["value_{$i}"];
			} else {
				break;
			}
		}
		
		$dbClassName = "DB_{$dbspec['db-class']}";
		require_once("{$dbClassName}.php");
		eval( "\$dbInstance = new {$dbClassName}();" );
		if ( $debug )	{
            $dbInstance->setDebugMode();
        }
        include( 'params.php' );
        $dbInstance->setDbSpecServer(
            isset( $dbspec['server'] ) ? $dbspec['server'] :
                (isset ( $dbServer ) ? $dbServer : '')
        );
        $dbInstance->setDbSpecPort(
            isset( $dbspec['port'] ) ? $dbspec['port'] :
                (isset ( $dbPort ) ? $dbPort : '')
        );
        $dbInstance->setDbSpecUser(
            isset( $dbspec['user'] ) ? $dbspec['user'] :
                (isset ( $dbUser ) ? $dbUser : '')
        );
        $dbInstance->setDbSpecPassword(
            isset( $dbspec['password'] ) ? $dbspec['password'] :
                (isset ( $dbPassword ) ? $dbPassword : '')
        );
        $dbInstance->setDbSpecDataType(
             isset( $dbspec['datatype'] ) ? $dbspec['datatype'] :
                 (isset ( $dbDataType ) ? $dbDataType : '')
        );
        $dbInstance->setDbSpecDatabase(
             isset( $dbspec['database'] ) ? $dbspec['database'] :
                 (isset ( $dbDatabase ) ? $dbDatabase : '')
        );
        $dbInstance->setDbSpecProtocol(
            isset( $dbspec['protocol'] ) ? $dbspec['protocol'] :
                (isset ( $dbProtocol ) ? $dbProtocol : '')
        );
        $dbInstance->setDbSpecOption(
            isset( $dbspec['option'] ) ? $dbspec['option'] :
                (isset ( $dbOption ) ? $dbOption : '')
        );
        $dbInstance->setDbSpecDSN(
            isset( $dbspec['dsn'] ) ? $dbspec['dsn'] :
                (isset ( $dbDSN ) ? $dbDSN : '')
        );

		$dbInstance->setSeparator( isset( $options['separator'] ) ? $options['separator'] : '@' );
		$dbInstance->setDataSource( $datasrc );
		if ( isset($options['formatter']))  {
            $dbInstance->setFormatter( $options['formatter'] );
        }
		$dbInstance->setTargetTable( $_GET['table'] );
        if ( isset($_GET['start']))	{
            $dbInstance->setStart( $_GET['start'] );
        }
        if ( isset($_GET['records']))	{
            $dbInstance->setRecordCount( $_GET['records'] );
        }
        for ( $count = 0 ; $count < 10000 ; $count++ )  {
            if ( isset($_GET["ext_cond{$count}field"]))	{
                $dbInstance->setExtraCriteria(
                    $_GET["ext_cond{$count}field"],
                    $_GET["ext_cond{$count}operator"],
                    $_GET["ext_cond{$count}value"] );
            } else {
                break;
            }
        }

		$dbInstance->setTargetFields( $fieldsRequired );
		$dbInstance->setValues( $valuesRequired );
		if ( isset( $_GET['parent_keyval'] ))	{
			$dbInstance->setParentKeyValue( $_GET['parent_keyval'] );
		}
		switch( $_GET['access'] )	{
			case 'select':	$result = $dbInstance->getFromDB();		break;
			case 'update':	$result = $dbInstance->setToDB();		break;
			case 'insert':	$result = $dbInstance->newToDB();		break;
			case 'delete':	$result = $dbInstance->deleteFromDB();	break;
		}
		$returnData = array();
		foreach( $dbInstance->getErrorMessages() as $oneError )	{
			$returnData[] = "INTERMediator.errorMessages.push({$q}" . addslashes( $oneError ) . "{$q});";
		}
		foreach( $dbInstance->getDebugMessages() as $oneError )	{
			$returnData[] = "INTERMediator.debugMessages.push({$q}" . addslashes( $oneError ) . "{$q});";
		}
        switch( $_GET['access'] )	{
            case 'select':
                echo implode( '', $returnData ),
                    'var dbresult=' . arrayToJS( $result, '' ), ';',
                    'var resultCount=', $dbInstance->mainTableCount, ';';
                break;
            case 'insert':
                echo implode( '', $returnData ), 'var newRecordKeyValue=', $result, ';';
                break;
            default:
                echo implode( '', $returnData );
                break;
        }

	}
}
?>