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

require_once('INTER-Mediator.php');

class DataConverter_NumberBase
{

    protected $decimalMark = null;
    protected $thSepMark = null;
    protected $currencyMark = null;
    protected $useMbstring;
    protected $choosenLocale;

    public function __construct()
    {
        IMLocale::setLocaleAsBrowser(LC_ALL);
        $this->choosenLocale = IMLocale::$choosenLocale;
        $this->useMbstring = IMLocale::$useMbstring;
        /*
        $locInfo = localeconv();
        $this->decimalMark = $locInfo['mon_decimal_point'];
        // @codeCoverageIgnoreStart
        if (strlen($this->decimalMark) == 0) {
            $this->decimalMark = '.';
        }
        // @codeCoverageIgnoreEnd
        $this->thSepMark = $locInfo['mon_thousands_sep'];
        if (strlen($this->thSepMark) == 0) {
            $this->thSepMark = ',';
        }
        $this->currencyMark = $locInfo['currency_symbol'];
        if (strlen($this->currencyMark) == 0) {
            $this->currencyMark = '¥';
        }
*/
        $this->decimalMark = '.';
        $this->thSepMark = ',';
        $this->currencyMark = '¥';

        $nfClass = IMLocale::numberFormatterClassName();
        $formatter = new $nfClass($this->choosenLocale, 1 /*NumberFormatter::DECIMAL*/);
        if ($formatter) {
            $this->decimalMark = $formatter->getSymbol(0 /*NumberFormatter::DECIMAL_SEPARATOR_SYMBOL*/);
            $this->thSepMark = $formatter->getSymbol(1 /*NumberFormatter::GROUPING_SEPARATOR_SYMBOL*/);
            $this->currencyMark = $formatter->getSymbol(8 /*NumberFormatter::CURRENCY_SYMBOL*/);
        }
    }

    public function converterFromUserToDB($str)
    {
        $str = mb_convert_kana($str, "a");
        $comp = explode($this->decimalMark, $str);
        $intPart = intval(str_replace($this->thSepMark, '', $comp[0]));
        if (isset($comp[1])) {
            return floatval(strval($intPart) . '.' . strval($comp[1]));
        } else {
            return $intPart;
        }
    }
}
