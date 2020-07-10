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
    [
        [
            'name' => 'invoice',
            'records' => 1,
            'paging' => true,
            'key' => 'id',
            'query' => [],
            'sort' => [['field' => 'id', 'direction' => 'ASC',],],
            'repeat-control' => 'insert delete',
            'calculation' => [[
                'field' => 'total_calc',
                'expression' => 'sum(item@amount_calc)',
            ],],
        ],
        [
            'name' => 'item',
            'key' => 'id',
            'relation' => [['foreign-key' => 'invoice_id', 'join-field' => 'id', 'operator' => '=',]],
            'repeat-control' => 'insert delete copy',
            'default-values' => [['field' => 'product_id', 'value' => 3,]],
            'validation' => [
                [
                    'field' => 'qty',
                    'rule' => 'value>=0 && value < 100',
                    'message' => 'Quantity should be between 1..99.',
                    'notify' => 'inline',
                ], [
                    'field' => 'unitprice',
                    'rule' => 'value>=0 && value<10000',
                    'message' => 'Unit price should be between 1.. 9999.',
                    'notify' => 'end-of-sibling',
                ],
            ],
            'calculation' => [
                [
                    'field' => 'net_price',
                    'expression' => 'qty * product_unitprice',
                ],                [
                    'field' => 'tax_price',
                    'expression' => 'net_price * _@taxRate',
                ],                [
                    'field' => 'amount_calc',
                    'expression' => 'net_price + tax_price',
                ], [
                    'field' => 'qty_color',
                    'expression' => 'if (qty >= 10, \'red\', \'black\')',
//                ], [
//                    'field' => 'popup_style',
//                    'expression' => "if (length(product_id) = 0, 'block', 'none')",
//                ], [
//                    'field' => 'pinfo_style',
//                    'expression' => "if (length(product_id) > 0, 'block', 'none')",
                ],
            ],
        ],
        array(
            'name' => 'product',
            'key' => 'id',
            'relation' =>
                array(
                    array(
                        'foreign-key' => 'id',
                        'join-field' => 'product_id',
                        'operator' => '=',
                    ),
                ),
        ),
        array(
            'name' => 'productlist',
            'view' => 'product',
            'table' => 'dummy',
            'key' => 'id',
        ),
    ],
    array(),
    array(
        'db-class' => 'PDO',
    ),
    false
);
