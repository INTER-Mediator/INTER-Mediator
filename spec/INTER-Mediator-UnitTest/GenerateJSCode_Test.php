<?php
/**
 * GenerateJSCode_Test file
 */
use INTERMediator\GenerateJSCode;
use PHPUnit\Framework\TestCase;

//$imRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
//require "{$imRoot}" . DIRECTORY_SEPARATOR .'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class GenerateJSCode_Test extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $this->generater = new GenerateJSCode();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    function test_generateAssignJS()
    {
        $this->expectOutputString('INTERMediatorOnPage.getEditorPath=function(){return \'\';};' . "\n");
        $this->generater->generateAssignJS('INTERMediatorOnPage.getEditorPath', 'function(){return \'\';}');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    function test_generateErrorMessageJS()
    {
        $this->expectOutputString('INTERMediatorLog.setErrorMessage("PHP extension \"mbstring\" is required for running INTER-Mediator. ");');
        $this->generater->generateErrorMessageJS('PHP extension "mbstring" is required for running INTER-Mediator.' . "\n");
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    function test___construct()
    {
        if (function_exists('xdebug_get_headers')) {
            ob_start();
            $this->generater->__construct();
            $headers = xdebug_get_headers();
            header_remove();
            ob_end_flush();
            ob_clean();

            $this->assertStringContainsString('Content-Type: text/javascript;charset="UTF-8"', implode("\n", $headers));
            $this->assertStringContainsString('X-XSS-Protection: 1; mode=block', implode("\n", $headers));
            $this->assertStringContainsString('X-Frame-Options: SAMEORIGIN', implode("\n", $headers));
        } else {
            $this->assertTrue(true, "Preventing Risky warning.");
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_combineScripts()
    {
        if (((float)phpversion()) >= 5.3) {
            $this->reflectionMethod = new ReflectionMethod('\INTERMediator\GenerateJSCode', 'combineScripts');
            $this->reflectionMethod->setAccessible(true);
            $currentDir = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR .
                'src' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
            $content = $this->reflectionMethod->invokeArgs($this->generater, array($currentDir));
            $jsLibDir = dirname(dirname($currentDir)) . DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR;
            $method = new ReflectionMethod('\INTERMediator\GenerateJSCode', 'readJSSource');
            $method->setAccessible(true);
            $partOfCode = $method->invokeArgs($this->generater, array($jsLibDir . 'jssha/dist/sha.js'));
            $this->assertStringContainsString($partOfCode, $content);
        }
    }

}