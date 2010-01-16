<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

require_once( 'DataConverter_NumberBase.php' );
class DataConverter_FMDateTime extends DataConverter_NumberBase	{
	
	var $d = null;
	
	function __construct( $digits )	{
		parent::__construct();
		$this->d = $digits;
	}

	function converterFromDBtoUser( $str )	{
		return number_format( $str, $this->d, $this->decimalMark, $this->thSepMark );
	}
}
?>
