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
            'name' => 'Article',
            'key' => 'id',
        ),
        array(
            'name' => 'ContentKind',
            'key' => 'id',
        ),
        array(
            'name' => 'TitlesOfArticle',
            'view' => 'Titles',
            'table' => 'Titles',
            'key' => 'id',
            'repeat-control' => 'insert',
            'relation' => array(
                array('foreign-key' => 'Article_id', 'join-field' => 'id', 'operator' => '='),
            ),
            'sort' => array(
                array('field' => 'Ordering', 'direction' => 'ASC'),
            ),
            'authentication' => array(
                'update' => array('target' => 'table'),
                'new' => array('target' => 'table')
            )
        ),
        array(
            'name' => 'ContentsOfArticle',
            'view' => 'Contents',
            'table' => 'Contents',
            'key' => 'id',
            'repeat-control' => 'insert',
            'relation' => array(
                array('foreign-key' => 'Article_id', 'join-field' => 'id', 'operator' => '='),
            ),
            'sort' => array(
                array('field' => 'Ordering', 'direction' => 'ASC'),
            ),
            'authentication' => array(
                'update' => array('target' => 'table'),
                'new' => array('target' => 'table')
            )
        ),
    ),
    array(
        'authentication' => array(
            'storing' => 'cookie'
        )
    ),
    array(
        'db-class' => 'PDO',
        'dsn' => 'mysql:unix_socket=/tmp/mysql.sock;dbname=im_website;',
        'option' => array(),
        'user' => 'website',
        'password' => 'thirdparty422',
    ),
false);

?>
