<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../INTER-Mediator.php');

IM_Entry(array(
    array(
        'name' => 'testtable',
        'key' => 'id',
        'records' => 20,
        'paging' => true,
        'repeat-control' => 'insert delete',
        'calculation' => [
            ['field' => 'info1', 'expression' => "if(isnull(num1),'XXXX',format(num1,2))"],
            ['field' => 'info2', 'expression' => "if(length(num1)=0,'XXXX',format(num1,2))"],
        ],
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
