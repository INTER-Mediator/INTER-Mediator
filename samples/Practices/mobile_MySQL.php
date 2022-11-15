<?php
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    [
        [
            'name' => 'memolist',
            'table' => 'testtable',
            'view' => 'testtable',
            'records' => 10000,
            'key' => 'id',
            'navi-control' => 'step',
            'before-move-nextstep'=>'nextStepFromList',
        ],
        [
            'name' => 'memoview',
            'table' => 'testtable',
            'view' => 'testtable',
            'records' => 1,
            'key' => 'id',
            'navi-control' => 'step-hide',
            'calculation' => [['field'=>'htmltext','expression'=>"substitute(text1,'\n','<br>')"]]
        ],
        [
            'name' => 'memoedit',
            'table' => 'testtable',
            'view' => 'testtable',
            'records' => 1,
            'key' => 'id',
            'navi-control' => 'step-hide',
            'just-move-thisstep'=>'editPageStart'
        ],
    ],
    [
        'credit-including' => 'footer',
    ],
    [
        'db-class' => 'PDO',
    ],
    false
);
