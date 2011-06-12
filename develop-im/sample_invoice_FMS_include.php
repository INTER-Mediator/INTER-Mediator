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
				'query'	=> array(),
				'sort'		=> array( array( 'field'=>'id', 'direction'=>'ascend' ),),
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
				array( 'field' => 'issued', 	'converter-class' =>'FMDateTime', 'parameter' => '%Y年%b月%e日(%a)' ),
				),
			'trigger' => array(
				array( 'field' => 'item@qty', 	'event' =>'change',	'function' => 'modLine' ),
				array( 'field' => 'item@unitprice', 	'event' =>'change',	'function' => 'modLine' ),
			),
//			'validation' => array(
//				array( 'field' => 'item@qty', 	'rule' =>'require' /*, 'option' => '数量' */ ),
//				array( 'field' => 'title', 	'rule' =>'mail' /*, 'option' => '数量' */ ),
//			),
		),
		null, 
		false		// debug
	);
?>
