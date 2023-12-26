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
        [
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
            'sync-control' => "create update delete",
        ],
        [
            'name' => 'contact',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'insert-confirm delete-confirm copy',
            'sync-control' => "create update delete",
        ],
        [
            'name' => 'contact_way',
            'key' => 'id',
        ],
        [
            'name' => 'cor_way_kindname',
            'aggregation-select' => 'cor_way_kind.*,contact_kind.name as name_kind',
            'aggregation-from' => 'cor_way_kind INNER JOIN contact_kind ON cor_way_kind.kind_id = contact_kind.id',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'way_id', 'join-field' => 'way', 'operator' => '=')
            ),],
        [
            'name' => 'history',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'insert delete',
        ],
        [
            "name" => "testtable",
            "key" => "id",
            "repeat-control" => "insert delete",
            "records" => 1,
            "paging" => true,
            "sort" => [["field" => "id", "direction" => "DESC",],],
        ],
        [
            "name" => "selection",
            "key" => "id",
            "data" => [
                ["id" => "select1", "num" => "10"],
                ["id" => "select2", "num" => 20,],
                ["id" => "select3", "num" => 30]
            ]
        ],
        [
            "name" => "postalcode",
            "view" => "postalcode",
            "key" => "id",
            "records" => 100,
            "paging" => true,
            "navi-control" => "master-hide",
            "sort" => [["field" => "f3", "direction" => "asc",],],],
        [
            "name" => "detail",
            "view" => "postalcode",
            "table" => "postalcode",
            "key" => "id",
            "records" => 1,
            "navi-control" => "detail-update",
        ],
        [
            "name" => "postalcode-dp",
            "view" => "postalcode",
            "key" => "id",
            "records" => 100,
            "paging" => true,
            "navi-control" => "master",
            "sort" => [["field" => "f3", "direction" => "asc",],],],
        [
            "name" => "detail-dp",
            "view" => "postalcode",
            "table" => "postalcode",
            "key" => "id",
            "records" => 1,
            "navi-control" => "detail",
        ],
        [
            "name" => "postalcode-sep",
            "view" => "postalcode",
            "key" => "id",
            "records" => 100,
            "paging" => true,
            "sort" => [["field" => "f3", "direction" => "asc",],],],
        [
            "name" => "detail-sep",
            "view" => "postalcode",
            "table" => "postalcode",
            "key" => "id",
            "records" => 1,
        ],
        [
            "name" => "postalcode-search",
            "view" => "postalcode",
            "key" => "id",
            "records" => 1000000,
            "paging" => true,
            "sort" => [["field" => "f3", "direction" => "asc",],],],
    ),
    [
        "terms" => [
            "en" => [
                "header" => "INTER-Mediator - Sample - Form Style/SQLite",
                "category" => "Category",
                "check" => "Check",
                "page" => ["title" => "Contact Management (Sample for Several Fundamental Features",],
                "way" => [
                    "Calling" => "Telephone",
                    "Mail" => "Paper Mail",
                    "Email" => "Electronic Mail",],],
            "ja" => [
                "header" => "INTER-Mediator - サンプル - フォーム形式/SQLite",
                "category" => "カテゴリ",
                "check" => "チェック",
                "page" => ["title" => "コンタクト先管理 (さまざまな機能を確認するためのサンプル)"],
                "way" => [
                    "Calling" => "電話",
                    "Mail" => "手紙",
                    "Email" => "電子メール",],]
        ],
    ],
    array(
        'db-class' => 'PDO',
        'dsn' => getenv('GITHUB_ACTIONS')
            ? 'sqlite:/home/runner/work/INTER-Mediator/INTER-Mediator/sample.sq3'
            : 'sqlite:/var/db/im/sample.sq3',
    ),
    2
);
