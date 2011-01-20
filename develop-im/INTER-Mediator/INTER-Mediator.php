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
require_once( 'operation_common.php' );

function IM_Entry( $datasrc, $options = null, $dbspec = null, $debug=false )	{
	$LF = "\n";	$q = '"';
	if ( ! isset( $_GET['access'] ) )	{
		header( 'Content-Type: text/javascript' );
		echo file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'INTER-Mediator.js');

//		echo "{$LF}{$LF}var separator='{$options['separator']}';{$LF}";
//		if ( $debug )	echo 'debugMode(true);', $LF;
		
		echo "function IM_getEntryPath(){return {$q}{$_SERVER['SCRIPT_NAME']}{$q};}{$LF}"; 
		echo "function IM_getMyPath(){return {$q}", getRelativePath(), "/INTER-Mediator.php{$q};}{$LF}";
		echo "function IM_getDataSources(){return ", arrayToJS( $datasrc, '' ), ";}{$LF}";
		echo "function IM_getOptions(){return ", arrayToJS( $options, '' ), ";}{$LF}";
		echo "function IM_getDatabases(){return ", arrayToJS( $dbspec, '' ), ";}{$LF}";
	
		echo "function IM_getDataSourceParams(){return {$q}", 
			arrayToQuery( $datasrc, '__imparameters__datasrc' ), "{$q};}{$LF}";
		echo "function IM_getOptionParams(){return {$q}", 
			arrayToQuery( $options, '__imparameters__options' ), "{$q};}{$LF}";
		echo "function IM_getDatabaseParams(){return {$q}", 
			arrayToQuery( $dbspec, '__imparameters__dbspec' ), "{$q};}{$LF}";
	} else {
		$dbClassName = "DB_{$dbspec['db-class']}";
		require_once("{$dbClassName}.php");
		eval( "\$dbInstance = new {$dbClassName}();" );

		include( 'params.php' );
		if ( isset( $dbUser ))	{
			$dbspec['user'] = $dbUser;
		}
		if ( isset( $dbPassword ))	{
			$dbspec['password'] = $dbPassword;
		}

		$dbInstance->setDBSpec( $dbspec );
		if ( $debug )	$dbInstance->setDebugMode();
		$dbInstance->setSeparator( isset( $options['separator'] ) ? $options['separator'] : '@' );
		$dbInstance->setDataSource( $datasrc );
		$dbInstance->setStartSkip( 0, isset($dbspec['records']) ? $dbspec['records'] : 1 );
		$dbInstance->setFormatter( $options['formatter'] );
		if ( isset( $_GET['parent_keyval'] ) /* && strlen( $_GET['parent_keyval'] ) > 0 */)	{
		//	if ( isset( $datasrc[$_GET['table']]['foreign-key'] ))	{
				$dbInstance->setParentKeyValue( $_GET['parent_keyval'] );
		//	}
		}
		switch( $_GET['access'] )	{
			case 'select':	$result = $dbInstance->getFromDB( $_GET['table'] );		break;
			case 'update':	$result = $dbInstance->setToDB();			break;
			case 'insert':	$result = $dbInstance->newToDB();			break;
			case 'delete':	$result = $dbInstance->deleteFromDB();	break;
		}
		$returnData = array();
		foreach( $dbInstance->getErrorMessages() as $oneError )	{
			$returnData[] = "messages.push({$q}" . addslashes( $oneError ) . "{$q});";
		}
		foreach( $dbInstance->getDebugMessages() as $oneError )	{
			$returnData[] = "messages.push({$q}" . addslashes( $oneError ) . "{$q});";
		}
		echo implode( '', $returnData ) . 'var dbresult=' . arrayToJS( $result, '' ) . ';';
	}
}

function InitializePage( $datasrc, $options = null, $dbspec = null, $debug=false )	{
	$LF = "\n";
	$relPath = getRelativePath();
	
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

	if ( isset($options['accept-get']) )	{
		foreach( $_GET as $key => $val )	{
			if ( $key != 'p' )	{
				$dbInstance->setExtraCriteria( $key, $val );
			}
		}
	}
	if ( isset($options['accept-post']) )	{
		foreach( $_POST as $key => $val )	{
			$dbInstance->setExtraCriteria( $key, $val );
		}
	}
	
	echo '<script type="text/javascript" language="JavaScript">', $LF;
	echo file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'INTER-Mediator.js');
	echo "{$LF}{$LF}var separator='{$options['separator']}';{$LF}";
	if ( $debug )	echo 'debugMode(true);', $LF;
	
	echo "function getDataSourceParams(){return '", arrayToQuery( $datasrc, '__imparameters__datasrc' ), "';}{$LF}";
	echo "function getOptionParams(){return '", arrayToQuery( $options, '__imparameters__options' ), "';}{$LF}";
	echo "function getDatabaseParams(){return '", arrayToQuery( $dbspec, '__imparameters__dbspec' ), "';}{$LF}";

	echo "function getTrrigerParams(){return ", 
			isset($options['trigger']) ? arrayToJS( $options['trigger'], '' ) : 'null', 
			";}{$LF}";
	echo "function getvalidationParams(){return ", 
			isset($options['validation']) ? arrayToJS( $options['validation'], '' ) : 'null', 
			";}{$LF}";
	
	$mainTableName = $datasrc[0]['name'];
	$tableData = $dbInstance->getFromDB( $mainTableName );

	echo "function getSaveURL(){return '{$relPath}/operation_save.php';}$LF";
	
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

	$messages = getErrorMessageClass();
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
	global $recPosition, $isFirstPage, $isLastPage;
	$recPosition['current'] = $currentPage * $groupSize;
	$recPosition['size'] = $groupSize;
	$recPosition['end'] = $dbInstance->getMainTableCount();
	$lastPage = ceil($recPosition['end'] / $recPosition['size'])-1;
	$prevPage = max( 0, $currentPage - 1 );
	$nextPage = min( $lastPage, $currentPage + 1 );
	$isFirstPage = ($prevPage == $currentPage);
	$isLastPage = ($nextPage == $currentPage);
	echo "function pageNaviTop(){window.location='{$uri}?p=0';}{$LF}";
	echo "function pageNaviPrev(){window.location='{$uri}?p={$prevPage}';}{$LF}";
	echo "function pageNaviNext(){window.location='{$uri}?p={$nextPage}';}{$LF}";
	echo "function pageNaviEnd(){window.location='{$uri}?p={$lastPage}';}{$LF}";

	echo '</script>', $LF;
}

function GenerateConsole( $consoleDef = null )	{
	$defAll = 'pos nav new delete save';
	if ( $consoleDef == null )	$consoleDef = $defAll;
	$messages = getErrorMessageClass();

	global $recPosition, $isFirstPage, $isLastPage;
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
		if ( $isFirstPage )	{
			echo "&nbsp;<span class={$q}easypage_navigation_dimmed{$q}>{$messages[6]}</span>";
			echo "&nbsp;<span class={$q}easypage_navigation_dimmed{$q}>{$messages[7]}</span>";
		} else {
			echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}pageNaviTop();{$q}>{$messages[6]}</span>";
			echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}pageNaviPrev();{$q}>{$messages[7]}</span>";
		}
		if ( $isLastPage )	{
			echo "&nbsp;<span class={$q}easypage_navigation_dimmed{$q}>{$messages[8]}</span>";
			echo "&nbsp;<span class={$q}easypage_navigation_dimmed{$q}>{$messages[9]}</span>";
		} else {
			echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}pageNaviNext();{$q}>{$messages[8]}</span>";
			echo "&nbsp;<span class={$q}easypage_navigation_link{$q} onclick={$q}pageNaviEnd();{$q}>{$messages[9]}</span>";
		}
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