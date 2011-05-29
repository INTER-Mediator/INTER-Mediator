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

class DB_PDO extends DB_Base	{
	
	var $sqlResult = array();
	var $link = null;
	
	function __construct()	{
	}
	
	function getFromDB(  )	{
		$tableName = $this->tableName;
		$tableInfo = $this->getDataSourceTargetArray();
		
		try {
			$this->link = new PDO( 	$this->getDbSpecDSN(),
									$this->getDbSpecUser(),
									$this->getDbSpecPassword(),
									is_array($this->getDbSpecOption())?$this->getDbSpecOption():array());
		} catch( PDOException $ex )	{
			$this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
			return array();
		}
		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'load' )	{
					if ( $condition['situation'] == 'pre' )	{
						$sql = $condition['definition'];
						if ( $this->isDebug )	$this->debugMessage[] = $sql;
						$result = $this->link->query($sql);
						if ( $result === false ) {
							$this->errorMessage[] = 'Query Error: Code=' 
													. $this->link->errorCode()
													. ' Info=' . $this->link->errorInfo();
						}
					}
				}
			}
		}

		$viewOrTableName = isset($tableInfo['view'])?$tableInfo['view']:$tableName;
		
		$queryClause = $this->getWhereClause();
		if ( $queryClause != '' )	{
			$queryClause = "WHERE {$queryClause}";
		}
		$sortClause = $this->getSortClause();
		if ( $sortClause != '' )	{
			$sortClause = "ORDER BY {$sortClause}";
		}	

        // Count all records matched with the condtions
		$sql = "SELECT count(*) FROM {$viewOrTableName} {$queryClause}";
		if ( $this->isDebug )	$this->debugMessage[] = $sql;
		$result = $this->link->query($sql);
		if ( $result === false ) {
			$this->errorMessage[] = 'Query Error: Code=' . $this->link->errorCode()
											. ' Info=' . $this->link->errorInfo();
            return array();
		}
		$this->mainTableCount = $result->fetchColumn(0);

        // Create SQL
		$limitParam = 100000000;
		if ( isset($tableInfo['records']) )	{
			$limitParam = $tableInfo['records'];
		}
		if ( $this->recordCount > 0 )	{
			$limitParam = $this->recordCount;
		}
        $skipParam = 0;
        if ( isset($tableInfo['paging']) and $tableInfo['paging'] == true ) {
            $skipParam = $this->start;
        }
		$sql = "SELECT * FROM {$viewOrTableName} {$queryClause} {$sortClause} "
				. " LIMIT {$limitParam} OFFSET {$skipParam}";
		if ( $this->isDebug )	$this->debugMessage[] = $sql;

        // Query
		$result = $this->link->query($sql);
		if ( $result === false ) {
			$this->errorMessage[] = 'Query Error: Code=' . $this->link->errorCode()
									. ' Info=' . var_export($this->link->errorInfo(), true);
			return array();
		}
		$this->sqlResult = array();
		foreach( $result->fetchAll( PDO::FETCH_ASSOC ) as $row )	{
			$rowArray = array();
			foreach( $row as $field => $val )	{
			//	$filedInForm = $field;
				$filedInForm = "{$tableName}{$this->separator}{$field}";
				$rowArray[$field] = $this->formatterFromDB( $filedInForm, $val );
			}
			$this->sqlResult[] = $rowArray;
		}

		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'load' )	{
					if ( $condition['situation'] == 'post' )	{
						$sql = $condition['definition'];
						if ( $this->isDebug )	$this->debugMessage[] = $sql;
						$result = $this->link->query($sql);
						if ( $result === false ) {
							$this->errorMessage[] = 'Query Error: Code=' . $this->link->errorCode()
															. ' Info=' . $this->link->errorInfo();
						}
					}
				}
			}
		}
		return $this->sqlResult;
	}
	
	function setToDB( )	{
		try {
			$this->link = new PDO( 	$this->getDbSpecDSN(),
									$this->getDbSpecUser(),
									$this->getDbSpecPassword(),
									is_array($this->getDbSpecOption())?$this->getDbSpecOption():array());
		} catch( PDOException $ex )	{
			$this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
			return false;
		}
		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'update' )	{
					if ( $condition['situation'] == 'pre' )	{
						$sql = $condition['definition'];
						if ( $this->isDebug )	$this->debugMessage[] = $sql;
						$result = $this->link->query($sql);
						if (!$result) {
							$this->errorMessage[] 
								= 'Query Error: Code=' . $this->link->errorCode()
									. ' Info=' . $this->link->errorInfo()
									. ' ';
							return false;
						}
					}
				}
			}
		}

		$setCaluse = array();
		$counter = 0;
		foreach ( $this->fieldsRequired as $field )	{
			$value = $this->fieldsValues[$counter];
			$counter++;
			$convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
			$convVal = $this->link->quote( $this->formatterToDB( $field, $convVal ));
			$setCaluse[] = "{$field}={$convVal}";
		}
		if ( count( $setCaluse ) < 1 )	{
			$this->errorMessage[] = 'No data to update.';
			return false;
		}
		$setCaluse = implode( ',', $setCaluse );

		$queryClause = $this->getWhereClause();
		if ( $queryClause != '' )	{
			$queryClause = "WHERE {$queryClause}";
		}
		$sql = "UPDATE {$this->tableName} SET {$setCaluse} {$queryClause}";
		if ( $this->isDebug )	$this->debugMessage[] = $sql;
		$result = $this->link->query($sql);
		if ( $result === false ) {
			$this->errorMessage[] = 'Query Error: SQL =' . $sql
											. ' Code=' . $this->link->errorCode()
											. ' Info=' . var_export($this->link->errorInfo(),true);
			return false;
		}

		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'update' )	{
					if ( $condition['situation'] == 'post' )	{
						$sql = $condition['definition'];
						if ( $this->isDebug )	$this->debugMessage[] = $sql;
						$result = $this->link->query($sql);
						if (!$result) {
							$this->errorMessage[] = 'Query Error: Code=' . $this->link->errorCode()
															. ' Info=' . $this->link->errorInfo();
							return false;
						}
					}
				}
			}
		}

		return true;
	}
	
	function newToDB( $tableName, $data, &$keyValue )	{
        $tableName = $this->tableName;
        $tableInfo = $this->getDataSourceTargetArray();

		$keyFieldName = $tableInfo['key'];
		
		require( 'params.php' );
		$this->link = mysql_connect( $mysql_connect, $this->dbSpec['user'], $this->dbSpec['password'] );
		if ( ! $this->link ) {
			$this->errorMessage[] = 'MySQL Connect Error: ' . mysql_error();
			return false;
		}
		mysql_select_db( $this->dbSpec['db'] );
		
		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'new' )	{
					if ( $condition['situation'] == 'pre' )	{
						$sql = $condition['definition'];
						if ( $this->isDebug )	$this->debugMessage[] = $sql;
						$result = mysql_query($sql);
						if (!$result) {
							$this->errorMessage[] = 'MySQL Query Error at pre-script: ' . mysql_error();
						}
						mysql_free_result($result);
					}
				}
			}
		}

		$setCaluse = array();
		foreach ( $data as $field=>$value )	{
			if ( $field != $keyFieldName){
				$filedInForm = $field;
				if ( $this->skip == 1 && $tableName == $this->tableName )	{
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
		mysql_free_result($result);

		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'new' )	{
					if ( $condition['situation'] == 'post' )	{
						$sql = $condition['definition'];
						if ( $this->isDebug )	$this->debugMessage[] = $sql;
						$result = mysql_query($sql);
						if (!$result) {
							$this->errorMessage[] = 'MySQL Query Error at pre-script: ' . mysql_error();
						}
						mysql_free_result($result);
					}
				}
			}
		}

		return true;
	}
	
	function deleteFromDB( )	{
        $tableName = $this->tableName;
        $tableInfo = $this->getDataSourceTargetArray();

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
		
		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'delete' )	{
					if ( $condition['situation'] == 'pre' )	{
						$sql = $condition['definition'];
						if ( $this->isDebug )	$this->debugMessage[] = $sql;
						$result = mysql_query($sql);
						if (!$result) {
							$this->errorMessage[] = 'MySQL Query Error at pre-script: ' . mysql_error();
						}
						mysql_free_result($result);
					}
				}
			}
		}

		$whereClause = array();
		foreach ( $data as $field=>$value )	{
			$convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
			$convVal = $this->formatterToDB(
				($this->tableName==$tableName)?$field:"{$tableName}{$this->separator}{$field}", $convVal );
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
		mysql_free_result($result);

		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'delete' )	{
					if ( $condition['situation'] == 'post' )	{
						$sql = $condition['definition'];
						if ( $this->isDebug )	$this->debugMessage[] = $sql;
						$result = mysql_query($sql);
						if (!$result) {
							$this->errorMessage[] = 'MySQL Query Error at pre-script: ' . mysql_error();
						}
						mysql_free_result($result);
					}
				}
			}
		}

		return true;
	}
}
?>