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

IM_Entry(array (
  0 =>
  array (
    'name' => 'invoice',
    'records' => 1,
    'paging' => true,
    'key' => 'id',
    'query' =>
    array (
    ),
    'sort' =>
    array (
      0 =>
      array (
        'field' => 'id',
        'direction' => 'ASC',
      ),
    ),
    'repeat-control' => 'insert delete',
    'calculation' =>
    array (
      0 =>
      array (
        'field' => 'total_calc',
        'expression' => 'format(sum(item@amount_calc) * (1 + _@taxRate ))',
      ),
    ),
  ),
  1 =>
  array (
    'name' => 'item',
    'key' => 'id',
    'relation' =>
    array (
      0 =>
      array (
        'foreign-key' => 'invoice_id',
        'join-field' => 'id',
        'operator' => '=',
      ),
    ),
    'repeat-control' => 'insert delete',
    'default-values' =>
    array (
      0 =>
      array (
        'field' => 'product_id',
        'value' => 1,
      ),
    ),
    'validation' =>
    array (
      0 =>
      array (
        'field' => 'qty',
        'rule' => 'value>=0 && value < 100',
        'message' => 'Quantity should be between 1..99.',
        'notify' => 'inline',
      ),
      1 =>
      array (
        'field' => 'unitprice',
        'rule' => 'value>=0 && value<10000',
        'message' => 'Unit price should be between 1.. 9999.',
        'notify' => 'end-of-sibling',
      ),
    ),
    'calculation' =>
    array (
      0 =>
      array (
        'field' => 'amount_calc',
        'expression' => 'format(qty * if ( unitprice = \'\', product@unitprice, unitprice ))',
      ),
      1 =>
      array (
        'field' => 'qty_color',
        'expression' => 'if (qty >= 10, \'red\', \'black\')',
      ),
    ),
  ),
  2 =>
  array (
    'name' => 'product',
    'key' => 'id',
    'relation' =>
    array (
      0 =>
      array (
        'foreign-key' => 'id',
        'join-field' => 'product_id',
        'operator' => '=',
      ),
    ),
  ),
),
array (
  'formatter' =>
  array (
    0 =>
    array (
      'field' => 'item@qty',
      'converter-class' => 'NullZeroString',
      'parameter' => '0',
    ),
    1 =>
    array (
      'field' => 'item@unitprice',
      'converter-class' => 'NullZeroString',
      'parameter' => '0',
    ),
    2 =>
    array (
      'field' => 'product@unitprice',
      'converter-class' => 'Number',
      'parameter' => '2',
    ),
  ),
),
array (
  'db-class' => 'PDO',
),
false);
