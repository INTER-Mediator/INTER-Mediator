<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'placelist',
            'aggregation-select' => 'DISTINCT f7, f8',
            'aggregation-from' => 'postalcode',
//            'aggregation-group-by' => 'id',
            'records' => 1000,
            'maxrecords' => 1000,
            'key' => 'id',
        ),
    ),
    array(
        'credit-including' => 'footer',
    ),
    array(
        'db-class' => 'PDO',
    ),
    //todo ## Set the debug level to false, 1 or 2.
    false
);
