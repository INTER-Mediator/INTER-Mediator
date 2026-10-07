<?php
/**
 * GenerateJSCode_Test file
 */

use INTERMediator\GenerateJSCode;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class GenerateJSCode_Test extends TestCase
{
    /**
     * The generater.
     *
     * @var GenerateJSCode
     */
    private GenerateJSCode $generater;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    protected function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $this->generater = new GenerateJSCode();
    }

    /**
     * Test generate Assign JS.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    function test_generateAssignJS(): void
    {
        $this->expectOutputString('INTERMediatorOnPage.getEditorPath=function(){return \'\';};' . "\n");
        $this->generater->generateAssignJS('INTERMediatorOnPage.getEditorPath', 'function(){return \'\';}');
    }

    /**
     * Test generate Error Message JS.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    function test_generateErrorMessageJS(): void
    {
        $this->expectOutputString('INTERMediatorLog.setErrorMessage("PHP extension \"mbstring\" is required for running INTER-Mediator. ");');
        $this->generater->generateErrorMessageJS('PHP extension "mbstring" is required for running INTER-Mediator.' . "\n");
    }

    /**
     * Test generate Initial JS Code.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    function test_generateInitialJSCode(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['REMOTE_ADDR'] = '';
        $this->expectOutputRegex('/INTERMediatorOnPage.serviceServerURL="ws:\/\/localhost:/');
        $this->generater->generateInitialJSCode([], [], ['db-class' => 'PDO'], false);
    }

    /**
     * Test generate Initial JS Code 2.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    function test_generateInitialJSCode2(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost:80';
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['REMOTE_ADDR'] = '';
        $this->expectOutputRegex('/INTERMediatorOnPage.serviceServerURL="ws:\/\/localhost:/');
        $this->generater->generateInitialJSCode([], [], ['db-class' => 'PDO'], false);
    }

    /**
     * Test generate Initial JS Code 3.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    function test_generateInitialJSCode3(): void
    {
        //$_SERVER['HTTP_HOST'] = '';
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['REMOTE_ADDR'] = '';
        $this->expectOutputRegex('/INTERMediatorOnPage.serviceServerURL="ws:\/\/localhost:/');
        $this->generater->generateInitialJSCode([], [], ['db-class' => 'PDO'], false);
    }

//    #[RunInSeparateProcess]
//    #[PreserveGlobalState(false)]
//    function test___construct()
//    {
//        if (function_exists('xdebug_get_headers') && false) {
//            ob_start();
//            $this->generater->__construct();
//            $headers = xdebug_get_headers();
//            header_remove();
//            ob_end_flush();
//            ob_clean();
//
//            $this->assertStringContainsString('Content-Type: text/javascript;charset="UTF-8"', implode("\n", $headers));
//            $this->assertStringContainsString('X-XSS-Protection: 1; mode=block', implode("\n", $headers));
//            $this->assertStringContainsString('X-Frame-Options: SAMEORIGIN', implode("\n", $headers));
//        } else {
//            $this->assertTrue(true, "Preventing Risky warning.");
//        }
//    }
//
    /**
     * Test combine Scripts.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_combineScripts(): void
    {
        if (((float)phpversion()) >= 5.3) {
            $reflectionMethod = new ReflectionMethod('\INTERMediator\GenerateJSCode', 'combineScripts');
//            $reflectionMethod->setAccessible(true);
            $currentDir = dirname(__FILE__, 4) . DIRECTORY_SEPARATOR .
                'src' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
            $content = $reflectionMethod->invokeArgs($this->generater, array($currentDir));
            $jsLibDir = dirname($currentDir, 2) . DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR;
            $method = new ReflectionMethod('\INTERMediator\GenerateJSCode', 'readJSSource');
//            $method->setAccessible(true);
            $partOfCode = $method->invokeArgs($this->generater, array($jsLibDir . 'jssha/dist/sha.js'));
            $this->assertStringContainsString($partOfCode, $content);
        }
    }

}
