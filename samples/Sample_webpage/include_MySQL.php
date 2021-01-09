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
            ],
            'post-reconstruct' => true,
            'repeat-control' => 'insert delete',
            'default-values' => [['field' => 'dt1', 'value' => date('Y-m-d H:i:s'),],],
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
        'media-root-dir' => '/tmp',
        'import' => [
            '1st-line' => true,
            'skip-lines' => 0,
        ]
    ],
    ['db-class' => 'PDO'],
    2
);
