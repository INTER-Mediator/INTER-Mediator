<?php
/**
 * DataConverter_FMDateTime_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../DataConverter_FMDateTime.php');

class DataConverter_FMDateTime_Test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';
        
        $this->dataconverter = new DataConverter_FMDateTime();
    }
    
    public function test_converterFromDBtoUser()
    {
        $expected = '';
        $string = '';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));

        if (getenv('TRAVIS') === 'true') {
            $expected = '01/05/00 12:34:56';  // for Travis CI
        } else {
            $expected = strftime('%x %H:%M:%S', strtotime('01/05/00 12:34:56'));
        }
        $datetimeString = '01/05/2000 12:34:56';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($datetimeString));

        if (getenv('TRAVIS') === 'true') {
            $expected = '01/05/00';  // for Travis CI
        } else {
            $expected = strftime('%x', strtotime('01/05/00'));
        }
        $dateString = '01/05/2000';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($dateString));

        $expected = '12:34:56';
        $timeString = '12:34:56';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($timeString));
    }
    
    public function test_converterFromUserToDB()
    {
        $expected = '';
        $string = '';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '12:34:56';
        $timeString = '12:34:56';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($timeString));
    }

    public function test_dateArrayFromFMDate()
    {
        $expected = '';
        $string = '';
        $this->assertSame($expected, $this->dataconverter->dateArrayFromFMDate($string));

        $expected = array(
            'unixtime' => '947043296',
            'year' => '2000',
            'jyear' => '平成12年',
            'month' => '01',
            'day' => '05',
            'hour' => '12',
            'minute' => '34',
            'second' => '56',
            'weekdayName' => '水',
            'weekday' => '3',
            'longdate' => '2000/01/05',
            'jlongdate' => '平成 12 年 1 月 5 日 水曜日'
        );
        $string = '01/05/2000 12:34:56';
        $this->assertSame($expected, $this->dataconverter->dateArrayFromFMDate($string));
    }
}