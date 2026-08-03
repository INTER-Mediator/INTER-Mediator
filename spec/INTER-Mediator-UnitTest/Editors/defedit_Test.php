<?php
/**
 * defedit_Test file
 */

namespace Editors;

use INTERMediator\IMUtil;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class defedit_Test extends TestCase
{
    /**
     * Set up the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);
    }

    /**
     * Test construct.
     *
     * @return void
     */
    #[BackupGlobals(true)]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test___construct(): void
    {
        ob_start();
        $imPath = IMUtil::pathToINTERMediator();
        require_once($imPath . '/editors/defedit.php');
        $output = ob_get_contents();
        $this->assertStringNotContainsString('INTERMediatorLog.debugMode=', (string)$output);
        ob_end_clean();
    }
}
