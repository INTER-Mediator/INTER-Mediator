<?php
/**
 * FileUploader_Test file
 */

namespace deprecated;

use INTERMediator\FileUploader;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

//require_once(dirname(__FILE__) . '/../../../INTER-Mediator.php');
//require_once(dirname(__FILE__) . '/../../../params.php');

class FileUploader_Test extends TestCase
{
    /**
     * The uploader.
     *
     * @var FileUploader
     */
    private FileUploader $uploader;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->uploader = new FileUploader();
    }

    /**
     * Test get Redirect Url.
     *
     * @return void
     */
    public function test_getRedirectUrl()
    {
        if (((float)phpversion()) >= 5.3) {
            $reflectionMethod = new ReflectionMethod('FileUploader', 'getRedirectUrl');
            $reflectionMethod->setAccessible(true);

            $expected = 'http://' . php_uname('n') . '/';
            $result = $reflectionMethod->invokeArgs($this->uploader, array('http://' . php_uname('n') . '/'));
            $this->assertEquals($expected, $result);

            $expected = 'https://' . php_uname('n') . '/';
            $result = $reflectionMethod->invokeArgs($this->uploader, array('https://' . php_uname('n') . '/'));
            $this->assertEquals($expected, $result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0a'));
            $this->assertNull($result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0d'));
            $this->assertNull($result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0A'));
            $this->assertNull($result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0D'));
            $this->assertNull($result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/%0d%0a%20'));
            $this->assertNull($result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('ftp://inter-mediator.com/'));
            $this->assertNull($result);
        }
    }

    /**
     * Test check Redirect Url.
     *
     * @return void
     */
    public function test_checkRedirectUrl()
    {
        if (((float)phpversion()) >= 5.3) {
            $reflectionMethod = new ReflectionMethod('FileUploader', 'checkRedirectUrl');
            $reflectionMethod->setAccessible(true);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('http://www.inter-mediator.com/', 'inter-mediator.com'));
            $this->assertTrue($result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('http://www.inter-mediator.com:8080/', 'inter-mediator.com'));
            $this->assertTrue($result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('https://www.inter-mediator.com/', 'inter-mediator.com'));
            $this->assertTrue($result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('https://www.inter-mediator.com/', 'www.inter-mediator.com'));
            $this->assertTrue($result);

            $result = $reflectionMethod->invokeArgs($this->uploader, array('ftp://www.inter-mediator.com/', 'inter-mediator.com'));
            $this->assertFalse($result);
        }
    }
}
