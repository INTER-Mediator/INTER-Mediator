<?php
/**
 * DataConverter_Currency_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator/INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../INTER-Mediator/DataConverter_Currency.php');

class DataConverter_Currency_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        
        $this->dataconverter = new DataConverter_Currency();
    }
    
    public function test_converterFromDBtoUser()
    {
        $string = '1000';
        $convertedString = '¥1,000';
        $this->assertEquals($this->dataconverter->converterFromDBtoUser($string), $convertedString);
    }

    public function test_converterFromUserToDB()
    {
        $string = '1000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);

        $string = '¥1000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);

        $string = '¥1,000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);
    }
}