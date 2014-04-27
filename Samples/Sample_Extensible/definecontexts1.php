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
            'name' => 'everymonth',
            'view' => 'item_master',
            'query' => array(array('field' => 'id', 'operator' => '=', 'value' => '1'),),
            'records' => 1,
        ),
        array(
            'name' => 'summary1',
            'view' => 'saleslog',
            'relation' => array(
                array('foreign-key' => 'dt', 'operator' => '>=', 'join-field' => 'startdt', ),
                array('foreign-key' => 'dt', 'operator' => '<', 'join-field' => 'enddt', ),
            ),
            //    'records' => 10,
        ),
        array(
            'name' => 'summary2',
            'view' => 'saleslog',
            'relation' => array(
                array('foreign-key' => 'dt', 'operator' => '>=', 'join-field' => 'startdt', ),
                array('foreign-key' => 'dt', 'operator' => '<', 'join-field' => 'enddt', ),
            ),
            //    'records' => 10,
        ),
        array(
            'name' => 'data',
            'view' => 'saleslog',
            'relation' => array(
                array('foreign-key' => 'dt', 'operator' => '>=', 'join-field' => 'startdt', ),
                array('foreign-key' => 'dt', 'operator' => '<', 'join-field' => 'enddt', ),
            ),
            'sort' => array(
                array('field'=>'total', 'direction'=>'desc'),
            ),
                'records' => 10,
        ),
    ),
    array(),
    array('db-class' => 'Extended_PDO'),
    false
);
