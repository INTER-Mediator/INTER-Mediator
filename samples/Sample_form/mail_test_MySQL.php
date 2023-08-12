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
            'name' => 'person_list',
            'view' => 'person',
            'table' => 'person',
            'key' => 'id',
            'records' => 100,
            'paging' => true,
            'query' => [],
            'sort' => [['field' => 'id', 'direction' => 'asc',],],
            'repeat-control' => 'insert copy-contact,history delete',
            'button-names' => [
                'insert' => 'レコード追加',
                'delete' => 'レコード削除',
                'copy' => 'レコード複製',
            ],
        ],
        [
            'name' => 'mail_send_1',
            'view' => 'person',
            'table' => 'dummy',
            'key' => 'id',
            'records' => 1,
            'send-mail' => ['read' => [
                'to' => '@@mail@@',
//                'to-constant' => 'msyk@msyk.net',
//                'from-constant' => 'msyk@msyk.net',
                'subject' => 'テストメール1',
                'from' => 'Masayuki Nii <msyk@msyk.net>',
                'body' => "@@name@@様、\nこれはテーストメールです。",
                'store' => "maillog",
                'body-wrap' => 78,
            ],],
        ],
        [
            'name' => 'mailtemplate',
            'key' => 'id',
            'records' => 1,
        ],
        [
            'name' => 'maillog',
            'key' => 'id',
            'relation' => [['foreign-key' => 'foreign_id', 'join-field' => 'id', 'operator' => '='],],
            'sort' => [['field' => 'dt', 'direction' => 'desc',],],
        ],
        [
            'name' => 'mail_send_2',
            'view' => 'person',
            'table' => 'dummy',
            'key' => 'id',
            'records' => 1,
            'send-mail' => ['read' => [
                'template-context' => "mailtemplate@id=2",
                'store' => "maillog",
            ],],
        ],
        [
            'name' => 'mail_send_3',
            'view' => 'person',
            'table' => 'dummy',
            'key' => 'id',
            'records' => 1,
            'send-mail' => ['read' => [
                'to' => '@@name@@ <@@mail@@>',
                'cc' => '新居テスト <msyk@msyk.net>, 新居テスト2 <nii@msyk.net>',
                'subject' => 'テストメール3',
                'from' => 'Masayuki Nii <msyk@msyk.net>',
                'body' => "@@name@@様、\nこれはテーストメール3です。",
                'store' => "maillog",
            ],],
        ],
        [
            'name' => 'mail_send_4',
            'view' => 'person',
            'table' => 'dummy',
            'key' => 'id',
            'records' => 1,
            'send-mail' => ['read' => [
                'to' => '@@name@@ <@@mail@@>',
                'subject' => 'テストメール4',
                'from' => 'Masayuki Nii <msyk@msyk.net>',
                'body' => "@@name@@様、\nこれはテーストメール4です。ファイルの添付のチェックです。",
                'attachment' => "samples/Sample_products/images/orange_1.png",
                'store' => "maillog",
            ],],
        ],
        [
            'name' => 'mail_send_5',
            'view' => 'person',
            'table' => 'dummy',
            'key' => 'id',
            'records' => 1,
            'send-mail' => ['read' => [
                'to' => '@@name@@ <@@mail@@>',
                'subject' => 'テストメール5',
                'from' => 'Masayuki Nii <msyk@msyk.net>',
                'body' => "<html><body>@@name@@様、<br><span style='color:red'>これはテーストメール5です。</span><br>HTMLメールとファイルの添付のチェックです。<br><hr><img src='##image##'><hr></body></html>",
                'attachment' => "samples/Sample_products/images/orange_1.png",
                'store' => "maillog",
            ],],
        ],
    ],
    [
        'media-root-dir' => "/Users/msyk/Code/INTER-Mediator/"
    ],
    [
        'db-class' => 'PDO',
    ],
    2
);
