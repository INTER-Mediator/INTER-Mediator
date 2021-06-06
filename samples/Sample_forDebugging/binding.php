<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../INTER-Mediator.php');

IM_Entry(array(
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
//        'authentication' =>
//            array(
//                'storing' => 'credential',
//                'realm' => 'Sample',
//                'authexpired' => '3600',
//            ),
    ),
    array(
        'db-class' => 'PDO',
    ),
    false);
