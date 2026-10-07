<?php
/**
 * DataConverter_AppendPrefix_Test file
 */

namespace Text;

use INTERMediator\Data_Converter\AppendPrefix;
use PHPUnit\Framework\TestCase;

class DataConverter_AppendPrefix_Test extends TestCase
{
    /**
     * The dataconverter.
     *
     * @var mixed
     */
    private $dataconverter;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';

        $this->dataconverter = new AppendPrefix('￥');
    }

    /**
     * Test converter From DB to User.
     *
     * @return void
     */
    public function test_converterFromDBtoUser(): void
    {
        $string = '1000';
        $convertedString = '￥1000';
        $this->assertEquals($this->dataconverter->converterFromDBtoUser($string), $convertedString);
    }

    /**
     * Test converter From User To DB.
     *
     * @return void
     */
    public function test_converterFromUserToDB(): void
    {
        $string = '1000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);

        $string = '￥1000';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);
    }
}