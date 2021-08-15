<?php
/**
 * MessageStrings_Test file
 */

use \PHPUnit\Framework\TestCase;
use \INTERMediator\Messaging\SendMail;

//require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
//require_once(dirname(__FILE__) . '/../MessageStrings.php');

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
        $this->assertEquals($result, "aamsyk@msyk.netbb");

        $tempStr = "aa@@email@@bb@@id@@cc";
        $result = $sMail->modernTemplating($record, $tempStr);
        $this->assertEquals($result, "aamsyk@msyk.netbb1cc");

        $tempStr = "aa@@email@bb";
        $result = $sMail->modernTemplating($record, $tempStr);
        $this->assertEquals($result, $tempStr);

        $tempStr = "aa@@nothing@@bb";
        $result = $sMail->modernTemplating($record, $tempStr);
        $this->assertEquals($result, "aabb");
    }
}
