<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2014 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once('../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'chat',
            'records' => 10,
            'paging' => true,
            'key' => 'id',
            'query' => array( //    array('field' => 'issued', 'value' => '2012-01-01', 'operator' => '>=')
            ),
            'sort' => array(
                array('field' => 'postdt', 'direction' => 'DESC'),
            ),
            'repeat-control' => 'insert delete',
            'calculation' => array(
                array(
                    'field' => 'calc',
                    'expression' => "'<special>' + message",
                ),
            ),
            'validation' => array(
                array(
                    'field' => 'message',
                    'rule' => 'length(value)>10',
                    'message' => 'You should write more than 10 characters.',
                    'notify' => 'end-of-sibling'
                ),
            ),
        ),
    ),
    array(),
    array(
        'db-class' => 'PDO'
    ),
    0
);
