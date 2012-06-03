<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/03
 * Time: 20:08
 * To change this template use File | Settings | File Templates.
 */
require_once('../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => '10',
            'name' => 'productlist',
            'view' => 'product',
            'key' => 'id',
            'query' => array(array('field' => 'name', 'value' => '%', 'operator' => 'LIKE')),
            'sort' => array(array('field' => 'name', 'direction' => 'ASC'),),
        ),
        array(
            'records' => '1',
            'name' => 'productdetail',
            'view' => 'product',
            'key' => 'id',
        ),
    ),
    array(
        'formatter' => array(
            array('field' => 'product@unitprice',
                'converter-class' => 'Number',
                'parameter' => '0'),
        ),
    ),
    array('db-class' => 'PDO'),
    2);
