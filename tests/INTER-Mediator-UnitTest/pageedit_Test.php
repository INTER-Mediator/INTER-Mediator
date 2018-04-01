<?php
/**
 * pageedit_Test file
 */

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
        require_once(dirname(__FILE__) . '/../editors/pageedit.php');
        $output = ob_get_contents();
        $this->assertNotContains('INTERMediatorLog.debugMode=', $output);
        ob_clean();
    }
}
