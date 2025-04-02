<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(array(
    array(
        'name' => 'item',
        'table' => 'dummy',
        'view' => 'alphabet',
        'records' => 100000,
        'maxrecords' => 100000,
        'key' => 'id',
        'query' => array(
            array(
                'field' => 'id',
                'value' => '12',
                'operator' => '>=',
            ),
            array(
                'field' => 'id',
                'value' => '20',
                'operator' => '<=',
            ),
        ),
        'sort' => array(
            array(
                'field' => 'c',
                'direction' => 'asc',
            ),
        ),
        'calculation' => array(
            array(
                'field' => 'idone',
                'expression' => 'id + 1',
            ),
        ),
    ),
    array(
        'name' => 'customer',
        'table' => 'dummy',
        'view' => 'alphabet',
        'records' => 100000,
        'maxrecords' => 100000,
        'key' => 'id',
        'sort' => array(
            array(
                'field' => 'c',
                'direction' => 'asc',
            ),
        ),
        'calculation' => array(
            array(
                'field' => 'idone',
                'expression' => 'id + 1',
            ),
        ),
    ),
    array(
        'name' => 'salessummary',
        'table' => 'dummy',
        'view' => 'saleslog_summary',
        'records' => 100000,
        'key' => 'id',
        'relation' => array(
            array(
                'foreign-key' => 'item',
                'join-field' => 'c',
                'operator' => '=',
            ),
            array(
                'foreign-key' => 'customer',
                'join-field' => 'c',
                'operator' => '=',
            ),
        ),
        'calculation' => array(
            array(
                'field' => 'addone',
                'expression' => 'total + 1',
            ),
        ),
    ),
),
    array(
        'local-context' => array(
            array(
                'key' => 'pageTitle',
                'value' => 'INTER-Mediator',
            ),
            array(
                'key' => 'copyright',
                'value' => 'INTER-Mediator Directive Committee',
            ),
        ),
    ),
    array(
        'db-class' => 'PDO',
    ),
    2
);
