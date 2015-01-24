<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
*
*   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
*   INTER-Mediator is supplied under MIT License.
*/

require_once('DataConverter_NumberBase.php');

class DataConverter_Currency extends DataConverter_NumberBase
{

    private $d = null;

    function __construct($digits = 0)
    {
        parent::__construct();
        $this->d = $digits;
    }

    function converterFromDBtoUser($str)
    {
        return $this->currencyMark . number_format($str, $this->d, $this->decimalMark, $this->thSepMark);
    }

    function converterFromUserToDB($str)
    {
        if (mb_strlen($this->currencyMark) > 0 && strpos($str, $this->currencyMark) === 0) {
            $str = substr($str, strlen($this->currencyMark));
        }
        $normalized = str_replace($this->thSepMark, '', mb_convert_kana($str, 'n'));
        $numberString = '';
        $isPeriod = false;
        for ($i = 0; $i < mb_strlen($normalized); $i++) {
            $c = mb_substr($normalized, $i, 1);
            if (($c >= "0" && $c <= "9") || $c = ".") {
                $numberString .= $c;
                if ($c == ".") {
                    $isPeriod = true;
                }
            }
        }
        return $isPeriod ? floatval($numberString) : intval($numberString);
    }

}

?>
