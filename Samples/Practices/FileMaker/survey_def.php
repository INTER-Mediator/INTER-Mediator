<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 * 
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once(dirname(__FILE__) . '/../../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'surveyinput',
            'table' => 'survey',
            'key' => 'id',
            'post-dismiss-message' => 'ありがとうございました',
            'validation' => array(
                array(
                    'field' => 'Q1',
                    'rule' => "value != ''",
                    'message' => '何か入力してください',
                    'notify' => 'inline'
                ),
            ),
        ),
        array(
            'name' => 'surveylist',
            'view' => 'survey',
            'key' => 'id',
            'records' => 10,
            'maxrecords' => 10,
            'paging' => true,
            'sort' => array(
                array('field'=>'Q2', 'direction' => 'DESC')
            ),
            'query' => array(
          //      array('field' => 'Q2', 'operator'=>'>', 'value' => '43')
            )
        ),
    ),
    null,
    array('db-class' => 'FileMaker_FX'),
    false
);