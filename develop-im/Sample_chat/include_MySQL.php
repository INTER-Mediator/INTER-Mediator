<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once ('../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 100000000,
            'name' => 'chat',
            'key' => 'id',
            'sort' => array(
                array('field' => 'postdt', 'direction' => 'desc'),
            ),
            'default-values' => array(
                array('field' => 'postdt', 'value' => date("Y-m-d H:i:s")),
            ),
            'authentication' => array(
                'all' => array( // load, update, new, delete
//                    'user' => array (),
//                    'group' => array(),
//                    'target' => 'table',
//                    'target' => 'field-user',
//                    'field' => 'user',
//                    'field' => 'groupname',
                ),
            ),
        ),
    ),
    array(
        'formatter' => array(
            array('field' => 'chat@postdt', 'converter-class' => 'MySQLDateTime'),
        ),
        'authentication' => array( // table only, for all operations
            'user' => array('user1'), // Itemize permitted users
            'group' => array('group2'), // Itemize permitted groups
            'privilege' => array(), // Itemize permitted privileges
            'user-table' => 'authuser', // Default values, or "_Native"
            'group-table' => 'authgroup',
            'corresponding-table' => 'authcor',
            'challenge-table' => 'issuedhash',
            'authexpired' => '300', // Set as seconds.
            'storing' => 'cookie-domainwide', // 'cookie'(default), 'cookie-domainwide', 'none'
        ),
    ),
    array('db-class' => 'PDO'),
    1
);

?>