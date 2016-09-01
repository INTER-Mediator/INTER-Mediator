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

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

IM_Entry(array(
    array(
        'records' => 1,
        'paging' => true,
        'name' => 'person',
        'key' => 'id',
        'query' =>
            array(),
        'sort' =>
            array(
                array(
                    'field' => 'id',
                    'direction' => 'asc',
                ),
            ),
        'repeat-control' => 'insert copy-contact,history delete',
    ),
    array(
        'name' => 'contact',
        'key' => 'id',
        'relation' =>
            array(
                array(
                    'foreign-key' => 'person_id',
                    'join-field' => 'id',
                    'operator' => '=',
                ),
            ),
        'repeat-control' => 'insert delete copy',
//        'query' =>
//            array(
//                array(
//                    'field' => 'datetime',
//                    'value' => '2005-01-01 00:00:00',
//                    'operator' => '>',
//                ),
//            ),
        'default-values' =>
            array(
                array(
                    'field' => 'datetime',
                    'value' => '2012-01-01 00:00:00',
                ),
            ),
    ),
    array(
        'name' => 'contact_way',
        'key' => 'id',
    ),
    array(
        'name' => 'cor_way_kindname',
        'key' => 'id',
        'relation' =>
            array(
                array(
                    'foreign-key' => 'way_id',
                    'join-field' => 'way',
                    'operator' => '=',
                ),
            ),
    ),
    array(
        'name' => 'history',
        'key' => 'id',
        'relation' =>
            array(
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
    ),
    array(
        'db-class' => 'PDO',
    ),
    false
);
