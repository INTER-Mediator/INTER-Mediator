<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: nii
 * Date: 11/05/22
 * Time: 15:02
 * To change this template use File | Settings | File Templates.
 */

require_once ('../INTER-Mediator/INTER-Mediator.php');

$tableDefs= array(
    array(
        'name' 	=> 'Titles',
        'key' 	=> 'id',
        'query'	=> array(
            array( 'field'=>'Article::NotShow', 'value'=>'1', 'operator'=>'neq' ),
        ),
        'sort'	=> array(
            array( 'field'=>'Article::Order', 'direction'=>'ascend' ),
        ),
    ),
    array(
        'name'	=> 'Contents',
        'key'	=> 'id',
        'records'	=>	100,
        'foreign-key' => 'Article_id',
        'join-field' => 'id',
        'sort'	=> array(
            array( 'field'=>'order', 'direction'=>'ascend' ),
        ),
    ),
    array(
        'name' 	=> 'News',
        'key' 	=> 'id',
        'query'	=> array(
            array( 'field'=>'Article_News::ContentKind_id', 'value'=>'1', 'operator'=>'eq' ),
            array( 'field'=>'creditDate', 'value'=>date('m/d/Y', time()-84000*200), 'operator'=>'gt' ),
        ),
        'sort'	=> array(
            array( 'field'=>'creditDate', 'direction'=>'descend' ),
        ),
    ),
);

$optionDefs= array();

$dbDefs = array(
    'db-class' => 'WebSite_FMSFX',
    'server' => 'msyk.net',
    'database' => 'WebSite',
    'user' => 'web',
    'password' => 'webpassword',
    'port' => '80',
    'protocol' => 'HTTP',
    'datatype' => 'FMPro7'
);

IM_Entry( $tableDefs, $optionDefs, $dbDefs, true );

?>