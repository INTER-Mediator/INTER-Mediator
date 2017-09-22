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

/*
 * database connection (PDO or FileMaker_FX)
 */
$dbClass = 'PDO';

/*
 * common settings for DB_FileMaker_FX and DB_PDO:
 */
$dbUser = 'web';
$dbPassword = 'password';

/* DB_FileMaker_FX aware below:
 */
$dbServer = '127.0.0.1';
$dbPort = '80';
//$dbDataType = 'FMPro12';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';

/* DB_PDO awares below:
 */
$dbDSN = 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;charset=utf8';
//$dbDSN = 'mysql:unix_socket=/tmp/mysql.sock;dbname=test_db;charset=utf8mb4';
$dbOption = array();


/* Security
 * Please change the value of $webServerName and $generatedPrivateKey.
 */
/* FQDN or domain name of your web server for protecting CSRF
 * Example:
 *  $webServerName = array('www.inter-mediator.com');
 *  $webServerName = array('inter-mediator.com', 'example.jp');
 */
$webServerName = array('');

/*
Command to generate the following RSA key:
$ openssl genrsa -out gen.key 2048
 */
$passPhrase = '';
$generatedPrivateKey = <<<EOL
-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEAyTFuj/i52z0pXsoa6HNTUFcmWBcaG5DodB5ac6WAKBxn4G/j
knKwIBjRluCjIcRdFk6m91ChSOoDvW3p3rk2UFMIfq9e6ojhsWO3AFrHXOVHt+P/
QWnI2KUtUmxO0jw9hbqK/Hl4IbWc8aGnxP/uGOmLJnLSP3wEtahXXaVSJrGTPZuk
pbzarqzS3waraUYP+b2aMGLL/BbPnc7xCF13TtkVdcVdjyQtofn7hM3Kd8fOFFOg
ix+JYOrf5jMG0aTs9NxwcHXb6DMwt/L30s+eIMI/81/sthT5TD7kEMUudz+yomnM
G6C+bFYgykcFlYwEeUD//5naitc3ZNYXEpyzWwIDAQABAoIBAQCWmpwqxYNKrBPl
0uAllP6Oq04WruRqMiTvlzEaVI8Ed48CoH73x0Y0IJ/zkyBKTJVp92Jgy0iQLiyy
hi6E/Ju9sQow2tHwOprHkN8SMuH9ldwDuXX/31HramnswwqVsWZUTnlv2PWmNi7P
abUOcI4os9nn5BeiUhGsceFERlaigwFJ1eWI7M+XfIh9YfLx8ERaZYi9g6MDNZ1k
TEirV/rGbts4K62IJ+UGiW5UYW4qfPvOdmsOKcr0IMmM4hu5/ZlGg/xDXrLRCQzj
Pt0+dJ1UZyb5PuhlUyjqF0vBBr/hLQhkAUPLE1CyXCgbDWrXEJkoT7DVILskZHo0
1+DmgbsxAoGBAPp8upw+vsY2yzxZep0GtXqqXQOVQ8f+XPeDK9kE5TgNPofCr8+3
cqerbwGPBRJueYnYNANNc0aVgNnX+rkUYEJMlrkeEqPPpNvEzOd/l067EprGgA5s
HZkMLJsxLTrJEuj5NczMtsJia6ufWD8l4XTvB6WKNSDCf4/sdZCFF0JVAoGBAM2e
+YU7AsC70q9BxPR4sc0vLk8kEY9eKuP7PCb991qpIxD/VFpRWy9znO7t9+EQKsJ1
U1HdU/YTSuSTmg9z+a7s4En1tI+ryUHmwv8run9r11lx7yuXgJhx6mg5Lc6BaFIN
QsbQIm/7HL0p5ugPfDiObPIxQUgR1s+Xl7HnkK7vAoGAaHzjMw4Rcomk2bXRqfME
fPjX+Aipz6FRkoYLImoiW/FaZjNWN2Wk1EB0+8d3LCsdU9z2RXJnZcgziavIkK/p
P37HWM0spVyWvn4no2Hb8iGjLyEiheGfrxoe+VXYMi9yTfC2+oliq0927o53t0/L
7oVPQUSXyOSZZaYTnIeIHkkCgYBYr+f5ohE25gwiUWDM/T3bPS1hLzJvvvMK8DLq
soG81dTtIOPWLN8CoYAfwf43UczPoOE2Hxt2uK2F13AMmD4qR7sZy2N80GB3Dzwt
6UOAcBgrWSwKhkcN+ZxcJcVvG3vOYC/cJquj1xB3OpqAnyU6E5xD/iClICSh10Wz
kyhhewKBgC1bAmPbOHoaNecuHTSO+pe5s29KagojaWMFsH1+Zs5HiVBmLmO9UdG9
UeplZBKmxW3+wQ5gVWIguqisfvi9/m07Z/3+uwCLSryHU6Kgg7Md9ezU9Obx+jxp
cmyuR8KhUNJ6zf23TUgQE6Dt1EAHB+uPIkWiH1Yv1BFghe4M4Ijk
-----END RSA PRIVATE KEY-----
EOL;


/* Browser Compatibility Check:
 */
$browserCompatibility = array(
    'Edge' => '12+',
    // Edge/12.0(Microsoft Edge 20)
    'Trident' => '4+',
    // Trident/4.0(Internet Explorer 8)
    // Trident/5.0(Internet Explorer 9)
    // Trident/6.0(Internet Explorer 10)
    // Trident/7.0(Internet Explorer 11)
    // Before IE 7, 'Trident' token doesn't exist.
    'Chrome' => '1+',
    'Firefox' => '2+',
    'Safari' => '4+',
    //'Safari'=>array('Mac'=>'4+','Win'=>'4+'), // Sample for dividing with OS
    'Opera' => '1+',
    'WebKit' => '1+',
);

/*
 * The id attribute for the Non support browser message.
 * The default value is "nonsupportmessage."
 */
//$nonSupportMessageId = "nonsupport";

/*
 * The list of User Agents, it's a wonderful site!
 * http://www.openspc2.org/userAgent/
 */

/* This statement set debug to false forcely. */
$prohibitDebugMode = false;


// The DOCUMENT_ROOT isn't full path on a rental server, this variable
// is set before the result of DOCUMENT_ROOT.
//$documentRootPrefix = "/usr/local/chroot";

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
//$passwordPolicy = "useAlphabet useNumber useUpper useLower usePunctuation length(10) notUserName";

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

/* LDAP Support */
// $ldapServer = "ldap://homeserver.msyk.net";
// $ldapPort = 389;
// $ldapBase = "dc=homeserver,dc=msyk,dc=net";
// $ldapContainer = "cn=users";
// $ldapAccountKey = "uid";
//$ldapExpiringSeconds = 1800;

/* OAuth Support */
// $oAuthProvider = 'Google';
// $oAuthClientID = '';
// $oAuthClientSecret = '';
// $oAuthRedirect = 'http://localhost:7001/Auth_Support/OAuthCatcher.php';

/* Initial values for local context with their keys. */
//$valuesForLocalContext = array(
//    "pagetitle" => "INTER-Mediator Samples",
//    "copyright" => "INTER-Mediator Directive Committee",
//);

/* Customize the X-Frame-Options header
 *
 * Possible values are "SAMEORIGIN", "DENY", "ALLOW-FROM <uri>" or ""
 * For "" string, the X-Frame-Options header won't be included in headers.
 * If you don't specify the $xFrameOptions variable, the header will be included
 * with value "SAMEORIGIN".
 */
//$xFrameOptions = "SAMEORIGIN";

/* Customize the Content-Security-Policy header
 *
 * The Content-Security-Policy header contains with the value of variable $contentSecurityPolicy.
 * If this variable isn't specified or "", the Content-Security-Policy header doesn't contains.
 * See below about Content-Security-Policy header.
 * https://developer.mozilla.org/ja/docs/Web/Security/CSP/Using_Content_Security_Policy
 */
//$contentSecurityPolicy = "";

/* Customize the path generation in uploading file
 *
 * The value "assjis" and "asucs4" are supported. This is not convert path string from key
 * field and value, but the string encoding is convert to sjis or ucs-4 and back to utf-8.
 * As the default, the string is going to be encoded with the urlencode function.
 */
$uploadFilePathMode = "";

/* Append the Access-Control-Allow-Origin header
 *
 * This header will be appended other server url than the origin.
 */
//$accessControlAllowOrigin = "https://server.msyk.net";

//$altThemePath = "/var/www/thmeme";    //Your original thmeme directory.
//$themeName = "blackbird";      //Default theme name.

// Server side locale for this application. This locale replaces the browser's accepting languages.
$appLocale = "ja_JP";   // Locale for application has to be specified the langunage_country code.
$appCurrency = "JP";    // Locale for currency has to be specified the country code.

