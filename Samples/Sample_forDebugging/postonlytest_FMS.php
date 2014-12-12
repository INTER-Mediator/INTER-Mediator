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

IM_Entry(
    array(
        array(
            'name' => 'chat',
            'records' => 10,
            'paging' => true,
            'query' => array( //    array('field' => 'issued', 'value' => '2012-01-01', 'operator' => '>=')
            ),
            'sort' => array(
                array('field' => 'postdt', 'direction' => 'DESC'),
            ),
            'post-reconstruct' => true,
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
    array(
        'formatter' => array(
            array('field' => 'chat@postdt', 'converter-class' => 'FMDateTime'),
        ),
    ),
    array(
        'db-class' => 'FileMaker_FX'
    ),
    0
);
