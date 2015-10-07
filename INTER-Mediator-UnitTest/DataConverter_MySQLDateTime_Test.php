<?php
/**
 * DataConverter_MySQLDateTime_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../DataConverter_MySQLDateTime.php');

class DataConverter_MySQLDateTime_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        
        $this->dataconverter = new DataConverter_MySQLDateTime();
    }
    
    public function test_converterFromDBtoUser()
    {
        $expected = '';
        $string = '';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '';
        $string = null;
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '';
        $string = '0000-00-00';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = '';
        $string = '1969-12-31';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        $expected = ' ';
        $string = ' ';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        if (getenv('TRAVIS') === 'true') {
            $expected = '01/05/00 12:34:56';  // for Travis CI
        } else {
            $expected = strftime('%x %H:%M:%S', strtotime('01/05/00 12:34:56'));
        }
        $datetimeString = '2000-01-05 12:34:56';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($datetimeString));

        if (getenv('TRAVIS') === 'true') {
            $expected = '01/05/00';  // for Travis CI
        } else {
            $expected = strftime('%x', strtotime('01/05/00'));
        }
        $dateString = '2000-01-05';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($dateString));

        $expected = '12:34:56';
        $timeString = '12:34:56';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($timeString));
    }
    
    public function test_converterFromUserToDB()
    {
        $expected = null;
        $string = '';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '2013-12-31';
        $dateString = '2013-12-31';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($dateString));

        $expected = '2013-12-31';
        $dateString = '2013/12/31';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($dateString));

        $expected = '2013-12-31';
        $dateString = '2013.12.31';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($dateString));

        $expected = '12:34:56';
        $timeString = '12:34:56';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($timeString));
    }
}
