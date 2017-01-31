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
            'records' => 1,
            'paging' => true,
            'name' => 'person_layout',
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
            'sort' => array(
                array('field' => 'id', 'direction' => 'ascend'
                ),
            ),
            'authentication' => array(
                'read' => array( /* load, update, new, delete*/
                    'group' => array("group1","group2"),
                ),
                'update' => array( /* load, update, new, delete*/
                    'group' => array("group2"),
                ),
                'create' => array( /* load, update, new, delete*/
                    'group' => array("dummy"),
                ),
                'delete' => array( /* load, update, new, delete*/
                    'group' => array("dummy"),
                ),
            ),
        ),
        array(
            'name' => 'contact_to',
            'key' => 'id',
            'repeat-control' => 'confirm-delete confirm-insert',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => 'eq')
            ),
        ),
        array(
            'name' => 'contact_way',
            'key' => 'id',
        ),
        array(
            'name' => 'cor_way_kind',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'way_id', 'join-field' => 'way', 'operator' => 'eq')
            ),
        ),
        array(
            'name' => 'history_to',
            'key' => 'id',
            'repeat-control' => 'delete insert',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => 'eq')
            ),
        ),
    ),
    array(
        'formatter' => array(
            array('field' => 'contact_to@datetime', 'converter-class' => 'FMDateTime'),
            array('field' => 'history_to@startdate', 'converter-class' => 'FMDateTime'),
            array('field' => 'history_to@enddate', 'converter-class' => 'FMDateTime'),
        ),
        'authentication' => array( // table only, for all operations
//            'user' => array('user1'), // Itemize permitted users
//           'user' => array('database_native'), // Use DB-Native users.
//            'group' => array('group2'), // Itemize permitted groups
//            'user-table' => 'authuser', // Default value "authuser"
//            'group-table' => '', //'authgroup',
//            'challenge-table' => 'issuedhash',
            'authexpired' => '3600', // Set as seconds.
            'email-as-username' => true,
            'storing' => 'session-storage', // 'cookie'(default), 'cookie-domainwide', 'none'
            'realm' => 'Sample_Auth/FMS_definitions', //
//            'issuedhash-dsn' => 'sqlite:/var/db/im/sample.sq3',
        ),
    ),
    array(
        'db-class' => 'FileMaker_FX',
//        'external-db' => array(
//            'issuedhash' => 'sqlite:/var/db/im/sample.sq3',
//        ),
    ),
    2
);
