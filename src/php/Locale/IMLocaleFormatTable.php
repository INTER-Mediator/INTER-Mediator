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

use INTERMediator\IMUtil;

/**
 * IMLocaleFormatTable provides locale-specific formatting information for INTER-Mediator.
 * It supplies methods and static tables for retrieving date, time, and currency formatting details by locale.
 */
class IMLocaleFormatTable
{
    /** Returns the current locale's formatting information, merged with static overrides if available.
     * @return array Associative array of formatting information for the current locale.
     */
    public static function getCurrentLocaleFormat(): array
    {
        $info = localeconv();
        if (isset(IMLocaleFormatTable::$localeInfoTable[IMLocale::$choosenLocale])) {
            $info = array_merge($info, IMLocaleFormatTable::$localeInfoTable[IMLocale::$choosenLocale]);
        } else {
            if (IMUtil::isPHPExecutingWindows()) {
                $info = array_merge($info, IMLocaleFormatTable::$localeInfoTable["en"]);
            } else {
                $info["ABDAY"] = array(
                    nl_langinfo(ABDAY_1),
                    nl_langinfo(ABDAY_2),
                    nl_langinfo(ABDAY_3),
                    nl_langinfo(ABDAY_4),
                    nl_langinfo(ABDAY_5),
                    nl_langinfo(ABDAY_6),
                    nl_langinfo(ABDAY_7));
                $info["DAY"] = array(
                    nl_langinfo(DAY_1),
                    nl_langinfo(DAY_2),
                    nl_langinfo(DAY_3),
                    nl_langinfo(DAY_4),
                    nl_langinfo(DAY_5),
                    nl_langinfo(DAY_6),
                    nl_langinfo(DAY_7));
                $info["ABMON"] = array(
                    nl_langinfo(ABMON_1),
                    nl_langinfo(ABMON_2),
                    nl_langinfo(ABMON_3),
                    nl_langinfo(ABMON_4),
                    nl_langinfo(ABMON_5),
                    nl_langinfo(ABMON_6),
                    nl_langinfo(ABMON_7),
                    nl_langinfo(ABMON_8),
                    nl_langinfo(ABMON_9),
                    nl_langinfo(ABMON_10),
                    nl_langinfo(ABMON_11),
                    nl_langinfo(ABMON_12));
                $info["MON"] = array(
                    nl_langinfo(MON_1),
                    nl_langinfo(MON_2),
                    nl_langinfo(MON_3),
                    nl_langinfo(MON_4),
                    nl_langinfo(MON_5),
                    nl_langinfo(MON_6),
                    nl_langinfo(MON_7),
                    nl_langinfo(MON_8),
                    nl_langinfo(MON_9),
                    nl_langinfo(MON_10),
                    nl_langinfo(MON_11),
                    nl_langinfo(MON_12));
                $info["AM_STR"] = nl_langinfo(AM_STR);
                $info["PM_STR"] = nl_langinfo(PM_STR);
                $info["D_T_FMT"] = nl_langinfo(D_T_FMT);
                $info["D_FMT"] = nl_langinfo(D_FMT);
                $info["T_FMT"] = nl_langinfo(T_FMT);
                $info["T_FMT_AMPM"] = nl_langinfo(T_FMT_AMPM);
                $info["ERA"] = nl_langinfo(ERA);
                $info["ERA_D_T_FMT"] = nl_langinfo(ERA_D_T_FMT);
                $info["ERA_D_FMT"] = nl_langinfo(ERA_D_FMT);
                $info["ERA_T_FMT"] = nl_langinfo(ERA_T_FMT);
            }
        }
        return $info;
    }

    /** Returns formatting information for a specified locale code.
     * @param string $localeCode The locale code (e.g., 'en_US', 'ja_JP').
     * @return array Associative array of formatting information for the locale.
     */
    public static function getLocaleFormat(string $localeCode): array
    {
        if (!isset(IMLocaleFormatTable::$localeFormatTable[$localeCode])) {
            $localeCode = 'ja_JP';
        }
        $locInfo = IMLocaleFormatTable::$localeFormatTable[$localeCode];

        return array(
            'mon_decimal_point' => $locInfo[0],
            'mon_thousands_sep' => $locInfo[1],
            'currency_symbol' => $locInfo[2],
            'positive_sign' => '',
            'negative_sign' => '-',
            'int_frac_digits' => '0',
            'frac_digits' => '0',
            'p_cs_precedes' => '1',
            'p_sep_by_space' => '0',
            'n_cs_precedes' => '1',
            'n_sep_by_space' => '0',
            'p_sign_posn' => '1',
            'n_sign_posn' => '4',
            'grouping' => array(
                '0' => '3',
                '1' => '3'
            ),
            'mon_grouping' => array(
                '0' => '3',
                '1' => '3'
            ),
        );
    }

    /** Static table with additional locale-specific information for days, months, and date/time formats.
     * @var array|array[]
     */
    private static array $localeInfoTable = array(
        'en' => array(  // Default
            "DAY" => array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"),
            "ABDAY" => array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"),
            "MON" => array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"),
            "ABMON" => array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"),
            "AM_STR" => "AM",
            "PM_STR" => "PM",
            "D_FMT_LONG" => "%M/%D/%Y %W",
            "T_FMT_LONG" => "%H:%M:%S",
            "D_FMT_MIDDLE" => "%M/%D/%Y",
            "T_FMT_MIDDLE" => "%H:%M:%S",
            "D_FMT_SHORT" => "%m/%d/%Y",
            "T_FMT_SHORT" => "%H:%M",
        ),
        'en_US' => array(  // Default
            "DAY" => array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"),
            "ABDAY" => array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"),
            "MON" => array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"),
            "ABMON" => array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"),
            "AM_STR" => "AM",
            "PM_STR" => "PM",
            "D_FMT_LONG" => "%A, %T %D,%Y",
            "T_FMT_LONG" => "%H:%M:%S",
            "D_FMT_MIDDLE" => "%a, %t %d,%Y",
            "T_FMT_MIDDLE" => "%H:%M:%S",
            "D_FMT_SHORT" => "%m/%d/%Y",
            "T_FMT_SHORT" => "%H:%M",
        ),
        'ja_JP' => array(
            "DAY" => array("日曜日", "月曜日", "火曜日", "水曜日", "木曜日", "金曜日", "土曜日"),
            "ABDAY" => array("日", "月", "火", "水", "木", "金", "土"),
            "MON" => array("睦月", "如月", "弥生", "卯月", "皐月", "水無月", "文月", "葉月", "長月", "神無月", "霜月", "師走"),
            "ABMON" => array("一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"),
            "AM_STR" => "午前",
            "PM_STR" => "午後",
            "D_FMT_LONG" => "%Y年%M月%D日 %W",
            "T_FMT_LONG" => "%H時%I分%S秒",
            "D_FMT_MIDDLE" => "%Y/%M/%D(%w)",
            "T_FMT_MIDDLE" => "%H:%I:%S",
            "D_FMT_SHORT" => "%Y/%m/%d",
            "T_FMT_SHORT" => "%H:%I",
        ),
    );

    /** Static table for currency and number formatting for each locale.
     * Each entry is an array: [decimal_point, thousands_separator, currency_symbol].
     * @var array|array[]
     */
    private static array $localeFormatTable = array(
        'ja' => array('.', ',', '￥'),
        'ja_JP' => array('.', ',', '￥'),
        'en_US' => array('.', ',', '$'),
        'af_ZA' => array(',', '.', 'R'),
        'am_ET' => array('.', ',', '$'),
        'hy_AM' => array('.', ',', 'ԴՐ  '),
        'eu_ES' => array(',', '.', '€'),
        'be_BY' => array(',', ' ', 'руб.'),
        'bg_BG' => array(',', ' ', 'лв.'),
        'ca_ES' => array(',', '.', '€'),
        'zh_CN' => array('.', ',', '￥'),
        'zh_HK' => array('.', ',', 'HK$'),
        'zh_TW' => array('.', ',', 'NT$'),
        'hr_HR' => array(',', ' ', 'Kn'),
        'cs_CZ' => array(',', ' ', 'Kč'),
        'da_DK' => array(',', '.', 'kr'),
        'nl_BE' => array(',', '.', '€'),
        'nl_NL' => array(',', ' ', '€'),
        'en_AU' => array('.', ',', '$'),
        'en_CA' => array('.', ',', '$'),
        'en_IE' => array('.', ',', '€'),
        'en_NZ' => array('.', ',', '$'),
        'en_GB' => array('.', ',', '£'),
        'et_EE' => array('.', ' ', 'kr'),
        'fi_FI' => array(',', '.', '€'),
        'fr_BE' => array(',', '.', '€'),
        'fr_CA' => array(',', ' ', '$'),
        'fr_FR' => array(',', ' ', '€'),
        'fr_CH' => array(',', '.', 'Fr.'),
        'de_AT' => array(',', ' ', '€'),
        'de_DE' => array(',', '.', '€'),
        'de_CH' => array(',', '.', 'Fr.'),
        'el_GR' => array(',', '.', '€'),
        'he_IL' => array('.', ',', 'שח'),
        'hu_HU' => array(',', ' ', 'Ft'),
        'is_IS' => array(',', '.', 'kr'),
        'it_IT' => array(',', '.', '€'),
        'it_CH' => array(',', '.', 'Fr.'),
        'kk_KZ' => array(',', ' ', 'тг.'),
        'ko_KR' => array('.', ',', '₩'),
        'lt_LT' => array(',', ' ', 'Lt'),
        'pl_PL' => array(',', ' ', 'zł'),
        'pt_BR' => array(',', '.', 'R$'),
        'pt_PT' => array('.', '.', '€'),
        'ro_RO' => array(',', ' ', 'Lei'),
        'ru_RU' => array(',', ' ', 'руб.'),
        'sk_SK' => array(',', ' ', 'Sk'),
        'sl_SI' => array(',', ' ', 'SIT'),
        'es_ES' => array(',', '.', '€'),
        'sv_SE' => array(',', ' ', 'kr'),
        'tr_TR' => array(',', '.', 'L'),
        'uk_UA' => array(',', ' ', 'грн.'),
    );
}

/*
 * This table was generated the following code. The big array comes from IMLocaleStringTable class.
 *
<?php
$localeStrTable = array(
	'aa' => 'Afar',
	'aa_DJ' => 'Afar_Djibouti',
	'aa_ER' => 'Afar_Eritrea',
	'aa_ET' => 'Afar_Ethiopia',
        :
	'dje' => 'Zarma',
	'dje_NE' => 'Zarma_Niger',
	'zu' => 'Zulu',
	'zu_ZA' => 'Zulu_South_Africa',
    );
$sq="'";
foreach($localeStrTable as $key=>$val){
	$s = setlocale(LC_ALL, $key);
	if($s){
	$a = localeconv();
	echo $sq.$key.$sq."=>array(".$sq.$a["mon_decimal_point"].$sq.",".$sq.$a["mon_thousands_sep"].$sq.",".$sq.$a["currency_symbol"].$sq."),\n";
	}
}
 */
