<?php
/**
 * MessageStrings_Test file
 */

use PHPUnit\Framework\TestCase;
use INTERMediator\Messaging\SendMail;

class Messaging_Test extends TestCase
{
    public function setUp(): void
    {
    }

    public function test_templating()
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
