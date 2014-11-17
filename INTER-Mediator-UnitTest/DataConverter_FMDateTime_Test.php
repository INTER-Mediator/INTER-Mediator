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

        $expected = '';
        $string = array();
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

        $expected = '1/13/2000 12:34:56';
        $timeString = '2000/01/13 12:34:56';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($timeString));

        $expected = '1/6/2000';
        $timeString = '2000/01/06';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($timeString));

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
            'unixtime' => '-1812227104',
            'year' => '1912',
            'jyear' => '明治45年',
            'month' => '07',
            'day' => '29',
            'hour' => '12',
            'minute' => '34',
            'second' => '56',
            'weekdayName' => '月',
            'weekday' => '1',
            'longdate' => '1912/07/29',
            'jlongdate' => '明治 45 年 7 月 29 日 月曜日'
        );
        $string = '07/29/1912 12:34:56';
        $this->assertSame($expected, $this->dataconverter->dateArrayFromFMDate($string));

        $expected = array(
            'unixtime' => '-1812140704',
            'year' => '1912',
            'jyear' => '大正元年',
            'month' => '07',
            'day' => '30',
            'hour' => '12',
            'minute' => '34',
            'second' => '56',
            'weekdayName' => '火',
            'weekday' => '2',
            'longdate' => '1912/07/30',
            'jlongdate' => '大正 元 年 7 月 30 日 火曜日'
        );
        $string = '07/30/1912 12:34:56';
        $this->assertSame($expected, $this->dataconverter->dateArrayFromFMDate($string));

        $expected = array(
            'unixtime' => '-1357676704',
            'year' => '1926',
            'jyear' => '大正15年',
            'month' => '12',
            'day' => '24',
            'hour' => '12',
            'minute' => '34',
            'second' => '56',
            'weekdayName' => '金',
            'weekday' => '5',
            'longdate' => '1926/12/24',
            'jlongdate' => '大正 15 年 12 月 24 日 金曜日'
        );
        $string = '12/24/1926 12:34:56';
        $this->assertSame($expected, $this->dataconverter->dateArrayFromFMDate($string));

        $expected = array(
            'unixtime' => '-1357590304',
            'year' => '1926',
            'jyear' => '昭和元年',
            'month' => '12',
            'day' => '25',
            'hour' => '12',
            'minute' => '34',
            'second' => '56',
            'weekdayName' => '土',
            'weekday' => '6',
            'longdate' => '1926/12/25',
            'jlongdate' => '昭和 元 年 12 月 25 日 土曜日'
        );
        $string = '12/25/1926 12:34:56';
        $this->assertSame($expected, $this->dataconverter->dateArrayFromFMDate($string));

        $expected = array(
            'unixtime' => '600147296',
            'year' => '1989',
            'jyear' => '昭和64年',
            'month' => '01',
            'day' => '07',
            'hour' => '12',
            'minute' => '34',
            'second' => '56',
            'weekdayName' => '土',
            'weekday' => '6',
            'longdate' => '1989/01/07',
            'jlongdate' => '昭和 64 年 1 月 7 日 土曜日'
        );
        $string = '01/07/1989 12:34:56';
        $this->assertSame($expected, $this->dataconverter->dateArrayFromFMDate($string));

        $expected = array(
            'unixtime' => '600233696',
            'year' => '1989',
            'jyear' => '平成元年',
            'month' => '01',
            'day' => '08',
            'hour' => '12',
            'minute' => '34',
            'second' => '56',
            'weekdayName' => '日',
            'weekday' => '0',
            'longdate' => '1989/01/08',
            'jlongdate' => '平成 元 年 1 月 8 日 日曜日'
        );
        $string = '01/08/1989 12:34:56';
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