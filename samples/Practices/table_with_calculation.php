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
            'name' => 'item',
            'key' => 'id',
            'relation' =>
                array(
                    array(
                        'foreign-key' => 'invoice_id',
                        'join-field' => 'id',
                        'operator' => '=',
                    ),
                ),
            'repeat-control' => 'insert delete',
            'default-values' =>
                array(
                    array(
                        'field' => 'product_id',
                        'value' => 1,
                    ),
                ),
            'validation' =>
                array(
                    array(
                        'field' => 'qty',
                        'rule' => 'value>=0 && value < 100',
                        'message' => 'Quantity should be between 1..99.',
                        'notify' => 'inline',
                    ),
                    array(
                        'field' => 'unitprice',
                        'rule' => 'value>=0 && value<10000',
                        'message' => 'Unit price should be between 1.. 9999.',
                        'notify' => 'end-of-sibling',
                    ),
                ),
            'calculation' =>
                array(
                        array(
                            'field' => 'total',
                            'expression' => 'sum(item@qty)',
                        ),
                    array(
                        'field' => 'amount_calc',
                        'expression' => 'qty * if(unitprice = \'\', product@unitprice, unitprice)',
                    ),
                    array(
                        'field' => 'qty_color',
                        'expression' => 'if (qty >= 10, \'red\', \'black\')',
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
        ),
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
    ),
    array(),
    array(
        'db-class' => 'PDO',
    ),
    false
);
