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