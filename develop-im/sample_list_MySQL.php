<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>INTER-Mediator - Sample - List Style/MySQL</title>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<link href="sample.css" rel="stylesheet" type="text/css" />
<?php 
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
	require_once( 'INTER-Mediator/INTER-Mediator.php' );
	InitializePage(
		array(
			array(
				'records'	=>	16,
				'name'	=> 'postalcode',	
				'key' 	=> 'id',
				'query'	=> array( array( 'field'=>'f8', 'operator'=>'=', 'value'=>'渋谷区' ) ),
				'sort'	=> array( array( 'field'=>'f3', 'direction'=>'DESC' ),),
			),
		),
		null,
		array(
			'db-class' 	=> 'MySQL',
			'db' 		=> 'test_db',
		), 
		true
	);
	?>
<script type="text/javascript"></script>
</head>
<body onload="doAtTheStarting();" onbeforeunload="return doAtTheFinishing();">
<?php GenerateConsole( 'pos nav'); ?>
<table border="1">
	<tr>
		<th>郵便番号</th>
		<th>都道府県</th>
		<th>市区町村</th>
		<th>町域名</th>
	</tr>
	<tr>
		<td><div title="postalcode@f3"></div></td>
		<td><div title="postalcode@f7"></div></td>
		<td><div title="postalcode@f8"></div></td>
		<td><div title="postalcode@f9"></div></td>
	</tr>
</table>
</body>
</html>