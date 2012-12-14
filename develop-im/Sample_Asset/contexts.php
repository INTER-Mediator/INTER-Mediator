<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2012 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
require_once ('../INTER-Mediator/INTER-Mediator.php');

IM_Entry(
    array(
        array(
            'name' => 'asset',
            'view' => 'asset',
            'repeat-control'=>'insert delete',
            'sort' => array(
                array('field' => 'purchase', 'direction' => 'ASC'),
            ),
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
    ),
    array(
        // Modification 3: Modification for a data in single field.
        // - This context is copied from the above one, and modified.
        'formatter' => array(
            array('field' => 'asset@purchase', 'converter-class' => 'MySQLDateTime', 'parameter'=>'%y/%m/%d'),
            array('field' => 'asset@discard', 'converter-class' => 'MySQLDateTime'),
        ),
        // [END OF] Modification 3
    ),
    array(
        'db-class' => 'PDO',
        'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;',
        'option' => array(),
        'user' => 'web',
        'password' => 'password',
    ),
    1
);

?>