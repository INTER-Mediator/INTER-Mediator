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
        'name' => 'send-mail',
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
        'name' => 'local-context',
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

if (php_uname('n') === 'inter-mediator-server' && $_SERVER['SERVER_ADDR'] === '192.168.56.101') {
    // for the INTER-Mediator-Server virtual machine
    IM_Entry($defContexts, array('theme'=>'thosedays'), array('db-class' => 'DefEditor'), false);
}

/**
 * Don't remove comment slashes below on any 'release.'
 */
IM_Entry($defContexts, array('theme'=>'thosedays'), array('db-class' => 'DefEditor'), false);
