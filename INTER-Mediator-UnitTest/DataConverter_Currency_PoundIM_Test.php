<?php
/**
 * DataConverter_Currency_Test file
 */
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../IMNumberFormatter.php');
require_once(dirname(__FILE__) . '/../Data_Converter/DataConverter_Currency.php');
require_once(dirname(__FILE__) . '/DataConverter_Currency_Base_Test.php');

class DataConverter_Currency_PoundIM_Test extends DataConverter_Currency_Base_Test
{
    public function setUp(): void
    {
        IMLocale::$localForTest = 'en_GB';
        IMLocale::$alwaysIMClasses = true;
        $this->dataconverter = new DataConverter_Currency();

        $this->thSepMark = ',';
        $this->currencyMark = '£';
    }
}
