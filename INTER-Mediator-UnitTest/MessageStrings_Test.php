<?php
/**
 * MessageStrings_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../MessageStrings.php');

class MessageStrings_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->messagestrings = new MessageStrings();
    }

    public function test_getMessages()
    {
        $expected = 'Are you sure to delete?';
        $messages = $this->messagestrings->getMessages();
        $number = 1025;
        $this->assertEquals($expected, $messages[$number]);

        $expected = 'Are you sure to create record?';
        $messages = $this->messagestrings->getMessages();
        $number = 1026;
        $this->assertEquals($expected, $messages[$number]);
    }
}