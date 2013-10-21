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
        define('TRAVIS_CI' , (bool) getenv('TRAVIS_PHP_VERSION'));
        
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
        
        $this->dataconverter = new DataConverter_FMDateTime();
    }
    
    public function test_converterFromDBtoUser()
    {
        $testName = 'Check converterFromDBtoUser function in DataConverter_FMDateTime.php.';
        
        $datetimeString = '01/05/2000 12:34:56';
        if (getenv('TRAVIS_PHP_VERSION')) {
            $convertedDatetimeString = "01/05/00 12:34:56";  // for Travis CI
        } else {
            $convertedDatetimeString = strftime('%x %H:%M:%S', strtotime('01/05/00 12:34:56'));
        }
        $this->assertSame($this->dataconverter->converterFromDBtoUser($datetimeString), $convertedDatetimeString, $testName);

        $dateString = '01/05/2000';
        if (TRAVIS_CI) {
            $convertedDateString = "01/05/00";  // for Travis CI
        } else {
            $convertedDateString = strftime('%x', strtotime('01/05/00'));
        }
        $this->assertSame($this->dataconverter->converterFromDBtoUser($dateString), $convertedDateString, $testName);

        $timeString = '12:34:56';
        $convertedTimeString = '12:34:56';
        $this->assertSame($this->dataconverter->converterFromDBtoUser($timeString), $convertedTimeString, $testName);
    }
}