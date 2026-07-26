<?php
/**
 * MessageStrings_Test file
 */

use PHPUnit\Framework\TestCase;
use INTERMediator\Messaging\SendMail;

class Messaging_Test extends TestCase
{
    /**
     * Set up the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
    }

    /**
     * Test templating.
     *
     * @return void
     */
    public function test_templating(): void
    {
        $sMail = new SendMail();
        $record = ['id' => 1, 'email' => 'msyk@msyk.net'];

        $tempStr = "aa@@email@@bb";
        $result = $sMail->modernTemplating($record, $tempStr);
        $this->assertEquals("aamsyk@msyk.netbb", $result);

        $tempStr = "aa@@email@@bb@@id@@cc";
        $result = $sMail->modernTemplating($record, $tempStr);
        $this->assertEquals("aamsyk@msyk.netbb1cc", $result);

        $tempStr = "aa@@email@bb";
        $result = $sMail->modernTemplating($record, $tempStr);
        $this->assertEquals($tempStr, $result);

        $tempStr = "aa@@nothing@@bb";
        $result = $sMail->modernTemplating($record, $tempStr);
        $this->assertEquals("aabb", $result);
    }
}
