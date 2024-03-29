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

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 3,
            'name' => 'productlist',
            'view' => 'product',
            'table' => 'product',
            'key' => 'id',
            'paging' => true,
            'sort' => array(array('field' => 'unitprice', 'direction' => 'ASC'),),
            'navi-control' => 'master',
            'repeat-control' => 'insert delete copy',
        ),
        array(
            'records' => 1,
            'name' => 'productdetail',
            'view' => 'product',
            'table' => 'product',
            'key' => 'id',
            'navi-control' => 'detail-top',
        ),
    ),
    array(
//        'formatter' => array(
//            array('field' => 'product@unitprice', 'converter-class' => 'Number', 'parameter' => '0'),
//        ),
    ),
    array('db-class' => 'PDO'),
    false
);
