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
class IMNumberFormatter
{
    private $locale = '';

    public function __construct($locale, $style, $pattern = '')
    {
        $this->locale = $locale;
        setlocale($locale);
    }

    public function getSymbol($attr)
    {
        $locInfo = localeconv();
        $s = '';
        switch ($attr) {
            case 0: /*NumberFormatter::DECIMAL_SEPARATOR_SYMBOL*/
                $s = $locInfo['mon_decimal_point'];
                $s = strlen($s) > 0 ? $s : ".";
                break;
            case 1: /*NumberFormatter::GROUPING_SEPARATOR_SYMBOL*/
                $s = $locInfo['mon_thousands_sep'];
                $s = strlen($s) > 0 ? $s : ",";
                break;
            case 8: /*NumberFormatter::CURRENCY_SYMBOL*/
                $s = $locInfo['currency_symbol'];
                $s = strlen($s) > 0 ? $s : "¥";
                break;
        }
        return $s;
    }
}