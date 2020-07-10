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

require_once('../../INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 10000,
            'name' => 'testtable',
            'key' => 'id',
            'sort' => array(
                array('field' => 'dt1', 'direction' => 'desc'),
            ),
            'file-upload' => array(
                array('field' => 'vc2', 'context' => 'fileupload'),
            ),
            'post-reconstruct' => true,
            'repeat-control' => 'insert delete',
            'default-values' => array(
                array('field' => 'dt1', 'value' => date('Y-m-d H:i:s'),
                ),
            ),
        ),
        array(
            'name' => 'fileupload',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'f_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'delete',
        ),
        array(
            'name' => 'item_master',
            'view'=>'product',
        )
    ),
    array(
        'formatter' => array(
            array('field' => 'chat@postdt', 'converter-class' => 'MySQLDateTime'),
        ),
        'media-root-dir' => '/tmp',
    ),
    array('db-class' => 'PDO'),
    false
);
