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
        $this->expectOutputString('INTERMediatorLog.setErrorMessage("PHP extension \"mbstring\" is required for running INTER-Mediator. ");' . "\n");
        $this->generater->generateErrorMessageJS('PHP extension "mbstring" is required for running INTER-Mediator.' . "\n");
    }

    /**
     * Test generate Initial JS Code.
     *
     * @return void
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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
     * @throws ReflectionException
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_combineScripts(): void
    {
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

    /**
     * Test that the entry path embedded in generated JS code is escaped against script injection.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_entryPathIsEscaped(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['QUERY_STRING'] = 'a=";alert(1);//</script>';
        ob_start();
        $this->generater->generateInitialJSCode(null, null, ['db-class' => 'PDO'], false);
        $output = strval(ob_get_clean());
        $this->assertStringNotContainsString('</script>', $output);
        $this->assertStringContainsString('?a=\\";alert(1);//\\u003C/script\\u003E"', $output);
        $this->assertStringNotContainsString('?a=";alert(1)', $output);
    }

    /**
     * Test that readJSSource skips the line following an '@@IM@@IgnoringNextLine' marker.
     *
     * @return void
     */
    public function test_readJSSourceIgnoringNextLine(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'imjs');
        file_put_contents($file, "a\n// @@IM@@IgnoringNextLine\nb\nc\n// @@IM@@IgnoringNextLine");
        $method = new ReflectionMethod('\INTERMediator\GenerateJSCode', 'readJSSource');
        $this->assertEquals("a\nc", $method->invokeArgs($this->generater, [$file]));
        unlink($file);
    }

    /**
     * Test that pathFromDocumentRoot converts a filesystem path to a document-root relative path.
     *
     * @return void
     */
    public function test_pathFromDocumentRoot(): void
    {
        $method = new ReflectionMethod('\INTERMediator\GenerateJSCode', 'pathFromDocumentRoot');
        $imRoot = dirname(__FILE__, 4);
        $parent = dirname($imRoot);
        $this->assertSame('/' . basename($imRoot) . '/src',
            $method->invokeArgs($this->generater, [$imRoot . '/src', $parent]));
        $this->assertSame('/' . basename($imRoot) . '/src',
            $method->invokeArgs($this->generater, [$imRoot . '/src', $parent . '/']));
        $this->assertSame('', $method->invokeArgs($this->generater, [$imRoot, $imRoot]));
        $this->assertNull($method->invokeArgs($this->generater, [$parent, $imRoot]));
        $this->assertNull($method->invokeArgs($this->generater, [$imRoot . '-not-exist', $parent]));
        $this->assertNull($method->invokeArgs($this->generater, [$imRoot . '/src', $imRoot . '/sr']));
    }

    /**
     * Test that decideServiceServerHost resolves the service server host from the configured value,
     * HTTP_HOST, SERVER_ADDR, or the localhost fallback.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_decideServiceServerHost(): void
    {
        $method = new ReflectionMethod('\INTERMediator\GenerateJSCode', 'decideServiceServerHost');
        $decide = fn($configured, $httpHost, $serverAddr) => (function () use ($method, $configured, $httpHost, $serverAddr) {
            unset($_SERVER['HTTP_HOST'], $_SERVER['SERVER_ADDR']);
            if (!is_null($httpHost)) {
                $_SERVER['HTTP_HOST'] = $httpHost;
            }
            if (!is_null($serverAddr)) {
                $_SERVER['SERVER_ADDR'] = $serverAddr;
            }
            return $method->invokeArgs($this->generater, [$configured]);
        })();

        $this->assertSame('ss.example.com', $decide('ss.example.com', 'www.example.com', '172.17.0.2'));
        $this->assertSame('www.example.com', $decide(null, 'www.example.com', '172.17.0.2'));
        $this->assertSame('www.example.com', $decide('', 'www.example.com:8080', '172.17.0.2'));
        $this->assertSame('www.example.com', $decide('  ', 'www.example.com', null));
        $this->assertSame('[::1]', $decide(null, '[::1]:8080', null));
        $this->assertSame('[::1]', $decide(null, '[::1]', null));
        $this->assertSame('172.17.0.2', $decide(null, null, '172.17.0.2'));
        $this->assertSame('[::1]', $decide(null, '', '::1'));
        $this->assertSame('localhost', $decide(null, null, null));
    }

    /**
     * Test that the generated service server URL prefers HTTP_HOST over SERVER_ADDR.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_serviceServerURLPrefersHttpHost(): void
    {
        $_SERVER['SERVER_ADDR'] = '172.17.0.2';
        \INTERMediator\Params::setVar('serviceServerHost', '');
        $output = $this->generateInitialCode();
        $this->assertStringContainsString('INTERMediatorOnPage.serviceServerURL="ws://localhost:', $output);
    }

    /**
     * Generate the initial JS code and capture its output.
     *
     * @param array<int|string, mixed>|null $dataSource The data source definitions.
     * @param array<int|string, mixed>|null $options The option definitions.
     * @param array<string, mixed> $dbSpec The DB specification.
     * @return string The captured JS output.
     */
    private function generateInitialCode(?array $dataSource = [], ?array $options = [],
                                         array  $dbSpec = ['db-class' => 'PDO']): string
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        ob_start();
        try {
            $this->generater->generateInitialJSCode($dataSource, $options, $dbSpec, false);
        } finally {
            $output = strval(ob_get_clean());
        }
        return $output;
    }

    /**
     * Test that the entry path falls back to SCRIPT_NAME when no referer or query string is set.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_entryPathWithoutReferer(): void
    {
        unset($_SERVER['HTTP_REFERER'], $_SERVER['QUERY_STRING']);
        $_SERVER['SCRIPT_NAME'] = '/app/def.php';
        $output = $this->generateInitialCode();
        $this->assertStringContainsString('INTERMediatorOnPage.getEntryPath=function(){return "/app/def.php";}', $output);
    }

    /**
     * Test that generated paths are empty when INTER-Mediator is outside the document root.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_pathsOutsideDocumentRoot(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = sys_get_temp_dir();
        $output = $this->generateInitialCode();
        $this->assertStringContainsString("INTERMediatorOnPage.getEditorPath=function(){return '';}", $output);
        $this->assertStringContainsString('INTERMediatorOnPage.getPathToIMRoot=function(){return "";}', $output);
    }

    /**
     * Test that generated paths are document-root relative when INTER-Mediator is inside the document root.
     * **** This code was generated by AI, but it should be considered more precisely. (msyk, 2026-10-26)****
     *
     * @return void
     */
//    #[RunInSeparateProcess]
//    #[PreserveGlobalState(false)]
//    public function test_pathsInsideDocumentRoot(): void
//    {
//        $imRoot = dirname(__FILE__, 4);
//        $_SERVER['DOCUMENT_ROOT'] = dirname($imRoot);
//        $_SERVER['SCRIPT_NAME'] = '/' . basename($imRoot) . '/samples/index.html';
//        $output = $this->generateInitialCode();
//        $imName = basename($imRoot);
//        $this->assertStringContainsString(
//            "INTERMediatorOnPage.getPathToIMRoot=function(){return \"/{$imName}\";}", $output);
//        if (is_file($imRoot . '/editors/defedit.html')) {
//            $this->assertStringContainsString(
//                "INTERMediatorOnPage.getEditorPath=function(){return \"/{$imName}/editors/defedit.html?target=../samples/index.html\";}",
//                $output);
//        }
//    }

    /**
     * Test that local-context items without a key are ignored in the generated JS code.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_localContextIgnoresItemsWithoutKey(): void
    {
        $output = $this->generateInitialCode([], ['local-context' => [
            ['key' => 'a', 'value' => 1],
            ['value' => 2],
            ['key' => 'b'],
        ]]);
        $this->assertStringContainsString("INTERMediatorOnPage.initLocalContext={'a':'1','b':''};", $output);
    }

    /**
     * Test that OAuth is reported unavailable when all configured providers are invalid.
     *
     * @return void
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_oAuthNotAvailableWhenAllProvidersInvalid(): void
    {
        \INTERMediator\Params::setVar('oAuth', ['NoSuchProvider' => ['ClientId' => 'x'], 'Broken' => 'not-array']);
        $output = $this->generateInitialCode();
        $this->assertStringContainsString('IMLibAuthenticationUI.isOAuthAvailable=false;', $output);
        $this->assertStringContainsString('IMLibAuthentication.oAuthParams={};', $output);
        $this->assertStringNotContainsString('oAuthParams["NoSuchProvider"]', $output);
    }

}
