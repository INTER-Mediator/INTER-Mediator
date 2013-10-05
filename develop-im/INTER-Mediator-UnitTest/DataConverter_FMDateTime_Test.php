<?php
/**
 * DataConverter_FMDateTime_Test file
 */
require_once('../INTER-Mediator/INTER-Mediator.php');
require_once('../INTER-Mediator/DataConverter_FMDateTime.php');

class DataConverter_FMDateTime_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        
        $this->dataconverter = new DataConverter_FMDateTime();
    }
    
    public function test_converterFromDBtoUser()
    {
        $testName = "Check converterFromDBtoUser function in DataConverter_FMDateTime.php.";
        
        $datetimeString = "01/05/2000 12:34:56";
        //$convertedDatetimeString = "2000/01/05 12:34:56";
        $convertedDatetimeString = "01/05/00 12:34:56";  // for Travis CI
        $this->assertSame($this->dataconverter->converterFromDBtoUser($datetimeString), $convertedDatetimeString, $testName);

        $dateString = "01/05/2000";
        //$convertedDateString = "2000/01/05";
        $convertedDateString = "01/05/00";  // for Travis CI
        $this->assertSame($this->dataconverter->converterFromDBtoUser($dateString), $convertedDateString, $testName);

        $timeString = "12:34:56";
        $convertedTimeString = "12:34:56";
        $this->assertSame($this->dataconverter->converterFromDBtoUser($timeString), $convertedTimeString, $testName);
    }
}