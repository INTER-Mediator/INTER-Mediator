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

$fpath = 'INTER-Mediator/INTER-Mediator.php';
if (file_exists($fpath)) {
    require_once ($fpath);
} else {
    $fpath = "../{$fpath}";
    if (file_exists($fpath)) {
        require_once ($fpath);
    }
}

header('Content-Type: text/javascript');

$tableDefs = array(
    array(
        'name' => 'PageInfo',
        'view' => "Article",
        'key' => 'id',
    ),
    array(
        'name' => 'Titles',
        'key' => 'id',
        'query' => array(
            array('field' => 'Article::NotShow', 'value' => '1', 'operator' => 'neq'),
        ),
        'sort' => array(
            array('field' => 'Article::Order', 'direction' => 'ascend'),
        ),
    ),
    array(
        'name' => 'Contents',
        'key' => 'id',
        'records' => 100,
        'foreign-key' => 'Article_id',
        'join-field' => 'id',
        'sort' => array(
            array('field' => 'order', 'direction' => 'ascend'),
        ),
    ),
    array(
        'name' => 'News',
        'key' => 'id',
        'records' => 6,
        'query' => array(
            array('field' => 'Article_News::ContentKind_id', 'value' => '1', 'operator' => 'eq'),
            //    array( 'field'=>'creditDate', 'value'=>date('m/d/Y', time()-84000*200), 'operator'=>'gt' ),
        ),
        'sort' => array(
            array('field' => 'creditDate', 'direction' => 'descend'),
        ),
    ),
    array(
        'name' => 'NewsPage',
        'view' => 'News',
        'key' => 'id',
        'query' => array(
            array('field' => 'Article_News::ContentKind_id', 'value' => '1', 'operator' => 'eq'),
        ),
        'sort' => array(
            array('field' => 'creditDate', 'direction' => 'descend'),
        ),
    ),
);

$optionDefs = array(
    'formatter' => array(
        array('field' => 'PageInfo@updateDate', 'converter-class' => 'FMDateTime'),
        array('field' => 'NewsPage@updateDate', 'converter-class' => 'FMDateTime'),
    )
);

$dbDefs = array(
    'db-class' => 'WebSite_FMSFX',
    'database' => 'WebSite',
    'user' => 'web',
    'password' => 'webpassword',
    'port' => '80',
    'protocol' => 'HTTP',
    'datatype' => 'FMPro7',

    'server' => 'db00050.worldcloud.com',
);

IM_Entry($tableDefs, $optionDefs, $dbDefs, false);

?>
