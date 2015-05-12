<?php
/**
 * pageedit_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../GenerateJSCode.php');
require_once(dirname(__FILE__) . '/../INTER-Mediator-Support/pageedit.php');

class pageedit_Test extends PHPUnit_Framework_TestCase
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
        $output = ob_get_contents();
        $this->assertNotContains('INTERMediator.debugMode=', $output);
        ob_clean();
    }
}
