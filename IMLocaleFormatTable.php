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
class IMLocaleFormatTable
{
    public static function getLocaleFormat($localeCode)
    {
        if (!isset(IMLocaleFormatTable::$localeFormatTable[$localeCode])) {
            $localeCode = 'ja_JP';
        }
        $locInfo = IMLocaleFormatTable::$localeFormatTable[$localeCode];

        return array(
            'mon_decimal_point' => $locInfo[0],
            'mon_thousands_sep' => $locInfo[1],
            'currency_symbol' => $locInfo[2]
        );
    }

    private static $localeFormatTable = array(
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
