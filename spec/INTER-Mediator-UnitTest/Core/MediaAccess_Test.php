<?php
/**
 * MediaAccess_Test file
 */

use INTERMediator\MediaAccess;
use PHPUnit\Framework\TestCase;

class MediaAccess_Test extends TestCase
{
    private MediaAccess $mediaaccess;

    protected function setUp(): void
    {
        $this->mediaaccess = new MediaAccess();
    }

    public function test_asAttachment(): void
    {
        $reflectionClass = new ReflectionClass('\INTERMediator\MediaAccess');
        $disposition = $reflectionClass->getProperty('disposition');
        $disposition->setAccessible(true);

        $expected = 'inline';
        $this->assertSame($expected, $disposition->getValue($this->mediaaccess));

        $expected = 'attachment';
        $attachment = $reflectionClass->getMethod('asAttachment');
        $attachment->setAccessible(true);
        try {
            $attachment->invoke($this->mediaaccess);
        } catch (Exception $e) {

        }
        $this->assertSame($expected, $disposition->getValue($this->mediaaccess));
    }

    public function test_exitAsError(): void
    {
        $reflectionMethod = new ReflectionMethod('\INTERMediator\MediaAccess', 'exitAsError');
        $reflectionMethod->setAccessible(true);

        $code = -1;
        $expected = 'Respond HTTP Error.';
        try {
            $reflectionMethod->invokeArgs($this->mediaaccess, array($code));
            $this->fail('No Exception happens');
        } catch (Exception $e) {
            $this->assertSame($expected, (string)$e->getMessage());
        }
    }
}
