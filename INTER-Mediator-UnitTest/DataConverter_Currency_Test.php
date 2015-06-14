<?php
/**
 * DataConverter_Currency_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../DataConverter_Currency.php');

class DataConverter_Currency_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        setlocale(LC_ALL, 'ja_JP', 'ja');

        $this->dataconverter = new DataConverter_Currency();
//
//        $locInfo = localeconv();
//        $this->thSepMark = $locInfo['mon_thousands_sep'];
//        $this->currencyMark = $locInfo['currency_symbol'];
    }

    public function test_converterFromDBtoUser()
    {
        $currencyMark = "¥";
        $thSepMark = ",";
        $expected = $currencyMark . '1' . $thSepMark . '000';
        $string = '1000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));
    }

    public function test_converterFromUserToDB()
    {
        $expected = '100';
        $string = '100';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '1000';
        $string = '¥1,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000';
        $string = '¥10,000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000.1';
        $string = '¥10,000.1';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));
    }
}