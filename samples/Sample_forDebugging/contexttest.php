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

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'personlist',
            'view' => 'person',
            'table' => 'person',
            'key' => 'id',
            'records' => 10,
            'paging' => true,
            'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
            'sort' => array(
                array('field' => 'id', 'direction' => 'asc'),
            ),
            'repeat-control' => 'insert delete',
        ),
        array(
            'name' => 'persondetail',
            'view' => 'person',
            'table' => 'person',
            'key' => 'id',
            'records' => 1,
            'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
            'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
            'validation' => array(
                array("field" => "name", "rule" => " value != '' ", "message"=>"error!"),
            )
        ),
    ),
    array(),
    array('db-class' => 'PDO'),
    false
);
