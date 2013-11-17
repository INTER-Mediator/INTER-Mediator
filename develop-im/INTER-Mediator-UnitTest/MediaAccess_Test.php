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

    public function test_asAttachment()
    {
        $expected = 'inline';
        $this->reflectionClass = new ReflectionClass('MediaAccess');
        $disposition = $this->reflectionClass->getProperty('disposition');
        $disposition->setAccessible(true);
        $this->assertEquals($disposition->getValue($this->mediaaccess), $expected);

        $expected = 'attachment';
        $attachment = $this->reflectionClass->getMethod('asAttachment');
        $attachment->setAccessible(true);
        $attachment->invoke($this->mediaaccess);
        $this->assertEquals($disposition->getValue($this->mediaaccess), $expected);
    }

    public function test_exitAsError()
    {
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

        $path = 'test.jpeg';
        $expected = 'image/jpeg';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);

        $path = 'test.png';
        $expected = 'image/png';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);

        $path = 'test.html';
        $expected = 'text/html';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);

        $path = 'test.txt';
        $expected = 'text/plain';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);

        $path = 'test.gif';
        $expected = 'image/gif';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);

        $path = 'test.bmp';
        $expected = 'image/bmp';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);

        $path = 'test.tif';
        $expected = 'image/tiff';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);

        $path = 'test.tiff';
        $expected = 'image/tiff';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);

        $path = 'test.pdf';
        $expected = 'application/pdf';
        $this->assertEquals($this->reflectionMethod->invokeArgs($this->mediaaccess, array($path)), $expected);
    }
}