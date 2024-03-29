<?php
/**
 * DataConverter_HTMLString_Test file
 */

namespace Text;

use INTERMediator\Data_Converter\HTMLString;
use PHPUnit\Framework\TestCase;

class DataConverter_HTMLString_Test extends TestCase
{
    private $dataconverter;
    private $dataconverter2;
    private $dataconverter3;
    private $dataconverterForLinking;
    private $dataconverterForLinking2;
    private $dataconverterForLinking3;
    private $dataconverterWithoutEscaping;

    public function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';

        $this->dataconverter = new HTMLString();
        $this->dataconverter2 = new HTMLString(false);
        $this->dataconverter3 = new HTMLString(0);
        $this->dataconverterForLinking = new HTMLString(true);
        $this->dataconverterForLinking2 = new HTMLString('true');
        $this->dataconverterForLinking3 = new HTMLString('autolink');
        $this->dataconverterWithoutEscaping = new HTMLString('noescape');
    }

    public function test_converterFromUserToDB()
    {
        $expected = '';
        $string = '';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverterForLinking->converterFromUserToDB($string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverterWithoutEscaping->converterFromUserToDB($string));
    }

    public function test_converterFromDBtoUser()
    {
        $expected = '';
        $string = '';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '<br />';
        $string = "\n";
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '<br />';
        $string = "\r\n";
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '&gt;';
        $string = '>';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '&lt;';
        $string = '<';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '&#39;';
        $string = "'";
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '&quot;';
        $string = '"';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '&amp;';
        $string = '&';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = 'http://inter-mediator.org/';
        $string = 'http://inter-mediator.org/';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));
        $this->assertSame($expected, $this->dataconverter2->converterFromDBtoUser($string));
        $this->assertSame($expected, $this->dataconverter3->converterFromDBtoUser($string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>';
        $string = 'http://inter-mediator.org/';
        $this->assertSame($expected, $this->dataconverterForLinking->converterFromDBtoUser($string));
        $this->assertSame($expected, $this->dataconverterForLinking2->converterFromDBtoUser($string));
        $this->assertSame($expected, $this->dataconverterForLinking3->converterFromDBtoUser($string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a><br />';
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverterWithoutEscaping->converterFromDBtoUser($string));
    }
}
