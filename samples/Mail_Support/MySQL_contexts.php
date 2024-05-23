<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/09
 * Time: 8:29
 * To change this template use File | Settings | File Templates.
 */

require_once('../../INTER-Mediator.php');

IM_Entry(
    [
        [
            'name' => 'mailtemplate',
            'view' => 'mailtemplate',
            'table' => 'mailtemplate',
            'key' => 'id',
            'records' => 10,
            'paging' => true,
            'repeat-control' => 'confirm-delete confirm-insert',
            'sort' => [
                array('field' => 'id', 'direction' => 'ASC'),
            ],
        ],
    ],
    [
//        'authentication' => [
//            'authexpired' => '3600', // Set as seconds.
//            'storing' => 'credential',
//        ],
    ],
    ['db-class' => 'PDO',],
    0
);
