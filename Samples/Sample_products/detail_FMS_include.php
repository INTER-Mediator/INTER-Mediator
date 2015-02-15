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
            'records' => 1,
            'name' => 'product',
            'key' => 'id',
            'query' => array(array('field' => 'name', 'value' => '*', 'operator' => 'cn')),
            'sort' => array(array('field' => 'name', 'direction' => 'ascend'),),
        ),
    ),
    null,
    array('db-class' => 'FileMaker_FX'),
    false
);
