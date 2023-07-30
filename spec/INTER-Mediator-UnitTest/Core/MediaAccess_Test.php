<?php
/**
 * MediaAccess_Test file
 */

use INTERMediator\MediaAccess;
use PHPUnit\Framework\TestCase;

class MediaAccess_Test extends TestCase
{
    private MediaAccess $mediaaccess;
    private ReflectionClass $reflectionClass;
    private ReflectionMethod $reflectionMethod;

    protected function setUp(): void
    {
        $this->mediaaccess = new MediaAccess();
    }

    public function test_asAttachment(): void
    {
        $this->reflectionClass = new ReflectionClass('\INTERMediator\MediaAccess');
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

    public function test_exitAsError(): void
    {
        $this->reflectionMethod = new ReflectionMethod('\INTERMediator\MediaAccess', 'exitAsError');
        $this->reflectionMethod->setAccessible(true);

        $code = -1;
        $expected = 'Respond HTTP Error.';
        try {
            $this->reflectionMethod->invokeArgs($this->mediaaccess, array($code));
            $this->fail('No Exception happens');
        } catch (Exception $e) {
            $this->assertEquals($expected, $e->getMessage());
        }
    }
}
