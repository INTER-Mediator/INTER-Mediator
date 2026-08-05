<?php
/**
 * DataConverter_AppendSuffix_Test file
 */

namespace Text;

use INTERMediator\Data_Converter\AppendSuffix;
use PHPUnit\Framework\TestCase;

class DataConverter_AppendSuffix_Test extends TestCase
{
    /**
     * The dataconverter.
     *
     * @var AppendSuffix
     */
    private AppendSuffix $dataconverter;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ja';

        $this->dataconverter = new AppendSuffix('円');
    }

    /**
     * Test converter From DB to User.
     *
     * @return void
     */
    public function test_converterFromDBtoUser(): void
    {
        $string = '1000';
        $convertedString = '1000円';
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

        $string = '1000円';
        $convertedString = '1000';
        $this->assertEquals($this->dataconverter->converterFromUserToDB($string), $convertedString);
    }
}