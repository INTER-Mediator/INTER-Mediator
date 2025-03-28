<?php
/**
 * INTER-Mediator
 * Copyright (c] INTER-Mediator Directive Committee (http://inter-mediator.org]
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c] INTER-Mediator Directive Committee (http://inter-mediator.org]
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

require_once('../../INTER-Mediator.php');

IM_Entry(
    [
        [
            'records' => 10000,
            'name' => 'testtable',
            'key' => 'id',
            'sort' => [['field' => 'dt1', 'direction' => 'desc'],],
            'file-upload' => [
                ['field' => 'vc2', 'context' => 'fileupload',],
//                ['container' => 'S3',],
//                ['container' => 'Dropbox',],
            ],
//            'post-reconstruct' => true,
            'repeat-control' => 'insert delete',
            'default-values' => [['field' => 'dt1', 'value' => date('Y-m-d H:i:s'),],],
            'import' => [
                '1st-line' => true,
                'skip-lines' => 0,
                'use-replace' => true,
                'encoding' => "SJIS",
                'convert-number' => ['num1', 'num2', 'num3'],
                'convert-date' => ['dt1'],
//            'convert-datetime' => [],
            ]
        ],
        [
            'name' => 'fileupload',
            'key' => 'id',
            'relation' => [['foreign-key' => 'f_id', 'join-field' => 'id', 'operator' => '='],],
            'repeat-control' => 'delete',
        ],
        [
            'name' => 'item_master',
            'view' => 'product',
        ]
    ],
    [
        'formatter' => [['field' => 'chat@postdt', 'converter-class' => 'MySQLDateTime'],],
//        'media-root-dir' => '/tmp',
    ],
    ['db-class' => 'PDO'],
    2
);
