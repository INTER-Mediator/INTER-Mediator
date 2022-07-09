<?php
/**
 * INTERMediator_Test file
 */

use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;
use INTERMediator\Params;
use INTERMediator\Locale\IMLocale;
use PHPUnit\Framework\TestCase;

//require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
//spl_autoload_register('loadClass');

class INTERMediator_Test extends TestCase
{
    public function setUp(): void
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize(array(),
            array(
                'authentication' => array( // table only, for all operations
                    'user' => array('user1'), // Itemize permitted users
                    'group' => array('group2'), // Itemize permitted groups
                    'user-table' => 'authuser', // Default value
                    'group-table' => 'authgroup',
                    'corresponding-table' => 'authcor',
                    'challenge-table' => 'issuedhash',
                    'authexpired' => '300', // Set as seconds.
                    'storing' => 'credential', // 'cookie'(default), 'cookie-domainwide', 'none'
                ),
            ),
            array(
                'db-class' => 'PDO',
                'dsn' => 'mysql:dbname=test_db;host=127.0.0.1',
                'user' => 'web',
                'password' => 'password',
            ),
            false);
    }

    public function test_params()
    {
        $testName = "Check parameters in params.php.";

        [$issuedHashDSN, $scriptPathPrefix, $isSAML, $oAuthClientSecret] = Params::getParameterValue(
            ["issuedHashDSN", "scriptPathPrefix", "isSAML", "oAuthClientSecret"], false);

        $this->assertFalse($issuedHashDSN, $testName);
        $this->assertFalse($scriptPathPrefix, $testName);
        $this->assertFalse($isSAML, $testName);
        $this->assertFalse($oAuthClientSecret, $testName);
    }

    public function test_checkParamsFileDefault()
    {
        $params = Params::getParameterValue([
            "activateClientService", "serviceServerPort", "serviceServerHost", "serviceServerConnect",
            "stopSSEveryQuit", "bootWithInstalledNode", "preventSSAutoBoot", "notUseServiceServer", "foreverLog"
        ], [false, false, false, false, false, false, false, true, false]);
        $this->assertSame(9, count($params), "Params::getParameterValue should return any values.");

        $key = 'activateClientService';
        $assertValue = false;
        $assertStr = 'false';
        $message = "The variable {$key} in the params.php should be {$assertStr} for distribution.";
        $this->assertEquals($assertValue, Params::getParameterValue($key, false), $message);

        if (php_uname('n') === 'inter-mediator-server') {
            $key = 'serviceServerConnect';
            $assertValue = 'http://192.168.56.101';
            $assertStr = 'http://192.168.56.101';
            $message = "The variable {$key} in the params.php should be {$assertStr} for distribution.";
            $this->assertEquals($assertValue, Params::getParameterValue($key, 'http://192.168.56.101'), $message);
        } else if (getenv('CIRCLECI') !== 'true') {
            $key = 'serviceServerConnect';
            $assertValue = 'http://localhost';
            $assertStr = 'http://localhost';
            $message = "The variable {$key} in the params.php should be {$assertStr} for distribution.";
            $this->assertEquals($assertValue, Params::getParameterValue($key, 'http://localhost'), $message);
        }

        $key = 'stopSSEveryQuit';
        $assertValue = false;
        $assertStr = 'false';
        $message = "The variable {$key} in the params.php should be {$assertStr} for distribution.";
        $this->assertEquals($assertValue, Params::getParameterValue($key, false), $message);

        $key = 'bootWithInstalledNode';
        $assertValue = false;
        $assertStr = 'false';
        $message = "The variable {$key} in the params.php should be {$assertStr} for distribution.";
        $this->assertEquals($assertValue, Params::getParameterValue($key, false), $message);

        $key = 'preventSSAutoBoot';
        $assertValue = false;
        $assertStr = 'false';
        $message = "The variable {$key} in the params.php should be {$assertStr} for distribution.";
        $this->assertEquals($assertValue, Params::getParameterValue($key, false), $message);

        $key = 'notUseServiceServer';
        $assertValue = true;
        $assertStr = 'true';
        $message = "The variable {$key} in the params.php should be {$assertStr} for distribution.";
        $this->assertEquals($assertValue, Params::getParameterValue($key, false), $message);

//        $key = 'foreverLog';
//        $assertValue = false;
//        $assertStr = 'undefined';
//        $message = "The variable {$key} in the params.php should be {$assertStr} for distribution.";
//        $this->assertFalse(isset($params[$key]), $message);
    }

    private function checkNotDefined($key){
        $message = "The variable {$key} in the params.php should not be defined for distribution.";
        $value = Params::getParameterValue($key, 'Not defined');
        $this->assertEquals('Not defined', $value, $message);
    }

    public function test_checkParamsFileDefaultAWSS3(){
        $this->checkNotDefined('accessRegion');
        $this->checkNotDefined('rootBucket');
        $this->checkNotDefined('applyingACL');
        $this->checkNotDefined('s3AccessProfile');
        $this->checkNotDefined('s3AccessKey');
        $this->checkNotDefined('s3AccessSecret');
        $this->checkNotDefined('s3urlCustomize');
        $this->checkNotDefined('dropboxAppKey');
        $this->checkNotDefined('dropboxAppSecret');
        $this->checkNotDefined('dropboxRefreshToken');
        $this->checkNotDefined('dropboxAccessTokenPath');
        $this->checkNotDefined('rootInDropbox');
    }

    public function test_valueForJSInsert()
    {
        $expected = "\\'";
        $string = "'";
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));

        $expected = '\\"';
        $string = '"';
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));

        $expected = '\\/';
        $string = '/';
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));

        $expected = '\\x3e';
        $string = '>';
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));

        $expected = '\\x3c';
        $string = '<';
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));

        $expected = '\\n';
        $string = "\n";
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));

        $expected = '\\r';
        $string = "\r";
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));

        $expected = '\\n';
        $string = "\xe2\x80\xa8";
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));

        $expected = '\\n';
        $string = "\xe2\x80\xa9";
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));

        $expected = '\\\\';
        $string = '\\';
        $this->assertSame($expected, IMUtil::valueForJSInsert($string));
    }

    public function test_arrayToJS()
    {
        $testName = 'Check arrayToJS function in INTER-Mediator.php.';

        $ar = array('database' => 'TestDB', 'user' => 'web', 'password' => 'password');
        $prefix = '0';
        $resultString = "'0':{'database':'TestDB','user':'web','password':'password'}";

        $this->assertSame(IMUtil::arrayToJS($ar, $prefix), $resultString, $testName);
    }

    public function test_arrayToJSExcluding()
    {
        $testName = 'Check arrayToJSExcluding function in INTER-Mediator.php.';

        $ar = array('database' => 'TestDB', 'user' => 'web', 'password' => 'password');
        $prefix = '0';
        $exarray = array('password');
        $resultString = "'0':{'database':'TestDB','user':'web'}";
        $this->assertSame(IMUtil::arrayToJSExcluding($ar, $prefix, $exarray), $resultString, $testName);

        $ar = array('user' => 'web', 'password' => 'password', 'database' => 'TestDB');
        $prefix = '';
        $exarray = array('password');
        $resultString = "{'user':'web','database':'TestDB'}";
        $this->assertSame(IMUtil::arrayToJSExcluding($ar, $prefix, $exarray), $resultString, $testName);
    }

    public function test_getLocaleFromBrowser()
    {
        $testName = "Check getLocaleFromBrowser function in INTER-Mediator.php.";
        $headerStr = "ja";
        $locStr = IMLocale::getLocaleFromBrowser($headerStr);
        $this->assertTrue($locStr == "ja", $testName);
        $headerStr = "ja_JP";
        $locStr = IMLocale::getLocaleFromBrowser($headerStr);
        $this->assertTrue($locStr == "ja_JP", $testName);
        $headerStr = "en_US";
        $locStr = IMLocale::getLocaleFromBrowser($headerStr);
        $this->assertTrue($locStr == "en_US", $testName);
        $headerStr = "ja, en";
        $locStr = IMLocale::getLocaleFromBrowser($headerStr);
        $this->assertTrue($locStr == "ja", $testName);
        $headerStr = "en, ja";
        $locStr = IMLocale::getLocaleFromBrowser($headerStr);
        $this->assertTrue($locStr == "en", $testName);
        $headerStr = "ja; q=1.0, en; q=0.1";
        $locStr = IMLocale::getLocaleFromBrowser($headerStr);
        $this->assertTrue($locStr == "ja", $testName);
//        $headerStr = "ja; q=0.1, en; q=1.0";
//        $locStr = getLocaleFromBrowser($headerStr);
//        $this->assertTrue($locStr == "en", $testName);
    }
    /*
    function IM_Entry($datasource, $options, $dbspecification, $debug = false)
    function loadClass($className)
    function arrayToJS($ar, $prefix)
    function arrayToJSExcluding($ar, $prefix, $exarray)
    function arrayToQuery($ar, $prefix)
    function getRelativePath()
    function setLocaleAsBrowser($locType)
    */
}
