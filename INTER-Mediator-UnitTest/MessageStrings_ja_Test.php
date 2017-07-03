<?php
/**
 * MessageStrings_ja_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../MessageStrings.php');
require_once(dirname(__FILE__) . '/../MessageStrings_ja.php');

class MessageStrings_ja_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->messagestrings = new MessageStrings_ja();
    }

    public function test_getMessages()
    {
        $expected = 'レコードを本当に削除していいですか?';
        $messages = $this->messagestrings->getMessages();
        $number = 1025;
        $this->assertEquals($expected, $messages[$number]);

        $expected = 'レコードを本当に作成していいですか?';
        $messages = $this->messagestrings->getMessages();
        $number = 1026;
        $this->assertEquals($expected, $messages[$number]);
    }

    public function test_getMessagesAs()
    {
        $expected = 'レコード番号';
        $message = $this->messagestrings->getMessageAs(1, array());
        $this->assertEquals($expected, $message);
        
        $expected = '更新';
        $message = $this->messagestrings->getMessageAs(2, array());
        $this->assertEquals($expected, $message);
        
        $expected = 'レコード追加';
        $message = $this->messagestrings->getMessageAs(3, array());
        $this->assertEquals($expected, $message);
        
        $expected = 'レコード削除';
        $message = $this->messagestrings->getMessageAs(4, array());
        $this->assertEquals($expected, $message);
        
        $expected = '保存';
        $message = $this->messagestrings->getMessageAs(7, array());
        $this->assertEquals($expected, $message);
        
        $expected = 'ログインユーザー: ';
        $message = $this->messagestrings->getMessageAs(8, array());
        $this->assertEquals($expected, $message);
        
        $expected = 'ログアウト';
        $message = $this->messagestrings->getMessageAs(9, array());
        $this->assertEquals($expected, $message);
        
        $expected = 'ページファイルに指定したフィールド名「testfield」は、指定したコンテキストには存在しません';
        $message = $this->messagestrings->getMessageAs(1033, array('testfield'));
        $this->assertEquals($expected, $message);
    }
}
