<?php 
/*
 * INTER-Mediator
 * by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 * This project started at the end of 2009.
 * 
 */
require_once( 'DB_Base.php' );

class DB_MySQL extends DB_Base	{
	
	var $sqlResult = array();
	var $link = null;
	
	function __construct()	{
	}
	
	function getFromDB( $tableName )	{
		if ( isset ( $sqlResult[$tableName] ))	{
			return $sqlResult[$tableName];
		}
		$tableInfo = $this->getTableInfo( $tableName );
		
		require( 'params.php' );
		$this->link = mysql_connect( $mysql_connect, $this->dbSpec['user'], $this->dbSpec['password'] );
		if ( ! $this->link ) {
			$this->errorMessage[] = 'MySQL Connect Error: ' . mysql_error();
			return array();
		}
		mysql_select_db( $this->dbSpec['db'] );
		
		$queryClause = array();
		if ( isset( $tableInfo['foreign-key'] ) )	{
			$queryClause[] = "{$tableInfo['foreign-key']} = {$this->mainTalbeKeyValue}";
		}
		if ( isset( $tableInfo['query'] ))	{
			foreach( $tableInfo['query'] as $condition )	{
				if ( isset( $condition['operator'] ))	{
					$queryClause[] = "{$condition['field']} {$condition['operator']} {$condition['value']}";
				} else {
					$queryClause[] = "{$condition['field']} = {$condition['value']}";
				}
			}
		}
		$queryClause = ( count( $queryClause ) > 0 ) ? ('WHERE ' . implode( ',', $queryClause)) : '';
		
		$sortClause = array();
		if ( isset( $tableInfo['sort'] ))	{
			foreach( $tableInfo['sort'] as $condition )	{
				if ( isset( $condition['direction'] ))	{
					$sortClause[] = "{$condition['field']} {$condition['direction']}";
				} else {
					$sortClause[] = "{$condition['field']}";
				}
			}
		}
		$sortClause = ( count( $sortClause ) > 0 )	? ('ORDER BY ' . implode( ',', $sortClause)) : '';
		
		$sql = "SELECT count(*) FROM {$tableName} {$queryClause} {$sortClause}";
		if ( $this->isDebug )	$this->debugMessage[] = $sql;
		$result = mysql_query($sql);
		if (!$result) {
			$this->errorMessage[] = 'MySQL Query Error: ' . mysql_error();
			return array();
		}
		$row = mysql_fetch_row($result);
		$this->mainTableCount = $row[0];
		
		if ($tableName == $this->mainTableName) 	{
			$sql = "SELECT * FROM {$tableName} {$queryClause} {$sortClause} LIMIT {$this->skip} OFFSET {$this->start}";
		} else {
			$sql = "SELECT * FROM {$tableName} {$queryClause} {$sortClause}";
		}
		if ( $this->isDebug )	$this->debugMessage[] = $sql;
		$result = mysql_query($sql);
		if (!$result) {
			$this->errorMessage[] = 'MySQL Query Error: ' . mysql_error();
			return array();
		}
		while( $row = mysql_fetch_array($result, MYSQL_ASSOC) )	{
			$rowArray = array();
			foreach( $row as $field => $val )	{
				$rowArray[ $field ] = $val;
			}
			$sqlResult[$tableName][] = $rowArray;
			if ( $this->mainTableName == $tableName )	{
				$this->mainTalbeKeyValue = $rowArray[$tableInfo['key']];
			}
		}
		mysql_free_result($result);
		return $sqlResult[$tableName];
	}
	
	function setToDB( $tableName, $data )	{
		$tableInfo = $this->getTableInfo( $tableName );
		$keyFieldName = $tableInfo['key'];
		if ( ! $tableInfo['key'] || ! isset( $data[$keyFieldName] ))	{
			$this->errorMessage[] = 'Error: Can\'t get the key field value';
			return false;
		}
		
		require( 'params.php' );
		$this->link = mysql_connect( $mysql_connect, $this->dbSpec['user'], $this->dbSpec['password'] );
		if ( ! $this->link ) {
			$this->errorMessage[] = 'MySQL Connect Error: ' . mysql_error();
			return false;
		}
		mysql_select_db( $this->dbSpec['db'] );
		
		$setCaluse = array();
		foreach ( $data as $field=>$value )	{
			if ( $field != $keyFieldName){
				$convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
				$convVal = $this->formatterToDB(
					($this->mainTableName==$tableName)?$field:"{$tableName}{$this->separator}{$field}", $convVal );
				$setCaluse[] = "{$field}='{$convVal}'";
			}
		}
		if ( count( $setCaluse ) < 1 )	{
			$this->errorMessage[] = 'No data to update.';
			return false;
		}
		$setCaluse = implode( ',', $setCaluse );
		$sql = "UPDATE {$tableName} SET {$setCaluse} WHERE {$keyFieldName}={$data[$keyFieldName]}";
		if ( $this->isDebug )	$this->debugMessage[] = $sql;
		$result = mysql_query($sql);
		if (!$result) {
			$this->errorMessage[] = 'MySQL Query Error: ' . mysql_error();
			return false;
		}
		return true;
	}
	
	function newToDB( $tableName, $data, &$keyValue )	{
		$returnStr = '';
		$tableInfo = $this->getTableInfo( $tableName );
		$keyFieldName = $tableInfo['key'];

		$this->fx->setCharacterEncoding( 'UTF-8' );
		$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
		$this->fx->setDBData( $this->dbSpec['db'], $tableName, 1 );
		foreach ( $data as $field=>$value )	{
			if ( $field != $keyFieldName){
				$convVal = str_replace( "\n", "\r", str_replace( "\r\n", "\r", (is_array( $value )) ? implode( "\r", $value ) : $value ));
				$this->fx->AddDBParam( $field, $this->formatterToDB(
					($this->mainTableName==$tableName)?$field:"{$tableName}{$this->separator}{$field}", $convVal ));
			}
		}
		$result = $this->fx->FMNew();
		if ( $this->isDebug )	$this->debugMessage[] = $result['URL'];
		if( $result['errorCode'] > 0 && $result['errorCode'] != 401 )
			$returnStr .= "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>";
					
		foreach( $result['data'] as $row )	{
			$keyValue = $row[$keyFieldName][0];
		}
		return $returnStr;
	}
	
	function deleteFromDB( $tableName, $data )	{
		$returnStr = '';
		
		$this->fx->setCharacterEncoding( 'UTF-8' );
		$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
		$this->fx->setDBData( $this->dbSpec['db'], $tableName, 1 );
		foreach( $data as $field=>$val )	{
			$this->fx->AddDBParam( $field, $val, 'eq' );
		}
		$result = $this->fxResult = $this->fx->FMFind();
		if ( $this->isDebug )	$this->debugMessage[] = $result['URL'];
		if( $result['errorCode'] > 0 )
			$returnStr .= "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
			
		$recId = 0;
		if ( $result[ 'foundCount' ] != 0 )	{
			foreach( $result['data'] as $key=>$row )	{
				$recId =  substr( $key, 0, strpos( $key, '.' ) );
				
				$this->fx->setCharacterEncoding( 'UTF-8' );
				$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
				$this->fx->setDBData( $this->dbSpec['db'], $tableName, 1 );
				$this->fx->SetRecordID( $recId );
				$result = $this->fx->FMDelete();
				if( $result['errorCode'] > 0 )
					$returnStr .= "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>";
				if ( $this->isDebug )	$debugMessage[] = $result['URL'];
				break;
			}
		}
		return $returnStr;
	}
}
?>