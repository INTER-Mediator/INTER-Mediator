<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../../../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        0 =>
            array(
                'name' => 'person_list',
                'table' => 'person',
                'view' => 'person',
                'records' => 10,
                'maxrecords' => 100,
                'paging' => true,
                'key' => 'id',
                'repeat-control' => 'insert',
                'navi-control' => 'master',
            ),
        1 =>
            array(
                'name' => 'person_detail',
                'table' => 'person',
                'view' => 'person',
                'key' => 'id',
                'records' => 1,
                'maxrecords' => 100,
                'navi-control' => 'detail',
            ),
    ),
    array(),
    array(
        'db-class' => 'PDO',
        //'dsn' => 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=test_db;charset=utf8mb4',
        'user' => 'web',
        'password' => 'password',
    ),
    false
);
