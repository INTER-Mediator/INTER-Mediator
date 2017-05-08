<?php
/**
 * DataConverter_HTMLString_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../DataConverter_HTMLString.php');
require_once(dirname(__FILE__) . '/../DataConverter_MarkdownString.php');

class DataConverter_MarkdownString_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';

        $this->dataconverter = new DataConverter_MarkdownString();
    }

    public function test_converterFromDBtoUser()
    {
        $string = "-a\n-b";
        $expected = "<div class='_im_markdown'>"
            . "<ul class='_im_markdown_ul'>"
            . "<li class='_im_markdown_li'>a</li>"
            . "<li class='_im_markdown_li'>b</li>"
            . "</ul></div>";
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $string = "-a\n--b";
        $expected = "<div class='_im_markdown'>"
            . "<ul class='_im_markdown_ul'>"
            . "<li class='_im_markdown_li'>a"
            . "<ul class='_im_markdown_ul'><li class='_im_markdown_li'>b</li></ul>"
            . "</li></ul></div>";
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $string = "-a\n--b\n--c\n-a\n--b\n--c";
        $expected = "<div class='_im_markdown'>"
            . "<ul class='_im_markdown_ul'>"
            . "<li class='_im_markdown_li'>a"
            . "<ul class='_im_markdown_ul'><li class='_im_markdown_li'>b</li>"
            . "<li class='_im_markdown_li'>c</li></ul></li>"
            . "<li class='_im_markdown_li'>a"
            . "<ul class='_im_markdown_ul'><li class='_im_markdown_li'>b</li>"
            . "<li class='_im_markdown_li'>c</li></ul></li>"
            . "</ul></div>";
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));
    }
}
