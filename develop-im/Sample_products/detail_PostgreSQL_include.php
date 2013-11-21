<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2013 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 1,
            'name' => 'product',
            'key' => 'id',
        ),
    ),
    null,
    array(
        'db-class' => 'PDO',
        'dsn' => 'pgsql:host=localhost;port=5432;dbname=test_db',
    ),
    false
);