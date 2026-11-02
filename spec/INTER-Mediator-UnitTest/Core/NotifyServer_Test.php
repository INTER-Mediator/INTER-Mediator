<?php
/**
 * NotifyServer_Test file
 */

use INTERMediator\DB\DBClass;
use INTERMediator\DB\Logger;
use INTERMediator\DB\Support\DB_Notification_Common;
use INTERMediator\NotifyServer;
use PHPUnit\Framework\TestCase;

class NotifyServer_Test extends TestCase
{
    /**
     * Create a NotifyServer initialized with a mocked DBClass and notification handler.
     *
     * @param array<int, mixed>|null $channels The channels that the mocked handler returns for registered matches.
     * @return NotifyServer The initialized NotifyServer instance.
     */
    private function createNotifyServer(?array $channels): NotifyServer
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(true);
        $handler->method('matchInRegistered')->willReturn($channels);
        $handler->method('appendIntoRegistered')->willReturn($channels);
        $handler->method('removeFromRegistered')->willReturn($channels);

        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;
        $dbClass->logger = Logger::getInstance();

        $notifyServer = new NotifyServer();
        $this->assertTrue($notifyServer->initialize($dbClass, 'client-1'));
        return $notifyServer;
    }

    /**
     * Data provider of cases with no notification channels.
     *
     * @return array<string, array<int, array<int, mixed>|null>> The data set.
     */
    public static function noChannelsProvider(): array
    {
        return ['null' => [null], 'empty' => [[]]];
    }

    /**
     * Test that updated() does not trigger a service call when no channels are registered.
     *
     * @param array<int, mixed>|null $channels The channels returned by the mocked handler.
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('noChannelsProvider')]
    public function testUpdatedWithoutChannels(?array $channels): void
    {
        $this->createNotifyServer($channels)->updated('client-1', 'person', [1], ['name'], ['A'], false);
        $this->assertNoTriggerCall();
    }

    /**
     * Test that created() does not trigger a service call when no channels are registered.
     *
     * @param array<int, mixed>|null $channels The channels returned by the mocked handler.
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('noChannelsProvider')]
    public function testCreatedWithoutChannels(?array $channels): void
    {
        $this->createNotifyServer($channels)->created('client-1', 'person', [1], 'id', ['id' => 1], false);
        $this->assertNoTriggerCall();
    }

    /**
     * Test that deleted() does not trigger a service call when no channels are registered.
     *
     * @param array<int, mixed>|null $channels The channels returned by the mocked handler.
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('noChannelsProvider')]
    public function testDeletedWithoutChannels(?array $channels): void
    {
        $this->createNotifyServer($channels)->deleted('client-1', 'person', [1]);
        $this->assertNoTriggerCall();
    }

    /**
     * Test that methods called before initialize() behave safely and do not trigger a notification.
     *
     * @return void
     */
    public function testMethodsBeforeInitialize(): void
    {
        $notifyServer = new NotifyServer();
        $this->assertNull($notifyServer->register('person', '', [1]));
        $this->assertFalse($notifyServer->unregister('client-1', null));
        $notifyServer->updated('client-1', 'person', [1], ['name'], ['A'], false);
        $notifyServer->created('client-1', 'person', [1], 'id', ['id' => 1], false);
        $notifyServer->deleted('client-1', 'person', [1]);
        $this->assertStringNotContainsString('[NotifyServer] trigger',
            implode("\n", Logger::getInstance()->getDebugMessages()));
    }

    /**
     * Test that after a failed initialize(), operations do not delegate to the notification handler.
     *
     * @return void
     */
    public function testMethodsAfterFailedInitialize(): void
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(false);
        $handler->expects($this->never())->method('register');
        $handler->expects($this->never())->method('matchInRegistered');
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;

        $notifyServer = new NotifyServer();
        $this->assertFalse($notifyServer->initialize($dbClass, 'client-1'));
        $this->assertNull($notifyServer->register('person', '', [1]));
        $notifyServer->updated('client-1', 'person', [1], ['name'], ['A'], false);
    }

    /**
     * Test that initialize() fails when the DB class has no notification handler.
     *
     * @return void
     */
    public function testInitializeWithoutHandler(): void
    {
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = null;
        $this->assertFalse((new NotifyServer())->initialize($dbClass, 'client-1'));
    }

    /**
     * Test that register() and unregister() delegate to the notification handler with the expected arguments.
     *
     * @return void
     */
    public function testRegisterAndUnregisterDelegateToHandler(): void
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(true);
        $handler->expects($this->once())->method('register')
            ->with('client-1', 'person', 'id=1', [1])->willReturn('reg-1');
        $handler->expects($this->once())->method('unregister')
            ->with('client-1', ['5'])->willReturn(true);
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;

        $notifyServer = new NotifyServer();
        $this->assertTrue($notifyServer->initialize($dbClass, 'client-1'));
        $this->assertSame('reg-1', $notifyServer->register('person', 'id=1', [1]));
        $this->assertTrue($notifyServer->unregister('client-1', ['5']));
    }

    /**
     * Data provider of invalid table key inputs for unregister.
     *
     * @return array<string, array<int, array<int, mixed>>> The data set.
     */
    public static function invalidTableKeysProvider(): array
    {
        return ['nulls' => [[null, null]], 'empty strings' => [['', null]], 'empty array' => [[]]];
    }

    /**
     * Test that unregister() with invalid or empty table keys does not call the handler's unregister method.
     *
     * @param array<int, mixed> $tableKeys The table keys to pass to unregister().
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTableKeysProvider')]
    public function testUnregisterWithoutValidIdsDoesNotRemoveAllRegistrations(array $tableKeys): void
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(true);
        $handler->expects($this->never())->method('unregister');
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;

        $notifyServer = new NotifyServer();
        $this->assertTrue($notifyServer->initialize($dbClass, 'client-1'));
        $this->assertTrue($notifyServer->unregister('client-1', $tableKeys));
    }

    /**
     * Test that unregister() filters invalid IDs and keeps null to mean removing all registrations.
     *
     * @return void
     */
    public function testUnregisterFiltersInvalidIdsAndKeepsNullForAll(): void
    {
        $handler = $this->createMock(DB_Notification_Common::class);
        $handler->method('isExistRequiredTable')->willReturn(true);
        $handler->expects($this->exactly(2))->method('unregister')
            ->willReturnCallback(function (?string $client, ?array $tableKeys): bool {
                static $call = 0;
                $call++;
                $this->assertSame('client-1', $client);
                $this->assertSame($call === 1 ? ['5', 7] : null, $tableKeys);
                return true;
            });
        $dbClass = $this->createMock(DBClass::class);
        $dbClass->notifyHandler = $handler;

        $notifyServer = new NotifyServer();
        $this->assertTrue($notifyServer->initialize($dbClass, 'client-1'));
        $this->assertTrue($notifyServer->unregister('client-1', [null, '5', '', 7]));
        $this->assertTrue($notifyServer->unregister('client-1', null));
    }

    /**
     * Assert that no notification trigger was sent to the service server, based on the debug log.
     *
     * @return void
     */
    private function assertNoTriggerCall(): void
    {
        $messages = implode("\n", Logger::getInstance()->getDebugMessages());
        $this->assertStringContainsString('[NotifyServer] trigger / No channels to notify', $messages);
        $this->assertStringNotContainsString('[ServiceServerProxy] sync', $messages);
    }

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $logger = Logger::getInstance();
        $logger->clearLogs();
        $logger->setDebugMode(2);
    }
}
