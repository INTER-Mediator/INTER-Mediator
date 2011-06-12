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
				'records' 	=> '10', 
				'name' 		=> 'product', 
				'key' 		=> 'id',
				'query'		=> array( array( 'field'=>'name', 'value'=>'*', 'operator'=>'cn' )),
				'sort'		=> array( array( 'field'=>'name', 'direction'=>'ascend' ),),
			),
		),
		array(
			'formatter' => array(
				array( 'field' => 'product@photofile', 	'converter-class' =>'AppendPrefix', 'parameter' => 'images/' ),
				array( 'field' => 'product@id', 	'converter-class' =>'AppendPrefix', 'parameter' => 'sample_product_detail_FMS_include.php?id=' ),
				array( 'field' => 'product@unitprice', 	'converter-class' =>'Number', 'parameter' => '0' ),
			),
		),
		null, 
		false		// debug
	);
?>
</head>
<body onload="doAtTheStarting();" onbeforeunload="return doAtTheFinishing();">
<?php GenerateConsole('nav pos'); ?>
<table border="1">
<tr>
	<th>id's</th><th>name</th><th>unitprice</th><th>photo</th>
</tr>
<tr>
	<td><a title="product@id">Detail</a></td>
	<td><div title="product@name"></div></td>
	<td><div title="product@unitprice"></div></td>
	<td>
		<img title="product@photofile"/><br/>
		<a title="product@ack_link"><div style="font-size:10pt;" title="product@acknowledgement"></div></a>
	</td>
</tr>
</table>
</body>
</html>