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
namespace INTERMediator\Data_Converter;

use INTERMediator\Locale\IMLocale;

class NumberBase
{
    protected $decimalMark = null;
    protected $thSepMark = null;
    protected $currencyMark = null;
    protected $useMbstring;
    protected $choosenLocale;
    protected $formatter;

    public function __construct()
    {
        IMLocale::setLocale(LC_ALL);
        $this->choosenLocale = IMLocale::$choosenLocale;
        $this->useMbstring = IMLocale::$useMbstring;
        $nfClass = IMLocale::numberFormatterClassName();
        $this->formatter = new $nfClass($this->choosenLocale, 2 /*NumberFormatter::CURRENCY*/);
        if (!$this->formatter) {
            return null;
        }
        $this->decimalMark = $this->formatter->getSymbol(0 /*NumberFormatter::DECIMAL_SEPARATOR_SYMBOL*/);
        $this->thSepMark = $this->formatter->getSymbol(1 /*NumberFormatter::GROUPING_SEPARATOR_SYMBOL*/);
        $this->currencyMark = $this->formatter->getTextAttribute(5 /*NumberFormatter::CURRENCY_CODE*/);
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
