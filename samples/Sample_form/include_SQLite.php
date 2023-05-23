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
            'records' => 1,
            'paging' => true,
            'name' => 'person',
            'key' => 'id',
            'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
            'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
            'repeat-control' => 'insert delete copy-contact,history',
            'button-names' => array(
                'insert' => 'レコード追加',
                'delete' => 'レコード削除',
                'copy' => 'レコード複製',
            ),
        ),
        array(
            'name' => 'contact',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'insert-confirm delete-confirm copy',
        ),
        array(
            'name' => 'contact_way',
            'key' => 'id',
        ),
        array(
            'name' => 'cor_way_kindname',
            'aggregation-select' => 'cor_way_kind.*,contact_kind.name as name_kind',
            'aggregation-from' => 'cor_way_kind INNER JOIN contact_kind ON cor_way_kind.kind_id = contact_kind.id',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'way_id', 'join-field' => 'way', 'operator' => '=')
            ),
        ),
        array('name' => 'history',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'insert delete',
        ),
    ),
    [],
    array(
        'db-class' => 'PDO',
        'dsn' => getenv('GITHUB_ACTIONs')
            ? 'sqlite:/home/runner/work/INTER-Mediator/INTER-Mediator/sample.sq3'
            : 'sqlite:/var/db/im/sample.sq3',
    ),
    2
);
