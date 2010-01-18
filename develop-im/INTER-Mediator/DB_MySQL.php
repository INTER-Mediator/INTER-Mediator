<?php 
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

require_once( 'DB_Base.php' );

class DB_MySQL extends DB_Base	{
	
	var $sqlResult = array();
	var $link = null;
	
	function __construct()	{
	}
	
	function getFromDB( $tableName )	{
		if ( isset ( $this->sqlResult[$tableName] ))	{
			return $this->sqlResult[$tableName];
		}
		$tableInfo = $this->getTableInfo( $tableName );
		
		require( 'params.php' );
		$currentEr = error_reporting();
		error_reporting( 0 );
			// Suppress any error/warning messages to avoid to break JS codes in case of MySQL client error.
		$this->link = mysql_connect( $mysql_connect, $this->dbSpec['user'], $this->dbSpec['password'] );
		error_reporting( $currentEr );
		if ( ! $this->link ) {
			$this->errorMessage[] = 'MySQL Connect Error: ' . mysql_error();
			return array();
		}
		mysql_select_db( $this->dbSpec['db'] );
		
		$queryClause = '';
		if ( isset( $tableInfo['query'][0] ))	{
			if ( $tableInfo['query'][0]['field'] == '__clause__' )	{
				$queryClause = "{$tableInfo['query'][0]['value']}";
			} else {
				$queryClauseArray = array();
				$chanckCount = 0;
				$insideOp = ' AND ';	$outsiceOp = ' OR ';
				foreach( $tableInfo['query'] as $condition )	{
					if ( $condition['field'] == '__operation__' )	{
						$chanckCount++;
						if ( $condition['operation'] == 'ex' )	{
							$insideOp = ' OR ';	$outsiceOp = ' AND ';
						}
					} else {
						if ( isset( $condition['value'] ))	{
							$escedVal = addslashes( $condition['value'] );
							if ( isset( $condition['operation'] ))	{
								$queryClauseArray[$chanckCount][] = "{$condition['field']} {$condition['operation']} '{$escedVal}'";
							} else {
								$queryClauseArray[$chanckCount][] = "{$condition['field']} = '{$escedVal}'";
							}
						} else {
							$queryClauseArray[$chanckCount][] = "{$condition['field']} {$condition['operation']}";
						}
					}
				}
				foreach( $queryClauseArray as $oneTerm )	{
					$queryClause[] = '(' . implode( $insideOp, $oneTerm ) . ')';
				}
				$queryClause =  implode( $outsiceOp, $queryClause );
			}
		}
		
		if ( isset( $tableInfo['foreign-key'] ) )	{
			$queryClause = (($queryClause!='')?"({$queryClause}) AND ":'') . "{$tableInfo['foreign-key']} = {$this->mainTalbeKeyValue}";
		}
		if ( $queryClause != '' )	{
			$queryClause = "WHERE {$queryClause}";
		}
		
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
		
		if ($tableName == $this->mainTableName) 	{
			$sql = "SELECT count(*) FROM {$tableName} {$queryClause} {$sortClause}";
			if ( $this->isDebug )	$this->debugMessage[] = $sql;
			$result = mysql_query($sql);
			if (!$result) {
				$this->errorMessage[] = 'MySQL Query Error: ' . mysql_error();
				return array();
			}
			$row = mysql_fetch_row($result);
			$this->mainTableCount = $row[0];
		}
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
		$this->sqlResult[$tableName] = array();
		while( $row = mysql_fetch_array($result, MYSQL_ASSOC) )	{
			$rowArray = array();
			foreach( $row as $field => $val )	{
				$filedInForm = $field;
				if ( $this->skip != 1 || $tableName != $this->mainTableName )	{
					$filedInForm = "{$tableName}{$this->separator}{$field}";
				}
				$rowArray[$field] = $this->formatterFromDB( $filedInForm, $val );
			}
			$this->sqlResult[$tableName][] = $rowArray;
			if ( $this->mainTableName == $tableName )	{
				$this->mainTalbeKeyValue = $rowArray[$tableInfo['key']];
			}
		}
		mysql_free_result($result);
		return $this->sqlResult[$tableName];
	}
	
	function setToDB( $tableName, $data )	{
		$tableInfo = $this->getTableInfo( $tableName );
		$keyFieldName = $tableInfo['key'];
		if ( ! $tableInfo['key'] || ! isset( $data[$keyFieldName] ))	{
			$this->errorMessage[] = "Error: Can't get the key field value : table={$tableName}, key={$keyFieldName}";
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
				$filedInForm = $field;
				if ( $this->skip == 1 && $tableName == $this->mainTableName )	{
					$filedInForm = "{$tableName}{$this->separator}{$field}";
				}
				$convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
				$convVal = addcslashes( $this->formatterToDB( $filedInForm, $convVal ), "\'\"\\");
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
		$tableInfo = $this->getTableInfo( $tableName );
		$keyFieldName = $tableInfo['key'];
		
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
				$filedInForm = $field;
				if ( $this->skip == 1 && $tableName == $this->mainTableName )	{
					$filedInForm = "{$tableName}{$this->separator}{$field}";
				}
				$convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
				$convVal = addcslashes( $this->formatterToDB( $filedInForm, $convVal ), "\'\"\\");
				$setCaluse[] = "{$field}='{$convVal}'";
			}
		}
		if ( count( $setCaluse ) < 1 )	{
			$this->errorMessage[] = 'No data to update.';
			return false;
		}
		$setCaluse = implode( ',', $setCaluse );
		$sql = "INSERT {$tableName} SET {$setCaluse}";
		if ( $this->isDebug )	$this->debugMessage[] = $sql;
		$result = mysql_query($sql);
		if (!$result) {
			$this->errorMessage[] = 'MySQL Query Error: ' . mysql_error();
			return false;
		}
		$keyValue = mysql_insert_id();
		return true;
	}
	
	function deleteFromDB( $tableName, $data )	{
		$tableInfo = $this->getTableInfo( $tableName );
		$keyFieldName = $tableInfo['key'];
		if ( ! $tableInfo['key'] || ! isset( $data[$keyFieldName] ))	{
			$this->errorMessage[] = 'Error: Can\'t get the key field value';
			return false;
		}
		if ( $data[$keyFieldName] == '' )	{
			$this->errorMessage[] = 'Error: Should specify valid key value.';
			return false;
		}
		
		require( 'params.php' );
		$this->link = mysql_connect( $mysql_connect, $this->dbSpec['user'], $this->dbSpec['password'] );
		if ( ! $this->link ) {
			$this->errorMessage[] = 'MySQL Connect Error: ' . mysql_error();
			return false;
		}
		mysql_select_db( $this->dbSpec['db'] );
		
		$whereClause = array();
		foreach ( $data as $field=>$value )	{
			$convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
			$convVal = $this->formatterToDB(
				($this->mainTableName==$tableName)?$field:"{$tableName}{$this->separator}{$field}", $convVal );
			$whereClause[] = "{$field}='{$convVal}'";
		}
		if ( count( $whereClause ) < 1 )	{
			$this->errorMessage[] = 'Don\'t delete with no ciriteria.';
			return false;
		}
		$whereClause = implode( ',', $whereClause );
		$sql = "DELETE FROM {$tableName} WHERE {$whereClause}";
		if ( $this->isDebug )	$this->debugMessage[] = $sql;
		$result = mysql_query($sql);
		if (!$result) {
			$this->errorMessage[] = 'MySQL Query Error: ' . mysql_error();
			return false;
		}
		return true;
	}
}
?>