<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>INTER-Mediator - Sample - Form Style/MySQL</title>
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
				'records' 	=> '1', 
				'name' 		=> 'invoice', 
				'key' 		=> 'id',
				'query'		=> array(),
				'sort'		=> array( array( 'field'=>'id', 'direction'=>'ASC' ),),
			),
			array(	
				'name' 				=> 'item',
				'view'				=> 'item_display',
				'key' 				=> 'id',
				'foreign-key' 		=> 'invoice_id',
				'repeat-control'	=> 'insert delete',
			),
		),
		array(
			'trriger' => array(
				array( 'field' => 'contact@datetime', 	'event' =>'change',	'action' => 'modifyField' ),
			),
			'validation' => array(
				array( 'field' => 'contact@datetime', 	'rule' =>'change',	'option' => '' ),
			),
		),
		array(	'db-class' 	=> 'MySQL',
				'db' 		=> 'test_db',
		), 
		true		// debug
	);
?>
<script type="text/javascript">
function modifyField(target)	{
}
</script>
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
		<td><input type="text" name="issued" value="" /></td>
	</tr>
	<tr>
		<td>address</td>
		<td><input type="text" name="title" value="" /></td>
	</tr>
</table>
<table border="1">
<tr>
	<th>id's</th><th>product</th><th>qty</th>
	<th>important</th><th>way</th><th>kind</th><th>description</th>
</tr>
<tr>
	<td>
		<input type="text" name="contact@id" size="2"/>
		<input type="text" name="contact@invoice_id" size="2"/>
		<input type="text" name="contact@category_id" size="2"/>
	</td>
	<td>
		<input type="text" name="item_display@product_id" size="2"/>
		<input type="text" name="item_display@name"/>
	</td>
	<td><input type="text" name="qty"/></td>
	<td><input type="text" name="unitprice"/><div title="unitprice"/></td>
	<td><div name="amount"/></td>
</tr>
</table>
</body>
</html>