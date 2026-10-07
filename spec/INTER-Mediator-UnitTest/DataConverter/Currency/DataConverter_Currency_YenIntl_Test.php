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
    /**
     * Set up the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        IMLocale::$localForTest = 'ja';
        $this->dataconverter = new Currency();

        $this->thSepMark = ',';
        $this->currencyMark = '￥';
    }
}
