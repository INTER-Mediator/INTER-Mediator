<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'postalcode',
            'records' => 10,
            'maxrecords' => 10,
            'paging' => true,
            //	'sort'	    => array( array( 'field'=>'f3', 'direction'=>'ASC' ),),
        ),
    ),
    null,
    array('db-class' => 'PDO','dsn' => 'sqlite:/var/db/im/sample.sq3',),
    false
);