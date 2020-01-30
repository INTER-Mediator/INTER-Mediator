<?php
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'memolist',
            'table' => 'testtable',
            'view' => 'testtable',
            'records' => 10000,
            'maxrecords' => 10000,
            'key' => 'id',
            'navi-control' => 'step',
            'repeat-control' => 'insert-top',
           // 'before-move-nextstep'=>'nextStepFromList'
        ),
        array(
            'name' => 'memoview',
            'table' => 'testtable',
            'view' => 'testtable',
            'records' => 1,
            'maxrecords' => 1,
            'key' => 'id',
            'navi-control' => 'step-hide',
           // 'before-move-nextstep'=>'nextStepFromView'
        ),
        array(
            'name' => 'memoedit',
            'table' => 'testtable',
            'view' => 'testtable',
            'records' => 1,
            'maxrecords' => 1,
            'key' => 'id',
            'navi-control' => 'step-hide',
            'before-move-nextstep'=>'nextStepFromEdit',
            'just-move-thisstep'=>'editPageStart'
        ),
    ),
    array(
        'credit-including' => 'footer',
    ),
    array(
        'db-class' => 'PDO',
    ),
    false
);
