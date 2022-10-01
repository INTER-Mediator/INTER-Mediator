<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../INTER-Mediator.php');

IM_Entry(
    [
        [
            'name' => 'postalcode',
            'key' => 'id',
            'records' => 20,
            'paging' => true,
            'query' => [['field' => 'f3', 'operator' => 'LIKE', 'value' => "15%"]],
            'sort' => [['field' => 'f3', 'direction' => 'ASC']],
            'repeat-control' => 'insert delete',
            'extending-class' => 'Test\MorePostalCode'
        ],
    ],
    [
//        'authentication' =>
//            array(
//                'storing' => 'credential',
//                'realm' => 'Sample',
//                'authexpired' => '3600',
//            ),
    ],
    [
        'db-class' => 'PDO',
    ],
    2
);
