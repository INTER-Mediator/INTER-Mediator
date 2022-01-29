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

/* Database connection (PDO or FileMaker_FX)
 * ===================
 */
$dbClass = 'PDO';

/* Common settings for FileMaker_FX and PDO:
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
 * ===================
 * Please change the value of $webServerName and $generatedPrivateKey.
 * FQDN or domain name of your web server for protecting CSRF
 * Example:
 *  $webServerName = array('www.inter-mediator.com');
 *  $webServerName = array('inter-mediator.com', 'example.jp');
 */
$webServerName = array('');

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

/* Append the Access-Control-Allow-Origin header
 * This header will be appended other server url than the origin.
 */
//$accessControlAllowOrigin = "http://localhost:9000";

/* Browser Compatibility Check:
 * ===================
 * The list of User Agents, it's a wonderful site!
 * http://www.openspc2.org/userAgent/
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

/* Messaging Settings
 * =================== */
// If you want to specify the smtp server info, set them below.
//$sendMailSMTP = array(
//    'server' => 'anyserver',
//    'port' => 587,
//    'username' => 'username',
//    'password' => 'password',
//);
// $waitAfterMail = 20;  // Wait after send email with smtp server. Unit is Millisecond.

// Sending email features compatibility with INTER-Mediator v5 unless 'template-context' key isn't specified.
$sendMailCompatibilityMode = true;  // default is true

// Slack posting token and channel. You must create the Slack App permitting 'chat:write:bot' and generate OAuth2 token.
$slackParameters = [
    "token" => 'xoxp-XXXXXXXXXXX-XXXXXXXXXXX-XXXXXXXXXXXX-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
    "channel" => 'message-posting-test',
];

/* Authorization
 * =================== */
//$passwordHash = '1';  // '2m' supports SHA-256 and Wrapping SHA-1 with SHA-256,
// '2' supports SHA-256 password hash only,
// No specification or other string support SHA-1, SHA-256, and wrapping.
//$alwaysGenSHA2 = true; // On the password changing, generate SHA-2 hash. The default is false.
//$migrateSHA1to2 = true;// If the login account relays on SHA-a, exchange it with 2m style SHA-2 hash. The default is false.

// The 'issuedhash' table for storing challenges of authentication can be use another database.
//$issuedHashDSN = 'sqlite:/var/db/im/sample.sq3';

//$emailAsAliasOfUserName = true;
//$passwordPolicy = "useAlphabet useNumber useUpper useLower usePunctuation length(10) notUserName";

// A enrollment page and a password reset page are going to show on login panel.
//$resetPage = '...url...';
//$enrollPage = '...url...';

//$suppressDefaultValuesOnCopy = false; // If you don't want to set default values on copying records, set this true
//$suppressDefaultValuesOnCopyAssoc = false; // If you don't want to set default values on copying records of the associated records, set this true
//$suppressAuthTargetFillingOnCreate = false; // If you don' want to set the target field of authentication on carete operation, set this true.

/* OAuth Support */
// $oAuthProvider = 'Google';
// $oAuthClientID = '';
// $oAuthClientSecret = '';
// $oAuthRedirect = 'http://localhost:7001/Auth_Support/OAuthCatcher.php';

/* SAML Support
   Information about setting up an SAML Service Provider exists in the samples/saml-config directory. */
//$isSAML = true;
//$samlAuthSource = 'default-sp';
//$samlExpiringSeconds = 1800;
//$samlWithBuiltInAuth = true;
//$samlAttrRules = ['username' => 'uid|0', 'realname' => 'eduPersonAffiliation|0'];
//$samlAdditionalRules = ['username' => '(user02|user03)'];
//$samlExpiringSeconds = 1800;

/* Service Server Behavior
 * ===================
 * Port number and host name for service server */
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

/* Operation Log
 * ===================
 * the table named 'operationlog' is required.
 * The schema of the table describes in dist-docs/sample_schema_*.txt files. */
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

/* S3 Support
 * =================== */
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

/* Importing CSV file.
 * ===================
 The field names list can place on the first line of original csv file. */
//$import1stLine = 'num1 ,num2 ,num3 ,dt1 ,vc1 ,vc2 , vc3 ,text1 ,text2 ,'; // Field names list
//$importSkipLines = 3; // Skipping lines from the start of csv file.
//$importFormat = "TSV";  // or "TSV", the default is "CSV".
//$useReplace = true; // For MySQL, use REPLACE instead of INSERT
//$convert2Number = ['num1','num2','num3'];
//$convert2Date = ['dt1'];
//$convert2DateTime = [];

/* UI Matters
 * =================== */
/* The id attribute for the "Non support browser" message.
 * The default value is "nonsupportmessage." */
//$nonSupportMessageId = "nonsupport";

//$altThemePath = "/var/www/theme";    //Your original theme directory.
//$themeName = "blackbird";      //Default theme name.

// Altering messages, overwrite and/or adding new messages. The first index is a language, and the second is the error number.
$messages['default'][1022] = "We don't support Internet Explorer. We'd like you to access by Edge or any other major browsers.";
$messages['ja'][1022] = "Internet Explorerは使用できません。Edgeあるいは他の一般的なブラウザをご利用ください。";
// These messages are for sample purpose but they are used for unit tests. If you modify them, you have to care about the test code.

/* Initial values for local context with their keys. */
//$valuesForLocalContext = array(
//    "pagetitle" => "INTER-Mediator samples",
//    "copyright" => "INTER-Mediator Directive Committee",
//);

/* Customizing Server Behavior
 * =================== */
// If you don't set the default timezone in the php.ini file,
//      activate the line below and specify suitable timezone name.
$defaultTimezone = 'Asia/Tokyo';
//$followingTimezones = true;

// Server side locale for this application. This locale replaces the browser's accepting languages.
$appLocale = "ja_JP";   // Locale for application has to be specified the language_country code.
$appCurrency = "JP";    // Locale for currency has to be specified the country code.

/* Customize the path generation in uploading file
 *
 * The value "assjis" and "asucs4" are supported. This is not convert path string from key
 * field and value, but the string encoding is convert to sjis or ucs-4 and back to utf-8.
 * As the default, the string is going to be encoded with the urlencode function.
 */
$uploadFilePathMode = "";

/* Other settings
 * =================== */
/* This statement set debug to false forcely. */
$prohibitDebugMode = false;

// The DOCUMENT_ROOT isn't full path on a rental server, this variable
// is set before the result of DOCUMENT_ROOT.
//$documentRootPrefix = "/usr/local/chroot";

// in case of $_SERVER['SCRIPT_NAME'] didn't return the valid path.
// These are added before/after the path.
//$scriptPathPrefix = "";
//$scriptPathSuffix = "";

// INTER-Mediator client should call the definition file to work fine.
// Usually $_SERVER['SCRIPT_NAME'] is the url to request from client.
// In case of using INTER-Mediator with other frameworks, you might specify any special URL to call.
// So you can set the another url to the $callURL variables and it can be replaced with $_SERVER['SCRIPT_NAME'].
//$callURL = "http://yourdomai/your/path/to/definition-file.php"
