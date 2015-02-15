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
            'name' => 'personlist',
            'view' => 'person',
            'table' => 'person',
            'key' => 'id',
            'records' => 10,
            'paging' => true,
            'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
            'sort' => array(
                array('field' => 'id', 'direction' => 'asc'),
            ),
            'repeat-control' => 'insert delete',
        ),
        array(
            'name' => 'persondetail',
            'view' => 'person',
            'table' => 'person',
            'key' => 'id',
            'records' => 1,
            'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
            'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
        ),
    ),
    array(),
    array('db-class' => 'FileMaker_FX'),
    false
);
