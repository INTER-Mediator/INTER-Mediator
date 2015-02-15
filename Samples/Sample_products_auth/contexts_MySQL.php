<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
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
            'navi-control' => 'master-hide',
            'authentication'=> array(
                'media-handling' => true,
                'load' => array(),
            ),
        ),
        array(
            'records' => 1,
            'name' => 'productdetail',
            'view' => 'product',
            'key' => 'id',
            'navi-control' => 'detail',
            'authentication'=> array(
                'media-handling' => true,
                'load' => array(),
            ),
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
        'authentication' => array(
            'storing' => 'cookie',
            'realm' => 'Sample_products_auth',
        ),
        'media-root-dir'=>'/Library/WebServer/Documents/Samples/Sample_products/images',
        'media-context'=>'productlist',
    ),
    array('db-class' => 'PDO'),
    false
);
