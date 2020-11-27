<?php
/**
 * DataConverter_Currency_Test file
 */

require_once(dirname(__FILE__) . '/DataConverter_Currency_Base_Test.php');

class DataConverter_Currency_PoundIM_Test // extends DataConverter_Currency_Base_Test
{
    public function setUp(): void
    {
        \INTERMediator\Locale\IMLocale::$localForTest = 'en_GB';
        \INTERMediator\Locale\IMLocale::$alwaysIMClasses = true;
        $this->dataconverter = new \INTERMediator\Data_Converter\Currency();

        $this->thSepMark = ',';
        $this->currencyMark = '£';
    }

    // This is deprecated test cases.
}
