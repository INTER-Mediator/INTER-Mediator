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
            'records' => 10,
            'name' => 'productlist',
            'view' => 'product',
            'key' => 'id',
            'query' => array(array('field' => 'name', 'value' => '*', 'operator' => 'cn')),
            'sort' => array(array('field' => 'name', 'direction' => 'ascend'),),
            'post-repeater' => 'move',
        ),
        array(
            'records' => 1,
            'name' => 'productdetail',
            'view' => 'product',
            'key' => 'id',
            'query' => array(array('field' => 'name', 'value' => '*', 'operator' => 'cn')),
            'sort' => array(array('field' => 'name', 'direction' => 'ascend'),),
        ),
    ),
    array(
        'formatter' => array(
            //array('field' => 'product@photofile', 'converter-class' => 'AppendPrefix', 'parameter' => 'images/'),
            //array('field' => 'product@id', 'converter-class' => 'AppendPrefix', 'parameter' => 'detail_FMS_include.php?id='),
            array('field' => 'product@unitprice', 'converter-class' => 'Number', 'parameter' => '0'),
        ),
    ),
    array(
        'db-class' => 'FileMaker_DataAPI',
        'port' => '443',
        'protocol' => 'https',    ),
    false // debug
);
