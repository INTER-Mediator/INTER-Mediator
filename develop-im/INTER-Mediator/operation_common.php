<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

/**
 * Convert strings to JavaScript friendly strings.
 * Contributed by Atsushi Matsuo at Jan 17, 2010
 * @return string strings for JavaScript
 */
function valueForJSInsert($str)
{
    return str_replace("'", "\\'",
        str_replace('"', '\\"',
            str_replace("/", "\\/",
                str_replace(">", "\\x3e",
                    str_replace("<", "\\x3c",
                        str_replace("\n", "\\n",
                            str_replace("\r", "\\r",
                                str_replace("\\", "\\\\", $str))))))));
}

/**
 * Create JavaScript source from array
 * @param array ar parameter array
 * @param string prefix strings for the prefix for key
 * @return string JavaScript source
 */
function arrayToJS($ar, $prefix)
{
    if (is_array($ar)) {
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = arrayToJS($value, $key);
        }
        $currentKey = (string)$prefix;
        if ($currentKey == '')
            $returnStr = "{" . implode(',', $items) . '}';
        else
            $returnStr = "'{$currentKey}':{" . implode(',', $items) . '}';
    } else {
        $currentKey = (string)$prefix;
        if ($currentKey == '') {
            $returnStr = "'" . valueForJSInsert($ar) . "'";
        } else {
            $returnStr = "'{$prefix}':'" . valueForJSInsert($ar) . "'";
        }
    }
    return $returnStr;
}

/**
 * Create parameter strng from array
 * @param array ar parameter array
 * @param string prefix strings for the prefix for key
 * @return string parameter string
 */
function arrayToQuery($ar, $prefix)
{
    if (is_array($ar)) {
        $items = array();
        foreach ($ar as $key => $value) {
            $items[] = arrayToQuery($value, "{$prefix}_{$key}");
        }
        $returnStr = implode('', $items);
    } else {
        $returnStr = "&{$prefix}=" . urlencode($ar);
    }
    return $returnStr;
}

/**
 * Get the relative path from the caller to the directory of 'INTER-Mediator.php'.
 * @return Relative path as a part of URL.
 */
function getRelativePath()
{
    $caller = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_FILENAME']));
    $imDirectory = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
    $commonPath = '';
    $shorterLength = min(count($caller), count($imDirectory));
    for ($i = 0; $i < $shorterLength; $i++) {
        if ($caller[$i] != $imDirectory[$i]) {
            break;
        }
    }
    $relPath = str_repeat('../', count($caller) - $i)
        . implode('/', array_slice($imDirectory, $i));
    return $relPath;
}

/**
 * Generate the instance of the message class associated with browser's language.
 * @return object Generated instance of the message class.
 */
function getErrorMessageClass()
{
    $currentDir = dirname(__FILE__);
    $lang = getLocaleFromBrowser();
    $candClassName = 'MessageStrings_' . $lang;
    if (!file_exists($currentDir . DIRECTORY_SEPARATOR . $candClassName . '.php')) {
        if (strpos($lang, '_') !== false) {
            $lang = substr($lang, 0, strpos($lang, '_'));
            $candClassName = 'MessageStrings_' . $lang;
            if (!file_exists($currentDir . DIRECTORY_SEPARATOR . $candClassName . '.php')) {
                $candClassName = 'MessageStrings';
            }
        }
    }
    $c = null;
    require_once($candClassName . '.php');
    eval("\$c = new {$candClassName}();");
    return $c->getMessages();
}

/**
 * Set the locale with parameter, for UNIX and Windows OS.
 * @param string locType locale identifier string.
 * @return boolean If true, strings with locale are possibly multi-byte string.
 */
function setLocaleAsBrowser($locType)
{
    $lstr = getLocaleFromBrowser();

    // Detect server platform, Windows or Unix
    $isWindows = false;
    $uname = php_uname();
    if (strpos($uname, 'Windows')) {
        $isWindows = true;
    }

    $useMbstring = false;
    if ($lstr == 'ja_JP') {
        $useMbstring = true;
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        }
        else {
            setlocale($locType, 'ja_JP');
        }
    } else if ($lstr == 'ja') {
        $useMbstring = true;
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        }
        else {
            setlocale($locType, 'ja_JP');
        }
    } else if ($lstr == 'en_US') {
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        }
        else {
            setlocale($locType, 'en_US');
        }
    } else if ($lstr == 'en') {
        if ($isWindows) {
            setlocale($locType, 'jpn_jpn');
        }
        else {
            setlocale($locType, 'en_US');
        }
    }
    return $useMbstring;
}

/**
 * Get the locale string (ex. 'ja_JP') from HTTP header from a browser.
 * @return string Most prior locale identifier
 */
function getLocaleFromBrowser()
{
    $lstr = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
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

function hex2bin_for53( $str )
{
    return pack("H*", $str);
}

function randamString( $digit ) {
    $resultStr = '';
    for ( $i = 0 ; $i < $digit ; $i++ ) {
        $resultStr .= chr( rand(20, 126));
    }
    return $resultStr;
}
?>