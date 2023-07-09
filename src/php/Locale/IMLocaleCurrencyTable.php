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

class IMLocaleCurrencyTable
{
    public static function getCurrencyCode($localeCode)
    {
        if (substr($localeCode, 0, 2) == 'ja') {
            return "JPY";
        } else if (substr($localeCode, 0, 5) == 'en_US') {
            return "USD";
        }
        if (strpos($localeCode, "_") !== false) {
            $localeCode = substr($localeCode, strpos($localeCode, "_") + 1, 2);
        }
        if (!isset(IMLocaleCurrencyTable::$localeCurrencyTable[$localeCode])) {
            $localeCode = 'JP';
        }
        $locInfo = IMLocaleCurrencyTable::$localeCurrencyTable[strtoupper($localeCode)];

        return $locInfo;
    }

    public static function getCountryCurrencyCode($cCode)
    {
        if ($cCode == 'JP') {
            return "JPY";
        } else if ($cCode == 'US') {
            return "USD";
        }
        if (!isset(IMLocaleCurrencyTable::$localeCurrencyTable[$cCode])) {
            $cCode = 'JP';
        }
        $locInfo = IMLocaleCurrencyTable::$localeCurrencyTable[strtoupper($cCode)];

        return $locInfo;
    }

    /*
     * refers
     * https://www.ups.com/worldshiphelp/WS15/JPN/AppHelp/Codes/Country_Territory_and_Currency_Codes.htm
     */
    private static $localeCurrencyTable = array(
        'AF' => 'USD',
        'AL' => 'EUR',
        'DZ' => 'DZD',
        'AS' => 'USD',
        'AD' => 'EUR',
        'AO' => 'AOA',
        'AI' => 'XCD',
        'AG' => 'XCD',
        'AR' => 'ARS',
        'AM' => 'AMD',
        'AW' => 'AWG',
        'AU' => 'AUD',
        'AT' => 'EUR',
        'AZ' => 'AZM',
        'BS' => 'BSD',
        'BH' => 'BHD',
        'BD' => 'BDT',
        'BB' => 'BBD',
        'BY' => 'BYR',
        'BE' => 'EUR',
        'BZ' => 'BZD',
        'BJ' => 'XOF',
        'BM' => 'BMD',
        'BT' => 'BTN',
        'BO' => 'BOB',
        'BA' => 'BAM',
        'BW' => 'BWP',
        'BR' => 'BRL',
        'VG' => 'USD',
        'BN' => 'BND',
        'BG' => 'EUR',
        'BF' => 'XOF',
        'BI' => 'BIF',
        'KH' => 'KHR',
        'CM' => 'XAF',
        'CA' => 'CAD',
        'ES' => 'EUR',
        'CV' => 'CVE',
        'KY' => 'KYD',
        'CF' => 'XAF',
        'TD' => 'XAF',
        'CL' => 'CLP',
        'CN' => 'RMB',
        'CO' => 'COP',
        'KM' => 'USD',
        'CG' => 'XAF',
        'CD' => 'CDF',
        'CK' => 'NZD',
        'CR' => 'CRC',
        'HR' => 'EUR',
        'AN' => 'ANG',
        'CY' => 'EUR',
        'CZ' => 'CZK',
        'DK' => 'DKK',
        'DJ' => 'DJF',
        'DM' => 'XCD',
        'DO' => 'DOP',
        'EC' => 'USD',
        'EG' => 'EGP',
        'SV' => 'USD',
        'GB' => 'GBP',
        'GQ' => 'XAF',
        'ER' => 'ERN',
        'EE' => 'EUR',
        'ET' => 'ETB',
        'EP' => 'EUR',
        'FO' => 'DKK',
        'FJ' => 'FJD',
        'FI' => 'EUR',
        'FR' => 'EUR',
        'GF' => 'EUR',
        'PF' => 'XPF',
        'GA' => 'XAF',
        'GM' => 'GMD',
        'GE' => 'GEL',
        'DE' => 'EUR',
        'GH' => 'GHS',
        'GI' => 'GIP',
        'GR' => 'EUR',
        'GL' => 'DKK',
        'GD' => 'XCD',
        'GP' => 'EUR',
        'GU' => 'USD',
        'GT' => 'GTQ',
        'GG' => 'GBP',
        'GN' => 'GNF',
        'GW' => 'XOF',
        'GY' => 'GYD',
        'HT' => 'HTG',
        'NL' => 'EUR',
        'HN' => 'HNL',
        'HK' => 'HKD',
        'HU' => 'HUF',
        'IS' => 'ISK',
        'IN' => 'INR',
        'ID' => 'IDR',
        'IQ' => 'NID',
        'IE' => 'EUR',
        'IL' => 'ILS',
        'IT' => 'EUR',
        'CI' => 'XOF',
        'JM' => 'JMD',
        'JP' => 'JPY',
        'JE' => 'GBP',
        'JO' => 'JOD',
        'KZ' => 'KZT',
        'KE' => 'KES',
        'KI' => 'AUD',
        'KR' => 'KRW',
        'FM' => 'USD',
        'KW' => 'KWD',
        'KG' => 'KGS',
        'LA' => 'LAK',
        'LV' => 'LVL',
        'LB' => 'LBP',
        'LS' => 'LSL',
        'LR' => 'LRD',
        'LY' => 'LYD',
        'LI' => 'CHF',
        'LT' => 'LTL',
        'LU' => 'EUR',
        'MO' => 'MOP',
        'MK' => 'EUR',
        'MG' => 'MGA',
        'PT' => 'EUR',
        'MW' => 'MWK',
        'MY' => 'MYR',
        'MV' => 'MVR',
        'ML' => 'XOF',
        'MT' => 'EUR',
        'MH' => 'USD',
        'MQ' => 'EUR',
        'MR' => 'MRO',
        'MU' => 'MUR',
        'YT' => 'EUR',
        'MX' => 'MXN',
        'MD' => 'MDL',
        'MC' => 'EUR',
        'MN' => 'MNT',
        'ME' => 'EUR',
        'MS' => 'XCD',
        'MA' => 'MAD',
        'MZ' => 'MZM',
        'NA' => 'NAD',
        'NP' => 'NPR',
        'NC' => 'XPF',
        'NZ' => 'NZD',
        'NI' => 'NIO',
        'NE' => 'XOF',
        'NG' => 'NGN',
        'NF' => 'AUD',
        'MP' => 'USD',
        'NO' => 'NOK',
        'OM' => 'OMR',
        'PK' => 'PKR',
        'PW' => 'USD',
        'PA' => 'PAB',
        'PG' => 'PGK',
        'PY' => 'PYG',
        'PE' => 'PEN',
        'PH' => 'PHP',
        'PL' => 'PLN',
        'PR' => 'USD',
        'QA' => 'QAR',
        'RE' => 'EUR',
        'RO' => 'ROL',
        'RU' => 'RUB',
        'RW' => 'RWF',
        'WS' => 'WST',
        'SM' => 'EUR',
        'SA' => 'SAR',
        'SN' => 'XOF',
        'RS' => 'EUR',
        'SC' => 'SCR',
        'SL' => 'SLL',
        'SG' => 'SGD',
        'SK' => 'EUR',
        'SI' => 'EUR',
        'SB' => 'SBD',
        'ZA' => 'ZAR',
        'LK' => 'LKR',
        'KN' => 'XCD',
        'VI' => 'USD',
        'LC' => 'XCD',
        'VC' => 'XCD',
        'SR' => 'SRG',
        'SZ' => 'SZL',
        'SE' => 'SEK',
        'CH' => 'CHF',
        'TW' => 'TWD',
        'TJ' => 'TJS',
        'TZ' => 'TZS',
        'TH' => 'THB',
        'TG' => 'XOF',
        'TO' => 'TOP',
        'TT' => 'TTD',
        'TN' => 'TND',
        'TR' => 'TRY',
        'TM' => 'TMM',
        'TC' => 'USD',
        'TV' => 'AUD',
        'UG' => 'UGX',
        'UA' => 'UAH',
        'AE' => 'AED',
        'US' => 'USD',
        'UY' => 'UYU',
        'UZ' => 'UZS',
        'VU' => 'VUV',
        'VA' => 'EUR',
        'VE' => 'VEB',
        'VN' => 'VND',
        'WF' => 'XPF',
        'YE' => 'YER',
        'ZM' => 'ZMK',
        'ZW' => 'ZWD',
    );
}
