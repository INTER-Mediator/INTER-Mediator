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

use DateTime;
use INTERMediator\Locale\IMLocale;

/**
 *
 */
class FMDateTime
{

    /**
     * @var string
     */
    private string $tz = 'Asia/Tokyo'; // Should be custimizable.
    /**
     * @var bool
     */
    private bool $useMbstring;
    /**
     * @var string
     */
    private string $choosenLocale;
    /**
     * @var string|int
     */
    private string $fmt;

    /**
     *
     * @param integer $format
     * @return unknown_type
     */
    public function __construct(string $format = '')
    {
        $this->fmt = $format;
        IMLocale::setLocale(LC_ALL);
        $this->choosenLocale = IMLocale::$choosenLocale;
        $this->useMbstring = IMLocale::$useMbstring;
        date_default_timezone_set($this->tz);
    }

    /**
     * @param string $str
     * @return string
     * @throws \Exception
     */
    public function converterFromDBtoUser(?string $str): string
    {
        if ($str === array()) {
            return '';
        }
        $str = str_replace(".", "/", $str);
        $sp = strpos($str, ' ');
        $slash = substr_count($str, '/');
        $colon = substr_count($str, ':');
        $dtObj = false;
        $fmt = 'Y-m-d H:i:s';
        if (($sp !== FALSE) && ($slash === 2) && ($colon === 2)) {
            $sep = explode(' ', $str);
            $comp = explode('/', $sep[0]);
            $dtObj = new DateTime($comp[2] . '-' . $comp[0] . '-' . $comp[1] . ' ' . $sep[1]);
            $fmt = 'Y-m-d H:i:s';
        } elseif (($sp === FALSE) && ($slash === 2) && ($colon === 0)) {
            $comp = explode('/', $str);
            $dtObj = new DateTime($comp[2] . '-' . $comp[0] . '-' . $comp[1]);
            $fmt = 'Y-m-d';
        } elseif (($sp === FALSE) && ($slash === 0) && ($colon === 2)) {
            $dtObj = new DateTime($str);
            $fmt = 'H:i:s';
        }
        if ($dtObj === false) {
            return $str;
        }
        return date(($this->fmt == '') ? $fmt : $this->fmt, $dtObj->format('U'));
    }

    /**
     * @param string $str
     * @return string
     */
    public function converterFromUserToDB(string $str): string
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

    /**
     * @param string $d
     * @return array
     * @throws \Exception
     */
    public function dateArrayFromFMDate(string $d): array
    {
        if ($d == '') {
            return '';
        }
        $jYearStartDate = array(
            '2019-5-1' => '令和', '1989-1-8' => '平成', '1926-12-25' => '昭和', '1912-7-30' => '大正', '1868-1-25' => '明治');
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
            if ($dt->format('U') >= $dtStart->format('U')) {
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
