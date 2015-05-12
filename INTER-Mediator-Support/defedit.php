<?php
/**
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');

$defContexts = array(
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
        'name' => 'calculation',
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
        'name' => 'sending-email',
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
);
/**
 * Don't remove comment slashes below on any 'release.'
 */
//IM_Entry($defContexts, null, array('db-class' => 'DefEditor'), false);
