<?php
/**
 * DataConverter_AppendPrefix_Test file
 */

namespace Text;

use INTERMediator\Data_Converter\AppendPrefix;
use PHPUnit\Framework\TestCase;

class DataConverter_AppendPrefix_Test extends TestCase
{
    private $dataconverter;

    public function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';

        $this->dataconverter = new AppendPrefix('￥');
    }

    public function test_converterFromDBtoUser()
    {
        $string = '1000';
        $convertedString = '￥1000';
        $this->assertEquals($this->dataconverter->converterFromDBtoUser($string), $convertedString);
    }

    public function test_converterFromUserToDB()
    {
        $string = '1000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);

        $string = '￥1000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);
    }
}