<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2012/06/20
 * Time: 22:18
 * To change this template use File | Settings | File Templates.
 */
require_once('../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name'=>'postalcode',
//            'records'=>10,
//            'paging'=>true,
//            'key'=>'id',
//            'query'=>array(
//                array('field'=>'f3','value'=>'160','operator'=>'bw')
//            ),
//            'sort'=>array(
//                array('field'=>'f3','direction'=>'descend')
//            ),
//            'repeat-control' => 'insert delete',
//            'default-values' => array(
//                array('field'=>'f3', 'value'=>'1600099'),
//                array('field'=>'f7', 'value'=>'東京都'),
//            )
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
//        'db-class' => 'PDO',
//        'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;',
        'db-class'=>'FileMaker_FX',
        'server' => 'msyk.dyndns.org',
        'port' =>'80',
        'user' => 'web',
        'password' => 'password',
        'datatype' => 'FMPro7',
        'database' => 'TestDB',
        'protocol' => 'HTTP',
    ),
    false
);
