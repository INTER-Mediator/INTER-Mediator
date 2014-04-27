<?php
/**
 * DataConverter_NullZeroString_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../DataConverter_NullZeroString.php');

class DataConverter_NullZeroString_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        
        $this->dataconverter = new DataConverter_NullZeroString();
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
