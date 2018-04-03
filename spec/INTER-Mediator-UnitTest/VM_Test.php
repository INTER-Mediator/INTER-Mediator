<?php

class VM_Test extends PHPUnit_Framework_TestCase
{

    public function test_checkVersionString()
    {
        $expected = '';
        $content = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'dist-docs' . DIRECTORY_SEPARATOR . 'change_log.txt');
        $pos = strpos($content, 'Ver.');
        if ($pos !== FALSE) {
            $pos2 = strpos(substr($content, $pos + 4, strlen($content) - $pos + 1), ' ');
            $expected = substr($content, $pos + 4, $pos2);
        }

        $version = '-';
        $cmd = 'php -f "'. dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'metadata.json' . '"';
        exec($cmd, $output);
        if (isset($output[0])) {
            $content = json_decode($output[0]);
            if ($content) {
                $version = $content->version;
            }
        }

        $this->assertEquals($version, $expected);
    }

}
