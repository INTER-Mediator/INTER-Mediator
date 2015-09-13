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

class DataConverter_FMDateTime
{

    private $tz = 'Asia/Tokyo'; // Should be custimizable.

    private $useMbstring;
    private $fmtNum;

    /**
     *
     * @param integer $format
     * @return unknown_type
     */
    public function __construct($format = '')
    {
        $this->fmt = $format;
        $this->useMbstring = setLocaleAsBrowser(LC_TIME);
        date_default_timezone_set($this->tz);
    }

    public function converterFromDBtoUser($str)
    {
        if ($str === array()) {
            return '';
        }
        $str = str_replace(".", "/", $str);
        $sp = strpos($str, ' ');
        $slash = substr_count($str, '/');
        $colon = substr_count($str, ':');
        $dtObj = false;
        if (($sp !== FALSE) && ($slash === 2) && ($colon === 2)) {
            $sep = explode(' ', $str);
            $comp = explode('/', $sep[0]);
            $dtObj = new DateTime($comp[2] . '-' . $comp[0] . '-' . $comp[1] . ' ' . $sep[1]);
            $fmt = '%x %H:%M:%S';
        } elseif (($sp === FALSE) && ($slash === 2) && ($colon === 0)) {
            $comp = explode('/', $str);
            $dtObj = new DateTime($comp[2] . '-' . $comp[0] . '-' . $comp[1]);
            $fmt = '%x';
        } elseif (($sp === FALSE) && ($slash === 0) && ($colon === 2)) {
            $dtObj = new DateTime($str);
            $fmt = '%H:%M:%S';
        }
        if ($dtObj === false) {
            return $str;
        }
        return strftime(($this->fmt == '') ? $fmt : $this->fmt, $dtObj->format('U'));
    }

    public function converterFromUserToDB($str)
    {
        $dtAr = date_parse($str);
        if ($dtAr === false) return $str;
        $dt = '';
        $dateStr = "{$dtAr['month']}/{$dtAr['day']}/{$dtAr['year']}";
        $timeStr = "{$dtAr['hour']}:{$dtAr['minute']}:{$dtAr['second']}";
        if ($dtAr['year'] !== false && $dtAr['hour'] !== false) {
            $dt = "{$dateStr} {$timeStr}";
        } else if ($dtAr['year'] !== false) {
            $dt = $dateStr;
        } else if ($dtAr['hour'] !== false) {
            $dt = $timeStr;
        }
        return $dt;
    }

    public function dateArrayFromFMDate($d)
    {
        if ($d == '') {
            return '';
        }
        $jYearStartDate = array(
            '1989-1-8' => '平成', '1926-12-25' => '昭和', '1912-7-30' => '大正', '1868-1-25' => '明治');
        $wStrArray = array('日', '月', '火', '水', '木', '金', '土');

        // @codeCoverageIgnoreStart
        if (((float)phpversion()) >= 5.3) {
            $dateComp = date_parse_from_format('m/d/Y H:i:s', $d);
        } else {
            $dateComp = date_parse($d);
        }
        // @codeCoverageIgnoreEnd
        $dt = new DateTime();
        $dt->setDate($dateComp['year'], $dateComp['month'], $dateComp['day']);
        $dt->setTime($dateComp['hour'], $dateComp['minute'], $dateComp['second']);

        $gengoName = '';
        $gengoYear = 0;
        foreach ($jYearStartDate as $startDate => $gengo) {
            $dtStart = new DateTime($startDate);
            if ($dt->format('U') > $dtStart->format('U')) {
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
            'jlongdate' => $gengoName . ' ' . $gengoYear . $dt->format(' 年 n 月 j 日 ')
            . $wStrArray[$dt->format('w')] . '曜日',
        );
    }
}
