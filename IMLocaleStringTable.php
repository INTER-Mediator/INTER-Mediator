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
class IMLocaleStringTable
{
    public static function getLocaleString($localeCode)
    {
        if (substr($localeCode, 0, 2) == 'ja') {
            return "jpn_jpn";
        } else if (substr($localeCode, 0, 5) == 'en_US') {
            return "English_United_States";
        }
        return IMLocaleStringTable::$localeStrTable[$localeCode];
    }

    /*
     * This table is generated from Microsoft site:
     * https://msdn.microsoft.com/en-us/library/cc233982.aspx
     */
    private static $localeStrTable = array(
        'aa' => 'Afar',
        'aa_DJ' => 'Afar_Djibouti',
        'aa_ER' => 'Afar_Eritrea',
        'aa_ET' => 'Afar_Ethiopia',
        'af' => 'Afrikaans',
        'af_NA' => 'Afrikaans_Namibia',
        'af_ZA' => 'Afrikaans_South_Africa',
        'agq' => 'Aghem',
        'agq_CM' => 'Aghem_Cameroon',
        'ak' => 'Akan',
        'ak_GH' => 'Akan_Ghana',
        'sq' => 'Albanian',
        'sq_AL' => 'Albanian_Albania',
        'sq_MK' => 'Albanian_Macedonia,_FYRO',
        'gsw' => 'Alsatian',
        'gsw_FR' => 'Alsatian_France',
        'gsw_LI' => 'Alsatian_Liechtenstein',
        'gsw_CH' => 'Alsatian_Switzerland',
        'am' => 'Amharic',
        'am_ET' => 'Amharic_Ethiopia',
        'ar' => 'Arabic',
        'ar_DZ' => 'Arabic_Algeria',
        'ar_BH' => 'Arabic_Bahrain',
        'ar_TD' => 'Arabic_Chad',
        'ar_KM' => 'Arabic_Comoros',
        'ar_DJ' => 'Arabic_Djibouti',
        'ar_EG' => 'Arabic_Egypt',
        'ar_ER' => 'Arabic_Eritrea',
        'ar_IQ' => 'Arabic_Iraq',
        'ar_IL' => 'Arabic_Israel',
        'ar_JO' => 'Arabic_Jordan',
        'ar_KW' => 'Arabic_Kuwait',
        'ar_LB' => 'Arabic_Lebanon',
        'ar_LY' => 'Arabic_Libya',
        'ar_MR' => 'Arabic_Mauritania',
        'ar_MA' => 'Arabic_Morocco',
        'ar_OM' => 'Arabic_Oman',
        'ar_PS' => 'Arabic_Palestinian_Authority',
        'ar_QA' => 'Arabic_Qatar',
        'ar_SA' => 'Arabic_Saudi_Arabia',
        'ar_SO' => 'Arabic_Somalia',
        'ar_SS' => 'Arabic_South_Sudan',
        'ar_SD' => 'Arabic_Sudan',
        'ar_SY' => 'Arabic_Syria',
        'ar_TN' => 'Arabic_Tunisia',
        'ar_AE' => 'Arabic_U.A.E.',
        'ar_001' => 'Arabic_World',
        'ar_YE' => 'Arabic_Yemen',
        'hy' => 'Armenian',
        'hy_AM' => 'Armenian_Armenia',
        'as' => 'Assamese',
        'as_IN' => 'Assamese_India',
        'ast' => 'Asturian',
        'ast_ES' => 'Asturian_Spain',
        'asa' => 'Asu',
        'asa_TZ' => 'Asu_Tanzania',
        'az_Cyrl' => 'Azerbaijani_(Cyrillic)',
        'az_Cyrl_AZ' => 'Azerbaijani_(Cyrillic)_Azerbaijan',
        'az' => 'Azerbaijani_(Latin)',
        'az_Latn' => 'Azerbaijani_(Latin)',
        'az_Latn_AZ' => 'Azerbaijani_(Latin)_Azerbaijan',
        'ksf' => 'Bafia',
        'ksf_CM' => 'Bafia_Cameroon',
        'bm' => 'Bamanankan',
        'bm_Latn_ML' => 'Bamanankan_(Latin)_Mali',
        'bn' => 'Bangla',
        'bn_BD' => 'Bangla_Bangladesh',
        'bn_IN' => 'Bangla_India',
        'bas' => 'Basaa',
        'bas_CM' => 'Basaa_Cameroon',
        'ba' => 'Bashkir',
        'ba_RU' => 'Bashkir_Russia',
        'eu' => 'Basque',
        'eu_ES' => 'Basque_Spain',
        'be' => 'Belarusian',
        'be_BY' => 'Belarusian_Belarus',
        'bem' => 'Bemba',
        'bem_ZM' => 'Bemba_Zambia',
        'bez' => 'Bena',
        'bez_TZ' => 'Bena_Tanzania',
        'byn' => 'Blin',
        'byn_ER' => 'Blin_Eritrea',
        'brx' => 'Bodo',
        'brx_IN' => 'Bodo_India',
        'bs_Cyrl' => 'Bosnian_(Cyrillic)',
        'bs_Cyrl_BA' => 'Bosnian_(Cyrillic)_Bosnia_and_Herzegovina',
        'bs_Latn' => 'Bosnian_(Latin)',
        'bs' => 'Bosnian_(Latin)',
        'bs_Latn_BA' => 'Bosnian_(Latin)_Bosnia_and_Herzegovina',
        'br' => 'Breton',
        'br_FR' => 'Breton_France',
        'bg' => 'Bulgarian',
        'bg_BG' => 'Bulgarian_Bulgaria',
        'my' => 'Burmese',
        'my_MM' => 'Burmese_Myanmar',
        'yue' => 'Cantonese',
        'yue_HK' => 'Cantonese_Hong_Kong_SAR',
        'ca' => 'Catalan',
        'ca_AD' => 'Catalan_Andorra',
        'ca_FR' => 'Catalan_France',
        'ca_IT' => 'Catalan_Italy',
        'ca_ES' => 'Catalan_Spain',
        'tzm_Latn_MA' => 'Central_Atlas_Tamazight_(Latin)_Morocco',
        'ku' => 'Central_Kurdish',
        'ku_Arab' => 'Central_Kurdish',
        'ku_Arab_IQ' => 'Central_Kurdish_Iraq',
        'cd_RU' => 'Chechen_Russia',
        'chr' => 'Cherokee',
        'chr_Cher' => 'Cherokee',
        'chr_Cher_US' => 'Cherokee_United_States',
        'cgg' => 'Chiga',
        'cgg_UG' => 'Chiga_Uganda',
        'zh_Hans' => 'Chinese_(Simplified)',
        'zh' => 'Chinese_(Simplified)',
        'zh_CN' => 'Chinese_(Simplified)_People\'s_Republic_of_China',
        'zh_SG' => 'Chinese_(Simplified)_Singapore',
        'zh_Hant' => 'Chinese_(Traditional)',
        'zh_HK' => 'Chinese_(Traditional)_Hong_Kong_S.A.R.',
        'zh_MO' => 'Chinese_(Traditional)_Macao_S.A.R.',
        'zh_TW' => 'Chinese_(Traditional)_Taiwan',
        'cu_RU' => 'Church_Slavic_Russia',
        'swc' => 'Congo_Swahili',
        'swc_CD' => 'Congo_Swahili_Congo_DRC',
        'kw' => 'Cornish',
        'kw_GB' => 'Cornish_United_Kingdom',
        'co' => 'Corsican',
        'co_FR' => 'Corsican_France',
        'bs, hr, or sr' => 'Croatian',
        'hr_HR' => 'Croatian_Croatia',
        'hr_BA' => 'Croatian_(Latin)_Bosnia_and_Herzegovina',
        'cs' => 'Czech',
        'cs_CZ' => 'Czech_Czech_Republic',
        'da' => 'Danish',
        'da_DK' => 'Danish_Denmark',
        'da_GL' => 'Danish_Greenland',
        'prs' => 'Dari',
        'prs_AF' => 'Dari_Afghanistan',
        'dv' => 'Divehi',
        'dv_MV' => 'Divehi_Maldives',
        'dua' => 'Duala',
        'dua_CM' => 'Duala_Cameroon',
        'nl' => 'Dutch',
        'nl_AW' => 'Dutch_Aruba',
        'nl_BE' => 'Dutch_Belgium',
        'nl_BQ' => 'Dutch_Bonaire,_Sint_Eustatius_and_Saba',
        'nl_CW' => 'Dutch_Curaçao',
        'nl_NL' => 'Dutch_Netherlands',
        'nl_SX' => 'Dutch_Sint_Maarten',
        'nl_SR' => 'Dutch_Suriname',
        'dz' => 'Dzongkha',
        'dz_BT' => 'Dzongkha_Bhutan',
        'ebu' => 'Embu',
        'ebu_KE' => 'Embu_Kenya',
        'en' => 'English',
        'en_AS' => 'English_American_Samoa',
        'en_AI' => 'English_Anguilla',
        'en_AG' => 'English_Antigua_and_Barbuda',
        'en_AU' => 'English_Australia',
        'en_AT' => 'English_Austria',
        'en_BS' => 'English_Bahamas',
        'en_BB' => 'English_Barbados',
        'en_BE' => 'English_Belgium',
        'en_BZ' => 'English_Belize',
        'en_BM' => 'English_Bermuda',
        'en_BW' => 'English_Botswana',
        'en_IO' => 'English_British_Indian_Ocean_Territory',
        'en_VG' => 'English_British_Virgin_Islands',
        'en_BI' => 'English_Burundi',
        'en_CM' => 'English_Cameroon',
        'en_CA' => 'English_Canada',
        'en_029' => 'English_Caribbean',
        'en_KY' => 'English_Cayman_Islands',
        'en_CX' => 'English_Christmas_Island',
        'en_CC' => 'English_Cocos_[Keeling]_Islands',
        'en_CK' => 'English_Cook_Islands',
        'en_CY' => 'English_Cyprus',
        'en_DK' => 'English_Denmark',
        'en_DM' => 'English_Dominica',
        'en_ER' => 'English_Eritrea',
        'en_150' => 'English_Europe',
        'en_FK' => 'English_Falkland_Islands',
        'en_FI' => 'English_Finland',
        'en_FJ' => 'English_Fiji',
        'en_GM' => 'English_Gambia',
        'en_DE' => 'English_Germany',
        'en_GH' => 'English_Ghana',
        'en_GI' => 'English_Gibraltar',
        'en_GD' => 'English_Grenada',
        'en_GU' => 'English_Guam',
        'en_GG' => 'English_Guernsey',
        'en_GY' => 'English_Guyana',
        'en_HK' => 'English_Hong_Kong',
        'en_IN' => 'English_India',
        'en_IE' => 'English_Ireland',
        'en_IM' => 'English_Isle_of_Man',
        'en_IL' => 'English_Israel',
        'en_JM' => 'English_Jamaica',
        'en_JE' => 'English_Jersey',
        'en_KE' => 'English_Kenya',
        'en_KI' => 'English_Kiribati',
        'en_LS' => 'English_Lesotho',
        'en_LR' => 'English_Liberia',
        'en_MO' => 'English_Macao_SAR',
        'en_MG' => 'English_Madagascar',
        'en_MW' => 'English_Malawi',
        'en_MY' => 'English_Malaysia',
        'en_MT' => 'English_Malta',
        'en_MH' => 'English_Marshall_Islands',
        'en_MU' => 'English_Mauritius',
        'en_FM' => 'English_Micronesia',
        'en_MS' => 'English_Montserrat',
        'en_NA' => 'English_Namibia',
        'en_NR' => 'English_Nauru',
        'en_NL' => 'English_Netherlands',
        'en_NZ' => 'English_New_Zealand',
        'en_NG' => 'English_Nigeria',
        'en_NU' => 'English_Niue',
        'en_NF' => 'English_Norfolk_Island',
        'en_MP' => 'English_Northern_Mariana_Islands',
        'en_PK' => 'English_Pakistan',
        'en_PW' => 'English_Palau',
        'en_PG' => 'English_Papua_New_Guinea',
        'en_PN' => 'English_Pitcairn_Islands',
        'en_PR' => 'English_Puerto_Rico',
        'en_PH' => 'English_Republic_of_the_Philippines',
        'en_RW' => 'English_Rwanda',
        'en_KN' => 'English_Saint_Kitts_and_Nevis',
        'en_LC' => 'English_Saint_Lucia',
        'en_VC' => 'English_Saint_Vincent_and_the_Grenadines',
        'en_WS' => 'English_Samoa',
        'en_SC' => 'English_Seychelles',
        'en_SL' => 'English_Sierra_Leone',
        'en_SG' => 'English_Singapore',
        'en_SX' => 'English_Sint_Maarten',
        'en_SI' => 'English_Slovenia',
        'en_SB' => 'English_Solomon_Islands',
        'en_ZA' => 'English_South_Africa',
        'en_SS' => 'English_South_Sudan',
        'en_SH' => 'English_St_Helena,_Ascension,_Tristan_da_Cunha',
        'en_SD' => 'English_Sudan',
        'en_SZ' => 'English_Swaziland',
        'en_SE' => 'English_Sweden',
        'en_CH' => 'English_Switzerland',
        'en_TZ' => 'English_Tanzania',
        'en_TK' => 'English_Tokelau',
        'en_TO' => 'English_Tonga',
        'en_TT' => 'English_Trinidad_and_Tobago',
        'en_TC' => 'English_Turks_and_Caicos_Islands',
        'en_TV' => 'English_Tuvalu',
        'en_UG' => 'English_Uganda',
        'en_GB' => 'English_United_Kingdom',
        'en_US' => 'English_United_States',
        'en_UM' => 'English_US_Minor_Outlying_Islands',
        'en_VI' => 'English_US_Virgin_Islands',
        'en_VU' => 'English_Vanuatu',
        'en_001' => 'English_World',
        'en_ZM' => 'English_Zambia',
        'en_ZW' => 'English_Zimbabwe',
        'eo' => 'Esperanto',
        'eo_001' => 'Esperanto_World',
        'et' => 'Estonian',
        'et_EE' => 'Estonian_Estonia',
        'ee' => 'Ewe',
        'ee_GH' => 'Ewe_Ghana',
        'ee_TG' => 'Ewe_Togo',
        'ewo' => 'Ewondo',
        'ewo_CM' => 'Ewondo_Cameroon',
        'fo' => 'Faroese',
        'fo_DK' => 'Faroese_Denmark',
        'fo_FO' => 'Faroese_Faroe_Islands',
        'fil' => 'Filipino',
        'fil_PH' => 'Filipino_Philippines',
        'fi' => 'Finnish',
        'fi_FI' => 'Finnish_Finland',
        'fr' => 'French',
        'fr_DZ' => 'French_Algeria',
        'fr_BE' => 'French_Belgium',
        'fr_BJ' => 'French_Benin',
        'fr_BF' => 'French_Burkina_Faso',
        'fr_BI' => 'French_Burundi',
        'fr_CM' => 'French_Cameroon',
        'fr_CA' => 'French_Canada',
        'fr_CF' => 'French_Central_African_Republic',
        'fr_TD' => 'French_Chad',
        'fr_KM' => 'French_Comoros',
        'fr_CG' => 'French_Congo',
        'fr_CD' => 'French_Congo,_DRC',
        'fr_CI' => 'French_Côte_d\'Ivoire',
        'fr_DJ' => 'French_Djibouti',
        'fr_GQ' => 'French_Equatorial_Guinea',
        'fr_FR' => 'French_France',
        'fr_GF' => 'French_French_Guiana',
        'fr_PF' => 'French_French_Polynesia',
        'fr_GA' => 'French_Gabon',
        'fr_GP' => 'French_Guadeloupe',
        'fr_GN' => 'French_Guinea',
        'fr_HT' => 'French_Haiti',
        'fr_LU' => 'French_Luxembourg',
        'fr_MG' => 'French_Madagascar',
        'fr_ML' => 'French_Mali',
        'fr_MQ' => 'French_Martinique',
        'fr_MR' => 'French_Mauritania',
        'fr_MU' => 'French_Mauritius',
        'fr_YT' => 'French_Mayotte',
        'fr_MA' => 'French_Morocco',
        'fr_NC' => 'French_New_Caledonia',
        'fr_NE' => 'French_Niger',
        'fr_MC' => 'French_Principality_of_Monaco',
        'fr_RE' => 'French_Reunion',
        'fr_RW' => 'French_Rwanda',
        'fr_BL' => 'French_Saint_Barthélemy',
        'fr_MF' => 'French_Saint_Martin',
        'fr_PM' => 'French_Saint_Pierre_and_Miquelon',
        'fr_SN' => 'French_Senegal',
        'fr_SC' => 'French_Seychelles',
        'fr_CH' => 'French_Switzerland',
        'fr_SY' => 'French_Syria',
        'fr_TG' => 'French_Togo',
        'fr_TN' => 'French_Tunisia',
        'fr_VU' => 'French_Vanuatu',
        'fr_WF' => 'French_Wallis_and_Futuna',
        'fy' => 'Frisian',
        'fy_NL' => 'Frisian_Netherlands',
        'fur' => 'Friulian',
        'fur_IT' => 'Friulian_Italy',
        'ff' => 'Fulah',
        'ff_Latn' => 'Fulah',
        'ff_CM' => 'Fulah_Cameroon',
        'ff_GN' => 'Fulah_Guinea',
        'ff_MR' => 'Fulah_Mauritania',
        'ff_Latn_SN' => 'Fulah_Senegal',
        'gl' => 'Galician',
        'gl_ES' => 'Galician_Spain',
        'lg' => 'Ganda',
        'lg_UG' => 'Ganda_Uganda',
        'ka' => 'Georgian',
        'ka_GE' => 'Georgian_Georgia',
        'de' => 'German',
        'de_AT' => 'German_Austria',
        'de_BE' => 'German_Belgium',
        'de_DE' => 'German_Germany',
        'de_IT' => 'German_Italy',
        'de_LI' => 'German_Liechtenstein',
        'de_LU' => 'German_Luxembourg',
        'de_CH' => 'German_Switzerland',
        'el' => 'Greek',
        'el_CY' => 'Greek_Cyprus',
        'el_GR' => 'Greek_Greece',
        'kl' => 'Greenlandic',
        'kl_GL' => 'Greenlandic_Greenland',
        'gn' => 'Guarani',
        'gn_PY' => 'Guarani_Paraguay',
        'gu' => 'Gujarati',
        'gu_IN' => 'Gujarati_India',
        'guz' => 'Gusii',
        'guz_KE' => 'Gusii_Kenya',
        'ha' => 'Hausa_(Latin)',
        'ha_Latn' => 'Hausa_(Latin)',
        'ha_Latn_GH' => 'Hausa_(Latin)_Ghana',
        'ha_Latn_NE' => 'Hausa_(Latin)_Niger',
        'ha_Latn_NG' => 'Hausa_(Latin)_Nigeria',
        'haw' => 'Hawaiian',
        'haw_US' => 'Hawaiian_United_States',
        'he' => 'Hebrew',
        'he_IL' => 'Hebrew_Israel',
        'hi' => 'Hindi',
        'hi_IN' => 'Hindi_India',
        'hu' => 'Hungarian',
        'hu_HU' => 'Hungarian_Hungary',
        'is' => 'Icelandic',
        'is_IS' => 'Icelandic_Iceland',
        'ig' => 'Igbo',
        'ig_NG' => 'Igbo_Nigeria',
        'id' => 'Indonesian',
        'id_ID' => 'Indonesian_Indonesia',
        'ia' => 'Interlingua',
        'ia_FR' => 'Interlingua_France',
        'ia_001' => 'Interlingua_World',
        'iu' => 'Inuktitut_(Latin)',
        'iu_Latn' => 'Inuktitut_(Latin)',
        'iu_Latn_CA' => 'Inuktitut_(Latin)_Canada',
        'iu_Cans' => 'Inuktitut_(Syllabics)',
        'iu_Cans_CA' => 'Inuktitut_(Syllabics)_Canada',
        'ga' => 'Irish',
        'ga_IE' => 'Irish_Ireland',
        'it' => 'Italian',
        'it_IT' => 'Italian_Italy',
        'it_SM' => 'Italian_San_Marino',
        'it_CH' => 'Italian_Switzerland',
        'ja' => 'Japanese',
        'ja_JP' => 'Japanese_Japan',
        'jv' => 'Javanese',
        'jv_Latn' => 'Javanese_Latin',
        'jv_Latn_ID' => 'Javanese_Latin,_Indonesia',
        'dyo' => 'Jola_Fonyi',
        'dyo_SN' => 'Jola_Fonyi_Senegal',
        'kea' => 'Kabuverdianu',
        'kea_CV' => 'Kabuverdianu_Cabo_Verde',
        'kab' => 'Kabyle',
        'kab_DZ' => 'Kabyle_Algeria',
        'kkj' => 'Kako',
        'kkj_CM' => 'Kako_Cameroon',
        'kln' => 'Kalenjin',
        'kln_KE' => 'Kalenjin_Kenya',
        'kam' => 'Kamba',
        'kam_KE' => 'Kamba_Kenya',
        'kn' => 'Kannada',
        'kn_IN' => 'Kannada_India',
        'ks' => 'Kashmiri',
        'ks_Arab' => 'Kashmiri_Perso_Arabic',
        'ks_Arab_IN' => 'Kashmiri_Perso_Arabic',
        'kk' => 'Kazakh',
        'kk_KZ' => 'Kazakh_Kazakhstan',
        'km' => 'Khmer',
        'km_KH' => 'Khmer_Cambodia',
        'quc' => 'K\'iche',
        'quc_Latn_GT' => 'K\'iche_Guatemala',
        'ki' => 'Kikuyu',
        'ki_KE' => 'Kikuyu_Kenya',
        'rw' => 'Kinyarwanda',
        'rw_RW' => 'Kinyarwanda_Rwanda',
        'sw' => 'Kiswahili',
        'sw_KE' => 'Kiswahili_Kenya',
        'sw_TZ' => 'Kiswahili_Tanzania',
        'sw_UG' => 'Kiswahili_Uganda',
        'kok' => 'Konkani',
        'kok_IN' => 'Konkani_India',
        'ko' => 'Korean',
        'ko_KR' => 'Korean_Korea',
        'ko_KP' => 'Korean_North_Korea',
        'khq' => 'Koyra_Chiini',
        'khq_ML' => 'Koyra_Chiini_Mali',
        'ses' => 'Koyraboro_Senni',
        'ses_ML' => 'Koyraboro_Senni_Mali',
        'nmg' => 'Kwasio',
        'nmg_CM' => 'Kwasio_Cameroon',
        'ky' => 'Kyrgyz',
        'ky_KG' => 'Kyrgyz_Kyrgyzstan',
        'ku_Arab_IR' => 'Kurdish_Perso_Arabic,_Iran',
        'lkt' => 'Lakota',
        'lkt_US' => 'Lakota_United_States',
        'lag' => 'Langi',
        'lag_TZ' => 'Langi_Tanzania',
        'lo' => 'Lao',
        'lo_LA' => 'Lao_Lao_P.D.R.',
        'lv' => 'Latvian',
        'lv_LV' => 'Latvian_Latvia',
        'ln' => 'Lingala',
        'ln_AO' => 'Lingala_Angola',
        'ln_CF' => 'Lingala_Central_African_Republic',
        'ln_CG' => 'Lingala_Congo',
        'ln_CD' => 'Lingala_Congo_DRC',
        'lt' => 'Lithuanian',
        'lt_LT' => 'Lithuanian_Lithuania',
        'nds' => 'Low_German',
        'nds_DE' => 'Low_German_Germany',
        'nds_NL' => 'Low_German_Netherlands',
        'dsb' => 'Lower_Sorbian',
        'dsb_DE' => 'Lower_Sorbian_Germany',
        'lu' => 'Luba_Katanga',
        'lu_CD' => 'Luba_Katanga_Congo_DRC',
        'luo' => 'Luo',
        'luo_KE' => 'Luo_Kenya',
        'lb' => 'Luxembourgish',
        'lb_LU' => 'Luxembourgish_Luxembourg',
        'luy' => 'Luyia',
        'luy_KE' => 'Luyia_Kenya',
        'mk' => 'Macedonian',
        'mk_MK' => 'Macedonian_Macedonia_(Former_Yugoslav_Republic_of_Macedonia)',
        'jmc' => 'Machame',
        'jmc_TZ' => 'Machame_Tanzania',
        'mgh' => 'Makhuwa_Meetto',
        'mgh_MZ' => 'Makhuwa_Meetto_Mozambique',
        'kde' => 'Makonde',
        'kde_TZ' => 'Makonde_Tanzania',
        'mg' => 'Malagasy',
        'mg_MG' => 'Malagasy_Madagascar',
        'ms' => 'Malay',
        'ms_BN' => 'Malay_Brunei_Darussalam',
        'ms_MY' => 'Malay_Malaysia',
        'ml' => 'Malayalam',
        'ml_IN' => 'Malayalam_India',
        'mt' => 'Maltese',
        'mt_MT' => 'Maltese_Malta',
        'gv' => 'Manx',
        'gv_IM' => 'Manx_Isle_of_Man',
        'mi' => 'Maori',
        'mi_NZ' => 'Maori_New_Zealand',
        'arn' => 'Mapudungun',
        'arn_CL' => 'Mapudungun_Chile',
        'mr' => 'Marathi',
        'mr_IN' => 'Marathi_India',
        'mas' => 'Masai',
        'mas_KE' => 'Masai_Kenya',
        'mas_TZ' => 'Masai_Tanzania',
        'mzn_IR' => 'Mazanderani_Iran',
        'mer' => 'Meru',
        'mer_KE' => 'Meru_Kenya',
        'mgo' => 'Meta\'',
        'mgo_CM' => 'Meta\'_Cameroon',
        'moh' => 'Mohawk',
        'moh_CA' => 'Mohawk_Canada',
        'mn' => 'Mongolian_(Cyrillic)',
        'mn_Cyrl' => 'Mongolian_(Cyrillic)',
        'mn_MN' => 'Mongolian_(Cyrillic)_Mongolia',
        'mn_Mong' => 'Mongolian_(Traditional_Mongolian)',
        'mn_Mong_CN' => 'Mongolian_(Traditional_Mongolian)_People\'s_Republic_of_China',
        'mn_Mong_MN' => 'Mongolian_(Traditional_Mongolian)_Mongolia',
        'mfe' => 'Morisyen',
        'mfe_MU' => 'Morisyen_Mauritius',
        'mua' => 'Mundang',
        'mua_CM' => 'Mundang_Cameroon',
        'nqo' => 'N\'ko',
        'nqo_GN' => 'N\'ko_Guinea',
        'naq' => 'Nama',
        'naq_NA' => 'Nama_Namibia',
        'ne' => 'Nepali',
        'ne_IN' => 'Nepali_India',
        'ne_NP' => 'Nepali_Nepal',
        'nnh' => 'Ngiemboon',
        'nnh_CM' => 'Ngiemboon_Cameroon',
        'jgo' => 'Ngomba',
        'jgo_CM' => 'Ngomba_Cameroon',
        'lrc_IQ' => 'Northern_Luri_Iraq',
        'lrc_IR' => 'Northern_Luri_Iran',
        'nd' => 'North_Ndebele',
        'nd_ZW' => 'North_Ndebele_Zimbabwe',
        'no' => 'Norwegian_(Bokmal)',
        'nb' => 'Norwegian_(Bokmal)',
        'nb_NO' => 'Norwegian_(Bokmal)_Norway',
        'nn' => 'Norwegian_(Nynorsk)',
        'nn_NO' => 'Norwegian_(Nynorsk)_Norway',
        'nb_SJ' => 'Norwegian_Bokmål_Svalbard_and_Jan_Mayen',
        'nus' => 'Nuer',
        'nus_SD' => 'Nuer_Sudan',
        'nyn' => 'Nyankole',
        'nyn_UG' => 'Nyankole_Uganda',
        'oc' => 'Occitan',
        'oc_FR' => 'Occitan_France',
        'or' => 'Odia',
        'or_IN' => 'Odia_India',
        'om' => 'Oromo',
        'om_ET' => 'Oromo_Ethiopia',
        'om_KE' => 'Oromo_Kenya',
        'os' => 'Ossetian',
        'os_GE' => 'Ossetian_Cyrillic,_Georgia',
        'os_RU' => 'Ossetian_Cyrillic,_Russia',
        'ps' => 'Pashto',
        'ps_AF' => 'Pashto_Afghanistan',
        'fa' => 'Persian',
        'fa_AF' => 'Persian_Afghanistan',
        'fa_IR' => 'Persian_Iran',
        'pl' => 'Polish',
        'pl_PL' => 'Polish_Poland',
        'pt' => 'Portuguese',
        'pt_AO' => 'Portuguese_Angola',
        'pt_BR' => 'Portuguese_Brazil',
        'pt_CV' => 'Portuguese_Cabo_Verde',
        'pt_GQ' => 'Portuguese_Equatorial_Guinea',
        'pt_GW' => 'Portuguese_Guinea_Bissau',
        'pt_LU' => 'Portuguese_Luxembourg',
        'pt_MO' => 'Portuguese_Macao_SAR',
        'pt_MZ' => 'Portuguese_Mozambique',
        'pt_PT' => 'Portuguese_Portugal',
        'pt_ST' => 'Portuguese_São_Tomé_and_Príncipe',
        'pt_CH' => 'Portuguese_Switzerland',
        'pt_TL' => 'Portuguese_Timor_Leste',
        'prg_001' => 'Prussian',
        'qps_ploca' => 'Pseudo_Language_Pseudo_locale_for_east_Asian/complex_script_localization_testing',
        'qps_ploc' => 'Pseudo_Language_Pseudo_locale_used_for_localization_testing',
        'qps_plocm' => 'Pseudo_Language_Pseudo_locale_used_for_localization_testing_of_mirrored_locales',
        'pa' => 'Punjabi',
        'pa_Arab' => 'Punjabi',
        'pa_IN' => 'Punjabi_India',
        'pa_Arab_PK' => 'Punjabi_Islamic_Republic_of_Pakistan',
        'quz' => 'Quechua',
        'quz_BO' => 'Quechua_Bolivia',
        'quz_EC' => 'Quechua_Ecuador',
        'quz_PE' => 'Quechua_Peru',
        'ksh' => 'Ripuarian',
        'ksh_DE' => 'Ripuarian_Germany',
        'ro' => 'Romanian',
        'ro_MD' => 'Romanian_Moldova',
        'ro_RO' => 'Romanian_Romania',
        'rm' => 'Romansh',
        'rm_CH' => 'Romansh_Switzerland',
        'rof' => 'Rombo',
        'rof_TZ' => 'Rombo_Tanzania',
        'rn' => 'Rundi',
        'rn_BI' => 'Rundi_Burundi',
        'ru' => 'Russian',
        'ru_BY' => 'Russian_Belarus',
        'ru_KZ' => 'Russian_Kazakhstan',
        'ru_KG' => 'Russian_Kyrgyzstan',
        'ru_MD' => 'Russian_Moldova',
        'ru_RU' => 'Russian_Russia',
        'ru_UA' => 'Russian_Ukraine',
        'rwk' => 'Rwa',
        'rwk_TZ' => 'Rwa_Tanzania',
        'ssy' => 'Saho',
        'ssy_ER' => 'Saho_Eritrea',
        'sah' => 'Sakha',
        'sah_RU' => 'Sakha_Russia',
        'saq' => 'Samburu',
        'saq_KE' => 'Samburu_Kenya',
        'smn' => 'Sami_(Inari)',
        'smn_FI' => 'Sami_(Inari)_Finland',
        'smj' => 'Sami_(Lule)',
        'smj_NO' => 'Sami_(Lule)_Norway',
        'smj_SE' => 'Sami_(Lule)_Sweden',
        'se' => 'Sami_(Northern)',
        'se_FI' => 'Sami_(Northern)_Finland',
        'se_NO' => 'Sami_(Northern)_Norway',
        'se_SE' => 'Sami_(Northern)_Sweden',
        'sms' => 'Sami_(Skolt)',
        'sms_FI' => 'Sami_(Skolt)_Finland',
        'sma' => 'Sami_(Southern)',
        'sma_NO' => 'Sami_(Southern)_Norway',
        'sma_SE' => 'Sami_(Southern)_Sweden',
        'sg' => 'Sango',
        'sg_CF' => 'Sango_Central_African_Republic',
        'sbp' => 'Sangu',
        'sbp_TZ' => 'Sangu_Tanzania',
        'sa' => 'Sanskrit',
        'sa_IN' => 'Sanskrit_India',
        'gd' => 'Scottish_Gaelic',
        'gd_GB' => 'Scottish_Gaelic_United_Kingdom',
        'seh' => 'Sena',
        'seh_MZ' => 'Sena_Mozambique',
        'sr_Cyrl' => 'Serbian_(Cyrillic)',
        'sr_Cyrl_BA' => 'Serbian_(Cyrillic)_Bosnia_and_Herzegovina',
        'sr_Cyrl_ME' => 'Serbian_(Cyrillic)_Montenegro',
        'sr_Cyrl_RS' => 'Serbian_(Cyrillic)_Serbia',
        'sr_Cyrl_CS' => 'Serbian_(Cyrillic)_Serbia_and_Montenegro_(Former)',
        'sr_Latn' => 'Serbian_(Latin)',
        'sr' => 'Serbian_(Latin)',
        'sr_Latn_BA' => 'Serbian_(Latin)_Bosnia_and_Herzegovina',
        'sr_Latn_ME' => 'Serbian_(Latin)_Montenegro',
        'sr_Latn_RS' => 'Serbian_(Latin)_Serbia',
        'sr_Latn_CS' => 'Serbian_(Latin)_Serbia_and_Montenegro_(Former)',
        'nso' => 'Sesotho_sa_Leboa',
        'nso_ZA' => 'Sesotho_sa_Leboa_South_Africa',
        'tn' => 'Setswana',
        'tn_BW' => 'Setswana_Botswana',
        'tn_ZA' => 'Setswana_South_Africa',
        'ksb' => 'Shambala',
        'ksb_TZ' => 'Shambala_Tanzania',
        'sn' => 'Shona',
        'sn_Latn' => 'Shona_Latin',
        'sn_Latn_ZW' => 'Shona_Zimbabwe',
        'sd' => 'Sindhi',
        'sd_Arab' => 'Sindhi',
        'sd_Arab_PK' => 'Sindhi_Islamic_Republic_of_Pakistan',
        'si' => 'Sinhala',
        'si_LK' => 'Sinhala_Sri_Lanka',
        'sk' => 'Slovak',
        'sk_SK' => 'Slovak_Slovakia',
        'sl' => 'Slovenian',
        'sl_SI' => 'Slovenian_Slovenia',
        'xog' => 'Soga',
        'xog_UG' => 'Soga_Uganda',
        'so' => 'Somali',
        'so_DJ' => 'Somali_Djibouti',
        'so_ET' => 'Somali_Ethiopia',
        'so_KE' => 'Somali_Kenya',
        'so_SO' => 'Somali_Somalia',
        'st' => 'Sotho',
        'st_ZA' => 'Sotho_South_Africa',
        'nr' => 'South_Ndebele',
        'nr_ZA' => 'South_Ndebele_South_Africa',
        'st_LS' => 'Southern_Sotho_Lesotho',
        'es' => 'Spanish',
        'es_AR' => 'Spanish_Argentina',
        'es_VE' => 'Spanish_Bolivarian_Republic_of_Venezuela',
        'es_BO' => 'Spanish_Bolivia',
        'es_BR' => 'Spanish_Brazil',
        'es_CL' => 'Spanish_Chile',
        'es_CO' => 'Spanish_Colombia',
        'es_CR' => 'Spanish_Costa_Rica',
        'es_CU' => 'Spanish_Cuba',
        'es_DO' => 'Spanish_Dominican_Republic',
        'es_EC' => 'Spanish_Ecuador',
        'es_SV' => 'Spanish_El_Salvador',
        'es_GQ' => 'Spanish_Equatorial_Guinea',
        'es_GT' => 'Spanish_Guatemala',
        'es_HN' => 'Spanish_Honduras',
        'es_419' => 'Spanish_Latin_America',
        'es_MX' => 'Spanish_Mexico',
        'es_NI' => 'Spanish_Nicaragua',
        'es_PA' => 'Spanish_Panama',
        'es_PY' => 'Spanish_Paraguay',
        'es_PE' => 'Spanish_Peru',
        'es_PH' => 'Spanish_Philippines',
        'es_PR' => 'Spanish_Puerto_Rico',
        'es_ES_tradnl' => 'Spanish_Spain',
        'es_ES' => 'Spanish_Spain',
        'es_US' => 'Spanish_United_States',
        'es_UY' => 'Spanish_Uruguay',
        'zgh' => 'Standard_Moroccan_Tamazight',
        'zgh_Tfng_MA' => 'Standard_Moroccan_Tamazight_Morocco',
        'zgh_Tfng' => 'Standard_Moroccan_Tamazight_Tifinagh',
        'ss' => 'Swati',
        'ss_ZA' => 'Swati_South_Africa',
        'ss_SZ' => 'Swati_Swaziland',
        'sv' => 'Swedish',
        'sv_AX' => 'Swedish_Åland_Islands',
        'sv_FI' => 'Swedish_Finland',
        'sv_SE' => 'Swedish_Sweden',
        'syr' => 'Syriac',
        'syr_SY' => 'Syriac_Syria',
        'shi' => 'Tachelhit',
        'shi_Tfng' => 'Tachelhit_Tifinagh',
        'shi_Tfng_MA' => 'Tachelhit_Tifinagh,_Morocco',
        'shi_Latn' => 'Tachelhit_(Latin)',
        'shi_Latn_MA' => 'Tachelhit_(Latin)_Morocco',
        'dav' => 'Taita',
        'dav_KE' => 'Taita_Kenya',
        'tg' => 'Tajik_(Cyrillic)',
        'tg_Cyrl' => 'Tajik_(Cyrillic)',
        'tg_Cyrl_TJ' => 'Tajik_(Cyrillic)_Tajikistan',
        'tzm' => 'Tamazight_(Latin)',
        'tzm_Latn' => 'Tamazight_(Latin)',
        'tzm_Latn_DZ' => 'Tamazight_(Latin)_Algeria',
        'ta' => 'Tamil',
        'ta_IN' => 'Tamil_India',
        'ta_MY' => 'Tamil_Malaysia',
        'ta_SG' => 'Tamil_Singapore',
        'ta_LK' => 'Tamil_Sri_Lanka',
        'twq' => 'Tasawaq',
        'twq_NE' => 'Tasawaq_Niger',
        'tt' => 'Tatar',
        'tt_RU' => 'Tatar_Russia',
        'te' => 'Telugu',
        'te_IN' => 'Telugu_India',
        'teo' => 'Teso',
        'teo_KE' => 'Teso_Kenya',
        'teo_UG' => 'Teso_Uganda',
        'th' => 'Thai',
        'th_TH' => 'Thai_Thailand',
        'bo' => 'Tibetan',
        'bo_IN' => 'Tibetan_India',
        'bo_CN' => 'Tibetan_People\'s_Republic_of_China',
        'tig' => 'Tigre',
        'tig_ER' => 'Tigre_Eritrea',
        'ti' => 'Tigrinya',
        'ti_ER' => 'Tigrinya_Eritrea',
        'ti_ET' => 'Tigrinya_Ethiopia',
        'to' => 'Tongan',
        'to_TO' => 'Tongan_Tonga',
        'ts' => 'Tsonga',
        'ts_ZA' => 'Tsonga_South_Africa',
        'tr' => 'Turkish',
        'tr_CY' => 'Turkish_Cyprus',
        'tr_TR' => 'Turkish_Turkey',
        'tk' => 'Turkmen',
        'tk_TM' => 'Turkmen_Turkmenistan',
        'uk' => 'Ukrainian',
        'uk_UA' => 'Ukrainian_Ukraine',
        'dsb or hsb' => 'Upper_Sorbian',
        'hsb_DE' => 'Upper_Sorbian_Germany',
        'ur' => 'Urdu',
        'ur_IN' => 'Urdu_India',
        'ur_PK' => 'Urdu_Islamic_Republic_of_Pakistan',
        'ug' => 'Uyghur',
        'ug_CN' => 'Uyghur_People\'s_Republic_of_China',
        'uz_Arab' => 'Uzbek_Perso_Arabic',
        'uz_Arab_AF' => 'Uzbek_Perso_Arabic,_Afghanistan',
        'uz_Cyrl' => 'Uzbek_(Cyrillic)',
        'uz_Cyrl_UZ' => 'Uzbek_(Cyrillic)_Uzbekistan',
        'uz' => 'Uzbek_(Latin)',
        'uz_Latn' => 'Uzbek_(Latin)',
        'uz_Latn_UZ' => 'Uzbek_(Latin)_Uzbekistan',
        'vai' => 'Vai',
        'vai_Vaii' => 'Vai',
        'vai_Vaii_LR' => 'Vai_Liberia',
        'vai_Latn_LR' => 'Vai_(Latin)_ Liberia',
        'vai_Latn' => 'Vai_(Latin)',
        'ca_ES_valencia' => 'Valencian_Spain',
        've' => 'Venda',
        've_ZA' => 'Venda_South_Africa',
        'vi' => 'Vietnamese',
        'vi_VN' => 'Vietnamese_Vietnam',
        'vo' => 'Volapük',
        'vo_001' => 'Volapük_World',
        'vun' => 'Vunjo',
        'vun_TZ' => 'Vunjo_Tanzania',
        'wae' => 'Walser',
        'wae_CH' => 'Walser_Switzerland',
        'cy' => 'Welsh',
        'cy_GB' => 'Welsh_United_Kingdom',
        'wal' => 'Wolaytta',
        'wal_ET' => 'Wolaytta_Ethiopia',
        'wo' => 'Wolof',
        'wo_SN' => 'Wolof_Senegal',
        'xh' => 'Xhosa',
        'xh_ZA' => 'Xhosa_South_Africa',
        'yav' => 'Yangben',
        'yav_CM' => 'Yangben_Cameroon',
        'ii' => 'Yi',
        'ii_CN' => 'Yi_People\'s_Republic_of_China',
        'yo' => 'Yoruba',
        'yo_BJ' => 'Yoruba_Benin',
        'yo_NG' => 'Yoruba_Nigeria',
        'dje' => 'Zarma',
        'dje_NE' => 'Zarma_Niger',
        'zu' => 'Zulu',
        'zu_ZA' => 'Zulu_South_Africa',
    );
}