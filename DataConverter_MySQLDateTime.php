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

class DataConverter_MySQLDateTime
{

    private $tz = 'Asia/Tokyo'; // Should be custimizable.

    private $useMbstring;
    private $fmt;

    function __construct($format = '')
    {
        $this->fmt = $format;
        $this->useMbstring = setLocaleAsBrowser(LC_TIME);
        date_default_timezone_set($this->tz);
    }

    function converterFromDBtoUser($str)
    {
        if ( $str === NULL || $str === ''  || $str === '0000-00-00' ) {
            return '';
        }
        $sp = strpos($str, ' ');
        $slash = substr_count($str, '-');
        $colon = substr_count($str, ':');
        $dtObj = false;
        if (($sp !== FALSE) && ($slash == 2) && ($colon == 2)) {
            $sep = explode(' ', $str);
            $comp = explode('-', $sep[0]);
            $dtObj = new DateTime($comp[0] . '-' . $comp[1] . '-' . $comp[2]
                . ' ' . $sep[1], new DateTimeZone($this->tz));
            $fmt = '%x %H:%M:%S';
        } elseif (($sp === FALSE) && ($slash == 2) && ($colon == 0)) {
            $comp = explode('-', $str);
            $dtObj = new DateTime($comp[0] . '-' . $comp[1] . '-' . $comp[2],
                new DateTimeZone($this->tz));
            $fmt = '%x';
        } elseif (($sp === FALSE) && ($slash == 0) && ($colon == 2)) {
            $dtObj = new DateTime($str, new DateTimeZone($this->tz));
            $fmt = '%H:%M:%S';
        }
        if ($dtObj === false) {
            return $str;
        }
        if (strftime('%Y', $dtObj->format('U'))<1970) {
            return '';
        }
        return strftime(($this->fmt == '') ? $fmt : $this->fmt, $dtObj->format('U'));
    }

    function converterFromUserToDB($str)
    {
        if ($str == "") {
            return null;
        }
        $dtAr = date_parse(str_replace('.', '-', $str));
        if ($dtAr === false) return $str;
        $dt = '';
        if ($dtAr['year'] !== false && $dtAr['hour'] !== false)
            $dt = "{$dtAr['year']}-{$dtAr['month']}-{$dtAr['day']} {$dtAr['hour']}:{$dtAr['minute']}:{$dtAr['second']}";
        else if ($dtAr['year'] !== false)
            $dt = "{$dtAr['year']}-{$dtAr['month']}-{$dtAr['day']}";
        else if ($dtAr['hour'] !== false)
            $dt = "{$dtAr['hour']}:{$dtAr['minute']}:{$dtAr['second']}";
        return $dt;
    }

}
