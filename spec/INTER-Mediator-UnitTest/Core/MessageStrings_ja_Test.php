<?php
/**
 * MessageStrings_ja_Test file
 */

use PHPUnit\Framework\TestCase;
use INTERMediator\Message\MessageStrings_ja;

class MessageStrings_ja_Test extends TestCase
{
    /**
     * The messagestrings.
     *
     * @var MessageStrings_ja
     */
    private MessageStrings_ja $messagestrings;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->messagestrings = new MessageStrings_ja();
    }

    /**
     * Test get Messages.
     *
     * @return void
     */
    public function test_getMessages(): void
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

    /**
     * Test get Customized Messages.
     *
     * @return void
     */
    public function test_getCustomizedMessages(): void
    {
        $expected = '変更した';
        $messages = $this->messagestrings->getMessages();
        $number = 9999;
        $this->assertEquals($expected, $messages[$number]);
    }

    /**
     * Test get Messages As.
     *
     * @return void
     */
    public function test_getMessagesAs(): void
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
