<?php
/**
 * DataConverter_HTMLString_Test file
 */

namespace Text;

use INTERMediator\Data_Converter\HTMLString;
use PHPUnit\Framework\TestCase;

class DataConverter_HTMLString_Test extends TestCase
{
    /**
     * The dataconverter.
     *
     * @var mixed
     */
    private $dataconverter;
    /**
     * The dataconverter 2.
     *
     * @var mixed
     */
    private $dataconverter2;
    /**
     * The dataconverter 3.
     *
     * @var mixed
     */
    private $dataconverter3;
    /**
     * The dataconverter for linking.
     *
     * @var mixed
     */
    private $dataconverterForLinking;
    /**
     * The dataconverter for linking 2.
     *
     * @var mixed
     */
    private $dataconverterForLinking2;
    /**
     * The dataconverter for linking 3.
     *
     * @var mixed
     */
    private $dataconverterForLinking3;
    /**
     * The dataconverter without escaping.
     *
     * @var mixed
     */
    private $dataconverterWithoutEscaping;

    /**
     * Set up the test environment.
     *
     * @return void
     */
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

    /**
     * Test converter From User To DB.
     *
     * @return void
     */
    public function test_converterFromUserToDB(): void
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

    /**
     * Test converter From DB to User.
     *
     * @return void
     */
    public function test_converterFromDBtoUser(): void
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
