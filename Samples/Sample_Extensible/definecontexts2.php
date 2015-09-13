<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
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
