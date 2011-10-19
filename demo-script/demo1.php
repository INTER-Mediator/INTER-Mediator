<?php
require_once ('../develop-im/INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
         array(
             'records' => '1',
             'name' => 'person',
             'key' => 'id',
         //    'repeat-control' => 'insert delete',
         //    'paging' => true,
         ),
    ),
    null,
    array( 'db-class' => 'PDO' ),
    false
);
?>