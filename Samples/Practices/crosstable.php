<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(array (
  0 => 
  array (
    'name' => 'item',
    'table' => 'dummy',
    'view' => 'item_master',
    'records' => 100000,
    'maxrecords' => 100000,
    'key' => 'id',
    'query' => 
    array (
      0 => 
      array (
        'field' => 'id',
        'value' => 25,
        'operator' => '>=',
      ),
      1 => 
      array (
        'field' => 'id',
        'value' => 35,
        'operator' => '<=',
      ),
    ),
    'sort' => 
    array (
      0 => 
      array (
        'field' => 'id',
        'direction' => 'asc',
      ),
    ),
  ),
  1 => 
  array (
    'name' => 'customer',
    'table' => 'dummy',
    'view' => 'customer',
    'records' => 100000,
    'maxrecords' => 100000,
    'key' => 'id',
    'query' => 
    array (
      0 => 
      array (
        'field' => 'id',
        'value' => 250,
        'operator' => '>=',
      ),
      1 => 
      array (
        'field' => 'id',
        'value' => 259,
        'operator' => '<=',
      ),
    ),
    'sort' => 
    array (
      0 => 
      array (
        'field' => 'id',
        'direction' => 'asc',
      ),
    ),
  ),
  2 => 
  array (
    'name' => 'salessummary',
    'table' => 'dummy',
    'view' => 'saleslog',
    'records' => 100000,
    'key' => 'id',
    'relation' => 
    array (
      0 => 
      array (
        'foreign-key' => 'item_id',
        'join-field' => 'id',
        'operator' => '=',
      ),
      1 => 
      array (
        'foreign-key' => 'customer_id',
        'join-field' => 'id',
        'operator' => '=',
      ),
    ),
  ),
),
array (
  'local-context' => 
  array (
    1 => 
    array (
      'key' => '= new v',
      'value' => 'INTER-Mediator',
    ),
    2 => 
    array (
      'key' => '= new value =',
      'value' => '= new value =',
    ),
  ),
),
array (
  'db-class' => 'PDO',
),
2);
