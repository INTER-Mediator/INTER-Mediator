<?php
/**
 * Theme_Test file
 */

use INTERMediator\Params;
use INTERMediator\Theme;
use PHPUnit\Framework\TestCase;

class Theme_Test extends TestCase
{
    private string $altThemeDir;
    private ?Theme $theme = null;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->altThemeDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'im-theme-test-' . bin2hex(random_bytes(4));
        mkdir($this->altThemeDir . '/custom/css', 0777, true);
        mkdir($this->altThemeDir . '/custom/images', 0777, true);
        file_put_contents($this->altThemeDir . '/custom/css/a.css', implode("\n", [
            '.a{background:url(a.png)}',
            '.b{background:url("b.png")}',
            ".c{background:url( 'c.png' )}",
            '.d{background:url(data:image/png;base64,AAAA)}',
            '.e{background:url(https://example.com/e.png)}',
            '.f{background:url(/root/f.png)}',
            '.g{background:url(../images/g.png)}',
        ]));
        file_put_contents($this->altThemeDir . '/custom/images/a.png', 'PNGDATA');
        file_put_contents($this->altThemeDir . '/secret.txt', 'SECRET');

        Params::setVar('altThemePath', $this->altThemeDir);
        Params::setVar('accessLogLevel', 1);
        $_SERVER['DOCUMENT_ROOT'] = '/var/www';
        $_SERVER['SCRIPT_NAME'] = '/app/def.php';
    }

    /**
     * Tear down the test environment.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Params::setVar('altThemePath', null);
        Params::setVar('accessLogLevel', false);
        $_GET = [];
        foreach (new RecursiveIteratorIterator(
                     new RecursiveDirectoryIterator($this->altThemeDir, FilesystemIterator::SKIP_DOTS),
                     RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->altThemeDir);
    }

    /**
     * Issue a request to the Theme class and capture its output.
     *
     * @param array<string, string> $get The $_GET parameters to simulate.
     * @param string|null $deffile The deffile path to pass to Theme::processing().
     * @return string The captured output.
     */
    private function request(array $get, ?string $deffile = null): string
    {
        $_GET = $get;
        $this->theme = new Theme();
        ob_start();
        try {
            $this->theme->processing($deffile);
        } finally {
            $output = strval(ob_get_clean());
        }
        return $output;
    }

    /**
     * Data provider of malicious or invalid requests.
     *
     * @return array<string, array<int, array<string, string>>> The data set.
     */
    public static function maliciousRequestProvider(): array
    {
        return [
            'nonexistent theme with absolute path' => [['theme' => 'no-such-theme', 'type' => 'etc', 'name' => 'hosts']],
            'empty theme' => [['theme' => '', 'type' => 'etc', 'name' => 'hosts']],
            'missing theme' => [['type' => 'etc', 'name' => 'hosts']],
            'theme traversal' => [['theme' => '../../../../../../etc', 'type' => '', 'name' => 'hosts']],
            'name traversal' => [['theme' => 'custom', 'type' => 'images', 'name' => '../../secret.txt']],
            'type traversal' => [['theme' => 'custom', 'type' => '..', 'name' => 'secret.txt']],
            'theme dir escaping alt base' => [['theme' => '..', 'type' => '', 'name' => 'secret.txt']],
            'nonexistent file' => [['theme' => 'custom', 'type' => 'images', 'name' => 'none.png']],
            'directory as file' => [['theme' => 'custom', 'type' => 'images', 'name' => '']],
        ];
    }

    /**
     * Test that requests for resources outside the theme directory, or missing resources, are rejected.
     *
     * @param array<string, string> $get The $_GET parameters to simulate.
     * @return void
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('maliciousRequestProvider')]
    public function testRejectsOutsideOrMissingResources(array $get): void
    {
        $output = $this->request($get, null);
        $this->assertSame('', $output);
        $this->assertArrayHasKey('processing', $this->theme->getResultForLog());
    }

    /**
     * Test that an image file within a theme is returned correctly.
     *
     * @return void
     */
    public function testReturnsImageInTheme(): void
    {
        $this->assertSame('PNGDATA', $this->request(['theme' => 'custom', 'type' => 'images', 'name' => 'a.png']));
    }

    public function testReturnsFileInBuiltInTheme(): void
    {
        Params::setVar('altThemePath', null);
        $output = $this->request(['theme' => 'default', 'type' => 'images', 'name' => 'logo.gif']);
        $this->assertStringStartsWith('GIF', $output);
    }

    /**
     * Test that URLs within CSS files are replaced and encoded correctly.
     *
     * @return void
     */
    public function testCSSURLsAreReplacedAndEncoded(): void
    {
        $output = $this->request(['theme' => 'custom', 'type' => 'css'], '/var/www/app/def.php');
        $prefix = '/app/def.php?theme=custom&type=images&name=';
        $suffix = '&deffile=%2Fapp%2Fdef.php';
        $this->assertStringContainsString(".a{background:url(\"{$prefix}a.png{$suffix}\")}", $output);
        $this->assertStringContainsString(".b{background:url(\"{$prefix}b.png{$suffix}\")}", $output);
        $this->assertStringContainsString(".c{background:url(\"{$prefix}c.png{$suffix}\")}", $output);
        $this->assertStringContainsString('.d{background:url(data:image/png;base64,AAAA)}', $output);
        $this->assertStringContainsString('.e{background:url(https://example.com/e.png)}', $output);
        $this->assertStringContainsString('.f{background:url(/root/f.png)}', $output);
        $this->assertStringContainsString(".g{background:url(\"{$prefix}..%2Fimages%2Fg.png{$suffix}\")}", $output);
    }

    /**
     * Test that CSS injection attempts through the theme name parameter are encoded.
     *
     * @return void
     */
    public function testCSSInjectionThroughThemeNameIsEncoded(): void
    {
        mkdir($this->altThemeDir . '/x);}body{color:red', 0777, true);
        mkdir($this->altThemeDir . '/x);}body{color:red/css');
        file_put_contents($this->altThemeDir . '/x);}body{color:red/css/a.css', '.a{background:url(a.png)}');
        $output = $this->request(['theme' => 'x);}body{color:red', 'type' => 'css']);
        $this->assertStringNotContainsString('body{color:red', $output);
        $this->assertStringContainsString('theme=x%29%3B%7Dbody%7Bcolor%3Ared', $output);
    }

    /**
     * Test that a deffile path outside the document root is not embedded in the output.
     *
     * @return void
     */
    public function testDeffileOutsideDocumentRootIsNotEmbedded(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = '';
        $output = $this->request(['theme' => 'custom', 'type' => 'css'], '/secret/path/def.php');
        $this->assertStringNotContainsString('deffile', $output);
    }
}
