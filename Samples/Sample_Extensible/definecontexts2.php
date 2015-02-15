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
            'name' => 'everymonth',
            'view' => 'item_master',
            'query' => array(array('field' => 'id', 'operator' => '=', 'value' => '1'),),
            'records' => 1,
            'extending-class' => "YearMonthGen",
        ),
        array(
            'name' => 'summary1',
            'view' => 'saleslog',
            'relation' => array(
                array('foreign-key' => 'dt', 'operator' => '>=', 'join-field' => 'startdt',),
                array('foreign-key' => 'dt', 'operator' => '<', 'join-field' => 'enddt',),
            ),
            'extending-class' => "SumForItems",
        ),
        array(
            'name' => 'summary2',
            'view' => 'saleslog',
            'relation' => array(
                array('foreign-key' => 'dt', 'operator' => '>=', 'join-field' => 'startdt',),
                array('foreign-key' => 'dt', 'operator' => '<', 'join-field' => 'enddt',),
            ),
            'extending-class' => "SumForCustomers",
        ),
        array(
            'name' => 'data',
            'view' => 'saleslog',
            'relation' => array(
                array('foreign-key' => 'dt', 'operator' => '>=', 'join-field' => 'startdt',),
                array('foreign-key' => 'dt', 'operator' => '<', 'join-field' => 'enddt',),
            ),
            'sort' => array(
                array('field'=>'total', 'direction'=>'desc'),
            ),
            'records' => 10,
        ),
    ),
    array(),
    array('db-class' => 'PDO'),
    false
);
