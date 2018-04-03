<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'survey',
            'key' => 'id',
            'sort' =>
                array(
                    array(
                        'field' => 'f3',
                        'direction' => 'ASC',
                    ),
                ),
            'post-reconstruct' => true,
            'post-dismiss-message' => '送信しました',
            'post-move-url' => 'http://inter-mediator.org/',
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
