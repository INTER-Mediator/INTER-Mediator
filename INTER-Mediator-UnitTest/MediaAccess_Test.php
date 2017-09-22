<?php
/**
 * MediaAccess_Test file
 */
require_once(dirname(__FILE__) . '/../MediaAccess.php');

class MediaAccess_Test extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->mediaaccess = new MediaAccess();
    }

    public function test_asAttachment()
    {
        if (((float)phpversion()) >= 5.3) {
            $this->reflectionClass = new ReflectionClass('MediaAccess');
            $disposition = $this->reflectionClass->getProperty('disposition');
            $disposition->setAccessible(true);
        
            $expected = 'inline';
            $this->assertEquals($expected, $disposition->getValue($this->mediaaccess));
        
            $expected = 'attachment';
            $attachment = $this->reflectionClass->getMethod('asAttachment');
            $attachment->setAccessible(true);
            $attachment->invoke($this->mediaaccess);
            $this->assertEquals($expected, $disposition->getValue($this->mediaaccess));
        }
    }

    public function test_exitAsError()
    {
        if (((float)phpversion()) >= 5.3) {
            $this->reflectionMethod = new ReflectionMethod('MediaAccess', 'exitAsError');
            $this->reflectionMethod->setAccessible(true);
    
            $code = '';
            $expected = 'Respond HTTP Error.';
            try {
                $this->reflectionMethod->invokeArgs($this->mediaaccess, array($code));
                $this->fail('No Exception happens');
            } catch (Exception $e) {
                $this->assertEquals($expected, $e->getMessage());
            }
        }
    }

    public function test_getMimeType()
    {
        if (((float)phpversion()) >= 5.3) {
            $this->reflectionMethod = new ReflectionMethod('MediaAccess', 'getMimeType');
            $this->reflectionMethod->setAccessible(true);
    
            $path = '';
            $expected = 'application/octet-stream';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.jpg';
            $expected = 'image/jpeg';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.jpeg';
            $expected = 'image/jpeg';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.png';
            $expected = 'image/png';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.html';
            $expected = 'text/html';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.txt';
            $expected = 'text/plain';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.gif';
            $expected = 'image/gif';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.bmp';
            $expected = 'image/bmp';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.tif';
            $expected = 'image/tiff';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.tiff';
            $expected = 'image/tiff';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
    
            $path = 'test.pdf';
            $expected = 'application/pdf';
            $this->assertEquals($expected, $this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)));
        }
    }
}