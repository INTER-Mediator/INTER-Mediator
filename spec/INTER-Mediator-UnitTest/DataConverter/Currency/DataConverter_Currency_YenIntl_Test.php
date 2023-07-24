<?php
/**
 * DataConverter_Currency_Test file
 */

namespace Currency;
require_once(dirname(__FILE__) . '/../DataConverter_Currency_Base_Test.php');

use INTERMediator\Data_Converter\Currency;
use INTERMediator\Locale\IMLocale;

class DataConverter_Currency_YenIntl_Test extends DataConverter_Currency_Base_Test
{
    public function setUp(): void
    {
        IMLocale::$localForTest = 'ja';
        IMLocale::$alwaysIMClasses = false;
        $this->dataconverter = new Currency();

        $this->thSepMark = ',';
        $this->currencyMark = '￥';
    }
}
