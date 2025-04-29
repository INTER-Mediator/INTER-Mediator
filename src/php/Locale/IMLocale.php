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

/**
 * IMLocale manages locale and currency settings for INTER-Mediator.
 * It provides methods for locale detection, setting, and conversion, and stores related configuration.
 */
class IMLocale
{
    /**
     * Returns the class name to use for number formatting, depending on environment and settings.
     *
     * @return string The fully qualified class name for number formatting.
     */
    public static function numberFormatterClassName(): string
    {
        $cName = "INTERMediator\Locale\IMNumberFormatter";
        if (class_exists("\NumberFormatter") && !IMLocale::$alwaysIMClasses) {
            $cName = "\NumberFormatter"; // This class always exists after PHP 5.3.
        }
        return $cName;
    }

    /**
     * If true, always use INTER-Mediator classes for formatting (for unit testing).
     * @var bool
     */
    public static bool $alwaysIMClasses = false;
    /**
     * The currently chosen locale (e.g., 'en', 'ja_JP').
     * @var string
     */
    public static string $choosenLocale = 'en';
    /**
     * The currently chosen ISO currency code (e.g., 'USD', 'JPY').
     * @var string
     */
    public static string $currencyCode = 'USD';
    /**
     * Whether to use multibyte string functions (for certain Asian locales).
     * @var bool
     */
    public static bool $useMbstring = false;
    /**
     * Locale override for testing purposes.
     * @var string
     */
    public static string $localForTest = '';
    /**
     * Additional options, such as 'app-locale' and 'app-currency'.
     * @var array|null
     */
    public static ?array $options = null;
    /**
     * Table for converting browser locale codes to standard locale codes.
     * @var array
     */
    private static array $localeConvertTable = array("ja" => "ja_JP");

    /**
     * Set the locale and currency based on parameters, environment, and options.
     *
     * @param string $locType The locale category (e.g., LC_ALL).
     * @param string $localeName Optional locale name to use.
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

        // Locale Convert Table. Chrome requests "ja"
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
     * Detects the locale from the browser's Accept-Language header.
     *
     * @param string $localeString $_SERVER['HTTP_ACCEPT_LANGUAGE']
     * @return string The detected locale code.
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
