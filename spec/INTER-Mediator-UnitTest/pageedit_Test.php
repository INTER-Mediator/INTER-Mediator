<?php
/**
 * pageedit_Test file
 */
use \PHPUnit\Framework\TestCase;

class pageedit_Test extends TestCase
{
    public function setUp()
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test___construct()
    {
        ob_start();
        $imPath = \INTERMediator\IMUtil::pathToINTERMediator();
        require_once($imPath . '/editors/pageedit.php');
        $output = ob_get_contents();
        $this->assertNotContains('INTERMediatorLog.debugMode=', $output);
        ob_clean();
    }
}
