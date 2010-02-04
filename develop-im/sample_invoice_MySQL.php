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
				'name' 	=> 'invoice', 
				'key' 		=> 'id',
				'sort'		=> array( array( 'field'=>'id', 'direction'=>'ASC' ),),
			),
			array(	
				'name' 			=> 'item',
				'view'				=> 'item_display',
				'key' 				=> 'id',
				'foreign-key' 	=> 'invoice_id',
				'repeat-control'	=> 'insert delete',
			),
		),
		array(
			'formatter' => array(
				array( 'field' => 'item@amount', 	'converter-class' =>'Number', 'parameter' => '0' ),
			),
			'trigger' => array(
				array( 'field' => 'item@qty', 	'event' =>'change',	'function' => 'modLine' ),
				array( 'field' => 'item@unitprice', 	'event' =>'change',	'function' => 'modLine' ),
			),
		),
		array(	'db-class' => 'MySQL', 'db' => 'test_db', ), 
		true		// debug
	);
?>
<script type="text/javascript">
function modLine(target)	{
	var aNode = getElementNodeByName('item@amount', target);
	var qNode = getElementNodeByName('item@qty', target);
	var uNode = getElementNodeByName('item@unitprice', target);
	var mNode = getElementNodeByName('item@unitprice_master', target);
	var uPrice = mNode.innerHTML;
	if ( uNode.value > 0 )
		uPrice = uNode.value;
	aNode.innerHTML = numberFormat( uPrice * (qNode.value) );
	calcTotal();
}
function calcTotal()	{
	var nodes = getElementNodesByName( 'item@amount' );
	var s = 0;
	for ( var i in nodes )
		s += toNumber( nodes[i].innerHTML );
	document.getElementById('total').innerHTML = numberFormat( s );
}
function pageOnLoad()	{
	doAtTheStarting();
	calcTotal();
}
function afterFieldModified()	{
	calcTotal();
}
function afterTableRowDelete()	{
	calcTotal();
}
</script>
</head>
<body onload="pageOnLoad();" onbeforeunload="return doAtTheFinishing();">
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
		<td>title</td>
		<td><input type="text" name="title" value="" /></td>
	</tr>
</table>
<table border="1">
<tr>
	<th>id's</th><th>product</th><th>qty</th>
	<th>unitprice (master)</th><th>amount</th>
</tr>
<tr>
	<td>
		<input type="text" name="item@id" size="2"/>
		<input type="text" name="item@invoice_id" size="2"/>
		<input type="text" name="item@category_id" size="2"/>
	</td>
	<td>
		<input type="text" name="item@product_id" size="2"/>
		<div style="display:inline;" title="item@name"></div>
	</td>
	<td><input type="text" name="item@qty"/ size="5"></td>
	<td>
		<input type="text" name="item@unitprice" size="8"/>
		(<div style="display:inline;" title="item@unitprice_master"></div>)
	</td>
	<td><div align="right" title="item@amount"></div></td>
</tr>
</table>
<table border="1">
<tr><td>Total:</td><td><div id="total"></div></td></tr>
</table>
</body>
</html>