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

$tableDefinitions
	= array(	
		array(	'records'	=>	1,
                'paging'    =>  true,
				'name' 	=> 'person_layout', 
				'key' 		=> 'id',
                'repeat-control'	=> 'delete insert',
				'query'	=> array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/ ),
				'sort'		=> array( array( 'field'=>'id', 'direction'=>'ascend' ),),),
		array(	'name'			=> 'contact_to',
				'key'			=> 'id',
                'repeat-control'	=> 'delete insert',
				'foreign-key' => 'person_id',
				'join-field' 	=> 'id'),
		array(	'name' => 'contact_way', 'key' => 'id',),
		array(	'name' 		=> 'cor_way_kind', 
				'key' 			=> 'id',
				'foreign-key' => 'way_id',
				'join-field' 	=> 'way'),
		array(	'name' 			=> 'history_to', 
				'key' 			=> 'id',
				'foreign-key'	=> 'person_id',
				'repeat-control'	=> 'delete insert',
				'join-field' 	=> 'id',	),
	);

$optionDefinitions
	= array(
		'formatter' => array(
			array( 'field' => 'contact_to@datetime', 	'converter-class' =>'FMDateTime' ),
			array( 'field' => 'history_to@startdate',	'converter-class' =>'FMDateTime' ),
			array( 'field' => 'history_to@enddate', 	'converter-class' =>'FMDateTime' ),
		)
	);

$dbDefinitions = array(
    'db-class' => 'FileMaker_FX',
    'database' => 'TestDB',
    'user' => 'web',
    'password' => 'password',
    'server' => '127.0.0.1',
    'port' => '80',
    'protocol' => 'HTTP',
    'datatype' => 'FMPro7');

IM_Entry( $tableDefinitions, $optionDefinitions, $dbDefinitions, true );

?>