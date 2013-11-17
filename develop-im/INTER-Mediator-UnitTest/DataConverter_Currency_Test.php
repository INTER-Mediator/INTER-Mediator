<?php
/**
 * DataConverter_Currency_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator/INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../INTER-Mediator/DataConverter_NumberBase.php');
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
        if (getenv('TRAVIS') === 'true') {
            $convertedString = '1000';  // for Travis CI (temporary)
        } else {
            $convertedString = '¥1,000';
        }
        $this->assertEquals($this->dataconverter->converterFromDBtoUser($string), $convertedString);
    }

    public function test_converterFromUserToDB()
    {
        $string = '1000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);

        $string = '1,000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);

        if (getenv('TRAVIS') === 'true') {
            $string = '10,000';
        } else {
            $string = '¥10,000';
        }
        $convertedString = '10000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);
    }
}