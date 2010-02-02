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
	
	var $dbSpec = null;
	var $dataSource = null;
	var $mainTableName = null;
	var $mainTableCount = 0;
	var $mainTalbeKeyValue = null;
	var $formatter = null;
	var $separator = null;
	var $start = null;
	var $skip = null;
	var $errorMessage = array();
	var $debugMessage = array();
	var $isDebug = false;
	
	function __construct()	{
	}
	
	function setDebugMessage( $str )		{
		$this->debugMessage[] = $str;
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
	
	function setDBSpec( $dbspec )			{	
		$this->dbSpec = $dbspec;
	}
	
	function setDebugMode()	{
		$this->isDebug = true;
	}
	
	function setSeparator( $sep )			{
		$this->separator = $sep;
	}
	
	function setDataSource( $src )			{	
		$this->dataSource = $src;
		$this->mainTableName = $src[0]['name'];
	}
	
	function setCreteria( $criteria )		{	
		$this->criteria = $criteria;	
	}
	
	function setSortOrder( $sortorder )		{	
		$this->sortOrder = $sortorder;	
	}
	
	function setStartSkip( $st, $sk )		{	
		$this->start = $st;	$this->skip = $sk;	
	}
	
	function getTableInfo( $tableName )	{
		for ( $tableNum = 0 ; $tableNum < count($this->dataSource) ; $tableNum++ )	{
			if( $this->dataSource[$tableNum]['name'] == $tableName)	{
				return $this->dataSource[$tableNum];
				break;
			}
		}
		return array();
	}
	
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
	
	function getMainTableCount()	{	
		return $this->mainTableCount;
	}

	function getFromDB( $tableName )	{	}
	
	function setToDB( $tableName, $data )	{	}
	
	function newToDB( $tableName, $data, &$keyValue )	{	}
	
	function deleteFromDB( $tableName, $data )	{	}
}
?>