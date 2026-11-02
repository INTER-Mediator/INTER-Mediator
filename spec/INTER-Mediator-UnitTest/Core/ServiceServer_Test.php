<?php

namespace INTERMediator;

use PHPUnit\Framework\TestCase;

class ServiceServer_Test extends TestCase
{

    /**
     * Test instantiate.
     *
     * @return void
     */
    public function test_instantiate(): void
    {
        $ssProxy = ServiceServerProxy::instance();
        // $this->assertNotNull($ssProxy, "The ServiceServerProxy instance has to get.");
        // phpstan reports Call to method PHPUnit\Framework\Assert::assertNotNull() with INTERMediator\ServiceServerProxy and 'The…' will always evaluate to true.

        $checkResult = $ssProxy->checkServiceServer();
        $this->assertFalse($checkResult, "Usually the service server is offline.");

        $messages = $ssProxy->getMessages();
        $this->assertNotEmpty($messages, "Some massages have to stored.");
    }

    protected function tearDown(): void
    {
        $this->resetSingleton();
        Params::setVar('notUseServiceServer', null);
        Params::setVar('preventSSAutoBoot', null);
    }

    private function resetSingleton(): void
    {
        $ref = new \ReflectionProperty(ServiceServerProxy::class, 'gSSPInstance');
        $ref->setAccessible(true);
        $ref->setValue(null, null);
    }

    public function test_preventSSAutoBoot_doesNotTryToStartServer(): void
    {
        $this->resetSingleton();
        Params::setVar('notUseServiceServer', false);
        Params::setVar('preventSSAutoBoot', true);

        $ssProxy = ServiceServerProxy::instance();
        $ssProxy->checkServiceServer();

        $messages = implode("\n", $ssProxy->getMessages());
        $this->assertStringNotContainsString('startServer() called', $messages,
            "preventSSAutoBoot must not try to boot the server.");
        $this->assertStringNotContainsString("couldn't boot", $messages,
            "preventSSAutoBoot must not retry the booting loop.");
    }

}
