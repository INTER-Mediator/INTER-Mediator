<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'postalcode',
            'table' => 'postalcode',
            'view' => 'postalcode',
            'records' => 1000000,
            'maxrecords' => 1000000,
            'navi-control' => 'master',
            //'paging' => true,
            'key' => 'id',
            'sort' => array(
                array(
                    'field' => 'f3',
                    'direction' => 'ASC'
                )
            ),
            //'repeat-control' => 'confirm-insert confirm-delete',
        ),
        array(
            'name' => 'postaldetail',
            'table' => 'postalcode',
            'view' => 'postalcode',
            'records' => 1,
            'maxrecords' => 1,
            'navi-control' => 'detail',
            'key' => 'id',
            'sort' => array(
                array(
                    'field' => 'f3',
                    'direction' => 'ASC'
                )
            ),
            //'repeat-control' => 'confirm-insert confirm-delete',
        ),
    ),
    array(),
    array(
        'db-class' => 'PDO',
    ),
    false
);
