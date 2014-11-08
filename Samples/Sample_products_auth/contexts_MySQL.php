<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 10,
            'name' => 'productlist',
            'view' => 'product',
            'key' => 'id',
            'query' => array(array('field' => 'name', 'value' => '%', 'operator' => 'LIKE')),
            'sort' => array(array('field' => 'name', 'direction' => 'ASC'),),
            'post-repeater' => 'move',
            'authentication'=> array(
//                'media-handling' => true,
            ),
        ),
        array(
            'records' => 1,
            'name' => 'productdetail',
            'view' => 'product',
            'key' => 'id',
        ),
    ),
    array(
        'formatter' => array(
            array(
                'field' => 'product@unitprice',
                'converter-class' => 'Number',
                'parameter' => '0'
            ),
        ),
        'authentication' => array( // table only, for all operations
//            'user' => array('user1'), // Itemize permitted users
//            'group' => array('group2'), // Itemize permitted groups
//            'privilege' => array(), // Itemize permitted privileges
//            'user-table' => 'authuser', // Default value
//            'group-table' => 'authgroup',
//            'corresponding-table' => 'authcor',
//            'challenge-table' => 'issuedhash',
//            'authexpired' => '300', // Set as seconds.
            'storing' => 'cookie', // 'cookie'(default), 'cookie-domainwide', 'none'
            'realm' => 'Sample_products_auth',
        ),
        'media-root-dir'=>'/Library/WebServer/Documents/im/Sample_products/images',
    ),
    array('db-class' => 'PDO'),
    false
);
