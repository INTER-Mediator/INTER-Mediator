<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/06/03
 * Time: 20:08
 * To change this template use File | Settings | File Templates.
 */
require_once('../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 10,
            'name' => 'productlist',
            'view' => 'product',
            'key' => 'id',
            'query' => array(array('field' => 'name', 'value' => '%', 'operator' => 'LIKE')),
            'sort' => array(array('field' => 'name', 'direction' => 'ASC'),),
            'authentication'=> array( 'media-handling' => true ),
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
//            'user-table' => 'authuser', // Default values, or "_Native"
//            'group-table' => 'authgroup',
//            'corresponding-table' => 'authcor',
//            'challenge-table' => 'issuedhash',
//            'authexpired' => '300', // Set as seconds.
            'storing' => 'cookie', // 'cookie'(default), 'cookie-domainwide', 'none'
        ),
        'media-root-dir'=>'/Library/WebServer/Documents/im/Sample_products/images',
    ),
    array('db-class' => 'PDO'),
    2
);
