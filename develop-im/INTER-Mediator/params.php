<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

/* DB_FileMaker_FX awares below:
 */
$dbServer = '127.0.0.1';
$dbPort = '80';
$dbUser = 'web';
$dbPassword = 'password';
$dbDataType = 'FMPro7';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';

/* DB_PDO awares below:
 */
$dbDSN = 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;';
$dbOption = array();
$dbUser = 'web';
$dbPassword = 'password';

/* Browser Compatibility Check:
 */
$browserCompatibility = array(
    'msie' => '7+',
    'FireFox' => '2+',
    'Safari' => '4+',
//    'Safari'=>array('Mac'=>'4+','Win'=>'4+'), // Sample for dividing with OS
    'Chrome' => '1+',
    'Opera' => '1+'
);



?>