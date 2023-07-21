<?php
/**
 * DataConverter_NullZeroString_Test file
 */
use PHPUnit\Framework\TestCase;
use INTERMediator\Data_Converter\NullZeroString;

class DataConverter_NullZeroString_Test extends TestCase
{
    private $dataconverter;

    public function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        
        $this->dataconverter = new NullZeroString();
    }
    
    public function test_converterFromUserToDB()
    {
        $string = '';
        $this->assertNull($this->dataconverter->converterFromUserToDB($string));

        $expected = 'Test';
        $string = 'Test';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($string));
    }
    
    public function test_converterFromDBtoUser()
    {
        $expected = '';
        $fieldValue = null;
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($fieldValue));

        $expected = 'Test';
        $string = 'Test';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));
    }
}
