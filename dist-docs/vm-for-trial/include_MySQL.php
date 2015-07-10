<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'information',
            'key' => 'id',
            'records' => 1,
            'maxrecords' => 1,
        ),
    ),
    array(
        'formatter' => array(
            array(
                'field' => 'information@lastupdated',
                'converter-class' => 'MySQLDateTime',
                'parameter'=>'%Y年%-m月%-d日',
            )
        ),
    ),
    array(
        'db-class' => 'PDO',
    ),
    FALSE
);
