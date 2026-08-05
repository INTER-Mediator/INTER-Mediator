<?php
/**
 * DataConverter_Currency_Test file
 */

namespace Currency;

use PHPUnit\Framework\TestCase;

abstract class DataConverter_Currency_Base_Test extends TestCase
{
    /**
     * The currency mark.
     *
     * @var mixed
     */
    protected $currencyMark;
    /**
     * The th sep mark.
     *
     * @var mixed
     */
    protected $thSepMark;
    /**
     * The dataconverter.
     *
     * @var mixed
     */
    protected $dataconverter;

    /**
     * Test converter From DB to User.
     *
     * @return void
     */
    public function test_converterFromDBtoUser(): void
    {
        $expected = "{$this->currencyMark}1{$this->thSepMark}000";
        $string = '1000';
        $this->assertEquals($expected, $this->dataconverter->converterFromDBtoUser($string));
    }

    /**
     * Test converter From User To DB.
     *
     * @return void
     */
    public function test_converterFromUserToDB(): void
    {
        $expected = '100';
        $string = '100';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '1000';
        $string = $this->currencyMark . '1' . $this->thSepMark . '000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000';
        $string = $this->currencyMark . '10' . $this->thSepMark . '000';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));

        $expected = '10000.1';
        $string = $this->currencyMark . '10' . $this->thSepMark . '000.1';
        $this->assertEquals($expected, $this->dataconverter->converterFromUserToDB($string));
    }
}
