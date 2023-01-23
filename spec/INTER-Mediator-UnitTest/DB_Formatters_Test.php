<?php
/**
 * DB_Formatters_Test file
 */

use INTERMediator\DB\Formatters;
use PHPUnit\Framework\TestCase;

class DB_Formatters_Test extends TestCase
{
    private $dataconverter_htmlstring;

    public function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';

        $this->dataconverter_htmlstring = new Formatters();
        $this->dataconverter_htmlstring->setFormatter(array(
            array('field' => 'f1', 'converter-class' => 'HTMLString'),
            array('field' => 'f2', 'converter-class' => 'HTMLString', 'parameter' => false),
            array('field' => 'f3', 'converter-class' => 'HTMLString', 'parameter' => 'false'),
            array('field' => 'f4', 'converter-class' => 'HTMLString', 'parameter' => true),
            array('field' => 'f5', 'converter-class' => 'HTMLString', 'parameter' => 'true'),
            array('field' => 'f6', 'converter-class' => 'HTMLString', 'parameter' => 'autolink'),
            array('field' => 'f7', 'converter-class' => 'HTMLString', 'parameter' => 'noescape'),
        ));
    }

    public function test_formatterFromDB()
    {
        $expected = '';
        $string = '';
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f1', $string));

        $expected = '<br />';
        $string = "\n";
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f1', $string));

        $expected = 'http://inter-mediator.org/';
        $string = 'http://inter-mediator.org/';
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f1', $string));
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f2', $string));
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f3', $string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>';
        $string = 'http://inter-mediator.org/';
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f4', $string));
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f5', $string));
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f6', $string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a><br />';
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f7', $string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterFromDB('f8', $string));
    }

    public function test_formatterToDB()
    {
        $expected = '';
        $string = '';
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterToDB('f1', $string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterToDB('f1', $string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterToDB('f4', $string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterToDB('f7', $string));

        $expected = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $string = '<a href="http://inter-mediator.org/" target="_blank">http://inter-mediator.org/</a>' . "\n";
        $this->assertSame($expected, $this->dataconverter_htmlstring->formatterToDB('f8', $string));
    }

}
