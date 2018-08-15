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
            'records'=>1000,
            'calculation' =>
                array(
                    array(
                        'field' => 'total_qty',
                        'expression' => 'sum(item@qty)',
                    ),
                    array(
                        'field' => 'total_amount',
                        'expression' => 'sum(item@amount_calc)',
                    ),
                    array(
                        'field' => 'amount_calc',
                        'expression' => 'qty * if(unitprice = \'\', product@unitprice, unitprice)',
                    ),
                    array(
                        'field' => 'qty_color',
                        'expression' => 'if (qty >= 10, \'red\', \'black\')',
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
    ),
    array(),
    array(
        'db-class' => 'PDO',
    ),
    false
);
