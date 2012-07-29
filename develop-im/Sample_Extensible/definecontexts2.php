<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/18
 * Time: 0:19
 * To change this template use File | Settings | File Templates.
 */

require_once ('../INTER-Mediator/INTER-Mediator.php');

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

