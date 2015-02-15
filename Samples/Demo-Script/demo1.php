<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once('../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 1,
            'name' => 'person',
            'key' => 'id',
            //    'repeat-control' => 'insert delete',
            //    'paging' => true,
        ),
    ),
    null,
    array('db-class' => 'FileMaker_FX'),
//    array('db-class' => 'PDO'),
    false
);
?>