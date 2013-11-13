<?php
/**
 * MediaAccess_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator/MediaAccess.php');

class MediaAccess_Test extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mediaaccess = new MediaAccess();
    }
    
    public function test_getMimeType()
    {
        $this->reflectionMethod = new ReflectionMethod('MediaAccess', 'getMimeType');
        $this->reflectionMethod->setAccessible(true);

        $path = '';
        $expected = 'application/octet-stream';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);

        $path = 'test.jpg';
        $expected = 'image/jpeg';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);
    }
}