<?php
/**
 * FileUploader_Test file
 */
require_once(dirname(__FILE__) . '/../FileUploader.php');

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
            
            $expected = 'https://inter-mediator.com/';
            $result = $this->reflectionMethod->invokeArgs($this->uploader, array('https://inter-mediator.com/'));
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
        }
    }

}