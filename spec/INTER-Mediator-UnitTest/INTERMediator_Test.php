<?php
/**
 * INTERMediator_Test file
 */
use \PHPUnit\Framework\TestCase;
use \INTERMediator\DB\Proxy;
use \INTERMediator\Locale\IMLocale;
use \INTERMediator\IMUtil;
//require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
//spl_autoload_register('loadClass');

class INTERMediator_Test extends TestCase
{
    public function setUp()
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->db_proxy = new Proxy(true);
        $this->db_proxy->initialize(array(),
            array(
                'authentication' => array( // table only, for all operations
                    'user' => array('user1'), // Itemize permitted users
                    'group' => array('group2'), // Itemize permitted groups
                    'privilege' => array(), // Itemize permitted privileges
                    'user-table' => 'authuser', // Default value
                    'group-table' => 'authgroup',
                    'corresponding-table' => 'authcor',
                    'challenge-table' => 'issuedhash',
                    'authexpired' => '300', // Set as seconds.
                    'storing' => 'cookie-domainwide', // 'cookie'(default), 'cookie-domainwide', 'none'
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

        $imPath = IMUtil::pathToINTERMediator();
        include($imPath . '/params.php');

        $this->assertFalse(isset($issuedHashDSN), $testName);
        $this->assertFalse(isset($scriptPathPrefix), $testName);
        $this->assertFalse(isset($ldapServer), $testName);
        $this->assertFalse(isset($oAuthClientSecret), $testName);
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

    public function test_hex2bin_for53()
    {
        $testName = "Check hex2bin_for53 function in INTER-Mediator.php.";

        $hexString = "616263643132333441424344242526";
        $binaryString = "abcd1234ABCD$%&";

        $this->assertTrue(IMUtil::hex2bin_for53($hexString) === $binaryString, $testName);

        $version = explode('.', PHP_VERSION);
        if ($version[0] >= 5 && $version[1] >= 4) {
            $this->assertTrue(IMUtil::hex2bin_for53($hexString) === hex2bin($hexString), $testName);
        }
    }

    public function test_randomString()
    {
        $testName = "Check randamString function in INTER-Mediator.php.";
        $str = IMUtil::randomString(10);
        $this->assertTrue(is_string($str), $testName);
        $this->assertTrue(strlen($str) == 10, $testName);
        $str = IMUtil::randomString(100);
        $this->assertTrue(is_string($str), $testName);
        $this->assertTrue(strlen($str) == 100, $testName);
        $str = IMUtil::randomString(1000);
        $this->assertTrue(is_string($str), $testName);
        $this->assertTrue(strlen($str) == 1000, $testName);
        $str = IMUtil::randomString(0);
        $this->assertTrue(is_string($str), $testName);
        $this->assertTrue(strlen($str) == 0, $testName);
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
