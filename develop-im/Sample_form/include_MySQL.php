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
            'query' => array(
                array( 'field'=>'datetime', 'value'=>'2005-01-01 00:00:00', 'operator'=>'>' ),
            ),
            'default-values' => array(
                array( 'field'=>'datetime', 'value'=>'2012-01-01 00:00:00' ),
            )
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
        array('name' => 'history',
            'key' => 'id',
            'relation' => array(
                array('foreign-key' => 'person_id', 'join-field' => 'id', 'operator' => '=')
            ),
            'repeat-control' => 'insert delete',
        ),
    ),
    array(
        'formatter' => array(),
        'aliases' => array(
            'kindid' => 'cor_way_kindname@kind_id@value',
            'kindname' => 'cor_way_kindname@name_kind@innerHTML',
        ),
    ),
    array('db-class' => 'PDO'),
    false
);

?>