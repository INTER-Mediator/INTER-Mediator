<?php
/**
 * FileUploader_Test file
 */
require_once(dirname(__FILE__) . '/../FileUploader.php');
require_once(dirname(__FILE__) . '/../IMUtil.php');
require_once(dirname(__FILE__) . '/../params.php');

class FileUploader_Test extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->uploader = new FileUploader();
    }

    public function test_getRedirectUrl()
    {
        if (((float)phpversion()) >= 5.3) {
            $this->reflectionMethod = new ReflectionMethod('FileUploader', 'getRedirectUrl');
            $this->reflectionMethod->setAccessible(true);

            $expected = 'http://' . php_uname('n') . '/';
            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('http://' . php_uname('n') . '/'));
            $this->assertEquals($expected, $result);

            $expected = 'https://' . php_uname('n') . '/';
            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('https://' . php_uname('n') . '/'));
            $this->assertEquals($expected, $result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0a'));
            $this->assertNull($result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0d'));
            $this->assertNull($result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0A'));
            $this->assertNull($result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0D'));
            $this->assertNull($result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0d%0a%20'));
            $this->assertNull($result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('ftp://inter-mediator.com/'));
            $this->assertNull($result);
        }
    }

    public function test_checkRedirectUrl()
    {
        if (((float)phpversion()) >= 5.3) {
            $this->reflectionMethod = new ReflectionMethod('FileUploader', 'checkRedirectUrl');
            $this->reflectionMethod->setAccessible(true);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('http://www.inter-mediator.com/', 'inter-mediator.com'));
            $this->assertTrue($result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('http://www.inter-mediator.com:8080/', 'inter-mediator.com'));
            $this->assertTrue($result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('https://www.inter-mediator.com/', 'inter-mediator.com'));
            $this->assertTrue($result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('https://www.inter-mediator.com/', 'www.inter-mediator.com'));
            $this->assertTrue($result);

            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('ftp://www.inter-mediator.com/', 'inter-mediator.com'));
            $this->assertFalse($result);
        }
    }
}
