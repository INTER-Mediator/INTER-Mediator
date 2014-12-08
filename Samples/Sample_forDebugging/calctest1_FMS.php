<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

$tableDefinitions = array(
    array(
        'name' => 'chat',
        'records' => 10,
        'paging' => true,
        'query' => array(
            //    array('field' => 'issued', 'value' => '2012-01-01', 'operator' => '>=')
        ),
        'sort' => array(
           // array('field' => 'id', 'direction' => 'ASC'),
        ),
        'repeat-control' => 'insert delete',
        'calculation' => array(
            array(
                'field' => 'calc',
                'expression' => "'<special>' + message",
            ),
        ),
    ),
);
$optionDefinitions = array();
$dbDefinitions = array('db-class' => 'FileMaker_FX');

IM_Entry($tableDefinitions, $optionDefinitions, $dbDefinitions, 0);
