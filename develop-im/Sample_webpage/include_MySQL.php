<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once ('../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'records' => 2,
            'name' => 'chat',
            'key' => 'id',
            'sort' => array(
                array('field' => 'postdt', 'direction' => 'desc'),
            ),
            'repeat-control'=>'insert delete',
            'default-values'=>array(
                array('field'=>'postdt', 'value'=>date('Y-m-d H:i:s')),
            ),
            'file-upload' => 'fileupload',
        ),
        array(
            'name' => 'fileupload',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'f_id', 'join-field' => 'id', 'operator' => '=')
            ),
        ),
    ),
    array(
        'formatter' => array(
            array('field' => 'chat@postdt', 'converter-class' => 'MySQLDateTime'),
        ),
        'media-root-dir' => '/tmp',
    ),
    array('db-class' => 'PDO'),
    2
);

?>