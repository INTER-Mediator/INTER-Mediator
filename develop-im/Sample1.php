<?php 
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once ( 'INTER-Mediator/INTER-Mediator.php');

$tableDefs 
	= array(	
		array(	
			'records'	=>	1,
			'name' 	=> 'person_layout', 
			'key' 	=> 'id',
			'query'	=> array( 
				array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' )),
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
	);
	
$optionDefs
	= array(
		'formatter' => array(
			array( 'field' => 'contact_to@datetime', 	'converter-class' =>'FMDateTime' ),
			array( 'field' => 'history_to@startdate',	'converter-class' =>'FMDateTime' ),
			array( 'field' => 'history_to@enddate', 	'converter-class' =>'FMDateTime' ),
		)
	);

$dbDefs = array( 'db-class' => 'FileMaker_FX', 'db' => 'TestDB' );

IM_Entry( $tableDefs, $optionDefs, $dbDefs, true );

?>