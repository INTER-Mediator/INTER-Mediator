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
    [
        [
            'records' => 1,
            'paging' => true,
            'name' => 'person',
            'key' => 'id',
            'query' => [],
            'sort' => [['field' => 'id', 'direction' => 'asc',],],
            'repeat-control' => 'insert copy-contact,history delete',
            'sync-control' => 'create update delete',
            'button-names' => [
                'insert' => 'レコード追加',
                'delete' => 'レコード削除',
                'copy' => 'レコード複製',
            ],
            // => ['aaa'],
        ],
        [
            'name' => 'contact',
            'key' => 'id',
            'relation' => [
                [
                    'foreign-key' => 'person_id',
                    'join-field' => 'id',
                    'operator' => '=',
                ],
            ],
            'sync-control' => 'create update delete',
            'repeat-control' => 'insert delete copy',
//        'query' =>
//            [
//                [
//                    'field' => 'datetime',
//                    'value' => '2005-01-01 00:00:00',
//                    'operator' => '>',
//                ],
//            ],
            'default-values' => [
                [
                    'field' => 'datetime',
                    'value' => '2012-01-01 00:00:00',
                ],
            ],
        ],
        [
            'name' => 'contact_way',
            'key' => 'id',
        ],
        [
            'name' => 'cor_way_kindname',
            'key' => 'id',
            'relation' => [
                [
                    'foreign-key' => 'way_id',
                    'join-field' => 'way',
                    'operator' => '=',
                ],
            ],
        ],
        [
            'name' => 'history',
            'key' => 'id',
            'relation' => [
                [
                    'foreign-key' => 'person_id',
                    'join-field' => 'id',
                    'operator' => '=',
                ],
            ],
            'repeat-control' => 'insert delete',
        ],
    ],
    [
        'formatter' => [],
        'aliases' => [
            'kindid' => 'cor_way_kindname@kind_id@value',
            'kindname' => 'cor_way_kindname@name_kind@innerHTML',
        ],
    ],
    [
        'db-class' => 'PDO',
//        'dsn' => 'mysql:host=127.0.0.1;dbname=test_db;charset=utf8mb4',
    ],
    2
);
