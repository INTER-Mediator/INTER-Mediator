<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2013 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/
require_once('../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'contexts',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-top-insert',
        ),
        array(
            'name' => 'relation',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'query',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'sort',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'default-values',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'validation',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'script',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'global',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'file-upload',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),

        array(
            'name' => 'options',
            'records' => 100000,
            'key' => 'id',
        ),
        array(
            'name' => 'aliases',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'formatter',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'browser-compatibility',
            'records' => 100000,
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'context_id', 'join-field' => 'id', 'operator' => '='),
            ),
        ),
        array(
            'name' => 'dbsettings',
            'records' => 100000,
            'key' => 'id',
        ),
        array(
            'name' => 'debug',
            'records' => 100000,
            'key' => 'id',
        ),
    ),
    array(
        'formatter' => array(),
    ),
    array(
        'db-class' => 'DefEditor',
    ),
    false
);

?>