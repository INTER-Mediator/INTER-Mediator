<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
*
*   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
*   INTER-Mediator is supplied under MIT License.
*/

/*
 * common settings for DB_FileMaker_FX and DB_PDO:
 */
$dbUser = 'web';
$dbPassword = 'password';

/* DB_FileMaker_FX aware below:
 */
$dbServer = '127.0.0.1';
$dbPort = '80';
$dbDataType = 'FMPro12';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';

/* DB_PDO awares below:
 */
$dbDSN = 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;charset=utf8';
//$dbDSN = 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;charset=utf8mb4';
$dbOption = array();

/* Browser Compatibility Check:
 */
$browserCompatibility = array(
    'Chrome' => '1+',
    'FireFox' => '2+',
    'msie' => '8+',
    'Opera' => '1+',
    'Safari' => '4+',
//    'Safari'=>array('Mac'=>'4+','Win'=>'4+'), // Sample for dividing with OS
    'Trident' => '4+',
    // Trident/4.0(Internet Explorer 8)
    // Trident/5.0(Internet Explorer 9)
    // Trident/6.0(Internet Explorer 10)
    // Trident/7.0(Internet Explorer 11)
    // Before IE 7, 'Trident' token doesn't exist.
);
/*
 * The list of User Agents, it's a wonderful site!
 * http://www.openspc2.org/userAgent/
 */

/* This statement set debug to false forcely. */
$prohibitDebugMode = false;
$prohibitSetCondition = true;
$prohibitSetOrder = true;
$prohibitSetWriteCriticalFields = true;
//$prohibitIgnoreCondition = true;

/*
Command to generate the following RSA key:
$ openssl genrsa -out gen.key 512

*/
$passPhrase = '';
$generatedPrivateKey = <<<EOL
-----BEGIN RSA PRIVATE KEY-----
MIIBOwIBAAJBAKihibtt92M6A/z49CqNcWugBd3sPrW3HF8TtKANZd1EWQ/agZ65
H2/NdL8H6zCgmKpYFTqFGwlYrnWrsbD1UxcCAwEAAQJAWX5pl1Q0D7Axf6csBg1M
3V5u3qlLWqsUXo0ZtjuGDRgk5FsJOA9bkxfpJspbr2CFkodpBuBCBYpOTQhLUc2H
MQIhAN1stwI2BIiSBNbDx2YiW5IVTEh/gTEXxOCazRDNWPQJAiEAwvZvqIQLexer
TnKj7q+Zcv4G2XgbkhtaLH/ELiA/Fh8CIQDGIC3M86qwzP85cCrub5XCK/567GQc
GmmWk80j2KpciQIhAI/ybFa7x85Gl5EAS9F7jYy9ykjeyVyDHX0liK+V1355AiAG
jU6zr1wG9awuXj8j5x37eFXnfD/p92GpteyHuIDpog==
-----END RSA PRIVATE KEY-----
EOL;

//$httpAccounts = array('user'=>'testtest');
//$httpRedirectURL = "http://10.0.1.226/im/Sample_products/products_MySQL.html";

// in case of $_SERVER['SCRIPT_NAME'] didn't return the valid path.
// These are added before/after the path.
//$scriptPathPrefix = "";
//$scriptPathSuffix = "";

// INTER-Mediator client should call the definition file to work fine.
// Usually $_SERVER['SCRIPT_NAME'] is the url to request from client.
// In case of using INTER-Mediator with other frameworks, you might specify any special URL to call.
// So you can set the another url to the $callURL variables and it can be replaced with $_SERVER['SCRIPT_NAME'].
//$callURL = "http://yourdomai/your/path/to/definition-file.php"

// If you don't set the default timezone in the php.ini file,
//      activate the line below and specify suitable timezone name.
//$defaultTimezone = 'Asia/Tokyo';

// The 'issuedhash' table for storing challenges of authentication can be use another database.
//$issuedHashDSN = 'sqlite:/var/db/im/sample.sq3';

//$emailAsAliasOfUserName = true;

$customLoginPanel = '';

/*
 * If you want to specify the smtp server info, set them below.
$sendMailSMTP = array(
    'server' => 'string',
    'port' => 'integer',
    'username' => 'string',
    'password' => 'string',
);
*/

/*
 * If you want to specify the Pusher information, set them below.
$pusherParameters = array(
    'app_id' => '',
    'key' => '',
    'secret' => '',
);
*/

// This locale is going to be used to detect the decimal mark etc. formatting infos.
//$appLocale = "ja_JP";
