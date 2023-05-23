<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../INTER-Mediator.php');

IM_Entry(array(
    array(
        'name' => 'testtable',
        'key' => 'id',
        'sort' => array(array('field' => 'num2', 'direction' => 'ASC'),),
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
    2);
