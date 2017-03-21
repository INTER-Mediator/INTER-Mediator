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
class IMLocale
{
    public static function numberFormatterClassName()
    {
        $cName = "IMNumberFormatter";
        try {   // This exception handling requires just PHP 5.2. On above 5.3 or later, it doesn't need to 'try.'
            if (class_exists("NumberFormatter") && !IMLocale::$alwaysIMClasses) {
                $cName = "NumberFormatter";
            }
        } catch (Exception $e) {

        }
        return $cName;
    }

    public static $alwaysIMClasses = false; // for unit testing
    public static $choosenLocale = 'en';
    public static $currencyCode = 'USD';
    public static $useMbstring = false;
    public static $localForTest = '';
    public static $options = null;

    /**
     * Set the locale with parameter, for UNIX and Windows OS.
     * @param string locType locale identifier string.
     * @return boolean If true, strings with locale are possibly multi-byte string.
     */
    public static function setLocale($locType, $localeName = '')
    {
        $isSetLocale = false;
        $isSetCurrency = false;
        $appLocale = null;
        $appCurrency = null;
        $params = IMUtil::getFromParamsPHPFile(array("appLocale", "appCurrency",), true);
        $appLocale = isset(IMLocale::$options['app-locale']) ? IMLocale::$options['app-locale'] : $params["appLocale"];
        $appCurrency = isset(IMLocale::$options['app-currency']) ? IMLocale::$options['app-currency'] : $params["appCurrency"];
        if (!is_null($appLocale)) {
            IMLocale::$choosenLocale = $appLocale;
            IMLocale::$currencyCode = IMLocaleCurrencyTable::getCurrencyCode($appLocale);
            $isSetLocale = true;
            $isSetCurrency = true;
            $lstr = $appLocale;
        }
        if (!is_null($appCurrency)) {
            IMLocale::$currencyCode = IMLocaleCurrencyTable::getContoryCurrencyCode($appCurrency);
            $isSetCurrency = true;
        }
        if (!$isSetLocale) {
            if (IMLocale::$localForTest != '') {
                $lstr = IMLocale::$localForTest;
            } else {
                $lstr = ($localeName != '') ? $localeName : IMLocale::getLocaleFromBrowser();
            }
            IMLocale::$choosenLocale = $lstr;
        }

        // Detect server platform, Windows or Unix
        $isWindows = false;
        $uname = php_uname();
        if (strpos($uname, 'Windows')) {
            $isWindows = true;
        }

        IMLocale::$useMbstring = false;
        if (array_search(substr($lstr, 0, 2), array('zh', 'ja', 'ko', 'vi'))) {
            IMLocale::$useMbstring = true;
        }
        if ($isWindows) {
            setlocale($locType, IMLocaleStringTable::getLocaleString($lstr) . 'UTF-8');
        } else {
            setlocale($locType, $lstr . 'UTF-8');
        }
        if (!$isSetCurrency) {
            IMLocale::$currencyCode = IMLocaleCurrencyTable::getCurrencyCode($lstr);
        }
    }

    /**
     * Get the locale string (ex. 'ja_JP') from HTTP header from a browser.
     * @param string $accept $_SERVER['HTTP_ACCEPT_LANGUAGE']
     * @return string Most prior locale identifier
     */
    public static function getLocaleFromBrowser($localeString = '')
    {
        $lstr = ($localeString != '') ? $localeString : strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
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
