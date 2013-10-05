<?php
/**
 * DataConverter_MySQLDateTime_Test file
 */
require_once('../INTER-Mediator/INTER-Mediator.php');
require_once('../INTER-Mediator/DataConverter_MySQLDateTime.php');

class DataConverter_MySQLDateTime_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        
        $this->dataconverter = new DataConverter_MySQLDateTime();
    }
    
    public function test_converterFromDBtoUser()
    {
        $testName = "Check converterFromDBtoUser function in DataConverter_MySQLDateTime.php.";
        
        $datetimeString = "2000-01-05 12:34:56";
        $convertedDatetimeString = "2000/01/05 12:34:56";
        $this->assertSame($this->dataconverter->converterFromDBtoUser($datetimeString), $convertedDatetimeString, $testName);

        $dateString = "2000-01-05";
        $convertedDateString = "2000/01/05";
        $this->assertSame($this->dataconverter->converterFromDBtoUser($dateString), $convertedDateString, $testName);

        $timeString = "12:34:56";
        $convertedTimeString = "12:34:56";
        $this->assertSame($this->dataconverter->converterFromDBtoUser($timeString), $convertedTimeString, $testName);
    }
}