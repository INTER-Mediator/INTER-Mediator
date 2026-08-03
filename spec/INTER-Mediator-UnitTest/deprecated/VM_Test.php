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
        $pos = strpos((string)$content, 'Ver.');
        if ($pos !== FALSE) {
            $pos2 = strpos(substr((string)$content, $pos + 4, strlen((string)$content) - $pos + 1), ' ');
            $expected = substr((string)$content, $pos + 4, $pos2 ? intval($pos2): 0);
        }

        $version = '-';
        $fPath = $imPath . DIRECTORY_SEPARATOR . 'composer.json';
        $content = json_decode((string)file_get_contents($fPath));
        if ($content) {
            $version = $content->version;
        }

        $this->assertEquals($version, $expected);
    }

}
