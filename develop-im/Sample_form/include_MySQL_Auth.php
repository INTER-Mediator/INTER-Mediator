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
            'name' => 'person',
            'key' => 'id',
            'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
            'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
            'repeat-control' => 'insert delete',
        ),
        array(
            'name' => 'contact',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'insert delete',
        ),
        array(
            'name' => 'contact_way',
            'key' => 'id',
        ),
        array(
            'name' => 'cor_way_kindname',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'way_id', 'join-field' => 'way', 'operator' => '=')
            ),
            'foreign-key' => 'way_id',
            'join-field' => 'way'
        ),
        array(
            'name' => 'history',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'insert delete',
            'authentication' => array(
                'all' => array( /* load, update, new, delete*/
                    'user' => array (),
                    'group' => array(),
                    'privilege' => array(),
                    'target' => 'table',
                ),
                'load' => array( /* load, update, new, delete*/
                    'user' => array (),
                    'group' => array(),
                    'privilege' => array(),
                    'target' => 'record',
                    'field' => 'field'
                ),
            ),
        ),
    ),
    array(
        'formatter' => array(),
        'aliases' => array(
            'kindid' => 'cor_way_kindname@kind_id@value',
            'kindname' => 'cor_way_kindname@name_kind@innerHTML',
        ),
        //    'transaction' => 'none',
        'authentication' => array(  // table only, for all operations
            'user' => array (), // Itemize permitted users
            'group' => array(), // Itemize permitted groups
            'privilege' => array(), // Itemize permitted privileges
            'user-table' => 'authuser', // Default values, or "_Native"
            'group-table' => 'authgroup',
            'privilege-table' => 'authpriv',
            'corresponding-table' => 'authcor',
            'challenge-table' => 'issuedhash',
            'authexpired' => '3600',  // Set as seconds.
        ),

    ),
    array('db-class' => 'PDO'),
    true
);

?>