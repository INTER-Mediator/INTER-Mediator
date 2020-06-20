<?php

/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace INTERMediator\Locale;

class IMNumberFormatter
{
    private $locale = '';
    private $decimalPoint = '';
    private $thSeparator = '';
    private $currencySymbol = '';
    private $flactionDigit = 0;

    public function __construct($locale, $style, $pattern = '')
    {
        $this->locale = $locale;
        setlocale(LC_ALL, $locale . '.UTF-8');
        $locInfo = localeconv();
        if ($locInfo['currency_symbol'] == '') {
            $locInfo = IMLocaleFormatTable::getLocaleFormat($locale);
        }
        $this->decimalPoint = $locInfo['mon_decimal_point'];
        $this->thSeparator = $locInfo['mon_thousands_sep'];
        $this->currencySymbol = $locInfo['currency_symbol'];
    }

    public function getSymbol($attr)
    {
        $locInfo = localeconv();
        $s = '';
        switch ($attr) {
            case 0: /*NumberFormatter::DECIMAL_SEPARATOR_SYMBOL*/
                $s = $this->decimalPoint;
                break;
            case 1: /*NumberFormatter::GROUPING_SEPARATOR_SYMBOL*/
                $s = $this->thSeparator;
                break;
        }
        return $s;
    }

    public function getTextAttribute($attr)
    {
        $locInfo = localeconv();
        $s = '';
        switch ($attr) {
            case 5: /*NumberFormatter::CURRENCY_CODE*/
                $s = $this->currencySymbol;
                break;
        }
        return $s;
    }

    public function setAttribute($attr, $value)
    {
        switch ($attr) {
            case 8: /*NumberFormatter::FRACTION_DIGITS*/
                $this->flactionDigit = $value;
                break;
        }
    }

    public function formatCurrency($value, $currency)
    {
        return $this->currencySymbol .
            number_format($value, $this->flactionDigit, $this->decimalPoint, $this->thSeparator);
    }
}