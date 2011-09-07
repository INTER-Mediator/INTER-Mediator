<?php 
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

class DB_Base	{

    var $dbSpecServer = null;
    var $dbSpecPort = null;
    var $dbSpecUser = null;
    var $dbSpecPassword = null;
    var $dbSpecDatabase = null;
    var $dbSpecDataType = null;
    var $dbSpecProtocol = null;
    var $dbSpecDSN = null;
    var $dbSpecOption = null;

	var $dataSource = null;
    var $targetDataSource = null;
	var $extraCriteria = array();
	var $mainTableCount = 0;
	var $fieldsRequired = null;
	var $fieldsValues = null;
	var $formatter = null;
	var $separator = null;
	var $start = 0;
	var $recordCount = 0;
	var $errorMessage = array();
	var $debugMessage = array();
	var $isDebug = false;
	var $parentKeyValue = null;
    var $dataSourceName = '';

	function __construct()	{
	}

    /* Call on INTER-Mediator.php */
    function setDataSource( $src )		{   $this->dataSource = $src;       }
    function setSeparator( $sep )		{	$this->separator = $sep;        }
    function setTargetName( $val )      {   $this->dataSourceName = $val;   }
	function setParentKeyValue( $val )	{   $this->parentKeyValue = $val;   }
    function setTargetFields( $fields )	{	$this->fieldsRequired = $fields;}
    function setValues( $values )	    {	$this->fieldsValues = $values;  }
    function setStart( $st )		    {	$this->start = $st;	            }
    function setRecordCount( $sk )		{	$this->recordCount = $sk;		}
    function setExtraCriteria( $field, $operator, $value )	{
        $this->extraCriteria[] = array( 'field'=>$field, 'operator'=>$operator, 'value'=>$value);
    }

    /* get the information for the 'name'. */
    function getDataSourceTargetArray()	{
        if ( $this->targetDataSource == null )  {
            foreach( $this->dataSource as $record )	{
                if ( $record['name'] == $this->dataSourceName )	{
                    $this->targetDataSource = $record;
                    return $record;
                }
            }
        } else {
            return $this->targetDataSource;
        }
        return null;
    }

    function getEntityForRetrieve() {
        $dsrc = $this->getDataSourceTargetArray();
        if ( isset( $dsrc['view']) )  {
            return $dsrc['view'];
        }
        return $dsrc['name'];
    }

    function getEntityForUpdate()   {
        $dsrc = $this->getDataSourceTargetArray();
        if ( isset( $dsrc['table']) )  {
            return $dsrc['table'];
        }
        return $dsrc['name'];
    }

    /* Database connection paramters */
    function setDbSpecServer($str)  {   $this->dbSpecServer = $str;   }
    function getDbSpecServer()      {   return $this->dbSpecServer;   }
    function setDbSpecPort($str)    {   $this->dbSpecPort = $str;     }
    function getDbSpecPort()        {   return $this->dbSpecPort;     }
    function setDbSpecUser($str)    {   $this->dbSpecUser = $str;     }
    function getDbSpecUser()        {   return $this->dbSpecUser;     }
    function setDbSpecPassword($str){   $this->dbSpecPassword = $str; }
    function getDbSpecPassword()    {   return $this->dbSpecPassword; }
    function setDbSpecDataType($str){   $this->dbSpecDataType = $str; }
    function getDbSpecDataType()    {   return $this->dbSpecDataType; }
    function setDbSpecDatabase($str){   $this->dbSpecDatabase = $str; }
    function getDbSpecDatabase()    {   return $this->dbSpecDatabase; }
    function setDbSpecProtocol($str){   $this->dbSpecProtocol = $str; }
    function getDbSpecProtocol()    {   return $this->dbSpecProtocol; }
    function setDbSpecDSN($str)     {   $this->dbSpecDSN = $str;      }
    function getDbSpecDSN()         {   return $this->dbSpecDSN;      }
    function setDbSpecOption($str)  {   $this->dbSpecOption = $str;   }
    function getDbSpecOption()      {   return $this->dbSpecOption;   }

	// The following methods should be implemented in the inherited class.
	function getFromDB( )	{	}
	function setToDB( )	{	}
	function newToDB( )	{	}
	function deleteFromDB( )	{	}

    /* Debug and Messages */
	function setDebugMessage( $str )		{
        if ( $this->isDebug )   {
    		$this->debugMessage[] = $str;
        }
	}
	
	function setErrorMessage( $str )		{
		$this->errorMessage[] = $str;
	}
	
	function getDebugMessages()				{
		return $this->debugMessage;
	}
	
	function getErrorMessages()				{
		return $this->errorMessage;
	}

	function setDebugMode()	{
		$this->isDebug = true;
	}

	/* Formatter processing */
	function setFormatter( $fmt )		{
		if ( is_array( $fmt ))	{
			$this->formatter = array();
			foreach( $fmt as $oneItem )	{
				if( ! isset( $this->formatter[$oneItem['field']]) )	{
					require_once("DataConverter_{$oneItem['converter-class']}.php");
					$parameter = isset($oneItem['parameter']) ? $oneItem['parameter'] : '';
					eval( "\$cvInstance = new DataConverter_{$oneItem['converter-class']}('{$parameter}');" );
					$this->formatter[$oneItem['field']] = $cvInstance;
				}
			}
		}
	}
	
	function formatterFromDB( $field, $data )	{
		if ( is_array($this->formatter ))	{
			if ( isset($this->formatter[$field]) )	{
				return $this->formatter[$field]->converterFromDBtoUser( $data );
			}
		}
		return $data;
	}
	
	function formatterToDB( $field, $data )	{
		if ( is_array($this->formatter ))	{
			if ( isset($this->formatter[$field]) )	{
				return $this->formatter[$field]->converterFromUserToDB( $data );
			}
		}
		return $data;
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
		if ( isset( $tableInfo['foreign-key'] ) && isset($this->parentKeyValue) )	{
			$queryClause = (($queryClause!='')?"({$queryClause}) AND ":'') 
				. "{$tableInfo['foreign-key']} = {$this->parentKeyValue}";
		}
		return $queryClause;
	}


}
?>