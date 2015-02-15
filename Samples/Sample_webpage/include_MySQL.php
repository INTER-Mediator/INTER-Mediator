<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
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
            'repeat-control'=>'insert delete',
            'default-values'=>array(
                array('field'=>'dt1', 'value'=>date('Y-m-d H:i:s')),
            ),
            'file-upload' => array(
                array('field'=>'vc1', 'context'=>'fileupload',)
            ),
        ),
        array(
            'name' => 'fileupload',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'f_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control'=>'delete',
        ),
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

?>