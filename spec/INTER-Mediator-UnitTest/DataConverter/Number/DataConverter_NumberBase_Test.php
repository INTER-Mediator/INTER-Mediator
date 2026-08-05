<?php
/**
 * DataConverter_NumberBase_Test file
 */

namespace Number;

use INTERMediator\Data_Converter\NumberBase;
use INTERMediator\Locale\IMLocale;
use PHPUnit\Framework\TestCase;

class DataConverter_NumberBase_Test extends TestCase
{
    /**
     * The dataconverter.
     *
     * @var mixed
     */
    private $dataconverter;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        setlocale(LC_ALL, 'ja_JP', 'ja');
        $this->dataconverter = new NumberBase();

//        $locInfo = localeconv();
//        $this->decimalMark = $locInfo['mon_decimal_point'];
//        if (strlen($this->decimalMark) == 0) {
//            $this->decimalMark = '.';
//        }
//        $this->thSepMark = $locInfo['mon_thousands_sep'];
    }

    /**
     * Test converter From User To DBIM Locale.
     *
     * @return void
     */
    public function test_converterFromUserToDBIMLocale(): void
    {
        $expected = '100';
        $string = '100';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '1000';
        $string = '1,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000';
        $string = '10,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '100000';
        $string = '100,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '1000000';
        $string = '1,000,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000000.1';
        $string = '10,000,000.1';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));
    }

    /**
     * Test converter From User To DB Intl Locale.
     *
     * @return void
     */
    public function test_converterFromUserToDBIntlLocale(): void
    {
        $expected = '100';
        $string = '100';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '1000';
        $string = '1,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000';
        $string = '10,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '100000';
        $string = '100,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '1000000';
        $string = '1,000,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000000.1';
        $string = '10,000,000.1';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));
    }
}