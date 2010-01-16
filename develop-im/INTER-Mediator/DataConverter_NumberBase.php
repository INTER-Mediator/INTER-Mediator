<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

class DataConverter_NumberBase	{
	
	var $decimalMark = null;
	var $thSepMark = null;
	var $currencyMark = null;
	
	function __construct()	{
		$lstr = strtolower( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
		// Extracting first item and cutting the priority infos.
		if ( strpos( $lstr, ',' ) !== false )	$lstr = substr( $lstr, 0, strpos( $lstr, ',' ));
		if ( strpos( $lstr, ';' ) !== false )	$lstr = substr( $lstr, 0, strpos( $lstr, ';' ));
		
		/* Special procedures for specific language */
		if ( $lstr == 'ja' )	$lstr = 'ja_JP';
		
		// Convert to the right locale identifier.
		if ( strpos( $lstr, '-' ) !== false )	{
			$lstr = explode( '-', $lstr );
		} else if ( strpos( $lstr, '_' ) !== false )	{
			$lstr = explode( '_', $lstr );
		} else	{
			$lstr = array( $lstr );
		}
		if ( count($lstr) == 1 )
			$loc = $lstr[0];
		else
			$loc = "$lstr[0]_" . strtoupper($lstr[1]);
		setlocale( LC_TIME, $loc );
		$locInfo = localeconv;
		$this->decimalMark = $locInfo[ 'mon_decimal_point' ];
		$this->thSepMark = $locInfo[ 'mon_thousands_sep' ];
		$this->currencyMark = $locInfo[ 'currency_symbol' ];
	}
	
	function converterFromUserToDB( $str )	{
		$comp = explode( $this->decimalMark, $str );
		$intPart = intval( str_replace( $this->thSepMark, '', $comp[0] ));
		if ( isset( $comp[1] ))	{
			$decimalPart = intval( str_replace( $this->thSepMark, '', $comp[1] ));
			return floatval( strval( $intPart ) . '.' . strval( $decimalPart ));
		} else {
			return $intPart;
		}
	}
}
?>
