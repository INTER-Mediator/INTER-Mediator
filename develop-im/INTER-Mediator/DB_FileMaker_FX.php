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

$currentEr = error_reporting();
error_reporting( 0 );
include_once( 'FX/FX.php' );
if ( error_get_last() !== null ) {	// If FX.php isn't installed in valid directories, it shows error message and finishes.
	echo 'INTER-Mediator Error: Data Access Class "FileMaker_FX" requires FX.php on any right directory.';
	return;
}
error_reporting( $currentEr );

class DB_FileMaker_FX extends DB_Base	{
	
	var $fxResult = array();
	var $fx = null;
	
	function __construct()	{
		require( 'params.php' );
		$this->fx = new FX( $fx_server, $fx_port, $fx_dataType, $fx_urlType );
	}
	
	function getFromDB( $tableName )	{
		$tableInfo = $this->getTableInfo( $tableName );
		if ( ! isset( $tableInfo['foreign-key'] ) )	{
			if ( ! isset( $this->fxResult[$tableName] ) )	{
				$this->fx->setCharacterEncoding( 'UTF-8' );
				$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
				if ($tableName == $this->mainTableName) 	{
					$this->fx->setDBData( $this->dbSpec['db'], $tableName, $this->skip );
					$this->fx->FMSkipRecords( $this->start );
				} else {
					$this->fx->setDBData( $this->dbSpec['db'], $tableName, 1000000 );
				}
				if ( $this->isMainTable( $tableName ) )	{
					foreach( $this->extraCriteria as $field=>$value )	{
						$this->fx->AddDBParam( $field, $value, 'eq' );
					}
				}
				if ( isset( $tableInfo['query'] ))	{
					foreach( $tableInfo['query'] as $condition )	{
						if ( $condition['field'] == '__operation__' && $condition['operator'] == 'or' )	{
							$this->fx->SetLogicalOR();
						} else {
							if ( isset( $condition['operator'] ))	{
								$this->fx->AddDBParam( $condition['field'], $condition['value'], $condition['operator'] );
							} else {
								$this->fx->AddDBParam( $condition['field'], $condition['value'] );
							}
						}
					}
				}
				if ( isset( $tableInfo['sort'] ))	{
					foreach( $tableInfo['sort'] as $condition )	{
						if ( isset( $condition['direction'] ))	{
							$this->fx->AddSortParam( $condition['field'], $condition['direction'] );
						} else {
							$this->fx->AddSortParam( $condition['field'] );
						}
					}
				}
				if ( isset( $tableInfo['global'] ))	{
					foreach( $tableInfo['global'] as $condition )	{
						if ( $condition['db-operation'] == 'load' )	{
							$this->fx->SetFMGlobal( $condition['field'], $condition['value'] );
						}
					}
				}
				if ( isset( $tableInfo['script'] ))	{
					foreach( $tableInfo['script'] as $condition )	{
						if ( $condition['db-operation'] == 'load' )	{
							if ( $condition['situation'] == 'pre' )	{
								$this->fx->PerformFMScriptPrefind( $condition['definition'] );
							} else if ( $condition['situation'] == 'presort' )	{
								$this->fx->PerformFMScriptPresort( $condition['definition'] );
							} else if ( $condition['situation'] == 'post' )	{
								$this->fx->PerformFMScript( $condition['definition'] );
							}
						}
					}
				}
				$this->fxResult[$tableName] = $this->fx->DoFxAction( FX_ACTION_FIND, TRUE, TRUE, 'full' );
				if ( $this->isDebug )	$this->debugMessage[] = $this->fxResult[$tableName]['URL'];
				if( $this->fxResult[$tableName]['errorCode'] > 0 )	{
					$this->errorMessage[] = "FX reports error at find action: code={$this->fxResult[$tableName]['errorCode']}, url={$this->fxResult[$tableName]['URL']}";
					return false;
				}
				if ( $tableName == $this->mainTableName && isset($this->fxResult[$tableName]['foundCount']))
					$this->mainTableCount = $this->fxResult[$tableName]['foundCount'];
			}
			$returnArray = array();
			if ( isset($this->fxResult[$tableName]['data'] ))	{
				foreach( $this->fxResult[$tableName]['data'] as $oneRecord )	{
					$oneRecordArray = array();
					foreach( $oneRecord as $field=>$dataArray )	{
						if ( count( $dataArray ) == 1 )	{
							if ( $this->skip == 1 && $tableName == $this->mainTableName )	{
								$oneRecordArray[$field] = $this->formatterFromDB( $field, $dataArray[0] );
							} else {
								$oneRecordArray[$field] = $this->formatterFromDB( 
											"$tableName{$this->separator}$field", $dataArray[0] );
							}
						}
					}
					$returnArray[] = $oneRecordArray;
				}
			}
			return $returnArray;
		} else {
			$fieldsArray = array();	$repeatCount = 0;
			foreach( $this->fxResult[$this->mainTableName]['data'] as $oneRecord )	{
				foreach( $oneRecord as $field=>$dataArray )	{
					if ( strpos($field, $tableName) === 0 )	{
						$pos = strpos( $field, '::');
						if ( $pos !== FALSE )	{
							$fieldsArray[] = $field;
							$repeatCount = max( $repeatCount, count($dataArray) );
						}
					}
				}
				break;
			}
			$returnArray = array();
			$counter = 0;
			foreach( $this->fxResult[$this->mainTableName]['data'] as $oneRecord )	{
				for( $i=0; $i<$repeatCount; $i++ )	{
					$oneRecordArray = array();
					foreach( $fieldsArray as $oneField )	{
						$pos = strpos( $oneField, '::');
						$fieldName = substr($oneField, $pos+2, strlen($oneField));
						$oneRecordArray[$fieldName] 
							= $this->formatterFromDB( "$tableName{$this->separator}$fieldName", $oneRecord[$oneField][$i] );
					}
					$returnArray[] = $oneRecordArray;
				}
			}
			return $returnArray;
		}
	}
	
	function unifyCRLF( $str )	{
		return str_replace( "\n", "\r", str_replace( "\r\n", "\r", $str ));
	}
	
	function setToDB( $tableName, $data )	{
		$tableInfo = $this->getTableInfo( $tableName );
		$keyFieldName = $tableInfo['key'];
		$this->fx->setCharacterEncoding( 'UTF-8' );
		$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
		$this->fx->setDBData( $this->dbSpec['db'], $tableName, 1 );
		$this->fx->AddDBParam( $keyFieldName, $data[$keyFieldName], 'eq' );
		$result = $this->fxResult = $this->fx->FMFind();
		if ( $this->isDebug )	$this->debugMessage[] = $result['URL'];
		if( $result['errorCode'] > 0 )	{
			$this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
			return false;
		}
		$recId = 0;
		if ( $result[ 'foundCount' ] != 0 )	{
			foreach( $result['data'] as $key=>$row )	{
				$recId =  substr( $key, 0, strpos( $key, '.' ) );
				
				$this->fx->setCharacterEncoding( 'UTF-8' );
				$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
				$this->fx->setDBData( $this->dbSpec['db'], $tableName, 1 );
				$this->fx->SetRecordID( $recId );
				foreach ( $data as $field=>$value )
					if ( $field != $keyFieldName){
						$filedInForm = $field;
						if ( $this->skip != 1 || $tableName != $this->mainTableName )	{
							$filedInForm = "{$tableName}{$this->separator}{$field}";
						}
						$convVal = $this->unifyCRLF( (is_array( $value )) ? implode( "\r", $value ) : $value );
						$this->fx->AddDBParam( $field, $this->formatterToDB( $filedInForm, $convVal ));
					}
				if ( isset( $tableInfo['global'] ))	{
					foreach( $tableInfo['global'] as $condition )	{
						if ( $condition['db-operation'] == 'update' )	{
							$this->fx->SetFMGlobal( $condition['field'], $condition['value'] );
						}
					}
				}
				if ( isset( $tableInfo['script'] ))	{
					foreach( $tableInfo['script'] as $condition )	{
						if ( $condition['db-operation'] == 'update' )	{
							if ( $condition['situation'] == 'pre' )	{
								$this->fx->PerformFMScriptPrefind( $condition['definition'] );
							} else if ( $condition['situation'] == 'presort' )	{
								$this->fx->PerformFMScriptPresort( $condition['definition'] );
							} else if ( $condition['situation'] == 'post' )	{
								$this->fx->PerformFMScript( $condition['definition'] );
							}
						}
					}
				}
				$result = $this->fx->FMEdit();
				if( $result['errorCode'] > 0 )	{
					$this->errorMessage[] = "FX reports error at edit action: table={$tableName}, code={$result['errorCode']}, url={$result['URL']}<hr>";
					return false;
				}
				if ( $this->isDebug )	$this->debugMessage[] = $result['URL'];
				break;
			}
		}
		return true;
	}
	
	function newToDB( $tableName, $data, &$keyValue )	{
		$tableInfo = $this->getTableInfo( $tableName );
		$keyFieldName = $tableInfo['key'];

		$this->fx->setCharacterEncoding( 'UTF-8' );
		$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
		$this->fx->setDBData( $this->dbSpec['db'], $tableName, 1 );
		foreach ( $data as $field=>$value )	{
			if ( $field != $keyFieldName){
				$filedInForm = $field;
				if ( $this->skip != 1 || $tableName != $this->mainTableName )	{
					$filedInForm = "{$tableName}{$this->separator}{$field}";
				}
				$convVal = $this->unifyCRLF( (is_array( $value )) ? implode( "\r", $value ) : $value );
				$this->fx->AddDBParam( $field, $this->formatterToDB( $filedInForm, $convVal ));
			}
		}
		if ( isset( $tableInfo['global'] ))	{
			foreach( $tableInfo['global'] as $condition )	{
				if ( $condition['db-operation'] == 'new' )	{
					$this->fx->SetFMGlobal( $condition['field'], $condition['value'] );
				}
			}
		}
		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'new' )	{
					if ( $condition['situation'] == 'pre' )	{
						$this->fx->PerformFMScriptPrefind( $condition['definition'] );
					} else if ( $condition['situation'] == 'presort' )	{
						$this->fx->PerformFMScriptPresort( $condition['definition'] );
					} else if ( $condition['situation'] == 'post' )	{
						$this->fx->PerformFMScript( $condition['definition'] );
					}
				}
			}
		}
		$result = $this->fx->FMNew();
		if ( $this->isDebug )	$this->debugMessage[] = $result['URL'];
		if( $result['errorCode'] > 0 && $result['errorCode'] != 401 )	{
			$this->errorMessage[] = "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>";
			return false;
		}
		foreach( $result['data'] as $row )	{
			$keyValue = $row[$keyFieldName][0];
		}
		return true;
	}
	
	function deleteFromDB( $tableName, $data )	{
		$this->fx->setCharacterEncoding( 'UTF-8' );
		$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
		$this->fx->setDBData( $this->dbSpec['db'], $tableName, 1 );
		foreach( $data as $field=>$val )	{
			$this->fx->AddDBParam( $field, $val, 'eq' );
		}
		$result = $this->fxResult = $this->fx->FMFind();
		if ( $this->isDebug )	$this->debugMessage[] = $result['URL'];
		if( $result['errorCode'] > 0 )	{
			$this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
			return false;
		}
		$recId = 0;
		if ( $result[ 'foundCount' ] != 0 )	{
			foreach( $result['data'] as $key=>$row )	{
				$recId =  substr( $key, 0, strpos( $key, '.' ) );
				
				$this->fx->setCharacterEncoding( 'UTF-8' );
				$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
				$this->fx->setDBData( $this->dbSpec['db'], $tableName, 1 );
				$this->fx->SetRecordID( $recId );
				if ( isset( $tableInfo['global'] ))	{
					foreach( $tableInfo['global'] as $condition )	{
						if ( $condition['db-operation'] == 'delete' )	{
							$this->fx->SetFMGlobal( $condition['field'], $condition['value'] );
						}
					}
				}
				if ( isset( $tableInfo['script'] ))	{
					foreach( $tableInfo['script'] as $condition )	{
						if ( $condition['db-operation'] == 'delete' )	{
							if ( $condition['situation'] == 'pre' )	{
								$this->fx->PerformFMScriptPrefind( $condition['definition'] );
							} else if ( $condition['situation'] == 'presort' )	{
								$this->fx->PerformFMScriptPresort( $condition['definition'] );
							} else if ( $condition['situation'] == 'post' )	{
								$this->fx->PerformFMScript( $condition['definition'] );
							}
						}
					}
				}
				$result = $this->fx->FMDelete();
				if( $result['errorCode'] > 0 )	{
					$this->errorMessage[] = "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>";
					return false;
				}
				if ( $this->isDebug )	$debugMessage[] = $result['URL'];
				break;
			}
		}
		return true;
	}
}
?>