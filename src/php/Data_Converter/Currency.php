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

use Exception;
use INTERMediator\Locale\IMLocale;

/**
 *
 */
class Currency extends NumberBase
{
    /**
     * @var int
     */
    private int $d;

    /**
     * @param int $digits
     * @throws Exception
     */
    function __construct(int $digits = 0)
    {
        parent::__construct();
        $this->d = $digits;
    }

    /**
     * @param string|null $str
     * @return string
     */
    function converterFromDBtoUser(?string $str):string
    {
        $this->formatter->setAttribute(8 /*NumberFormatter::FRACTION_DIGITS*/, $this->d);
        return $this->formatter->formatCurrency($str, IMLocale::$currencyCode);
    }

    /**
     * @param string $str
     * @return string
     */
    function converterFromUserToDB(string $str):string
    {
        $normalized = str_replace($this->thSepMark, '', mb_convert_kana($str, 'n'));
        $numberString = '';
        $isPeriod = false;
        for ($i = 0; $i < mb_strlen($normalized); $i++) {
            $c = mb_substr($normalized, $i, 1);
            if (($c >= "0" && $c <= "9") || $c == ".") {
                $numberString .= $c;
                if ($c == ".") {
                    $isPeriod = true;
                }
            }
        }
        return (string)($isPeriod ? floatval($numberString) : intval($numberString));
    }

}
