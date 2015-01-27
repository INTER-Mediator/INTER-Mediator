<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'postalcode',
            'key' => 'id',
            'records' => 20,
            'paging' => true,
            'query' => array(array('field' => 'f3', 'operator' => 'LIKE', 'value' => "15%"),),
            'sort' => array(array('field' => 'f3', 'direction' => 'ASC'),),
            'repeat-control' => 'insert delete',
        ),
    ),
    array(
        'transaction' => 'none',
    ),
    array('db-class' => 'PDO'),
    false
);