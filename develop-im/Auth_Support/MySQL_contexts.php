<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/09
 * Time: 8:29
 * To change this template use File | Settings | File Templates.
 */

require_once ('../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        array(
        //    'paging' => true,
            'records' => 10,
            'name' => 'authuser',
            'view' => 'authuser',
            'table' => 'authuser',
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'sort' => array(
                array('field' => 'id', 'direction' => 'ASC'),
            ),
        ),
        array(
            'name' => 'belonggroup',
            'view' => 'authcor',
            'table' => 'authcor',
            'key' => 'id',
            'repeat-control' => 'confirm-delete insert',
            'relation' => array(
                array('foreign-key' => 'user_id', 'join-field' => 'id', 'operator' => '='),
            ),
            'sort' => array(
                array('field' => 'group_id', 'direction' => 'ASC'),
            ),
        ),
        array(
            'name' => 'groupname',
            'view' => 'authgroup',
            'sort' => array(
                array('field' => 'groupname', 'direction' => 'ASC'),
            ),
        ),
    ),
    array(
        'authentication' => array( // table only, for all operations
//            'group' => array('admin'), // Itemize permitted groups
            'user-table' => 'authuser', // Default values, or "_Native"
            'group-table' => '', //'authgroup',
            'challenge-table' => 'issuedhash',
            'authexpired' => '300', // Set as seconds.
            'storing' => 'none', // 'cookie'(default), 'cookie-domainwide', 'none'
        ),
    ),
    array(
        'db-class' => 'PDO',
        'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;',
        'option' => array(),
        'user' => 'web',
        'password' => 'password',
    ),
    1
);
