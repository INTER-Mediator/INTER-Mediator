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
    array(
        array(
            'paging' => true,
            'records' => 10,
            'name' => 'operationlog',
            'view' => 'operationlog',
            'table' => 'dummy',
            'key' => 'id',
            'sort' => array(
                array('field' => 'dt', 'direction' => 'DESC'),
            ),
        ),
     ),
    array(
//        'authentication' => array( // table only, for all operations
//            'authexpired' => '300', // Set as seconds.
//            'storing' => 'none', // 'cookie', 'cookie-domainwide', 'none', 'session-storage'
//        ),
    ),
    array(
        'db-class' => 'PDO',
//        'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;',
//        'option' => array(),
//        'user' => 'web',
//        'password' => 'password',
    ),
    false
);
