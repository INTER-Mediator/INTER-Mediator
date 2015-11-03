<?php
//todo ## Set the valid path to the file 'INTER-Mediator.php'
require_once('../../INTER-Mediator.php');

IM_Entry(array (
  0 => 
  array (
    'name' => 'invoice-all',
    'table' => 'invoice',
    'view' => 'invoice',
    'key' => 'id',
  ),
  1 => 
  array (
    'name' => 'invoice',
    'table' => 'invoice',
    'view' => 'invoice',
    'key' => 'id',
    'repeat-control' => 'delete insert',
    'authentication' => 
    array (
      'all' => 
      array (
        'target' => 'field-user',
        'field' => 'authuser',
      ),
    ),
  ),
),
array (
  'authentication' => 
  array (
    'storing' => 'session-storage',
    'realm' => 'Sample',
    'authexpired' => '3600',
  ),
),
array (
  'db-class' => 'FileMaker_FX',
  'database' => 'TestDB',
  'user' => 'web',
  'password' => 'password',
  'server' => '10.0.1.21',
  'port' => '80',
  'protocol' => 'http',
  'datatype' => 'fmpro12',
),
2);
