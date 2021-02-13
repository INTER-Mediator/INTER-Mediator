<?php
/**
 * pageedit_Test file
 */

if (!class_exists('PHPUnit_Framework_TestCase')) {
    class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}

class pageedit_Test extends PHPUnit_Framework_TestCase
{
    public function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test___construct()
    {
        ob_start();
        require_once(dirname(__FILE__) . '/../INTER-Mediator-Support/pageedit.php');
        $output = ob_get_contents();
        $this->assertNotContains('INTERMediatorLog.debugMode=', $output);
        ob_end_clean();
    }
}
