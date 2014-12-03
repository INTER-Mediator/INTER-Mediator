<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'postalcode',
            'table' => 'postalcode',
            'view' => 'postalcode',
            'records' => 10,
            'maxrecords' => 100,
            'paging' => true,
            'key' => 'id',
            'query' => array(
                array(
                    'field' => 'f3',
                    'value' => '1%',
                    'operator' => 'LIKE'
                )
            ),
            'sort' => array(
                array(
                    'field' => 'f3',
                    'direction' => 'ASC'
                )
            ),
            'repeat-control' => 'confirm-insert confirm-delete',
        ),
    ),
    array(
    ),
    array(
        'db-class' => 'PDO',
    ),
    2
);
