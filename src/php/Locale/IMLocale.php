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

use Exception;
use INTERMediator\Params;

class IMLocale
{
    public static function numberFormatterClassName()
    {
        $cName = "INTERMediator\Locale\IMNumberFormatter";
        try {   // This exception handling requires just PHP 5.2. On above 5.3 or later, it doesn't need to 'try.'
            if (class_exists("\NumberFormatter") && !IMLocale::$alwaysIMClasses) {
                $cName = "\NumberFormatter";
            }
        } catch (Exception $e) {

        }
        return $cName;
    }

    public static bool $alwaysIMClasses = false; // for unit testing
    public static string $choosenLocale = 'en';
    public static string $currencyCode = 'USD';
    public static bool $useMbstring = false;
    public static string $localForTest = '';
    public static ?array $options = null;
    private static array $localeConvertTable = array("ja" => "ja_JP");

    /**
     * Set the locale with parameter, for UNIX and Windows OS.
     * @param string locType locale identifier string.
     * @return void
     */
    public static function setLocale(string $locType, string $localeName = ''): void
    {
        $isSetLocale = false;
        $isSetCurrency = false;
        [$appLocale, $appCurrency] = Params::getParameterValue(["appLocale", "appCurrency"], ['ja_JP', 'JP']);
        $appLocale = IMLocale::$options['app-locale'] ?? $appLocale;
        $appCurrency = IMLocale::$options['app-currency'] ?? $appCurrency;

        if (IMLocale::$localForTest != '') {
            IMLocale::$choosenLocale = IMLocale::$localForTest;
        } else {
            if (!is_null($appLocale)) {
                IMLocale::$choosenLocale = $appLocale;
                $isSetLocale = true;
                IMLocale::$currencyCode = IMLocaleCurrencyTable::getCurrencyCode($appLocale);
                $isSetCurrency = true;
            }
            if (!is_null($appCurrency)) {
                IMLocale::$currencyCode = IMLocaleCurrencyTable::getCountryCurrencyCode($appCurrency);
                $isSetCurrency = true;
            }
            if (!$isSetLocale) {
                IMLocale::$choosenLocale = ($localeName != '') ? $localeName : IMLocale::getLocaleFromBrowser();
                $isSetLocale = true;
            }
        }

        // Locale Convert Talble. Chrome requests "ja"
        IMLocale::$choosenLocale = array_key_exists(IMLocale::$choosenLocale, IMLocale::$localeConvertTable) ?
            IMLocale::$localeConvertTable[IMLocale::$choosenLocale] : IMLocale::$choosenLocale;

        // Detect server platform, Windows or Unix
        $isWindows = false;
        $uname = php_uname();
        if (strpos($uname, 'Windows')) {
            $isWindows = true;
        }

        IMLocale::$useMbstring = false;
        if (array_search(substr(IMLocale::$choosenLocale, 0, 2), array('zh', 'ja', 'ko', 'vi'))) {
            IMLocale::$useMbstring = true;
        }
        if ($isWindows) {
            setlocale($locType, IMLocaleStringTable::getLocaleString(IMLocale::$choosenLocale) . '.UTF-8');
        } else {
            setlocale($locType, IMLocale::$choosenLocale . '.UTF-8');
        }
        if (!$isSetCurrency) {
            IMLocale::$currencyCode = IMLocaleCurrencyTable::getCurrencyCode(IMLocale::$choosenLocale);
        }
    }

    /**
     * Get the locale string (ex. 'ja_JP') from HTTP header from a browser.
     * @param string $accept $_SERVER['HTTP_ACCEPT_LANGUAGE']
     * @return string Most prior locale identifier
     */
    public static function getLocaleFromBrowser(string $localeString = ''): string
    {
        $lstr = $localeString;
        if ($localeString === '') {
            $lstr = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']) : 'en';
        }

        // Extracting first item and cutting the priority infos.
        if (strpos($lstr, ',') !== false) $lstr = substr($lstr, 0, strpos($lstr, ','));
        if (strpos($lstr, ';') !== false) $lstr = substr($lstr, 0, strpos($lstr, ';'));

        // Convert to the right locale identifier.
        if (strpos($lstr, '-') !== false) {
            $lstr = explode('-', $lstr);
        } else if (strpos($lstr, '_') !== false) {
            $lstr = explode('_', $lstr);
        } else {
            $lstr = array($lstr);
        }
        if (count($lstr) == 1)
            $lstr = $lstr[0];
        else
            $lstr = strtolower($lstr[0]) . '_' . strtoupper($lstr[1]);
        return $lstr;
    }

}
