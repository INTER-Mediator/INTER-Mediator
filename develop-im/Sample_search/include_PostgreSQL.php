<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2011 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once ('../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'postalcode',
            'view' => 'im_sample.postalcode',
            'records' => 10,
            'paging' => true,
            //	'sort'	    => array( array( 'field'=>'f3', 'direction'=>'ASC' ),),
        ),
    ),
    null,
    array('db-class' => 'PDO','dsn' => 'pgsql:host=localhost;port=5432;dbname=test_db'),
    false
);

?>