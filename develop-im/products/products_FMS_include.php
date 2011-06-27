<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
	require_once('../INTER-Mediator/INTER-Mediator.php');
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
				array( 'field' => 'product@id', 	'converter-class' =>'AppendPrefix', 'parameter' => 'detail_FMS_include.php?id=' ),
				array( 'field' => 'product@unitprice', 	'converter-class' =>'Number', 'parameter' => '0' ),
			),
		),
		null, 
		false		// debug
	);
?>
