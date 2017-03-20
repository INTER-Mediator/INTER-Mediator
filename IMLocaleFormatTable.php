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
class IMLocaleFormatTable
{
    public static function getLocaleFormat($localeCode)
    {
        if (!isset(IMLocaleFormatTable::$localeFormatTable[$localeCode])) {
            $localeCode = 'ja';
        }
        $locInfo = IMLocaleFormatTable::$localeFormatTable[$localeCode];

        return array(
            'mon_decimal_point' => $locInfo[0],
            'mon_thousands_sep' => $locInfo[1],
            'currency_symbol' => $locInfo[2]
        );
    }

    private static $localeFormatTable = array(
        'ja' => array('.', ',', '￥'),
        'ja_JP' => array('.', ',', '￥'),
        'en_US' => array('.', ',', '$'),
    );
}
