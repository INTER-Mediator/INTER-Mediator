<?php
/**
 * defedit_Test file
 */

class defedit_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test___construct()
    {
        ob_start();
        require_once(dirname(__FILE__) . '/../INTER-Mediator-Support/defedit.php');
        $output = ob_get_contents();
        $this->assertNotContains('INTERMediator.debugMode=', $output);
        ob_clean();
    }
}
