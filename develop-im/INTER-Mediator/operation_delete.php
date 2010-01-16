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

if ( isset( $_POST['__easypage__debug']))	$debug = true;	else $debug = false;

require_once( 'params.php' );
if ( isset( $_POST["__easypage__db-class"] )) $dbAccessClass = $_POST["__easypage__db-class"];
if ( isset( $_POST["__easypage__db-class"] )) 
	$dbspec['db'] = $_POST["__easypage__db-name"];	
else 
	$dbspec['db'] = $dbName;
require_once("{$dbAccessClass}.php");

$separator = $_POST['__easypage__separator'];
$mainTalbeName =$_POST["__easypage__table_name_0"];
$datasrc = array();
for( $i=0; $i < 5000 ; $i++ )	{
	if ( ! isset($_POST["__easypage__table_name_{$i}"])) break;
	$datasrc[$_POST["__easypage__table_name_{$i}"]] = array();
	if( isset( $_POST["__easypage__table_key_{$i}"] ) )
		$datasrc[$_POST["__easypage__table_name_{$i}"]]['key'] = $_POST["__easypage__table_key_{$i}"];
	if( isset( $_POST["__easypage__table_foreign-key_{$i}"] ) )
		$datasrc[$_POST["__easypage__table_name_{$i}"]]['foreign-key'] = $_POST["__easypage__table_foreign-key_{$i}"];
}
$formatter = array();
for( $i=0; $i < 5000 ; $i++ )	{
	if ( ! isset($_POST["__easypage__formatter_field_{$i}"])) break;
	$formatter[$_POST["__easypage__formatter_field_{$i}"]] = $_POST["__easypage__formatter_class_{$i}"];
}


$dbspec['user'] = $dbUser;
$dbspec['password'] = $dbPassword;

eval( "\$dbInstance = new {$dbAccessClass}();" );
$dbInstance->setSeparator( $separator );
$dbInstance->setDBSpec( $dbspec );
$dbInstance->setDataSource( $datasrc );
if ( isset($formatter))	$dbInstance->setFormatter( $formatter );

$errorStr = array();
//if ( $debug )	$errorStr[] = var_export( $_POST, true );

for( $i=0; $i < 5000 ; $i++ )	{
	if ( ! isset($_POST["__easypage__delete_table_{$i}"])) break;
	$tableName = $_POST["__easypage__delete_table_{$i}"];
	$val = $_POST["__easypage__delete_key_{$i}"];
	$keyField = $datasrc[$tableName]['key'];
	$errorStr[] = $dbInstance->deleteFromDB( $tableName, array($keyField=>$val) );
//	if ( $debug )	$errorStr[] = 'Delete from '.$tableName. ', '. $keyField . '=' . $val;
}

$insertIds = array();
for( $i=0; $i < 5000 ; $i++ )	{
	if ( ! isset($_POST["__easypage__insert_table_{$i}"])) break;
	$tableName = $_POST["__easypage__insert_table_{$i}"];
	$id = $_POST["__easypage__insert_id_{$i}"];
	$insertIds[$tableName][] = $id;
//	echo "insertIds[{$tableName},{$id}]";
}

$tableData = array();
foreach( $_POST as $key=>$val )	{
	if ( strpos( $key, '__easypage__' ) !== 0 )	{
		if ( substr_count( $key, $separator ) == 2 )	{
			$comp = explode( $separator, $key );
			$tableData[$comp[0]][$comp[2]][$comp[1]] = $val;
		} else {
			$tableData[$mainTalbeName][0][$key] = $val;
		}
	}
}

//$errorStr[] = var_export($insertIds, true);
//$errorStr[] = var_export($tableData, true);

$newKeyValueArray = array();

$newMainTableKey = false;
$tableName = $mainTalbeName;
$keyFieldName = $datasrc[$mainTalbeName]['key'];
if ( $tableData[$tableName][0][$keyFieldName] == '' ) {
	$errorStr[] = $dbInstance->newToDB( $tableName, $tableData[$tableName][0], $newKeyValue );
	$newKeyValueArray[ array_shift( $insertIds[$tableName] )] = $newKeyValue;
	$newMainTableKey = $newKeyValue;
} else {
	$errorStr[] = $dbInstance->setToDB( $tableName, $tableData[$tableName][0] );
}

foreach( $tableData as $tableName=>$data)	{
	if ( $tableName != $mainTalbeName )	{
		$keyFieldName = $datasrc[$tableName]['key'];
		foreach( $data as $row)	{
			if ( $row[$keyFieldName] == '' ) {
				if ( $newMainTableKey !== false )	
					$row[$datasrc[$tableName]['foreign-key']] = $newMainTableKey;
				$errorStr[] = $dbInstance->newToDB( $tableName, $row, $newKeyValue );
				$newKeyValueArray[ array_shift( $insertIds[$tableName] )] = $newKeyValue;
			} else {
				$errorStr[] = $dbInstance->setToDB( $tableName, $row );
			}
		}
	}
}

if ( strlen(implode('', $errorStr)) == 0 ) 
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
foreach( $errorStr as $aError )	{
	$eNode = $doc->createElement( 'error' );
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