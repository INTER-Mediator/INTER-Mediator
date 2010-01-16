<?php 
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

header('Content-Type: text/xml');

$realPrefix = '__imparameters__';
$returnArray = array();
foreach( $_POST as $key=>$val )	{
	if ( strpos( $key, $realPrefix ) === 0 )	{
		$keyAr = explode( '_', substr( $key, strlen( $realPrefix ) ));
		$tableName = array_shift( $keyAr );
		$escVal = addslashes( $val );
		eval( "\${$tableName}['". implode( "']['",  $keyAr) . "']='{$escVal}';" );
	}
}

if ( isset( $options['debug'] ))	$debug = true;	else $debug = false;
$separator = $options['separator'];
$mainTalbeName =$datasrc[0]['name'];

require_once( 'params.php' );
if ( ! isset( $dbspec['db-class'] )) 	$dbspec['db-class'] = $dbAccessClass;
if ( ! isset( $dbspec['db'] )) 			$dbspec['db'] = $dbName;
if ( ! isset( $dbspec['user'] )) 		$dbspec['user'] = $dbUser;
if ( ! isset( $dbspec['password'] )) 	$dbspec['password'] = $dbPassword;
if ( strpos( $dbspec['db-class'], 'DB_' ) !== 0 )	$dbspec['db-class'] = "DB_{$dbspec['db-class']}";
require_once("{$dbspec['db-class']}.php");
eval( "\$dbInstance = new {$dbspec['db-class']}();" );
if ( $debug )	$dbInstance->setDebugMode();
$dbInstance->setSeparator( $options[ 'separator' ] );
$dbInstance->setDBSpec( $dbspec );
$dbInstance->setDataSource( $datasrc );
if ( isset( $options[ 'formatter' ] ))	$dbInstance->setFormatter( $options[ 'formatter' ] );

// $dbInstance->setDebugMessage( var_export( $datasrc, true ));

$errorCheck = true;

for( $i=0; $i < 5000 ; $i++ )	{
	if ( ! isset($_POST["__easypage__delete_table_{$i}"])) break;
	$tableName = $_POST["__easypage__delete_table_{$i}"];
	$val = $_POST["__easypage__delete_key_{$i}"];
	for ( $tableNum = 0 ; $tableNum < count($datasrc) ; $tableNum++ )	{
		if( $datasrc[$tableNum]['name'] == $tableName)	{
			$keyField = $datasrc[$tableNum]['key'];
			$errorCheck = $errorCheck && $dbInstance->deleteFromDB( $tableName, array($keyField=>$val) );
		}
	}
}

$insertIds = array();
for( $i=0; $i < 5000 ; $i++ )	{
	if ( ! isset($_POST["__easypage__insert_table_{$i}"])) break;
	$tableName = $_POST["__easypage__insert_table_{$i}"];
	$id = $_POST["__easypage__insert_id_{$i}"];
	for ( $tableNum = 0 ; $tableNum < count($datasrc) ; $tableNum++ )	{
		if( $datasrc[$tableNum]['name'] == $tableName)	{
			$insertIds[$tableName][] = $id;
		}
	}
}

$tableData = array();
foreach( $_POST as $key=>$val )	{
	if ( strpos( $key, '__easypage__' ) !== 0 && strpos( $key, '__imparameters__' ) !== 0 )	{
		if ( substr_count( $key, $separator ) == 2 )	{
			$comp = explode( $separator, $key );
			$tableData[$comp[0]][$comp[2]][$comp[1]] = $val;
		} else {
			$tableData[$mainTalbeName][0][$key] = $val;
		}
	}
}

// $dbInstance->setDebugMessage( var_export( $tableData, true ));

$newKeyValueArray = array();

$newMainTableKey = false;
$tableName = $mainTalbeName;
$keyFieldName = $datasrc[0]['key'];
if ( $tableData[$tableName][0][$keyFieldName] == '' ) {
	$errorCheck &= $dbInstance->newToDB( $tableName, $tableData[$tableName][0], $newKeyValue );
	$newKeyValueArray[ array_shift( $insertIds[$tableName] )] = $newKeyValue;
	$newMainTableKey = $newKeyValue;
} else {
	$errorCheck = $errorCheck && $dbInstance->setToDB( $tableName, $tableData[$tableName][0] );
}

foreach( $tableData as $tableName=>$data)	{
	if ( $tableName != $mainTalbeName )	{
		for ( $tableNum = 0 ; $tableNum < count($datasrc) ; $tableNum++ )	{
			if( $datasrc[$tableNum]['name'] == $tableName)	{
				$keyFieldName = $datasrc[$tableNum]['key'];
				foreach( $data as $row)	{
					if ( $row[$keyFieldName] == '' ) {
						if ( $newMainTableKey !== false )	
							$row[$datasrc[$tableNum]['foreign-key']] = $newMainTableKey;
						$errorCheck = $errorCheck && $dbInstance->newToDB( $tableName, $row, $newKeyValue );
						$newKeyValueArray[ array_shift( $insertIds[$tableName] )] = $newKeyValue;
					} else {
						$errorCheck = $errorCheck && $dbInstance->setToDB( $tableName, $row );
					}
				}
			}
		}
	}
}

if ( $errorCheck ) 
	$message = '102';
else
	$message = '103';

$doc = new DOMDocument;
$rootNode = $doc->createElement( 'root' );
$doc->appendChild( $rootNode );

$tNode = $doc->createTextNode( $message );
$emNode = $doc->createElement( 'message' );
$emNode->appendChild( $tNode );
$rootNode->appendChild( $emNode );

$emNode = $doc->createElement( 'error-messages' );
$rootNode->appendChild( $emNode );
foreach( $dbInstance->getErrorMessages() as $aError )	{
	$eNode = $doc->createElement( 'error' );
	$eNode->appendChild( $doc->createTextNode( $aError ) );
	$emNode->appendChild( $eNode );
}
$emNode = $doc->createElement( 'debug-messages' );
$rootNode->appendChild( $emNode );
foreach( $dbInstance->getDebugMessages() as $aError )	{
	$eNode = $doc->createElement( 'debug-message' );
	$eNode->appendChild( $doc->createTextNode( $aError ) );
	$emNode->appendChild( $eNode );
}

$keyNode = $doc->createElement( 'generated-key-values' );
$rootNode->appendChild( $keyNode );
foreach($newKeyValueArray as $key=>$val)	{
	$id = $doc->createElement( 'element-id', $key );
	$value = $doc->createElement( 'value', $val );
	$pair = $doc->createElement( 'generated' );
	$pair->appendChild( $id );
	$pair->appendChild( $value );
	$keyNode->appendChild( $pair );
}

echo $doc->saveXML();

?>