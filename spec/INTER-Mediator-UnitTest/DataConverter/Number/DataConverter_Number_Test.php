<?php
/**
 * DataConverter_Number_Test file
 */

namespace Number;

use INTERMediator\Data_Converter\Number;
use INTERMediator\Locale\IMLocale;
use PHPUnit\Framework\TestCase;

class DataConverter_Number_Test extends TestCase
{
    private $dataconverter;
    private $dataconverter2;

    public function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        setlocale(LC_ALL, 'ja_JP', 'ja');
        $this->dataconverter = new Number();
        $this->dataconverter2 = new Number(TRUE);
//
//        $locInfo = localeconv();
//        $this->thSepMark = $locInfo['mon_thousands_sep'];
    }

    public function test_converterFromDBtoUserIMLocale()
    {
        IMLocale::$alwaysIMClasses = true;

        $expected = '100';
        $string = '100';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '1,000';
        $string = '1000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '10,000';
        $string = '10000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '100,000';
        $string = '100000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '1,000,000';
        $string = '1000000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '1,000,000';
        $string = '1000000.0';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '0';
        $string = '';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '';
        $string = '';
        $this->assertEquals($expected, $this->dataconverter2->converterFromDBtoUser($string));
    }

    public function test_converterFromDBtoUserIntlLocale()
    {
        IMLocale::$alwaysIMClasses = false;

        $expected = '100';
        $string = '100';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '1,000';
        $string = '1000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '10,000';
        $string = '10000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '100,000';
        $string = '100000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '1,000,000';
        $string = '1000000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '1,000,000';
        $string = '1000000.0';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '0';
        $string = '';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '';
        $string = '';
        $this->assertEquals($expected, $this->dataconverter2->converterFromDBtoUser($string));
    }
}