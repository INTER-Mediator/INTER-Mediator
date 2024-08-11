<?php
/**
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2024 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'invoice',
            'records' => 1,
            'paging' => true,
            'key' => 'id',
            'sort' => array(
                array('field' => 'id', 'direction' => 'ascend'),
            ),
            'repeat-control' => 'insert delete',
//            'post-enclosure' => 'invoiceExpanded',
            'calculation' => array(
                array(
                    'field' => 'total',
                    'expression' => 'im_server',
                ),
            ),
        ),
        array(
            'name' => 'item',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'invoice_id', 'join-field' => 'id', 'operator' => 'eq')
            ),
            'repeat-control' => 'insert delete',
            'default-values' => array(
                array('field' => 'product_id', 'value' => 1),
            ),
            'validation' => array(
                array(
                    'field' => 'qty',
                    'rule' => 'value>=0 && value<100',
                    'message' => 'Quantity should be between 1..99.'
                ),
                array(
                    'field' => 'unitprice',
                    'rule' => 'value>=0 && value<10000',
                    'message' => 'Unit price should be between 1.. 9999.'
                ),
                 array(
                    'field' => 'popup_style',
                    'expression' => "if (length(product_id) = 0, 'block', 'none')",
                ),
                 array(
                    'field' => 'pinfo_style',
                    'expression' => "if (length(product_id) > 0, 'block', 'none')",
                ),
            ),
            'calculation' => array(
                array(
                    'field' => 'amount',
                    'expression' => "im_server",
                ),
            ),
        ),
        array(
            'name' => 'product',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'id', 'join-field' => 'product_id', 'operator' => 'eq'),
            ),
        ),
        array(
            'name' => 'productlist',
            'view' => 'product',
            'table' => 'dummy',
            'key' => 'id',
        ),
    ),
    array(
        'formatter' => array(
            array(
                'field' => 'invoice@issued',
                'converter-class' => 'FMDateTime',
                'parameter' => '%Y-%m-%d'
            ),
            array(
                'field' => 'item@qty',
                'converter-class' => 'NullZeroString',
                'parameter' => '0'
            ),
            array(
                'field' => 'item@unitprice',
                'converter-class' => 'NullZeroString',
                'parameter' => '0'
            ),
            array(
                'field' => 'product@unitprice',
                'converter-class' => 'Number',
                'parameter' => '2'
            ),
        ),
    ),
    array(
        'db-class' => 'FileMaker_FX',
        'port' => '80',
        'protocol' => 'http',
    ),
    false
);
