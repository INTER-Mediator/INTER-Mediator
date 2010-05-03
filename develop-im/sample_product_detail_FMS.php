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
				'name' 		=> 'product', 
				'key' 		=> 'id',
				'query'		=> array( array( 'field'=>'name', 'value'=>'*', 'operator'=>'cn' )),
				'sort'		=> array( array( 'field'=>'name', 'direction'=>'ascend' ),),
			),
		),
		array(
			'accept-get' => true,
		),
		null, 
		false		// debug
	);
?>
</head>
<body onload="doAtTheStarting();" onbeforeunload="return doAtTheFinishing();">
<?php GenerateConsole('save'); ?>
<p><a href="sample_products_FMS.php">back</a></p>
<table border="1">
<tr><th>id</th><td><input type="text" name="id" size="30"/></td></tr>
<tr><th>name</th><td><input type="text" name="name" size="30"/></td></tr>
<tr><th>unitprice</th><td><input type="text" name="unitprice" size="30"/></td></tr>
<tr><th>photofile</th><td><input type="text" name="photofile" size="30"/></td></tr>
<tr><th>acknowledgement</th><td><input type="text" name="acknowledgement" size="30"/></td></tr>
<tr><th>ack_link</th><td><input type="text" name="ack_link" size="30"/></td></tr>
</table>
</body>
</html>