<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
*
*   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
*   INTER-Mediator is supplied under MIT License.
*/

require_once('INTER-Mediator.php');

class DataConverter_NumberBase
{

    protected $decimalMark = '.';
    protected $thSepMark = ',';
    protected $currencyMark = null;
    protected $useMbstring;

    public function __construct()
    {
        $this->useMbstring = setLocaleAsBrowser(LC_ALL);
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
    }

    public function converterFromUserToDB($str)
    {
        $comp = explode($this->decimalMark, $str);
        $intPart = intval(str_replace($this->thSepMark, '', $comp[0]));
        if (isset($comp[1])) {
            $decimalPart = intval(str_replace($this->thSepMark, '', $comp[1]));
            return floatval(strval($intPart) . '.' . strval($decimalPart));
        } else {
            return $intPart;
        }
    }
}
