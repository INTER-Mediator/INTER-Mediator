<?php
/**
 * IMUtil_Test file
 */

use INTERMediator\Params;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use INTERMediator\IMUtil;

class IMUtil_Test extends TestCase
{
    private IMUtil $util;

    public function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
        $this->util = new IMUtil();
    }

    public function test_RelativePath()
    {
        $fromPath = '/samples/Practices/search_page1.html';
        $toPath = '/samples/Practices/search_def.php';
        $p = IMUtil::relativePath($fromPath, $toPath);
        $this->assertEquals('search_def.php', $p);

        $fromPath = '/samples/Practices/search_page1.html';
        $toPath = '/index.php';
        $p = IMUtil::relativePath($fromPath, $toPath);
        $this->assertEquals('../../index.php', $p);

        $fromPath = '/samples/Practices/search_page1.html';
        $toPath = '/lib/src/INTER-Mediator/index.php';
        $p = IMUtil::relativePath($fromPath, $toPath);
        $this->assertEquals('../../lib/src/INTER-Mediator/index.php', $p);

        $fromPath = '/samples/Practices/search_page1.html';
        $toPath = '/samples/Practices/dir/search_def.php';
        $p = IMUtil::relativePath($fromPath, $toPath);
        $this->assertEquals('dir/search_def.php', $p);

        $fromPath = '/samples/Practices/search_page1.html';
        $toPath = '/samples/Practices/dir/dir/search_def.php';
        $p = IMUtil::relativePath($fromPath, $toPath);
        $this->assertEquals('dir/dir/search_def.php', $p);

        $fromPath = '/samples/Practices/page/search_page1.html';
        $toPath = '/samples/Practices/dir/search_def.php';
        $p = IMUtil::relativePath($fromPath, $toPath);
        $this->assertEquals('../dir/search_def.php', $p);

        $fromPath = '/samples/Practices/page/search_page1.html';
        $toPath = '/samples/Practices/dir/dir/search_def.php';
        $p = IMUtil::relativePath($fromPath, $toPath);
        $this->assertEquals('../dir/dir/search_def.php', $p);
    }

    public function test_phpVersion()
    {
        $version = IMUtil::phpVersion("5.4.45");
        $this->assertLessThan(6, $version);

        $version = IMUtil::phpVersion("5.5.38");
        $this->assertLessThan(6, $version);

        $version = IMUtil::phpVersion("5.6.39");
        $this->assertLessThan(6, $version);

        $version = IMUtil::phpVersion("5.6.40");
        $this->assertLessThan(6, $version);

        $version = IMUtil::phpVersion("7.0.0");
        $this->assertGreaterThanOrEqual(7, $version);

        $version = IMUtil::phpVersion("7.0.1");
        $this->assertGreaterThan(7, $version);

        $version = IMUtil::phpVersion("7.1.0");
        $this->assertGreaterThan(7, $version);
    }

    public function test_removeNull()
    {
        $str = IMUtil::removeNull("INTER\x00-Mediator");
        $this->assertEquals("INTER-Mediator", $str);
    }

    public function test_getParameterValue()
    {
        $webServerName = Params::getParameterValue('webServerName', '');
        if (php_uname('n') === 'inter-mediator-server') {
            $this->assertEquals('192.168.56.101', $webServerName);
        } else {
            $this->assertEquals(true, is_array($webServerName));
        }
    }

    public function test_protectCSRF()
    {
        $result = $this->util->protectCSRF();
        $this->assertFalse($result);

        $_SERVER = array();
        $_SERVER['HTTP_HOST'] = '192.168.56.101';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['HTTP_X_FROM'] = 'http://192.168.56.101/';
        $result = $this->util->protectCSRF();
        $this->assertTrue($result);

        $_SERVER['HTTP_ORIGIN'] = 'https://192.168.56.101/';
        $result = $this->util->protectCSRF();
        $this->assertFalse($result);

        $_SERVER['HTTP_ORIGIN'] = 'http://192.168.56.101/';
        $result = $this->util->protectCSRF();
        $this->assertTrue($result);

        $_SERVER['HTTP_ORIGIN'] = 'http://192.168.56.101:80/';
        $result = $this->util->protectCSRF();
        $this->assertTrue($result);
    }

    public function test_checkHost()
    {
        $reflectionMethod = new ReflectionMethod('\INTERMediator\IMUtil', 'checkHost');
        $reflectionMethod->setAccessible(true);

        $result = $reflectionMethod->invokeArgs($this->util, array('www.inter-mediator.com', 'www.inter-mediator.com'));
        $this->assertTrue($result);

        $result = $reflectionMethod->invokeArgs($this->util, array('www.inter-mediator.com', 'inter-mediator.com'));
        $this->assertTrue($result);

        $result = $reflectionMethod->invokeArgs($this->util, array('WWW.inter-mediator.com', 'inter-mediator.com'));
        $this->assertTrue($result);

        $result = $reflectionMethod->invokeArgs($this->util, array('inter-mediator.com', 'inter-mediator.com'));
        $this->assertTrue($result);

        $_SERVER = array();
        $_SERVER['SERVER_ADDR'] = '192.168.56.101';
        $result = $reflectionMethod->invokeArgs($this->util, array('192.168.56.101', $_SERVER['SERVER_ADDR']));
        $this->assertTrue($result);

        $result = $reflectionMethod->invokeArgs($this->util, array('www.inter-mediator.com', ''));
        $this->assertFalse($result);

        $result = $reflectionMethod->invokeArgs($this->util, array('www.inter-mediator.com', 'ww.inter-mediator.com'));
        $this->assertFalse($result);

        $_SERVER = array();
        $result = $reflectionMethod->invokeArgs($this->util, array('192.168.56.101', '56.101'));
        $this->assertFalse($result);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_outputSecurityHeaders()
    {
        $params = array();

        if (function_exists('xdebug_get_headers') && getenv('GITHUB_ACTIONS') !== 'true' && getenv('XDEBUG_MODE') !== false && getenv('XDEBUG_MODE') !== 'off') {
            ob_start();
            $this->util->outputSecurityHeaders();
            $headers = xdebug_get_headers();
            header_remove();
            ob_end_flush();
            ob_clean();
            $this->assertContains('X-Frame-Options: SAMEORIGIN', $headers);
            $this->assertNotContains('Content-Security-Policy: ', $headers);
            $this->assertContains('X-XSS-Protection: 1; mode=block', $headers);

            $params['xFrameOptions'] = '';
            $params['contentSecurityPolicy'] = '';
            $params['accessControlAllowOrigin'] = '';
            ob_start();
            $this->util->outputSecurityHeaders($params);
            $headers = xdebug_get_headers();
            header_remove();
            ob_end_flush();
            ob_clean();
            $this->assertContains('X-Frame-Options: SAMEORIGIN', $headers);
            $this->assertNotContains('Content-Security-Policy:', $headers);
            $this->assertNotContains('Access-Control-Allow-Origin:', $headers);

            $params["xFrameOptions"] = 'DENY';
            $params["contentSecurityPolicy"] = '';
            $params['accessControlAllowOrigin'] = '';
            ob_start();
            $this->util->outputSecurityHeaders($params);
            $headers = xdebug_get_headers();
            header_remove();
            ob_end_flush();
            ob_clean();
            $this->assertContains('X-Frame-Options: DENY', $headers);
            $this->assertNotContains('Content-Security-Policy:', $headers);
            $this->assertNotContains('Access-Control-Allow-Origin:', $headers);

            $params["xFrameOptions"] = 'ALLOW-FROM http://inter-mediator.com/';
            $params["contentSecurityPolicy"] = 'frame-ancestors https://inter-mediator.com http://inter-mediator.info';
            $params['accessControlAllowOrigin'] = '';
            ob_start();
            $this->util->outputSecurityHeaders($params);
            $headers = xdebug_get_headers();
            header_remove();
            ob_end_flush();
            ob_clean();
            $this->assertContains('X-Frame-Options: ALLOW-FROM http://inter-mediator.com/', $headers);
            $this->assertContains('Content-Security-Policy: frame-ancestors https://inter-mediator.com http://inter-mediator.info', $headers);
            $this->assertNotContains('Access-Control-Allow-Origin:', $headers);

            $params["xFrameOptions"] = "ALLOW-FROM\n http://inter-mediator.com/";
            $params["contentSecurityPolicy"] = "frame-ancestors\n https://inter-mediator.com http://inter-mediator.info";
            $params['accessControlAllowOrigin'] = '*';
            ob_start();
            $this->util->outputSecurityHeaders($params);
            $headers = xdebug_get_headers();
            header_remove();
            ob_end_flush();
            ob_clean();
            $this->assertContains('X-Frame-Options: ALLOW-FROM http://inter-mediator.com/', $headers);
            $this->assertContains('Content-Security-Policy: frame-ancestors https://inter-mediator.com http://inter-mediator.info', $headers);
            $this->assertContains('Access-Control-Allow-Origin: *', $headers);
        } else {
            /** @phpstan-ignore-next-line function.alreadyNarrowedType */
            $this->assertTrue(true, "This test did not perform any assertions because xdebug is not available.");
        }
    }

    public function test_randomString()
    {
        $testName = "Check randamString function in INTER-Mediator.php.";
        $str = IMUtil::randomString(10);
        $this->assertTrue(is_string($str), $testName); // @phpstan-ignore-line function.alreadyNarrowedType
        $this->assertTrue(strlen($str) === 10, $testName);
        $str = IMUtil::randomString(100);
        $this->assertTrue(is_string($str), $testName); // @phpstan-ignore-line function.alreadyNarrowedType
        $this->assertTrue(strlen($str) === 100, $testName);
        $str = IMUtil::randomString(1000);
        $this->assertTrue(is_string($str), $testName); // @phpstan-ignore-line function.alreadyNarrowedType
        $this->assertTrue(strlen($str) === 1000, $testName);
        $str = IMUtil::randomString(0);
        $this->assertTrue(is_string($str), $testName); // @phpstan-ignore-line function.alreadyNarrowedType
        $this->assertTrue(strlen($str) === 0, $testName);
    }

    public function test_DateTimeString()
    {
        $cdt1 = IMUtil::currentDTString();
        $cdt2 = IMUtil::currentDTString(20);
        $cdt3 = IMUtil::currentDTString(-20);

        $this->assertGreaterThan($cdt2, $cdt1, "IMUtil::currentDTString checked with order but it mighit be corrupted.");
        $this->assertGreaterThan($cdt2, $cdt3, "IMUtil::currentDTString checked with order but it mighit be corrupted.");
        $this->assertGreaterThan($cdt1, $cdt3, "IMUtil::currentDTString checked with order but it mighit be corrupted.");
    }

    public function test_getMimeType()
    {
        if (((float)phpversion()) >= 5.3) {
            $path = '';
            $expected = 'application/octet-stream';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.jpg';
            $expected = 'image/jpeg';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.jpeg';
            $expected = 'image/jpeg';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.png';
            $expected = 'image/png';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.html';
            $expected = 'text/html';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.txt';
            $expected = 'text/plain';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.gif';
            $expected = 'image/gif';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.bmp';
            $expected = 'image/bmp';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.tif';
            $expected = 'image/tiff';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.tiff';
            $expected = 'image/tiff';
            $this->assertEquals($expected, IMUtil::getMimeType($path));

            $path = 'test.pdf';
            $expected = 'application/pdf';
            $this->assertEquals($expected, IMUtil::getMimeType($path));
        }
    }

    public function test_UserNameHome()
    {
        $user = IMUtil::getServerUserName();
        $home = IMUtil::getServerUserHome();

        /** @phpstan-ignore-next-line function.alreadyNarrowedType */
        $this->assertNotNull($user, "IMUtil::getServerUserName has to return any strings.");
        /** @phpstan-ignore-next-line function.alreadyNarrowedType */
        $this->assertNotNull($home, "IMUtil::getServerUserHome has to return any strings.");
    }

    public function test_Profile()
    {
        $tempDir = sys_get_temp_dir();
        Params::setVar("profileRoot", $tempDir);
        $profileRoot = Params::getParameterValue("profileRoot", null);
        $this->assertEquals($profileRoot, $tempDir);

        $fileContent = [
            "[Tochigi]",
            "aaaa = bbbb",
            "bbbb = BBBB",
            "a1 = bbBB",
            "a2 = BBbb",
            "",
            "[Gunma]",
            "aaaaa",
            "",
            "[Ibaragi]",
            "mysecret = 1234",
            "your-secret = 9876",
            "big_city        = \t 1919",
            "noone-knows = 4567", "", "",
        ];
        if (!file_exists("$tempDir/.im")) {
            mkdir("$tempDir/.im");
        }
        if (!file_exists("$tempDir/.aws")) {
            mkdir("$tempDir/.aws");
        }
        file_put_contents("$tempDir/.im/credentials", implode("\n", $fileContent));
        file_put_contents("$tempDir/.aws/credentials", implode("\n", $fileContent));

        $profDesc = "Profile|AWS|Ibaragi|mysecret";
        $this->assertEquals("1234", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|AWS|Ibarakii|mysecret";
        $this->assertEquals($profDesc, IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "PROFILE|AWS|IBARAGI|MYSECRET";
        $this->assertEquals("1234", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|aws|Ibaragi|noone-knows";
        $this->assertEquals("4567", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|aws|Ibaragi|big_city";
        $this->assertEquals("1919", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|aws|Tochigi|aaaa";
        $this->assertEquals("bbbb", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|aws|Tochigi|bbbb";
        $this->assertEquals("BBBB", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|aws|Tochigi|aAaA";
        $this->assertEquals("bbbb", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|aws|Tochigi|a1";
        $this->assertEquals("bbBB", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|aws|Tochigi|A2";
        $this->assertEquals("BBbb", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|aws|Gunma|noone-knows";
        $this->assertEquals($profDesc, IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|aws|Tokyo|noone-knows";
        $this->assertEquals($profDesc, IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|im|ibaragi|mysecret";
        $this->assertEquals("1234", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|im|ibaraki|mysecret";
        $this->assertEquals($profDesc, IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "PROFILE|IM|IBARAGI|MYSECRET";
        $this->assertEquals("1234", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|im|Ibaragi|noone-knows";
        $this->assertEquals("4567", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|im|Ibaragi|big_city";
        $this->assertEquals("1919", IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|im|Gunma|noone-knows";
        $this->assertEquals($profDesc, IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "Profile|im|Tokyo|noone-knows";
        $this->assertEquals($profDesc, IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "mysecretpassword";
        $this->assertEquals($profDesc, IMUtil::getFromProfileIfAvailable($profDesc));
        $profDesc = "dfas3j5fd#'ajds*;dkalj";
        $this->assertEquals($profDesc, IMUtil::getFromProfileIfAvailable($profDesc));
    }

    public function test_GeneratePassword()
    {
        $seed = "2345678abcdefghijkmnoprstuvwxyzABCDEFGHJKLMNPRSTUVWXYZ";
        $seedPunctuation = "#$%&";

        $genPW = IMUtil::generatePassword(4);
        $this->assertEquals(4, strlen($genPW));
        for ($i = 0; $i < strlen($genPW) - 1; $i++) {
            $this->assertTrue(str_contains($seed, $genPW[$i]));
        }
        $this->assertTrue(str_contains($seedPunctuation, $genPW[strlen($genPW) - 1]));

        $genPW = IMUtil::generatePassword(10);
        $this->assertEquals(10, strlen($genPW));
        for ($i = 0; $i < strlen($genPW) - 1; $i++) {
            $this->assertTrue(str_contains($seed, $genPW[$i]));
        }
        $this->assertTrue(str_contains($seedPunctuation, $genPW[strlen($genPW) - 1]));

        $genPW = IMUtil::generatePassword(15);
        $this->assertEquals(15, strlen($genPW));
        for ($i = 0; $i < strlen($genPW) - 1; $i++) {
            $this->assertTrue(str_contains($seed, $genPW[$i]));
        }
        $this->assertTrue(str_contains($seedPunctuation, $genPW[strlen($genPW) - 1]));
    }
}