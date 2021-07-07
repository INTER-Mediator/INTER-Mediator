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
 * common settings for FileMaker_FX and PDO:
 */
$dbUser = 'web';
$dbPassword = 'password';

/* FileMaker_FX aware below:
 */
$dbServer = '127.0.0.1';
$dbPort = '80';
//$dbDataType = 'FMPro12';
$dbDatabase = 'TestDB';
$dbProtocol = 'HTTP';
//$certVerifying = false;

/* PDO awares below:
 */
$dbDSN = 'mysql:host=127.0.0.1;dbname=test_db;charset=utf8mb4';
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
    'Chrome' => '1+',
    'Edge' => '12+',
    // Edge/12.0(Microsoft Edge 20)
    //'Trident' => '7+',
    // Trident/7.0(Internet Explorer 11)
    'Firefox' => '2+',
    'Opera' => '1+',
    'Safari' => '4+',
    //'Safari'=>array('Mac'=>'4+','Win'=>'4+'), // Sample for dividing with OS
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
$defaultTimezone = 'Asia/Tokyo';
//$followingTimezones = true;

// The 'issuedhash' table for storing challenges of authentication can be use another database.
//$issuedHashDSN = 'sqlite:/var/db/im/sample.sq3';

//$emailAsAliasOfUserName = true;
//$passwordPolicy = "useAlphabet useNumber useUpper useLower usePunctuation length(10) notUserName";

//$passwordHash = '1';  // '2m' supports SHA-256 and Wrapping SHA-1 with SHA-256,
// '2' supports SHA-256 password hash only,
// No specification or other string support SHA-1, SHA-256, and wrapping.
//$alwaysGenSHA2 = true; // On the password changing, generate SHA-2 hash. The default is false.
//$migrateSHA1to2 = true;// If the login account relays on SHA-a, exchange it with 2m style SHA-2 hash. The default is false.

// A enrollment page and a password reset page are going to show on login panel.
//$resetPage = '...url...';
//$enrollPage = '...url...';

/*
 * If you want to specify the smtp server info, set them below.
$sendMailSMTP = array(
    'server' => 'string',
    'port' => 'integer',
    'username' => 'string',
    'password' => 'string',
);
*/
// Sending email features compatibility with INTER-Mediator v5 unless 'template-context' key isn't specified.
$sendMailCompatibilityMode = true;  // default is true

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

/* SAML Support, also activate the variable $ldapExpiringSeconds.
   Information about setting up an SAML Service Provider exists in the samples/saml-config directory. */
//$isSAML = true;
//$samlAuthSource = 'default-sp';
//$samlWithBuiltInAuth = true;
$samlAttrRules = ['username' => 'uid|0', 'realname' => 'eduPersonAffiliation|0'];
$samlAdditionalRules = ['username' => '(user02|user03)'];

/* Initial values for local context with their keys. */
//$valuesForLocalContext = array(
//    "pagetitle" => "INTER-Mediator samples",
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
//$accessControlAllowOrigin = "http://localhost:9000";

//$altThemePath = "/var/www/theme";    //Your original theme directory.
//$themeName = "blackbird";      //Default theme name.

// Server side locale for this application. This locale replaces the browser's accepting languages.
$appLocale = "ja_JP";   // Locale for application has to be specified the language_country code.
$appCurrency = "JP";    // Locale for currency has to be specified the country code.

// Port number and host name for service server
$notUseServiceServer = false;  // Default is FALSE!. If it sets to false, every features with Service Server don't work.
$activateClientService = true;  // Default is TRUE!!.
$serviceServerProtocol = "ws";  // The Service Server url components to connect from client.
$serviceServerHost = "";    // "" for public ip address.
$serviceServerPort = "11478";
$serviceServerKey = "";  // Path of Key file for wss protocol **** wss protocol doesn't work so far.
$serviceServerCert = ""; // Path of Cert file for wss protocol
$serviceServerCA = ""; // Path of CA file for wss protocol
$serviceServerConnect = "http://localhost"; // The Service Server host name to connect from the INTER-Mediator server
$stopSSEveryQuit = false;
$bootWithInstalledNode = false;
$preventSSAutoBoot = false;
$foreverLog = '/tmp/forever.log';

// Altering messages, overwrite and/or adding new messages. The first index is a language, and the second is the error number.
$messages['default'][1022] = "We don't support Internet Explorer. We'd like you to access by Edge or any other major browsers.";
$messages['ja'][1022] = "Internet Explorerは使用できません。Edgeあるいは他の一般的なブラウザをご利用ください。";
// These messages are for sample purpose but they are used for unit tests. If you modify them, you have to care about the test code.

// Slack posting token and channel. You must create the Slack App permitting 'chat:write:bot' and generate OAuth2 token.
$slackParameters = [
    "token" => 'xoxp-XXXXXXXXXXX-XXXXXXXXXXX-XXXXXXXXXXXX-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    "channel" => 'message-posting-test',
];

// Operation Log: the table named 'operationlog' is required.
// The schema of the table describes in dist-docs/sample_schema_*.txt files.
$accessLogLevel = false;    // false: No logging, 1: without data, 2: with data
$dbClassLog = $dbClass;
$dbDSNLog = $dbDSN;
$dbUserLog = $dbUser;
$dbPasswordLog = $dbPassword;
$recordingContexts = false; // false or no-definition: record all context, or an array of context names you want to record.
$dontRecordTheme = false;
$dontRecordChallenge = false;
$dontRecordDownload = false;
$dontRecordDownloadNoGet = false;

// S3 Support
$accessRegion = "ap-northeast-1"; // This means the Tokyo region.
// Set the code of the endpoint from https://docs.aws.amazon.com/general/latest/gr/rande.html
$rootBucket = "inter-mediator-developping";
$applyingACL = "bucket-owner-read";
// You can choose from two way, specifying key and secret or setting them into the profile file
// 'private|public-read|public-read-write|authenticated-read|aws-exec-read|bucket-owner-read|bucket-owner-full-control'
$s3AccessProfile = "im-develop";
$s3AccessKey = "AKIAXXXXXXXXXXXXXXXX";
$s3AccessSecret = "XXXXXXXXXXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXX";
// Profile can push any credentials out of codes. The profile is prior than key/secret.
// https://docs.aws.amazon.com/ja_jp/sdk-for-php/v3/developer-guide/guide_credentials_profiles.html
$s3urlCustomize = true; // The default value is TRUE.
// Replacing the string "https://" to "s3://" of the object url for working with the MediaAccess class.

// Importing CSV file. The field names list can place on the first line of original csv file.
//$import1stLine = 'num1 ,num2 ,num3 ,dt1 ,vc1 ,vc2 , vc3 ,text1 ,text2 ,'; // Field names list
//$importSkipLines = 3; // Skipping lines from the start of csv file.
//$importFormat = "TSV";  // or "TSV", the default is "CSV".
//$useReplace = true; // For MySQL, use REPLACE instead of INSERT
//$convert2Number = ['num1','num2','num3'];
//$convert2Date = ['dt1'];
//$convert2DateTime = [];
