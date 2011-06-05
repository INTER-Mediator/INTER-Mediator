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
	
	function __construct()	{
	}
	
	function getFromDB( )	{
        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
		$tableInfo = $this->getDataSourceTargetArray();
        $fx->setCharacterEncoding( 'UTF-8' );
        $fx->setDBUserPass( $this->getDbSpecUser(), $this->getDbSpecPassword() );
        $limitParam = 100000000;
        if ( isset($tableInfo['records']) )	{
            $limitParam = $tableInfo['records'];
        }
        if ( $this->recordCount > 0 )	{
            $limitParam = $this->recordCount;
        }

        $fx->setDBData( $this->getDbSpecDatabase(), $this->tableName, $limitParam );
        $skipParam = 0;
        if ( isset($tableInfo['paging']) and $tableInfo['paging'] == true ) {
            $skipParam = $this->start;
        }
        $fx->FMSkipRecords( $skipParam );

       	if ( isset( $tableInfo['query'] ))	{
            foreach( $tableInfo['query'] as $condition )	{
                if ( $condition['field'] == '__operation__' && $condition['operator'] == 'or' )	{
                    $fx->SetLogicalOR();
                } else {
                    if ( isset( $condition['operator'] ))	{
                        $fx->AddDBParam( $condition['field'], $condition['value'], $condition['operator'] );
                    } else {
                        $fx->AddDBParam( $condition['field'], $condition['value'] );
                    }
                }
            }
        }
        foreach ( $this->extraCriteria as $value )	{
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $fx->AddDBParam( $value['field'], $value['value'], $op );
        }
        if ( $this->parentKeyValue != null && isset( $tableInfo['foreign-key'] ))	{
            $fx->AddDBParam( $tableInfo['foreign-key'], $this->parentKeyValue, 'eq' );
        }
        if ( isset( $tableInfo['sort'] ))	{
            foreach( $tableInfo['sort'] as $condition )	{
                if ( isset( $condition['direction'] ))	{
                    $fx->AddSortParam( $condition['field'], $condition['direction'] );
                } else {
                    $fx->AddSortParam( $condition['field'] );
                }
            }
        }
        if ( isset( $tableInfo['global'] ))	{
            foreach( $tableInfo['global'] as $condition )	{
                if ( $condition['db-operation'] == 'load' )	{
                    $fx->SetFMGlobal( $condition['field'], $condition['value'] );
                }
            }
        }
        if ( isset( $tableInfo['script'] ))	{
            foreach( $tableInfo['script'] as $condition )	{
                if ( $condition['db-operation'] == 'load' )	{
                    if ( $condition['situation'] == 'pre' )	{
                        $fx->PerformFMScriptPrefind( $condition['definition'] );
                    } else if ( $condition['situation'] == 'presort' )	{
                        $fx->PerformFMScriptPresort( $condition['definition'] );
                    } else if ( $condition['situation'] == 'post' )	{
                        $fx->PerformFMScript( $condition['definition'] );
                    }
                }
            }
        }
        $fxResult = $fx->DoFxAction( FX_ACTION_FIND, TRUE, TRUE, 'full' );
        if( $fxResult['errorCode'] != 0 && $fxResult['errorCode'] != 401 )	{
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
        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
		$tableInfo = $this->getDataSourceTargetArray();
		$fx->setCharacterEncoding( 'UTF-8' );
		$fx->setDBUserPass( $this->getDbSpecUser(), $this->getDbSpecPassword() );
		$fx->setDBData( $this->getDbSpecDatabase(), $this->tableName, 1 );
	//	$fx->AddDBParam( $keyFieldName, $data[$keyFieldName], 'eq' );
		foreach ( $this->extraCriteria as $value )	{
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $fx->AddDBParam( $value['field'], $value['value'], $op );
        }
		$result = $fx->DoFxAction( FX_ACTION_FIND, TRUE, TRUE, 'full' );
		if ( $this->isDebug )	$this->debugMessage[] = $result['URL'];
		if( $result['errorCode'] > 0 )	{
			$this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
			return false;
		}
		if ( $result[ 'foundCount' ] == 1 )	{
			foreach( $result['data'] as $key=>$row )	{
				$recId =  substr( $key, 0, strpos( $key, '.' ) );
				
				$fx->setCharacterEncoding( 'UTF-8' );
				$fx->setDBUserPass( $this->getDbSpecUser(), $this->getDbSpecPassword() );
        		$fx->setDBData( $this->getDbSpecDatabase(), $this->tableName, 1 );
				$fx->SetRecordID( $recId );
				$counter = 0;
				foreach ( $this->fieldsRequired as $field )	{
					$value = $this->fieldsValues[$counter];
					$counter++;
					$convVal = (is_array( $value )) ? implode( "\n", $value ) : $value ;
					$convVal = $this->formatterToDB( $field, $convVal );
					$fx->AddDBParam( $field, $this->unifyCRLF( $convVal ));
				}
				if ( $counter < 1 )	{
					$this->errorMessage[] = 'No data to update.';
					return false;
				}
				if ( isset( $tableInfo['global'] ))	{
					foreach( $tableInfo['global'] as $condition )	{
						if ( $condition['db-operation'] == 'update' )	{
							$fx->SetFMGlobal( $condition['field'], $condition['value'] );
						}
					}
				}
				if ( isset( $tableInfo['script'] ))	{
					foreach( $tableInfo['script'] as $condition )	{
						if ( $condition['db-operation'] == 'update' )	{
							if ( $condition['situation'] == 'pre' )	{
								$fx->PerformFMScriptPrefind( $condition['definition'] );
							} else if ( $condition['situation'] == 'presort' )	{
								$fx->PerformFMScriptPresort( $condition['definition'] );
							} else if ( $condition['situation'] == 'post' )	{
								$fx->PerformFMScript( $condition['definition'] );
							}
						}
					}
				}
				$result = $fx->DoFxAction( FX_ACTION_EDIT, TRUE, TRUE, 'full' );
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
	
	function newToDB( )	{
		$tableInfo = $this->getDataSourceTargetArray();
		$keyFieldName = $tableInfo['key'];

        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding( 'UTF-8' );
        $fx->setDBUserPass( $this->getDbSpecUser(), $this->getDbSpecPassword() );
        $fx->setDBData( $this->getDbSpecDatabase(), $this->tableName, 1 );
        $countFields = count( $this->fieldsRequired );
		for ( $i = 0 ; $i < $countFields ; $i++ )	{
            $field = $this->fieldsRequired[$i];
            $value = $this->fieldsValues[$i];
			if ( $field != $keyFieldName){
				$filedInForm = "{$this->tableNam}{$this->separator}{$field}";
				$convVal = $this->unifyCRLF( (is_array( $value )) ? implode( "\r", $value ) : $value );
				$fx->AddDBParam( $field, $this->formatterToDB( $filedInForm, $convVal ));
			}
		}
		if ( isset( $tableInfo['global'] ))	{
			foreach( $tableInfo['global'] as $condition )	{
				if ( $condition['db-operation'] == 'new' )	{
					$fx->SetFMGlobal( $condition['field'], $condition['value'] );
				}
			}
		}
		if ( isset( $tableInfo['script'] ))	{
			foreach( $tableInfo['script'] as $condition )	{
				if ( $condition['db-operation'] == 'new' )	{
					if ( $condition['situation'] == 'pre' )	{
						$fx->PerformFMScriptPrefind( $condition['definition'] );
					} else if ( $condition['situation'] == 'presort' )	{
						$fx->PerformFMScriptPresort( $condition['definition'] );
					} else if ( $condition['situation'] == 'post' )	{
						$fx->PerformFMScript( $condition['definition'] );
					}
				}
			}
		}
		$result = $fx->DoFxAction( FX_ACTION_NEW, TRUE, TRUE, 'full' );
		if ( $this->isDebug )	{
            $this->debugMessage[] = $result['URL'];
        }
		if( $result['errorCode'] > 0 && $result['errorCode'] != 401 )	{
			$this->errorMessage[] = "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>";
			return false;
		}
		foreach( $result['data'] as $row )	{
			$keyValue = $row[$keyFieldName][0];
		}
		return $keyValue;
	}
	
	function deleteFromDB( )	{
        $tableInfo = $this->getDataSourceTargetArray();;
        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding( 'UTF-8' );
        $fx->setDBUserPass( $this->getDbSpecUser(), $this->getDbSpecPassword() );
        $fx->setDBData( $this->getDbSpecDatabase(), $this->tableName, 1 );
        $countFields = count( $this->fieldsRequired );
        for ( $i = 0 ; $i < $countFields ; $i++ )	{
            $field = $this->fieldsRequired[$i];
            $value = $this->fieldsValues[$i];
			$fx->AddDBParam( $field, $value, 'eq' );
		}
		$result = $this->fxResult = $fx->DoFxAction( FX_ACTION_FIND, TRUE, TRUE, 'full' );
		if ( $this->isDebug )	{
            $this->debugMessage[] = $result['URL'];
        }
		if( $result['errorCode'] > 0 )	{
			$this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
			return false;
		}
		if ( $result[ 'foundCount' ] != 0 )	{
			foreach( $result['data'] as $key=>$row )	{
				$recId =  substr( $key, 0, strpos( $key, '.' ) );
				
				$fx->setCharacterEncoding( 'UTF-8' );
                $fx->setDBUserPass( $this->getDbSpecUser(), $this->getDbSpecPassword() );
                $fx->setDBData( $this->getDbSpecDatabase(), $this->tableName, 1 );
				$fx->SetRecordID( $recId );
				if ( isset( $tableInfo['global'] ))	{
					foreach( $tableInfo['global'] as $condition )	{
						if ( $condition['db-operation'] == 'delete' )	{
							$fx->SetFMGlobal( $condition['field'], $condition['value'] );
						}
					}
				}
				if ( isset( $tableInfo['script'] ))	{
					foreach( $tableInfo['script'] as $condition )	{
						if ( $condition['db-operation'] == 'delete' )	{
							if ( $condition['situation'] == 'pre' )	{
								$fx->PerformFMScriptPrefind( $condition['definition'] );
							} else if ( $condition['situation'] == 'presort' )	{
								$fx->PerformFMScriptPresort( $condition['definition'] );
							} else if ( $condition['situation'] == 'post' )	{
								$fx->PerformFMScript( $condition['definition'] );
							}
						}
					}
				}
				$result = $fx->DoFxAction( FX_ACTION_DELETE, TRUE, TRUE, 'full' );
				if( $result['errorCode'] > 0 )	{
					$this->errorMessage[] = "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>";
					return false;
				}
				if ( $this->isDebug )	{
                    $this->debugMessage[] = $result['URL'];
                }
				break;
			}
		}
		return true;
	}
}
?>