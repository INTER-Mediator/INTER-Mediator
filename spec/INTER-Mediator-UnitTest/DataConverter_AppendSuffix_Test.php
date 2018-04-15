<?php
/**
 * DataConverter_AppendSuffix_Test file
 */
use \PHPUnit\Framework\TestCase;
use \INTERMediator\Data_Converter\DataConverter_AppendSuffix;
//require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
//require_once(dirname(__FILE__) . '/../Data_Converter/DataConverter_AppendSuffix.php');

class DataConverter_AppendSuffix_Test extends TestCase
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