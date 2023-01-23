<?php
/**
 * MessageStrings_Test file
 */

use PHPUnit\Framework\TestCase;
use INTERMediator\Message\MessageStrings;

class MessageStrings_Test extends TestCase
{
    private $messagestrings;

    public function setUp(): void
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

    public function test_getCustomizedMessages()
    {
        $expected = "We don't support Internet Explorer. We'd like you to access by Edge or any other major browsers.";
        $messages = $this->messagestrings->getMessages();
        $number = 1022;
        $this->assertEquals($expected, $messages[$number]);
    }

    public function test_getMessagesAs()
    {
        $expected = 'Record #';
        $message = $this->messagestrings->getMessageAs(1, array());
        $this->assertEquals($expected, $message);

        $expected = 'Refresh';
        $message = $this->messagestrings->getMessageAs(2, array());
        $this->assertEquals($expected, $message);

        $expected = 'Add Record';
        $message = $this->messagestrings->getMessageAs(3, array());
        $this->assertEquals($expected, $message);

        $expected = 'Delete Record';
        $message = $this->messagestrings->getMessageAs(4, array());
        $this->assertEquals($expected, $message);

        $expected = 'Save';
        $message = $this->messagestrings->getMessageAs(7, array());
        $this->assertEquals($expected, $message);

        $expected = 'Login as: ';
        $message = $this->messagestrings->getMessageAs(8, array());
        $this->assertEquals($expected, $message);

        $expected = 'Logout';
        $message = $this->messagestrings->getMessageAs(9, array());
        $this->assertEquals($expected, $message);

        $expected = 'The field name specified in the page file doesn\'t exist [folder=testfield]';
        $message = $this->messagestrings->getMessageAs(1033, array('testfield'));
        $this->assertEquals($expected, $message);
    }
}
