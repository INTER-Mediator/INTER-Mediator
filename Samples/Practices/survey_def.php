<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2014/04/10
 * Time: 23:38
 */

require_once('../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'surveyinput',
            'view' => 'survey',
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
    array('db-class' => 'PDO'),
    false
);