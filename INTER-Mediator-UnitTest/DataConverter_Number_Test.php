<?php
/**
 * DataConverter_Number_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../DataConverter_Number.php');

class DataConverter_Number_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        setlocale (LC_ALL, 'ja_JP', 'ja');
        $this->dataconverter = new DataConverter_Number();
        $this->dataconverter2 = new DataConverter_Number(TRUE);
//
//        $locInfo = localeconv();
//        $this->thSepMark = $locInfo['mon_thousands_sep'];
    }

    public function test_converterFromDBtoUser()
    {
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