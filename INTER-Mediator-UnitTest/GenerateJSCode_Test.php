<?php
/**
 * GenerateJSCode_Test file
 */
require_once(dirname(__FILE__) . '/../GenerateJSCode.php');
require_once(dirname(__FILE__) . '/../IMUtil.php');

class GenerateJSCode_Test extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
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
        $this->expectOutputString('INTERMediator.setErrorMessage("PHP extension \"mbstring\" is required for running INTER-Mediator. ");');
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
            ob_clean();
            
            $this->assertContains('Content-Type: text/javascript;charset="UTF-8"', $headers);
            $this->assertContains('X-XSS-Protection: 1; mode=block', $headers);
            $this->assertContains('X-Frame-Options: SAMEORIGIN', $headers);
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_combineScripts()
    {
        if (((float)phpversion()) >= 5.3) {
            $this->reflectionMethod = new ReflectionMethod('GenerateJSCode', 'combineScripts');
            $this->reflectionMethod->setAccessible(true);
            $currentDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
            $content = $this->reflectionMethod->invokeArgs($this->generater, array($currentDir));
            $jsLibDir = $currentDir . 'lib' . DIRECTORY_SEPARATOR . 'js_lib' . DIRECTORY_SEPARATOR;
            $this->assertContains(';' . file_get_contents($jsLibDir . 'tinySHA1.js'), $content);
        }
    }

}