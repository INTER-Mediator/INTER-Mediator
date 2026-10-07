<?php
/**
 * DataConverter_NullZeroString_Test file
 */

namespace Text;

use INTERMediator\Data_Converter\NullZeroString;
use PHPUnit\Framework\TestCase;

class DataConverter_NullZeroString_Test extends TestCase
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

        $this->dataconverter = new NullZeroString();
    }

    /**
     * Test converter From User To DB.
     *
     * @return void
     */
    public function test_converterFromUserToDB(): void
    {
        $string = '';
        $this->assertNull($this->dataconverter->converterFromUserToDB($string));

        $expected = 'Test';
        $string = 'Test';
        $this->assertSame($expected, $this->dataconverter->converterFromUserToDB($string));
    }

    /**
     * Test converter From DB to User.
     *
     * @return void
     */
    public function test_converterFromDBtoUser(): void
    {
        $expected = '';
        $fieldValue = null;
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($fieldValue));

        $expected = 'Test';
        $string = 'Test';
        $this->assertSame($expected, $this->dataconverter->converterFromDBtoUser($string));
    }
}
