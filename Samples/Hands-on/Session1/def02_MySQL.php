<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'survey',
            'records' => 10,
            'maxrecords' => 10,
            'key' => 'id',
            'paging' => true,
            'repeat-control' => 'confirm-insert confirm-delete',
        ),
    ),
    array(),
    array(
        'db-class' => 'PDO',
        'dsn' => 'mysql:host=localhost;dbname=test_db;charset=utf8mb4',
        'user' => 'web',
        'password' => 'password',
    ),
    false
);
