<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2012/06/20
 * Time: 22:18
 * To change this template use File | Settings | File Templates.
 */
require_once( '../INTER-Mediator/INTER-Mediator.php');
IM_Entry(
    array(
        array(
            'name'=>'postalcode',
//            'records'=>'10',
//            'paging'=>true,
//            'key'=>'id',
//            'query'=>array(
//                array('field'=>'f3','value'=>'160','operator'=>'bw')
//            ),
        ),
//        array(
//            'name'=>'restaurant',
//            'key'=>'id',
//            'relation'=>array(
//                array('foreign-key'=>'postalcode', 'join-field' => 'f3', 'operator' => 'eq'),
//            )
//        ),
    ),
    null,
//    array(
//        'authentication'=>array(),
//    ),
    array(
        'db-class' => 'FileMaker_FX',
//        'db-class' => 'PDO',
//        'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;',
//        'user'=> 'web',
//        'password' => 'password'
    ),
    false
);
