<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
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
