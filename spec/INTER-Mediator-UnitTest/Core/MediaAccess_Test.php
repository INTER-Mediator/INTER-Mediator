<?php
/**
 * MediaAccess_Test file
 */

use INTERMediator\MediaAccess;
use PHPUnit\Framework\TestCase;

class MediaAccess_Test extends TestCase
{
    /**
     * The mediaaccess.
     *
     * @var MediaAccess
     */
    private MediaAccess $mediaaccess;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mediaaccess = new MediaAccess();
    }

    /**
     * Test as Attachment.
     *
     * @return void
     */
    public function test_asAttachment(): void
    {
        $reflectionClass = new ReflectionClass('\INTERMediator\MediaAccess');
        $disposition = $reflectionClass->getProperty('disposition');

        $expected = 'inline';
        $this->assertSame($expected, $disposition->getValue($this->mediaaccess));

        $expected = 'attachment';
        $attachment = $reflectionClass->getMethod('asAttachment');
        try {
            $attachment->invoke($this->mediaaccess);
        } catch (Exception $e) {

        }
        $this->assertSame($expected, $disposition->getValue($this->mediaaccess));
    }

    /**
     * Test exit As Error.
     *
     * @return void
     */
    public function test_exitAsError(): void
    {
        $reflectionMethod = new ReflectionMethod('\INTERMediator\MediaAccess', 'exitAsError');

        $code = -1;
        $expected = 'Respond HTTP Error.';
        try {
            $reflectionMethod->invokeArgs($this->mediaaccess, array($code));
            $this->fail('No Exception happens');
        } catch (Exception $e) {
            $this->assertSame($expected, $e->getMessage());
        }
    }

    /**
     * Regression test: exitAsError(403) must be handled by the switch (unlike the previously
     * used, unhandled 400), so an HTTP 403 header is actually sent instead of silently falling
     * through to the default 200 response.
     *
     * @return void
     */
    public function test_exitAsError403SendsForbiddenHeader(): void
    {
        $reflectionMethod = new ReflectionMethod('\INTERMediator\MediaAccess', 'exitAsError');

        try {
            $reflectionMethod->invokeArgs($this->mediaaccess, array(403));
            $this->fail('No Exception happens');
        } catch (Exception $e) {
            $this->assertSame('Respond HTTP Error.', $e->getMessage());
        }
        if (!function_exists('xdebug_get_headers')) {
            $this->markTestSkipped('xdebug_get_headers() is unavailable to verify sent headers.');
        }
        $this->assertTrue(http_response_code() === 403, 'Expected a 403 status code to be sent.');
    }

    /**
     * Regression test: a key value that contains '=' (e.g. a base64-padded id) must not be
     * truncated at the first extra '=' when parsing "<context>/<field>=<value>" style targets.
     *
     * @return void
     */
    public function test_analyzeTargetKeepsEqualsSignInKeyValue(): void
    {
        $reflectionMethod = new ReflectionMethod('\INTERMediator\MediaAccess', 'analyzeTarget');
        $target = 'file:///tmp/media/mycontext/id=abc=123';

        $result = $reflectionMethod->invoke($this->mediaaccess, $target);

        $this->assertTrue($result);
        $this->assertSame('mycontext',
            (new ReflectionProperty('\INTERMediator\MediaAccess', 'targetContextName'))
                ->getValue($this->mediaaccess));
        $this->assertSame('id',
            (new ReflectionProperty('\INTERMediator\MediaAccess', 'targetKeyField'))
                ->getValue($this->mediaaccess));
        $this->assertSame('abc=123',
            (new ReflectionProperty('\INTERMediator\MediaAccess', 'targetKeyValue'))
                ->getValue($this->mediaaccess));
    }
}
