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
            'sort' => [['field' => 'id', 'direction' => 'ascend'],],
            'repeat-control' => 'insert delete',
//            'post-enclosure' => 'invoiceExpanded',
            'sync-control' => 'create update delete',
            'calculation' => [[
                'field' => 'total_calc',
                'expression' => 'format(sum(item@amount_calc) * (1 + _@taxRate ))',
            ],],
        ],
        [
            'name' => 'item',
            'key' => 'id',
            'relation' => [['foreign-key' => 'invoice_id', 'join-field' => 'id', 'operator' => 'eq'],],
            'repeat-control' => 'insert delete',
            'default-values' => [['field' => 'product_id', 'value' => 1],],
            'sync-control' => 'create update delete',
            'validation' => [[
                'field' => 'qty',
                'rule' => 'value>=0 && value<100',
                'message' => 'Quantity should be between 1..99.'
            ], [
                'field' => 'unitprice',
                'rule' => 'value>=0 && value<10000',
                'message' => 'Unit price should be between 1.. 9999.'
            ],],
            'calculation' => [[
                'field' => 'amount_calc',
                'expression' => "qty * if ( product_unitprice='', product@unitprice, product_unitprice )",
            ],],
//            'post-repeater' => 'itemsExpanded',
        ],
        [
            'name' => 'product',
            'key' => 'id',
            'relation' => [['foreign-key' => 'id', 'join-field' => 'product_id', 'operator' => 'eq'],],
        ],
    ],
    [
        'formatter' => [
            [
                'field' => 'invoice@issued',
                'converter-class' => 'FMDateTime',
                'parameter' => '%Y-%m-%d'
            ],
            [
                'field' => 'item@qty',
                'converter-class' => 'NullZeroString',
                'parameter' => '0'
            ],
            [
                'field' => 'item@unitprice',
                'converter-class' => 'NullZeroString',
                'parameter' => '0'
            ],
            [
                'field' => 'product@unitprice',
                'converter-class' => 'Number',
                'parameter' => '2'
            ],
        ],
    ],
    [
        'db-class' => 'FileMaker_FX',
        'port' => '80',
        'protocol' => 'http',
    ],
    false
);
