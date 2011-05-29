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
	
	var $fx = null;
	
	function __construct()	{
	}
	
	function getFromDB( )	{
        $this->fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
		$tableInfo = $this->getDataSourceTargetArray();
        $this->fx->setCharacterEncoding( 'UTF-8' );
        $this->fx->setDBUserPass( $this->getDbSpecUser(), $this->getDbSpecPassword() );

        $limitParam = 100000000;
        if ( isset($tableInfo['records']) )	{
            $limitParam = $tableInfo['records'];
        }
        if ( $this->recordCount > 0 )	{
            $limitParam = $this->recordCount;
        }

        $this->fx->setDBData( $this->getDbSpecDatabase(), $this->tableName, $limitParam );
        $this->fx->FMSkipRecords( $this->start );

        /*	if ( $this->isMainTable( $tableName ) )	{
            foreach( $this->extraCriteria as $field=>$value )	{
                $this->fx->AddDBParam( $field, $value, 'eq' );
            }
        }
    */	if ( isset( $tableInfo['query'] ))	{
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
        foreach ( $this->extraCriteria as $value )	{
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $this->fx->AddDBParam( $value['field'], $value['value'], $op );
        }
        if ( $this->parentKeyValue != null && isset( $tableInfo['foreign-key'] ))	{
            $this->fx->AddDBParam( $tableInfo['foreign-key'], $this->parentKeyValue, 'eq' );
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
        $fxResult = $this->fx->DoFxAction( FX_ACTION_FIND, TRUE, TRUE, 'full' );
        if( $fxResult['errorCode'] != 0 )	{
            $this->errorMessage[] = "FX reports error at find action: "
                . "code={$fxResult['errorCode']}, url={$fxResult['URL']}";
            return '';
        }
        $this->setDebugMessage( $fxResult['URL'] );
        //$this->setDebugMessage( arrayToJS( $fxResult['data'], '' ));
        $this->mainTableCount = $fxResult['foundCount'];

        $returnArray = array();
        if ( isset( $fxResult['data'] ))	{
            foreach( $fxResult['data'] as $oneRecord )	{
                $oneRecordArray = array();
                foreach( $oneRecord as $field=>$dataArray )	{
                    if ( count( $dataArray ) == 1 )	{
                        $oneRecordArray[$field] = $this->formatterFromDB(
                            "{$this->tableName}{$this->separator}$field", $dataArray[0] );
                    }
                }
                $returnArray[] = $oneRecordArray;
            }
        }
        return $returnArray;
    }
	
	function unifyCRLF( $str )	{
		return str_replace( "\n", "\r", str_replace( "\r\n", "\r", $str ));
	}
	
	function setToDB( )	{
        $this->fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
		$tableInfo = $this->getDataSourceTargetArray();
		$keyFieldName = $tableInfo['key'];
		$this->fx->setCharacterEncoding( 'UTF-8' );
		$this->fx->setDBUserPass( $this->getDbSpecUser(), $this->getDbSpecPassword() );
		$this->fx->setDBData( $this->getDbSpecDatabase(), $this->tableName, 1 );
	//	$this->fx->AddDBParam( $keyFieldName, $data[$keyFieldName], 'eq' );
		foreach ( $this->extraCriteria as $value )	{
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $this->fx->AddDBParam( $value['field'], $value['value'], $op );
        }
		$result = $this->fxResult = $this->fx->FMFind();
		if ( $this->isDebug )	$this->debugMessage[] = $result['URL'];
		if( $result['errorCode'] > 0 )	{
			$this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
			return false;
		}
		$recId = 0;
		if ( $result[ 'foundCount' ] == 1 )	{
			foreach( $result['data'] as $key=>$row )	{
				$recId =  substr( $key, 0, strpos( $key, '.' ) );
				
				$this->fx->setCharacterEncoding( 'UTF-8' );
				$this->fx->setDBUserPass( $this->dbSpec['user'], $this->dbSpec['password'] );
				$this->fx->setDBData( $this->dbSpec['db'], $this->tableName, 1 );
				$this->fx->SetRecordID( $recId );
				$counter = 0;
				foreach ( $this->fieldsRequired as $field )	{
					$value = $this->fieldsValues[$counter];
					$counter++;
					$convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
					$convVal = $this->formatterToDB( $field, $convVal );
					$this->fx->AddDBParam( $field, $convVal );
				}
				if ( $counter < 1 )	{
					$this->errorMessage[] = 'No data to update.';
					return false;
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
					$this->errorMessage[] = "FX reports error at edit action: table={$this->tableName}, code={$result['errorCode']}, url={$result['URL']}<hr>";
					return false;
				}
				if ( $this->isDebug )	$this->debugMessage[] = $result['URL'];
				break;
			}
		} else {
			
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