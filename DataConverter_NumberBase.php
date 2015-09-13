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
        if (strlen($this->currencyMark) == 0) {
            $this->currencyMark = '¥';
        }
    }

    public function converterFromUserToDB($str)
    {
        $comp = explode($this->decimalMark, $str);
        $intPart = intval(str_replace($this->thSepMark, '', $comp[0]));
        if (isset($comp[1])) {
            return floatval(strval($intPart) . '.' . strval($comp[1]));
        } else {
            return $intPart;
        }
    }
}
