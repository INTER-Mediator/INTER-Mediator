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
            'name' => 'person',
            'key' => 'id',
            'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
            'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
            'repeat-control' => 'insert delete',
//            'authentication' => array(
//                'read' => array( /* load, update, new, delete*/
//                    'group' => array("group1"),
//                ),
//                'update' => array( /* load, update, new, delete*/
//                    'group' => array("group2"),
//                ),
//                'create' => array( /* load, update, new, delete*/
//                    'group' => array("group1"),
//                ),
//                'delete' => array( /* load, update, new, delete*/
//                    'group' => array("group2"),
//                ),
//            ),
        ),
        array(
            'name' => 'contact',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'insert delete',
//            'authentication' => array(
////                'read' => array( /* load, update, new, delete*/
////                    'group' => array("group1","group2"),
////                ),
//                'update' => array( /* load, update, new, delete*/
//                    'group' => array("group2"),
//                ),
//                'create' => array( /* load, update, new, delete*/
//                    'group' => array("group1"),
//                ),
//                'delete' => array( /* load, update, new, delete*/
//                    'group' => array("group2"),
//                ),
//            ),
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
        ),
        array(
            'name' => 'history',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'insert delete',
//            'authentication' => array(
//                'all' => array( /* load, update, new, delete*/
//                    'user' => array(),
//                    'group' => array(),
//                    'target' => 'table',
//                ),
//                'load' => array( /* load, update, new, delete*/
//                    'user' => array(),
//                    'group' => array(),
//                    'target' => 'record',
//                    'field' => 'username',
//                ),
//            ),
        ),
    ),
    array(
        'formatter' => array(),
        'aliases' => array(
            'kindid' => 'cor_way_kindname@kind_id@value',
            'kindname' => 'cor_way_kindname@name_kind@innerHTML',
        ),
        //    'transaction' => 'none',
        'authentication' => array( // table only, for all operations
//            'user' => array('user1'), // Itemize permitted users
//            'group' => array('group2'), // Itemize permitted groups
//            'user-table' => 'authuser', // Default values
//            'group-table' => 'authgroup',
//            'corresponding-table' => 'authcor',
//            'challenge-table' => 'issuedhash',
//            'authexpired' => '600', // Set as seconds.
            'storing' => 'session-storage', // 'cookie'(default), 'cookie-domainwide', 'none'
            'realm' => 'Sample_Auth/MySQL_definitions', //
//            'email-as-username' => true,
//        'password-policy' => "useAlphabet useNumber useUpper useLower usePunctuation length(10) notUserName",
        ),
    ),
    array('db-class' => 'PDO'),
    false
);
