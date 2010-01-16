<?php

/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

mb_internal_encoding('UTF-8');

define ( 'ALTERNATIVE_NEXTLINE', '__easypage__linefeed__');
define ( 'ALTERNATIVE_TAGOPEN', '__easypage__tagopen__');
define ( 'ALTERNATIVE_TAGCLOSE', '__easypage__tagclose__');

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
	if( ! isset( $dbspec['db'] )) 		$dbspec['db'] = $dbName;
	if( ! isset( $dbspec['user'] )) 	$dbspec['user'] = $dbUser;
	if( ! isset( $dbspec['password'] )) $dbspec['password'] = $dbPassword;
	
	$options['separator'] = isset($options['separator'])?$options['separator']:'@';
	if( $debug )	$options['debug'] = $debug;
	
	if ( strpos( $dbspec['db-class'], 'DB_' ) !== 0 )
		$dbspec['db-class'] = 'DB_' . $dbspec['db-class'];
	require_once("{$dbspec['db-class']}.php");
	
	eval( "\$dbInstance = new {$dbspec['db-class']}();" );
	if ( $debug )	$dbInstance->setDebugMode();
	$dbInstance->setSeparator( $options['separator'] );
	$dbInstance->setDBSpec( $dbspec );
	$dbInstance->setDataSource( $datasrc );
	$groupSize = isset( $datasrc[0]['records'] ) ? $datasrc[0]['records'] : 1;
	$currentPage = isset($_GET['p']) ? $_GET['p'] : 0;
	$dbInstance->setStartSkip( $currentPage * $groupSize , $groupSize );
	if ( isset($options['formatter']))	$dbInstance->setFormatter( $options['formatter'] );
	
	echo '<script type="text/javascript" language="JavaScript">', $LF;
	echo file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'INTER-Mediator.js');
	echo "{$LF}{$LF}var separator='{$options['separator']}';{$LF}";
	if ( $debug )	echo 'debugMode(true);', $LF;
	
	echo "function getDataSourceParams(){return '", arrayToQuery( $datasrc, '__imparameters__datasrc' ), "';}{$LF}";
	echo "function getOptionParams(){return '", arrayToQuery( $options, '__imparameters__options' ), "';}{$LF}";
	echo "function getDatabaseParams(){return '", arrayToQuery( $dbspec, '__imparameters__dbspec' ), "';}{$LF}";
	
	$mainTableName = $datasrc[0]['name'];
	$tableData = $dbInstance->getFromDB( $mainTableName );

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
	
	$replaceNewLine = ALTERNATIVE_NEXTLINE;
	echo "function getNewLineAlternative(){return '{$replaceNewLine}';}{$LF}";
	$replaceTagOpen = ALTERNATIVE_TAGOPEN;
	echo "function getTagOpenAlternative(){return '{$replaceTagOpen}';}{$LF}";
	$replaceTagClose = ALTERNATIVE_TAGCLOSE;
	echo "function getTagCloseAlternative(){return '{$replaceTagClose}';}{$LF}";
	
	echo 'function initializeWithDBValues(){',$LF;
	echo "checkKeyFieldMainTable('{$datasrc[0]['key']}');$LF";
	if ( count( $tableData ) == 0 )	{
		echo "showNoRecordMessage();$LF";
	} else {
		$isFirst = false;
		if ( $groupSize == 1 )	{
			foreach( $tableData as $row )	{
				foreach( $row as $field=>$value )	{
					$escVal = valueForJSInsert( $value );
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
				if( isset( $ar['repeat-control'] ))	{
					echo "addRepeatTableControl('{$ar['name']}','{$ar['repeat-control']}');$LF";
				}
				$tableData = $dbInstance->getFromDB($ar['name']);
				foreach( $tableData as $row )	{
					$valueList = array();
					foreach( $row as $field=>$value )	{
						$escVal = valueForJSInsert( $value );
						$valueList[] = "'{$ar['name']}{$options['separator']}{$field}':'{$escVal}'";
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

function valueForJSInsert( $str )	{
	return	addslashes(
			str_replace(">", ALTERNATIVE_TAGCLOSE,
			str_replace("<", ALTERNATIVE_TAGOPEN,
			str_replace("\n", ALTERNATIVE_NEXTLINE, 
			str_replace("\r", ALTERNATIVE_NEXTLINE, 
			str_replace("\r\n", ALTERNATIVE_NEXTLINE, $str))))));
}

function arrayToQuery( $ar, $prefix )	{
	if( is_array( $ar ))	{
		$items = array();
		foreach( $ar as $key=>$value )	{
			$items[] = arrayToQuery( $value, "{$prefix}_{$key}" );
		}
		$returnStr = implode( '', $items );
	} else {
		$returnStr = "&{$prefix}=" . urlencode( $ar );
	}
	return $returnStr;
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