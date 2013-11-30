<?php
/**
 * DataConverter_AppendSuffix_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../DataConverter_AppendSuffix.php');

class DataConverter_AppendSuffix_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        
        $this->dataconverter = new DataConverter_AppendSuffix('円');
    }
    
    public function test_converterFromDBtoUser()
    {
        $string = '1000';
        $convertedString = '1000円';
        $this->assertEquals($this->dataconverter->converterFromDBtoUser($string), $convertedString);
    }

    public function test_converterFromUserToDB()
    {
        $string = '1000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);

        $string = '1000円';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);
    }
}