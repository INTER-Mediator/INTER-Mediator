<?php
/**
 * DataConverter_Currency_Test file
 */

require_once(dirname(__FILE__) . '/DataConverter_Currency_Base_Test.php');

use INTERMediator\Data_Converter\Currency;
use INTERMediator\Locale\IMLocale;

class DataConverter_Currency_DollerIM_Test // extends DataConverter_Currency_Base_Test
{
    public function setUp(): void
    {
        IMLocale::$localForTest = 'en_US';
        IMLocale::$alwaysIMClasses = true;
        $this->dataconverter = new Currency();

        $this->thSepMark = ',';
        $this->currencyMark = '$';
    }

    // This is deprecated test cases.
}
