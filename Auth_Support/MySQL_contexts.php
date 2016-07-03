<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/09
 * Time: 8:29
 * To change this template use File | Settings | File Templates.
 */

require_once('../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'paging' => true,
            'records' => 10,
            'name' => 'authuser',
            'view' => 'authuser',
            'table' => 'authuser',
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'sort' => array(
                array('field' => 'id', 'direction' => 'ASC'),
            ),
            'extending-class'=>"UserList",
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
                array('field' => 'dest_group_id', 'direction' => 'ASC'),
            ),
        ),
        array(
            'name' => 'groupname',
            'view' => 'authgroup',
            'sort' => array(
                array('field' => 'id', 'direction' => 'ASC'),
            ),
        ),
        array(
            //    'paging' => true,
            //    'records' => 10,
            'name' => 'authgroup',
            'view' => 'authgroup',
            'table' => 'authgroup',
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'sort' => array(
                array('field' => 'id', 'direction' => 'ASC'),
            ),
        ),
        array(
            'name' => 'groupingroup',
            'view' => 'authcor',
            'table' => 'authcor',
            'key' => 'id',
            'repeat-control' => 'confirm-delete insert',
            'relation' => array(
                array('foreign-key' => 'group_id', 'join-field' => 'id', 'operator' => '='),
            ),
            'sort' => array(
                array('field' => 'dest_group_id', 'direction' => 'ASC'),
            ),
        ),
    ),
    array(
        'authentication' => array( // table only, for all operations
            'authexpired' => '300', // Set as seconds.
            'storing' => 'none', // 'cookie'(default), 'cookie-domainwide', 'none'
        ),
    ),
    array(
        'db-class' => 'PDO',
//        'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;',
//        'option' => array(),
//        'user' => 'web',
//        'password' => 'password',
    ),
    0
);
