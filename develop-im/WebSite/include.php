<?php
/*
 * INTER-Mediator Ver.0.63 Released 2011-05-29
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
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
IM_Entry(
    array(
        array(
            'name' => 'PageInfo',
            'view' => "ArticleInfo",
            'key' => 'id',
        ),
        array(
            'name' => 'Titles',
            'key' => 'id',
            'query' => array(
                array('field' => 'NotShow', 'value' => '1', 'operator' => '!='),
            ),
            'sort' => array(
                array('field' => 'Ordering', 'direction' => 'asc'),
            ),
        ),
        array(
            'name' => 'EachScript',
            'view' => 'Contents',
            'key' => 'id',
            'records' => 100,
            'sort' => array(
                array('field' => 'Ordering', 'direction' => 'asc'),
            ),
        ),
        array(
            'name' => 'News',
            'view' => 'Contents',
            'key' => 'id',
            'records' => 6,
            'query' => array(
                array('field' => 'Article_id', 'value' => '2', 'operator' => '='),
                //    array( 'field'=>'creditDate', 'value'=>date('m/d/Y', time()-84000*200), 'operator'=>'gt' ),
            ),
            'sort' => array(
                array('field' => 'creditDate', 'direction' => 'desc'),
            ),
        ),
        array(
            'name' => 'NewsPage',
            'view' => 'Contents',
            'key' => 'id',
            'query' => array(
                array('field' => 'Article_id', 'value' => '2', 'operator' => '='),
            ),
            'sort' => array(
                array('field' => 'creditDate', 'direction' => 'desc'),
            ),
        ),
    ),
    array(
        'formatter' => array(
            array(
                'field' => 'PageInfo@Modified',
                'converter-class' => 'MySQLDateTime',
                'parameter' => 'Y/m/d H:i'),
            array(
                'field' => 'NewsPage@Modified',
                'converter-class' => 'MySQLDateTime',
                'parameter' => 'Y/m/d H:i'),
        )
    ),
    array(
        'db-class' => 'WebSite_MySQL',
        'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=im_website;',
        'option' => array(),
        'user' => 'website',
        'password' => 'thirdparty422',
    ),
    false);

?>
