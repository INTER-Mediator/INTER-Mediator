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
    private GenerateJSCode $generater;

    protected function setUp(): void
    {
        $_SERVER['SCRIPT_NAME'] = __FILE__;
        $this->generater = new GenerateJSCode();
    }

    /**
     * #[runInSeparateProcess]
     * #[preserveGlobalState(false)]
     */
    function test_generateAssignJS()
    {
        $this->expectOutputString('INTERMediatorOnPage.getEditorPath=function(){return \'\';};' . "\n");
        $this->generater->generateAssignJS('INTERMediatorOnPage.getEditorPath', 'function(){return \'\';}');
    }

    /**
     * #[runInSeparateProcess]
     * #[preserveGlobalState(false)]
     */
    function test_generateErrorMessageJS()
    {
        $this->expectOutputString('INTERMediatorLog.setErrorMessage("PHP extension \"mbstring\" is required for running INTER-Mediator. ");');
        $this->generater->generateErrorMessageJS('PHP extension "mbstring" is required for running INTER-Mediator.' . "\n");
    }

    /**
     * #[runInSeparateProcess]
     * #[preserveGlobalState(false)]
     */
    function test_generateInitialJSCode()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['REMOTE_ADDR'] = '';
        $this->expectOutputRegex('/INTERMediatorOnPage.serviceServerURL="ws:\/\/localhost:/');
        $this->generater->generateInitialJSCode([], [], ['db-class' => 'PDO'], false);
    }

    /**
     * #[runInSeparateProcess]
     * #[preserveGlobalState(false)]
     */
    function test_generateInitialJSCode2()
    {
        $_SERVER['HTTP_HOST'] = 'localhost:80';
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['REMOTE_ADDR'] = '';
        $this->expectOutputRegex('/INTERMediatorOnPage.serviceServerURL="ws:\/\/localhost:/');
        $this->generater->generateInitialJSCode([], [], ['db-class' => 'PDO'], false);
    }

    /**
     * #[runInSeparateProcess]
     * #[preserveGlobalState(false)]
     */
    function test_generateInitialJSCode3()
    {
        //$_SERVER['HTTP_HOST'] = '';
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['REMOTE_ADDR'] = '';
        $this->expectOutputRegex('/INTERMediatorOnPage.serviceServerURL="ws:\/\/localhost:/');
        $this->generater->generateInitialJSCode([], [], ['db-class' => 'PDO'], false);
    }

    /**
     * #[runInSeparateProcess]
     * #[preserveGlobalState(false)]
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
     * #[runInSeparateProcess]
     * #[preserveGlobalState(false)]
     */
    public function test_combineScripts()
    {
        if (((float)phpversion()) >= 5.3) {
            $reflectionMethod = new ReflectionMethod('\INTERMediator\GenerateJSCode', 'combineScripts');
            $reflectionMethod->setAccessible(true);
            $currentDir = dirname(__FILE__, 4) . DIRECTORY_SEPARATOR .
                'src' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
            $content = $reflectionMethod->invokeArgs($this->generater, array($currentDir));
            $jsLibDir = dirname($currentDir, 2) . DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR;
            $method = new ReflectionMethod('\INTERMediator\GenerateJSCode', 'readJSSource');
            $method->setAccessible(true);
            $partOfCode = $method->invokeArgs($this->generater, array($jsLibDir . 'jssha/dist/sha.js'));
            $this->assertStringContainsString($partOfCode, $content);
        }
    }

}