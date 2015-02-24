<?php
/**
 * GenerateJSCode_Test file
 */
require_once(dirname(__FILE__) . '/../GenerateJSCode.php');

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
    function test___construct()    {
        $testName = "Check __construct function in GenerateJSCode.php.";
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
            if (function_exists('xdebug_get_headers')) {
                $this->reflectionMethod = new ReflectionMethod('GenerateJSCode', 'combineScripts');
                $this->reflectionMethod->setAccessible(true);
                $currentDir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
                ob_start();
                $content = $this->reflectionMethod->invokeArgs($this->generater, array($currentDir));
                ob_clean();
                
                $jsLibDir = $currentDir . 'lib' . DIRECTORY_SEPARATOR . 'js_lib' . DIRECTORY_SEPARATOR;
                $this->assertContains(';' . file_get_contents($jsLibDir . 'tinySHA1.js'), $content);
            }
        }
    }

}