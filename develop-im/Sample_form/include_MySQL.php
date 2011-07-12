<?php 
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once ('../INTER-Mediator/INTER-Mediator.php');

$tableDefinitions
	= array(	
		array(	'records'	=>	1,
                'paging'    =>  true,
				'name' 		=> 'person', 
				'key' 		=> 'id',
				'query'		=> array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/ ),
				'sort'		=> array( array( 'field'=>'id', 'direction'=>'asc' ),),
            'repeat-control'	=> 'insert delete',
		),
		array(	'name'			=> 'contact',
				'key'			=> 'id',
				'foreign-key'	=> 'person_id',
				'join-field' 	=> 'id',
				'repeat-control'	=> 'insert delete',
		),
		array(	'name' 	=> 'contact_way',
				 'key' 	=> 'id',),
		array(	'name' 			=> 'cor_way_kindname', 
				'key' 			=> 'id',
				'foreign-key' 	=> 'way_id',
				'join-field' 	=> 'way'),
		array(	'name' 			=> 'history', 
				'key' 			=> 'id',
				'foreign-key'	=> 'person_id',
				'repeat-control'	=> 'insert delete',
				'join-field' 	=> 'id',	),
	);
	
$optionDefinitions
	= array(
		'formatter' => array(
		)
	);

$dbDefinitions = array( 'db-class' => 'PDO');

IM_Entry( $tableDefinitions, $optionDefinitions, $dbDefinitions, false );

?>