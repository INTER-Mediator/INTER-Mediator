<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

class DataConverter_FMDateTime
{

    var $tz = 'Asia/Tokyo'; // Should be custimizable.

    var $useMbstring;
    var $fmtNum;

    /**
     *
     * @param integer $format
     * @return unknown_type
     */
    function __construct($format = '')
    {
        $this->fmt = $format;
        $this->useMbstring = setLocaleAsBrowser(LC_TIME);
        date_default_timezone_set($this->tz);
    }

    function converterFromDBtoUser($str)
    {
        $sp = strpos($str, ' ');
        $slash = substr_count($str, '/');
        $colon = substr_count($str, ':');
        $dtObj = false;
        if (($sp !== FALSE) && ($slash === 2) && ($colon === 2)) {
            $sep = explode(' ', $str);
            $comp = explode('/', $sep[0]);
            $dtObj = new DateTime($comp[2] . '-' . $comp[0] . '-' . $comp[1] . ' ' . $sep[1]);
            $fmt = '%x %T';
        } elseif (($sp === FALSE) && ($slash === 2) && ($colon === 0)) {
            $comp = explode('/', $str);
            $dtObj = new DateTime($comp[2] . '-' . $comp[0] . '-' . $comp[1]);
            $fmt = '%x';
        } elseif (($sp === FALSE) && ($slash === 0) && ($colon === 2)) {
            $dtObj = new DateTime($str);
            $fmt = '%T';
        }
        if ($dtObj === false) {
            return $str;
        }
        return strftime(($this->fmt == '') ? $fmt : $this->fmt, $dtObj->format('U'));
    }

    function converterFromUserToDB($str)
    {
        $dtAr = date_parse($str);
        if ($dtAr === false) return $str;
        $dt = '';
        if ($dtAr['year'] !== false && $dtAr['hour'] !== false)
            $dt = "{$dtAr['month']}/{$dtAr['day']}/{$dtAr['year']} {$dtAr['hour']}:{$dtAr['minute']}:{$dtAr['second']}";
        else if ($dtAr['year'] !== false)
            $dt = "{$dtAr['month']}/{$dtAr['day']}/{$dtAr['year']}";
        else if ($dtAr['hour'] !== false)
            $dt = "{$dtAr['hour']}:{$dtAr['minute']}:{$dtAr['second']}";
        return $dt;
    }

    function dateArrayFromFMDate($d)
    {
        if ($d == '') {
            return '';
        }
        $jYearStartDate = array('1989-1-8' => '平成', '1925-12-25' => '昭和', '1912-7-30' => '大正', '1868-1-25' => '明治');
        $wStrArray = array('日', '月', '火', '水', '木', '金', '土');

        $dateComp = date_parse_from_format('m/d/Y H:i:s', $d);
        $dt = new DateTime();
        $dt->setDate($dateComp['year'], $dateComp['month'], $dateComp['day']);

        $gengoName = '';
        $gengoYear = 0;
        foreach ($jYearStartDate as $startDate => $gengo) {
            $dtStart = new DateTime($startDate);
            $dinterval = $dt->diff($dtStart);
            if ($dinterval->invert == 1) {
                $gengoName = $gengo;
                $gengoYear = $dt->format('Y') - $dtStart->format('Y') + 1;
                $gengoYear = ($gengoYear == 1) ? '元' : $gengoYear;
                break;
            }
        }
        return array(
            'unixtime' => $dt->format('U'),
            'year' => $dt->format('Y'),
            'jyear' => $gengoName . $gengoYear . '年',
            'month' => $dt->format('m'),
            'day' => $dt->format('d'),
            'hour' => $dt->format('H'),
            'minute' => $dt->format('i'),
            'second' => $dt->format('s'),
            'weekdayName' => $wStrArray[$dt->format('w')],
            'weekday' => $dt->format('w'),
            'longdate' => $dt->format('Y/m/d'),
            'jlongdate' => $gengoName . ' ' . $gengoYear . $dt->format(' 年 n 月 j 日 ') . $wStrArray[$dt->format('w')] . '曜日',
        );
    }


}

?>
