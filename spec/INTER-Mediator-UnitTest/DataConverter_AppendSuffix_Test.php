<?php
/**
 * DataConverter_AppendSuffix_Test file
 */
use PHPUnit\Framework\TestCase;
use INTERMediator\Data_Converter\AppendSuffix;

class DataConverter_AppendSuffix_Test extends TestCase
{
    public function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        
        $this->dataconverter = new AppendSuffix('円');
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