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
    private string $decimalPoint;
    private string $thSeparator;
    private string $currencySymbol;
    private int $flactionDigit = 0;

    public function __construct(string $locale)
    {
        setlocale(LC_ALL, $locale . '.UTF-8');
        $locInfo = localeconv();
        if ($locInfo['currency_symbol'] == '') {
            $locInfo = IMLocaleFormatTable::getLocaleFormat($locale);
        }
        $this->decimalPoint = $locInfo['mon_decimal_point'];
        $this->thSeparator = $locInfo['mon_thousands_sep'];
        $this->currencySymbol = $locInfo['currency_symbol'];
    }

    public function getSymbol(int $attr):string
    {
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

    public function getTextAttribute(int $attr):string
    {
        $s = '';
        /*NumberFormatter::CURRENCY_CODE*/
        if ($attr == 5) {
            $s = $this->currencySymbol;
        }
        return $s;
    }

    public function setAttribute(int $attr, string $value):void
    {
        /*NumberFormatter::FRACTION_DIGITS*/
        if ($attr == 8) {
            $this->flactionDigit = intval($value);
        }
    }

    public function formatCurrency(?string $value, ?string $currency):string
    {
        return $this->currencySymbol .
            number_format($value, $this->flactionDigit, $this->decimalPoint, $this->thSeparator);
    }
}