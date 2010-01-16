<?php
/*
 * INTER-Mediator
 * by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 * This project started at the end of 2009.
 * 
 */
class DataConverter_FMDateTime	{
	
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
	}

	function converterFromDBtoUser( $str )	{
		$sp = strpos ( $str , ' ' );
		$slash = substr_count ( $str , '/' );
		$colon = substr_count ( $str , ':' );
		$dtObj = false;
		if ( ( $sp !== FALSE ) && ( $slash == 2 ) && ( $colon == 2 ) )	{
			$sep = explode( ' ', $str );
			$comp = explode( '/', $sep[0] );
			$dtObj = new DateTime( $comp[2] . '-' . $comp[0] . '-' . $comp[1] . ' ' . $sep[1] );
			$fmt = '%x %T';
		} elseif ( ( $sp === FALSE ) && ( $slash == 2 ) && ( $colon == 0 ) )	{
			$comp = explode( '/', $str );
			$dtObj = new DateTime( $comp[2] . '-' . $comp[0] . '-' . $comp[1] );
			$fmt = '%x';
		} elseif ( ( $sp === FALSE ) && ( $slash == 0 ) && ( $colon == 2 ) )	{
			$dtObj = new DateTime( $str );
			$fmt = '%T';
		}
		if ( $dtObj === false )	{	return $str;	}
		return strftime( $fmt, $dtObj->format('U') );
	}

	function converterFromUserToDB( $str )	{
		$dtAr = date_parse( $str );
		if ( $dtAr === false )	return $str;
		$dt = '';
		if ( $dtAr['year'] !== false && $dtAr['hour'] !== false )
			$dt = "{$dtAr['month']}/{$dtAr['day']}/{$dtAr['year']} {$dtAr['hour']}:{$dtAr['minute']}:{$dtAr['second']}";
		else if ( $dtAr['year'] !== false )
			$dt = "{$dtAr['month']}/{$dtAr['day']}/{$dtAr['year']}";
		else if ( $dtAr['hour'] !== false )
			$dt = "{$dtAr['hour']}:{$dtAr['minute']}:{$dtAr['second']}";
		return $dt;
	}

}
?>
