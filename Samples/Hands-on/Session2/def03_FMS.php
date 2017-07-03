<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../../../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        0 =>
            array(
                'name' => 'person_list',
                'table' => 'person_layout',
                'view' => 'person_layout',
                'records' => 10,
                'maxrecords' => 100,
                'paging' => true,
                'key' => '-recid',
                'repeat-control' => 'insert',
                'navi-control' => 'master',
            ),
        1 =>
            array(
                'name' => 'person_detail',
                'table' => 'person_layout',
                'view' => 'person_layout',
                'key' => '-recid',
                'records' => 1,
                'maxrecords' => 100,
                'navi-control' => 'detail',
            ),
    ),
    array(),
    array(
        'db-class' => 'FileMaker_FX',
        'database' => 'TestDB',
        'user' => 'web',
        'password' => 'password',
        'server' => '192.168.56.1',
        'port' => '80',
        'protocol' => 'http',
        'datatype' => 'FMPro12',
    ),
    false
);
