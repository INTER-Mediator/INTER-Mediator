<?php

/*
 * INTER-Mediator
 * by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 * This project started at the end of 2009.
 * 
 */
mb_internal_encoding('UTF-8');

$currentDir = dirname(__FILE__);
$lang = strtolower( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
if ( strpos( $lang, ',' ) !== false )	{
	$lang = substr( $lang, 0, strpos( $lang, ',' ));
}
$candClassName = 'MessageStrings_'.$lang;
if ( ! file_exists( $currentDir . DIRECTORY_SEPARATOR . $candClassName . '.php'))	{
	if ( strpos( $lang, '-') !== false )	{
		$lang = substr( $lang, 0, strpos( $lang, '-' ));
		$candClassName = 'MessageStrings_'.$lang;
		if ( ! file_exists( $currentDir . DIRECTORY_SEPARATOR . $candClassName . '.php'))	{
			$candClassName = 'MessageStrings';
		}
	}
}
require_once( $candClassName . '.php' );
	
function InitializePage( $datasrc, $options = null, $dbspec = null, $debug=false )	{
	
	$LF = "\n";

	$caller = dirname( $_SERVER['SCRIPT_FILENAME'] );
	$relPath = '';
	for ($myself = dirname(__FILE__);$myself!=$caller || strlen($myself)>strlen($caller);$myself = dirname($myself))	{
		$relPath = basename( $myself ) . '/' . $relPath;
	}
	
	require_once( 'params.php' );
	if( ! isset( $dbspec['db-class'] )) $dbspec['db-class'] = $dbAccessClass;
	if( ! isset( $dbspec['db'] )) $dbspec['db'] = $dbName;
	if( ! isset( $dbspec['user'] )) $dbspec['user'] = $dbUser;
	if( ! isset( $dbspec['password'] )) $dbspec['password'] = $dbPassword;
	
	$separator = isset($options['separator'])?$options['separator']:'@';
	
	if ( strpos( $dbspec['db-class'], 'DB_' ) !== 0 )
		$dbspec['db-class'] = 'DB_' . $dbspec['db-class'];
	require_once("{$dbspec['db-class']}.php");
	
	eval( "\$dbInstance = new {$dbspec['db-class']}();" );
	if ( $debug )	$dbInstance->setDebugMode();
	$dbInstance->setSeparator( $separator );
	$dbInstance->setDBSpec( $dbspec );
	$dbInstance->setDataSource( $datasrc );
	$groupSize = isset($options['skip']) ? $options['skip'] : 1;
	$currentPage = isset($_GET['p']) ? $_GET['p'] : 0;
	$dbInstance->setStartSkip( $currentPage * $groupSize , $groupSize );
	if ( isset($options['formatter']))	$dbInstance->setFormatter( $options['formatter'] );
	
	echo '<script type="text/javascript" language="JavaScript">', $LF;
	echo file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'INTER-Mediator.js');
	echo "{$LF}{$LF}var separator='{$separator}';{$LF}";
	if ( $debug )	echo 'debugMode(true);', $LF;
	
	$isFirst = true;
	$allTable = array();
	foreach( $datasrc as $ar )	{
		if ( $isFirst )	{
			$mainTableName = $ar['name'];
			$isFirst = false;
		}
		$oneTable = array();
		foreach( $ar as $attrName => $attrValue )	{
			if ( ! is_array( $attrValue ))	{
				$oneTable[] = "'$attrName':'$attrValue'";
			}
		}
		$oneTableArray = '{' . implode( ',', $oneTable ) . '}';
		$allTable[] = "'{$ar['name']}':$oneTableArray";
	}
	echo 'function getDataSourceInfo(){return {',implode( ',',$allTable),'};}',$LF;
	
	$tableData = $dbInstance->getFromDB( $mainTableName );
	
	echo 'function getDBAccessInfo(){',$LF;
	$infoStr = "__easypage__separator=" . urlencode($separator)
		. "&__easypage__debug=" . $debug
		. "&__easypage__db-class=" . urlencode($dbspec['db-class'])
		. "&__easypage__db-name=" . urlencode($dbspec['db']);
	$cnt = 0;
	foreach( $datasrc as $ar )	{
		$infoStr .= "&__easypage__table_name_{$cnt}=" . urlencode($ar['name']);
		if ( isset( $ar['key'] ))
			$infoStr .= "&__easypage__table_key_{$cnt}=" . urlencode($ar['key']);
		if ( isset( $ar['foreign-key'] ))
			$infoStr .= "&__easypage__table_foreign-key_{$cnt}=" . urlencode($ar['foreign-key']);
		$cnt++;
	}
	$cnt = 0;
	if( isset( $options['formatter'] ))	{
		foreach( $options['formatter'] as $oneItem )	{
			$infoStr .= "&__easypage__formatter_field_{$cnt}=" . urlencode($oneItem['field'])
					 . "&__easypage__formatter_class_{$cnt}=" . urlencode($oneItem['converter-class']);
			if ( isset( $oneItem['parameter'] ))
				$infoStr .= "&__easypage__parameter_field_{$cnt}=" . urlencode($oneItem['parameter']);
			$cnt++;
		}
	}
	echo "return '{$infoStr}';$LF";
	echo '}',$LF;
	
	echo "function getSaveURL(){return '{$relPath}operation_save.php';}$LF";
	
	echo "function getMainTableName(){return '{$mainTableName}';}$LF";

	echo 'function getKeyFieldName(tableName){',$LF;
	foreach( $datasrc as $ar )	{
		if ( isset($ar['key']))
			echo "if(tableName=='{$ar['name']}')return '{$ar['key']}';$LF";
	}
	echo '}',$LF;
	
	echo 'function getForeignKeyFieldName(tableName){',$LF;
	foreach( $datasrc as $ar )	{
		if ( isset($ar['foreign-key']))
			echo "if(tableName=='{$ar['name']}')return '{$ar['foreign-key']}';$LF";
	}
	echo '}',$LF;

	echo 'function getAccessUser(){';
	echo "return '", isset($_SERVER['PHP_AUTH_USER'])?$_SERVER['PHP_AUTH_USER']:'',"';}$LF";
	
	echo 'function getAccessPassword(){';
	echo "return '", /* isset($_SERVER['PHP_AUTH_PW'])?$_SERVER['PHP_AUTH_PW']: */'', "';}$LF";
	
	$replaceNewLine = '__easypage__linefeed__';
	echo "function getNewLineAlternative(){return '{$replaceNewLine}';}{$LF}";
	echo 'function initializeWithDBValues(){',$LF;
	echo "checkKeyFieldMainTable('{$datasrc[0]['key']}')$LF";
	if ( count( $tableData ) == 0 )	{
		echo "showNoRecordMessage();$LF";
	} else {
		$isFirst = false;
		if ( $groupSize == 1 )	{
			foreach( $tableData as $row )	{
				foreach( $row as $field=>$value )	{
					$escVal = addslashes(str_replace("\n", $replaceNewLine, str_replace("\r", 
									$replaceNewLine, str_replace("\r\n", $replaceNewLine, $value))));
					echo "setValue('{$field}','{$escVal}');$LF";
				}
			}
			$isFirst = true;
		}
		foreach( $datasrc as $ar )	{
			if ( ! $isFirst )	{
				$keyField = isset($ar['key']) ? $ar['key'] : '';
				$foreignKey = isset($ar['foreign-key']) ? $ar['foreign-key'] : '';
				echo "checkKeyFieldRepeatTable('{$ar['name']}','{$keyField}','{$foreignKey}');$LF";
				if( isset($options['repeat-control']) && array_search( $ar['name'], $options['repeat-control']) !== false )	{
					echo "addRepeatTableControl('{$ar['name']}');$LF";
				}
				$tableData = $dbInstance->getFromDB($ar['name']);
				foreach( $tableData as $row )	{
					$valueList = array();
					foreach( $row as $field=>$value )	{
						$escVal = addslashes(str_replace("\n", $replaceNewLine, str_replace("\r", $replaceNewLine, str_replace("\r\n", $replaceNewLine, $value))));
						$valueList[] = "'{$ar['name']}{$separator}{$field}':'{$escVal}'";
					}
					echo "addToRepeat('{$ar['name']}',{" . implode(',', $valueList) . "});{$LF}";
				}
			}
			$isFirst = false;
		}
	}

	echo "appendCredit();$LF";

	$msgFromDB = $dbInstance->getDebugMessages();
	if ( count( $msgFromDB ) > 0 )	{
		foreach( $msgFromDB as $oneMessage )	{
			echo "debugOut('", addslashes($oneMessage), "');";
		}
	}

	$msgFromDB = $dbInstance->getErrorMessages();
	if ( count( $msgFromDB ) > 0 )	{
		foreach( $msgFromDB as $oneMessage )	{
			echo "errorOut('", addslashes($oneMessage), "');";
		}
	}

	echo '}',$LF;	// End of function initializeWithDBValues

	global $messages;
	echo 'function getMessageString(n){',$LF;
	echo 'switch(n){',$LF;
	foreach( $messages as $n=>$msg )	{
		echo "case {$n}: case '{$n}': return '{$msg}';{$LF}";
	}
	echo "default:return 'Message undefined';}{$LF}";
	echo '}',$LF;
	
	$uri = $_SERVER['REQUEST_URI'];
	$qMarkPos = strpos( $uri, '?' );
	if ( $qMarkPos !== false )
		$uri = substr( $uri, 0, $qMarkPos );
	global $recPosition;
	$recPosition['current'] = $currentPage * $groupSize;
	$recPosition['size'] = $groupSize;
	$recPosition['end'] = $dbInstance->getMainTableCount();
	$lastPage = ceil($recPosition['end'] / $recPosition['size'])-1;
	$prevPage = max( 0, $currentPage - 1 );
	$nextPage = min( $lastPage, $currentPage + 1 );
	echo "function pageNaviTop(){window.location='{$uri}?p=0';}{$LF}";
	echo "function pageNaviPrev(){window.location='{$uri}?p={$prevPage}';}{$LF}";
	echo "function pageNaviNext(){window.location='{$uri}?p={$nextPage}';}{$LF}";
	echo "function pageNaviEnd(){window.location='{$uri}?p={$lastPage}';}{$LF}";

	echo '</script>', $LF;
}

function GenerateConsole( $consoleDef = null )	{
	$defAll = 'pos nav new delete save';
	if ( $consoleDef == null )	$consoleDef = $defAll;
	global $messages;
	global $recPosition;
	$q = '"';
	echo "<table width={$q}100%{$q} class={$q}easypage_navigation{$q}>";
	echo "<tr><td><div class={$q}easypage_navigation_message{$q} id={$q}__easypage_navigation_message{$q}></div></td>";
	echo "<td align={$q}right{$q}><div class={$q}easypage_navigation_controls{$q}>";
	$info = $messages[10];
	if ( $recPosition['size'] > 2 )	{
		$info = str_replace( '@3@', min( $recPosition['current']+$recPosition['size'], $recPosition['end'] ), 
				str_replace( '@2@', $recPosition['current']+1, $messages[11] ));
	} else {
		$info = str_replace( '@2@', $recPosition['current']+1, $messages[10] );
	}
	$info = str_replace( '@1@', $recPosition['end'], $info );
	if ( strpos( $consoleDef, 'pos' ) !== false )	{
		echo "&nbsp;<span class={$q}easypage_navigation_info{$q}>{$info}</span>";
	}
	if ( strpos( $consoleDef, 'nav' ) !== false )	{
		echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}pageNaviTop();{$q}>{$messages[6]}</span>";
		echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}pageNaviPrev();{$q}>{$messages[7]}</span>";
		echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}pageNaviNext();{$q}>{$messages[8]}</span>";
		echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}pageNaviEnd();{$q}>{$messages[9]}</span>";
	}
	if ( strpos( $consoleDef, 'new' ) !== false )	{
		echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}newRecord();{$q}>{$messages[2]}</span>";
	}
	if ( strpos( $consoleDef, 'delete' ) !== false )	{
		echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}deleteRecord();{$q}>{$messages[3]}</span>";
	}
	if ( strpos( $consoleDef, 'save' ) !== false )	{
		echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}saveRecord();{$q}>{$messages[1]}</span>";
	}
	echo '</div></td></tr></table>';
}

?>