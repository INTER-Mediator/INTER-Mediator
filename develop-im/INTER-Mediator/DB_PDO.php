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

    function errorMessageStore( $str )    {
        $errorInfo = var_export($this->link->errorInfo(),true);
		$this->errorMessage[] = "Query Error: [{$str}] Code={$this->link->errorCode()} Info = {$errorInfo}";
    }

    /*
     * Generate SQL style WHERE clause.
     */
	function getWhereClause()	{
		$tableInfo = $this->getDataSourceTargetArray();
		$queryClause = '';
		$queryClauseArray = array();
		if ( isset( $tableInfo['query'][0] ))	{
            $chanckCount = 0;
            $insideOp = ' AND ';	$outsiceOp = ' OR ';
            foreach( $tableInfo['query'] as $condition )	{
                if ( $condition['field'] == '__operation__' )	{
                    $chanckCount++;
                    if ( $condition['operator'] == 'ex' )	{
                        $insideOp = ' OR ';	$outsiceOp = ' AND ';
                    }
                } else {
                    if ( isset( $condition['value'] ))	{
                        $escedVal = $this->link->quote( $condition['value'] );
                        if ( isset( $condition['operator'] ))	{
                            $queryClauseArray[$chanckCount][]
                                    = "{$condition['field']} {$condition['operator']} {$escedVal}";
                        } else {
                            $queryClauseArray[$chanckCount][]
                                    = "{$condition['field']} = {$escedVal}";
                        }
                    } else {
                        $queryClauseArray[$chanckCount][]
                                = "{$condition['field']} {$condition['operator']}";
                    }
                    $chanckCount++;
                }
            }
            foreach( $queryClauseArray as $oneTerm )	{
                $oneClause[] = '(' . implode( $insideOp, $oneTerm ) . ')';
            }
            $queryClause = implode( $outsiceOp, $oneClause );
        }

		$queryClauseArray = array();
		foreach( $this->extraCriteria as $criteria )	{
            $field = $criteria['field'];
            $operator = isset($criteria['operator'])? $criteria['operator'] :'=';
            $escedVal = $this->link->quote( $criteria['value'] );
            $queryClauseArray[] = "({$field} {$operator} {$escedVal})";
 		}
		if ( count($queryClauseArray) > 0 )	{
			if ( $queryClause != '' )	{
				$queryClauseArray[] = $queryClause;
			}
			$queryClause = implode( ' AND ', $queryClauseArray );
		}
        if ( count( $this->foreignFieldAndValue ) > 0 )	{
            foreach( $this->foreignFieldAndValue as $foreignDef )    {
                foreach( $tableInfo['relation'] as $relDef )    {
                    if ( $relDef['foreign-key'] == $foreignDef['field'] )    {
                        $escedVal = $this->link->quote( $foreignDef['value'] );
                        if ( isset($relDef['operator']) )  {
                            $op = $relDef['operator'];
                        } else {
                            $op = 'eq';
                        }
                        $queryClause = (($queryClause!='')?"({$queryClause}) AND ":'')
                                       . "{$foreignDef['field']}{$op}{$escedVal}";
                    }
                }
            }
		}
	/*	if ( isset( $tableInfo['foreign-key'] ) && isset($this->parentKeyValue) )	{
			$queryClause = (($queryClause!='')?"({$queryClause}) AND ":'')
				. "{$tableInfo['foreign-key']} = {$this->parentKeyValue}";
		}
	*/
        return $queryClause;
	}


    /* Genrate SQL Sort and Where clause */
	function getSortClause()	{
		$tableInfo = $this->getDataSourceTargetArray();
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
		return implode( ',', $sortClause);
	}

	function getFromDB(  )	{
		$tableInfo = $this->getDataSourceTargetArray();
        $tableName = $this->getEntityForRetrieve();

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
                            $this->errorMessageStore( 'Pre-script:'+$sql );
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
			$this->errorMessageStore( 'Select:'+$sql );
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
			$this->errorMessageStore( 'Select:'+$sql );
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
							$this->errorMessageStore( 'Post-script:'+$sql );;
						}
					}
				}
			}
		}
		return $this->sqlResult;
	}
	
	function setToDB( )	{
        $tableName = $this->getEntityForUpdate();
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
				if ( $condition['db-operation'] == 'update' && $condition['situation'] == 'pre' )	{
                    $sql = $condition['definition'];
                    if ( $this->isDebug )	{
                        $this->debugMessage[] = $sql;
                    }
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore( 'Pre-script:'+$sql );
                        return false;
                    }
				}
			}
		}

		$setCaluse = array();
        $setParameter = array();
		$counter = 0;
		foreach ( $this->fieldsRequired as $field )	{
			$value = $this->fieldsValues[$counter];
			$counter++;
			$convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
            $convVal = $this->formatterToDB( $field, $convVal );
        //    $convVal = $this->link->quote( $this->formatterToDB( $field, $convVal ));
        	$setCaluse[] = "{$field}=?";
            $setParameter[] = $convVal;
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
		$sql = "UPDATE {$tableName} SET {$setCaluse} {$queryClause}";
        $prepSQL = $this->link->prepare( $sql );
		if ( $this->isDebug )	{
            $this->debugMessage[] = $prepSQL->queryString;
        }
	//	$result = $this->link->query($sql);
        $result = $prepSQL->execute( $setParameter );
		if ( $result === false ) {
			$this->errorMessageStore( 'Update:'+$prepSQL->erroInfo );
			return false;
		}

		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'update' && $condition['situation'] == 'post' )	{
                    $sql = $condition['definition'];
                    if ( $this->isDebug )	$this->debugMessage[] = $sql;
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore( 'Post-script:'+$sql );
                        return false;
					}
				}
			}
		}

		return true;
	}
	
	function newToDB( )	{
        $tableInfo = $this->getDataSourceTargetArray();
        $tableName = $this->getEntityForUpdate();

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
				if ( $condition['db-operation'] == 'new' && $condition['situation'] == 'pre' )	{
                    $sql = $condition['definition'];
                    if ( $this->isDebug )	{
                        $this->debugMessage[] = $sql;
                    }
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore( 'Pre-script:'+$sql );
                        return false;
                    }
				}
			}
		}

		$setCaluse = array();
        $countFields = count( $this->fieldsRequired );
		for ( $i = 0 ; $i < $countFields ; $i++ )	{
            $field = $this->fieldsRequired[$i];
            $value = $this->fieldsValues[$i];
            $filedInForm = "{$tableName}{$this->separator}{$field}";
            $convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
            $convVal = $this->link->quote( $this->formatterToDB( $filedInForm, $convVal ));
            $setCaluse[] = "{$field}={$convVal}";
		}
		$setCaluse = (count( $setCaluse ) == 0) ? "{$tableInfo['key']}=DEFAULT"
                : implode( ',', $setCaluse );
		$sql = "INSERT {$tableName} SET {$setCaluse}";
		if ( $this->isDebug )	{
            $this->debugMessage[] = $sql;
        }
		$result = $this->link->query($sql);
        if ( $result === false ) {
            $this->errorMessageStore( 'Insert:'+$sql );
            return false;
        }
        $lastKeyValue = $this->link->lastInsertId ( $tableInfo['key'] );
        
		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'new' && $condition['situation'] == 'post' )	{
                    $sql = $condition['definition'];
                    if ( $this->isDebug )	{
                        $this->debugMessage[] = $sql;
                    }
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore( 'Post-script:'+$sql );
                        return false;
 					}
				}
			}
		}

		return $lastKeyValue;
	}
	
	function deleteFromDB( )	{
        $tableInfo = $this->getDataSourceTargetArray();
        $tableName = $this->getEntityForUpdate();

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
				if ( $condition['db-operation'] == 'delete' && $condition['situation'] == 'pre' )	{
                    $sql = $condition['definition'];
                    if ( $this->isDebug )	{
                        $this->debugMessage[] = $sql;
                    }
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore( 'Pre-script:'+$sql );
                        return false;
                    }
				}
			}
		}
/*
		$whereClause = array();
        $countFields = count( $this->fieldsRequired );
		for ( $i = 0 ; $i < $countFields ; $i++ )	{
            $field = $this->fieldsRequired[$i];
            $value = $this->fieldsValues[$i];
            $filedInForm = "{$tableName}{$this->separator}{$field}";
            $convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
            $convVal = $this->link->quote( $this->formatterToDB( $filedInForm, $convVal ));
            $whereClause[] = "{$field}={$convVal}";
		}
		if ( count( $whereClause ) < 1 )	{
			$this->errorMessageStore( 'Don\'t delete with no ciriteria.' );
			return false;
		}
		$whereClause = implode( ',', $whereClause );    */
        $queryClause = $this->getWhereClause();
        if ( $queryClause == '' )	{
            $this->errorMessageStore( 'Don\'t delete with no ciriteria.' );
			return false;
        }
		$sql = "DELETE FROM {$tableName} WHERE {$queryClause}";
		if ( $this->isDebug )	{
            $this->debugMessage[] = $sql;
        }
		$result = $this->link->query($sql);
		if (!$result) {
			$this->errorMessageStore( 'Delete Error:'+$sql );
			return false;
		}

		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'delete' &&  $condition['situation'] == 'post' )	{
                    $sql = $condition['definition'];
                    if ( $this->isDebug )	{
                        $this->debugMessage[] = $sql;
                    }
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore( 'Post-script:'+$sql );
                        return false;
 					}
				}
			}
		}

		return true;
	}
}
?>