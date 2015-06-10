<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(array(
    0 =>
        array(
            'records' => 1,
            'paging' => true,
            'name' => 'person',
            'key' => 'id',
            'query' =>
                array(),
            'sort' =>
                array(
                    0 =>
                        array(
                            'field' => 'id',
                            'direction' => 'asc',
                        ),
                ),
            'repeat-control' => 'insert delete copy-contact,history',
        ),
    1 =>
        array(
            'name' => 'contact',
            'key' => 'id',
            'relation' =>
                array(
                    0 =>
                        array(
                            'foreign-key' => 'person_id',
                            'join-field' => 'id',
                            'operator' => '=',
                        ),
                ),
            'repeat-control' => 'insert delete copy',
            'query' =>
                array(
                    0 =>
                        array(
                            'field' => 'datetime',
                            'value' => '2005-01-01 00:00:00',
                            'operator' => '>',
                        ),
                ),
            'default-values' =>
                array(
                    0 =>
                        array(
                            'field' => 'datetime',
                            'value' => '2012-01-01 00:00:00',
                        ),
                ),
        ),
    2 =>
        array(
            'name' => 'contact_way',
            'key' => 'id',
        ),
    3 =>
        array(
            'name' => 'cor_way_kindname',
            'key' => 'id',
            'relation' =>
                array(
                    0 =>
                        array(
                            'foreign-key' => 'way_id',
                            'join-field' => 'way',
                            'operator' => '=',
                        ),
                ),
        ),
    4 =>
        array(
            'name' => 'history',
            'key' => 'id',
            'relation' =>
                array(
                    0 =>
                        array(
                            'foreign-key' => 'person_id',
                            'join-field' => 'id',
                            'operator' => '=',
                        ),
                ),
            'repeat-control' => 'insert delete',
        ),
),
    array(
        'formatter' =>
            array(),
        'aliases' =>
            array(
                'kindid' => 'cor_way_kindname@kind_id@value',
                'kindname' => 'cor_way_kindname@name_kind@innerHTML',
            ),
        'smtp' =>
            array(
                'port' => 300,
                'username' => 'm',
                'password' => ',',
                'server' => 'bnnn',
            ),
    ),
    array(
        'db-class' => 'PDO',
    ),
    2);
?>