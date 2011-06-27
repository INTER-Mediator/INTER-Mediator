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
$tableDefinitions = array(
    array(
        'records' 	=> '10',
        'name' 		=> 'product',
        'key' 		=> 'id',
        'query'		=> array( array( 'field'=>'name', 'value'=>'%', 'operator'=>'LIKE' )),
        'sort'		=> array( array( 'field'=>'name', 'direction'=>'ASC' ),),
    ),
);
$optionDefinitions = array(
    'formatter' => array(
        array( 'field' 				=> 'product@unitprice',
                'converter-class' 	=> 'Number',
                'parameter' 		=> '0' ),
    ),
);

$dbDefinitions = array(
    'db-class' => 'PDO',
	'dsn'=>'mysql:host=localhost;dbname=test_db',
);

IM_Entry( $tableDefinitions, $optionDefinitions, $dbDefinitions, true );

?>
