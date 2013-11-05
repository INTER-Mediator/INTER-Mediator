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
            'user' => array('user1'), // Itemize permitted users
//           'user' => array('database_native'), // Use DB-Native users.
//            'group' => array('group2'), // Itemize permitted groups
//            'user-table' => 'authuser', // Default value "authuser"
//            'group-table' => '', //'authgroup',
//            'challenge-table' => 'issuedhash',
            'authexpired' => '3600', // Set as seconds.
            'email-as-username' => true,
            'storing' => 'cookie-domainwide', // 'cookie'(default), 'cookie-domainwide', 'none'
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

?>