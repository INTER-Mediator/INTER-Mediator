<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once('../../INTER-Mediator.php');
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
                    'field' => 'total_calc',
                    'expression' => 'format(sum(item@amount_calc) * (1 + _@taxRate ))',
                ),
            ),
        ),
        array(
            'name' => 'item',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'invoice_id', 'join-field' => 'id', 'operator' => 'eq')
            ),
            //    'foreign-key' 	=> 'invoice_id',
            //    'join-field' 	=> 'id',
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
            ),
            'calculation' => array(
                array(
                    'field' => 'amount_calc',
                    'expression' => "qty * if (unitprice == '', product@unitprice, unitprice)",
                ),
                array(
                    'field' => 'qty@style.color',
                    'expression' => "if(qty > 10, 'red', 'black')",
                ),
            ),
//            'post-repeater' => 'itemsExpanded',
        ),
        array(
            'name' => 'product',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'id', 'join-field' => 'product_id', 'operator' => 'eq'),
            ),
            //    'foreign-key' 	=> 'id',
            //    'join-field' 	=> 'product_id',
        ),
    ),
    array(
        'formatter' => array(
            array('field' => 'item@amount', 'converter-class' => 'Number', 'parameter' => '0'),
        ),
    ),
    array('db-class' => 'FileMaker_FX'),
    false
);

?>
