<?php
/**
 * IMUtil_Test file
 */
require_once(dirname(__FILE__) . '/../IMUtil.php');

class IMUtil_Test extends PHPUnit_Framework_TestCase {

    private $util;
    public function setUp()
    {
        $this->util = new IMUtil();
    }

    public function test_removeNull()
    {
        $str = IMUtil::removeNull("INTER\x00-Mediator");
        $this->assertEquals($str, "INTER-Mediator");
    }

    public function test_getFromParamsPHPFile()
    {
        $result = $this->util->getFromParamsPHPFile(array('webServerName'), true);
        $this->assertEquals($result['webServerName'], array(''));
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
        if (((float)phpversion()) >= 5.3) {
            $this->reflectionMethod = new ReflectionMethod('IMUtil', 'checkHost');
            $this->reflectionMethod->setAccessible(true);

            $result = $this->reflectionMethod->invokeArgs($this->util, array('www.inter-mediator.com', 'www.inter-mediator.com'));
            $this->assertTrue($result);

            $result = $this->reflectionMethod->invokeArgs($this->util, array('www.inter-mediator.com', 'inter-mediator.com'));
            $this->assertTrue($result);

            $result = $this->reflectionMethod->invokeArgs($this->util, array('WWW.inter-mediator.com', 'inter-mediator.com'));
            $this->assertTrue($result);

            $result = $this->reflectionMethod->invokeArgs($this->util, array('inter-mediator.com', 'inter-mediator.com'));
            $this->assertTrue($result);

            $_SERVER = array();
            $_SERVER['SERVER_ADDR'] = '192.168.56.101';
            $result = $this->reflectionMethod->invokeArgs($this->util, array('192.168.56.101', $_SERVER['SERVER_ADDR']));
            $this->assertTrue($result);

            $result = $this->reflectionMethod->invokeArgs($this->util, array('www.inter-mediator.com', ''));
            $this->assertFalse($result);

            $result = $this->reflectionMethod->invokeArgs($this->util, array('www.inter-mediator.com', 'ww.inter-mediator.com'));
            $this->assertFalse($result);

            $_SERVER = array();
            $result = $this->reflectionMethod->invokeArgs($this->util, array('192.168.56.101', '56.101'));
            $this->assertFalse($result);
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_outputSecurityHeaders()
    {
        $params = array();

        if (function_exists('xdebug_get_headers')) {
            ob_start();
            $this->util->outputSecurityHeaders();
            $headers = xdebug_get_headers();
            header_remove();
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
            ob_clean();
            $this->assertContains('X-Frame-Options: ALLOW-FROM http://inter-mediator.com/', $headers);
            $this->assertContains('Content-Security-Policy: frame-ancestors https://inter-mediator.com http://inter-mediator.info', $headers);
            $this->assertContains('Access-Control-Allow-Origin: *', $headers);
        }
    }
}
