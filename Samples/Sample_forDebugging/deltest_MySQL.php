<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2014/04/27
 * Time: 19:12
 */
require_once('../../INTER-Mediator.php');

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
            'soft-delete' => 'delete',
        ),
    ),
    array(),
    array('db-class' => 'PDO'),
    2
);
