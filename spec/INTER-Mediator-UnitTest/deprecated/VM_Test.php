<?php

namespace deprecated;

use INTERMediator\IMUtil;
use PHPUnit\Framework\TestCase;

class VM_Test extends TestCase
{

    /**
     * Test check Version String.
     *
     * @return void
     */
    public function test_checkVersionString()
    {
        $expected = '';
        $imPath = IMUtil::pathToINTERMediator();
        $content = file_get_contents($imPath . DIRECTORY_SEPARATOR . 'dist-docs' . DIRECTORY_SEPARATOR . 'change_log.txt');
        $this->assertNotFalse($content, 'The content should have a value.');
        $pos = strpos($content, 'Ver.');
        if ($pos !== FALSE) {
            $pos2 = strpos(substr($content, $pos + 4, strlen($content) - $pos + 1), ' ');
            $expected = substr($content, $pos + 4, intval($pos2));
        }

        $version = '-';
        $fPath = $imPath . DIRECTORY_SEPARATOR . 'composer.json';
        $fContents = file_get_contents($fPath);
        $this->assertNotFalse($fContents, 'The content should have a value.');
        $content = json_decode($fContents);
        if ($content) {
            $version = $content->version;
        }

        $this->assertEquals($version, $expected);
    }

}
