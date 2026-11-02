<?php
/**
 * FileURL_Test file
 */

use INTERMediator\DB\Proxy;
use INTERMediator\Media\FileURL;
use INTERMediator\Params;
use PHPUnit\Framework\TestCase;

class FileURL_Test extends TestCase
{
    private string $rootDir;

    protected function setUp(): void
    {
        $this->rootDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'im-fileurl-test-' . bin2hex(random_bytes(4));
        mkdir($this->rootDir, 0777, true);
        file_put_contents($this->rootDir . DIRECTORY_SEPARATOR . 'a.txt', 'CONTENT');
    }

    protected function tearDown(): void
    {
        Params::setVar('mediaRootDir', null);
        Params::setVar('allowedMediaFileRootDirs', null);
        unlink($this->rootDir . DIRECTORY_SEPARATOR . 'a.txt');
        rmdir($this->rootDir);
    }

    /**
     * Regression test: when `allowedMediaFileRootDirs` isn't configured but `mediaRootDir` is a
     * non-empty string, getMedia() must not throw a TypeError from array_reduce() receiving a
     * scalar instead of an array (see Params::getParameterValue's scalar-key/array-default pitfall).
     */
    public function testGetMediaWorksWhenOnlyMediaRootDirIsConfigured(): void
    {
        Params::setVar('mediaRootDir', $this->rootDir);
        Params::setVar('allowedMediaFileRootDirs', null);

        $fileURL = new FileURL();
        $dbProxy = $this->createMock(Proxy::class);
        $content = $fileURL->getMedia('a.txt', $this->rootDir . DIRECTORY_SEPARATOR . 'a.txt', $dbProxy);

        $this->assertSame('CONTENT', $content);
    }

    public function testGetMediaRejectsFileOutsideAllowedDirs(): void
    {
        Params::setVar('mediaRootDir', $this->rootDir);
        Params::setVar('allowedMediaFileRootDirs', null);

        $outside = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'im-fileurl-outside-' . bin2hex(random_bytes(4));
        file_put_contents($outside, 'SECRET');

        $fileURL = new FileURL();
        $dbProxy = $this->createMock(Proxy::class);
        try {
            $this->expectException(Exception::class);
            $fileURL->getMedia('x', $outside, $dbProxy);
        } finally {
            unlink($outside);
        }
    }
}
