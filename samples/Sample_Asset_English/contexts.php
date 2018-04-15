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
            'name' => 'asset',
            'view' => 'asset',
            'key' => 'asset_id',
            'repeat-control'=>'insert delete',
            'records' => 5,
            'paging' => true,
            'sort' => array(
                array('field' => 'purchase', 'direction' => 'ASC'),
            ),
            'default-values'=>array(
                array('field'=>'purchase', 'value'=> strftime('%Y-%m-%d')),
            )
        ),
        // Modification 2: Modification for contexts
        // - This context is copied from the above one, and modified.
        array(
            'name' => 'asseteffect',
            'view' => 'asset',
            'sort' => array(
                array('field' => 'purchase', 'direction' => 'ASC'),
            ),
            'query' => array(
                array('field' => 'discard', 'operator' => '<', 'value'=>'1990-1-1'),
            ),
            'repeat-control'=>'insert delete',
            'records' => 5,
            'paging' => true,
        ),
        // [END OF] Modification 2
        array(
            'name' => 'assetdetail',
            'view' => 'asset',
            'table' => 'asset',
            'records' => 1,
            'key' => 'asset_id',
        ),
        array(
            'name' => 'rent',
            'key' => 'rent_id',
            'sort' => array(
                array('field' => 'rentdate', 'direction' => 'ASC'),
            ),
            'relation' => array(
                array('foreign-key' => 'asset_id', 'join-field'=> 'asset_id', 'operator' => '='),
            ),
            'repeat-control'=>'insert delete',
            'default-values'=>array(
                array('field'=>'rentdate', 'value'=> strftime('%Y-%m-%d')),
            )
        ),
        array(
            'name' => 'staff',
        ),
        array(
            'name' => 'rentback',
            'table' => 'rent',
            'key' => 'rent_id',
            'query' => array(
                array('field' => 'backdate', 'operator' => 'IS NULL'),
            ),
        ),
        array(
            'name' => 'category',
        ),
        array(
            'name' => 'category-in-list',
            'view' => 'category',
            'relation' => array (
                array('foreign-key' => 'category_id', 'join-field'=> 'category', 'operator' => '=')
            )
        ),
    ),
    array(
        // Modification 3: Modification for a data in single field.
        // - This context is copied from the above one, and modified.
//        'formatter' => array(
//             array('field' => 'asset@purchase', 'converter-class' => 'MySQLDateTime', 'parameter'=>'%y/%m/%d'),
//            array('field' => 'asset@discard', 'converter-class' => 'MySQLDateTime'),
//        ),
        // [END OF] Modification 3
    ),
    array(
        'db-class' => 'PDO',
        // 'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_e_db;charset=utf8mb4',
        // 'option' => array(),
        // 'user' => 'web',
        // 'password' => 'password',
    ),
    false
);
