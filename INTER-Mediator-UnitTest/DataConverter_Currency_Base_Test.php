<?php
/**
 * DataConverter_Currency_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../DataConverter_Currency.php');

abstract class DataConverter_Currency_Base_Test extends PHPUnit_Framework_TestCase
{
    protected $currencyMark;
    protected $thSepMark;
    protected $dataconverter;

    public function test_converterFromDBtoUser()
    {
        $expected = "{$this->currencyMark}1{$this->thSepMark}000";
        $string = '1000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));
    }

    public function test_converterFromUserToDB()
    {
        $expected = '100';
        $string = '100';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '1000';
        $string = $this->currencyMark . '1' . $this->thSepMark . '000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000';
        $string = $this->currencyMark . '10' . $this->thSepMark . '000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000.1';
        $string = $this->currencyMark . '10' . $this->thSepMark . '000.1';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));
    }
}
