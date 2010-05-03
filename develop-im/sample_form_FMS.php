<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>INTER-Mediator - Sample - Form Style/FileMaker Server</title>
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
				'records'	=>	1,
				'name' 	=> 'person_layout', 
				'key' 	=> 'id',
				'query'	=> array( ),
				'sort'	=> array( array( 'field'=>'id', 'direction'=>'ascend' ),),
			),
			array(	
				'name' 			=> 'contact_to', 
				'key' 			=> 'id',
				'foreign-key' 	=> 'person_id',
				'repeat-control'	=> 'insert delete',
			),
			array(	
				'name' 			=> 'history_to', 
				'key' 			=> 'id',
				'foreign-key'	=> 'person_id',
				'repeat-control'	=> 'insert',
			),
			array(	
				'name' 	=> 'postalcode', 
				'query'	=> array( array( 'field'=>'f9', 'value'=>'落合', 'operator'=>'cn' ) ),
				'sort'	=> array( array( 'field'=>'f3', 'direction'=>'ascend' ),),
			),
		),
		array(
			'formatter' => array(
				array( 'field' => 'contact_to@datetime', 	'converter-class' =>'FMDateTime' ),
				array( 'field' => 'history_to@startdate',	'converter-class' =>'FMDateTime' ),
				array( 'field' => 'history_to@enddate', 	'converter-class' =>'FMDateTime' ),
			),
		),
		array(	'db-class' => 'FileMaker_FX', 'db' => 'TestDB',), 
		false		// debug
	);
?>
<script type="text/javascript"></script>
</head>
<body onload="doAtTheStarting();" onbeforeunload="return doAtTheFinishing();">
<?php GenerateConsole(); ?>
<table border="1">
	<tr>
		<td>id</td>
		<td><input type="text" name="id"/></td>
	</tr>
	<tr>
		<td>name</td>
		<td><input type="text" name="name" value="" /></td>
	</tr>
	<tr>
		<td>address</td>
		<td><input type="text" name="address" value="" /></td>
	</tr>
	<tr>
		<td>mail</td>
		<td><input type="text" name="mail" value="" /></td>
	</tr>
	<tr>
		<td>category</td>
		<td>
			<select name="category">
				<option value="101">Family</option>
				<option value="102">ClassMate</option>
				<option value="103">Collegue</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>check</td>
		<td><input type="checkbox" name="check" value="1" /></td>
	</tr>
	<tr>
		<td>location</td>
		<td>
			<input type="radio" name="location" value="201" />Domestic
			<input type="radio" name="location" value="202" />International
			<input type="radio" name="location" value="203" />Neightbor
			<input type="radio" name="location" value="204" />Space
		</td>
	</tr>
	<tr>
		<td>memo</td>
		<td><textarea name="memo"></textarea></td>
	</tr>
</table>
<table border="1">
<tr>
	<th>person_id</th><th>datetime</th><th>summary</th>
	<th>important</th><th>way</th><th>kind</th><th>description</th>
</tr>
<tr>
	<td><div title="contact_to@person_id"></div></td>
	<td><input type="text" name="contact_to@datetime"/></td>
	<td><input type="text" name="contact_to@summary"/></td>
	<td><input type="checkbox" name="contact_to@important" value="1"/></td>
	<td>
		<input type="radio" name="contact_to@way" value="301" />Direct
		<input type="radio" name="contact_to@way" value="302" />Phone
		<input type="radio" name="contact_to@way" value="303" />Another
	</td>
	<td>
		<select name="contact@kind">
			<option value="401">Talk</option>
			<option value="402">Meet</option>
			<option value="403">Email</option>
		</select>
	</td>
	<td><textarea name="contact_to@description"></textarea></td>
</tr>
</table>
<table border="1">
<tr><th>id</th><th>person_id</th><th>startdate</th><th>enddate</th><th>description</th></tr>
<tr>
	<td><!-- <div title="history@id"></div> --></td>
	<td><!-- <div title="history@person_id"></div> --></td>
	<td><input type="text" name="history_to@startdate" /></td>
	<td><input type="text" name="history_to@enddate" /></td>
	<td><input type="text" name="history_to@description" /></td>
</tr>
</table>
<p>The following table is out of the above master-detail relation.</p>
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