<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

/**
 * DataConverter_template
 * @author Masayuki Nii
 * 
 * This class file is just for the documentation of the "Data Converter Class".
 * In the following codes, it explains the required methods and their specs.
 * "Data Converter Classes" are usually simple ones and you don't have to inhrit this class, 
 * however, of course this class has no function.
 * 
 * A example of "Data Converter Class" is DataConverter_FMDateTime.php.
 * It converts the string containing date and/or time. FileMaker Server accepts just m/d/Y order,
 * however Japanese date time system is Y/m/d order. DataConverter_FMDateTime class can covert 
 * the date-time data to/from each other.
 */
class DataConverter_template	{
	// "Data Converter Class" name must have the prefix "DataConverter_".

/**
 * This method converts to the data on a web browser from the data on database.
 * Requires to show the data on database.
 * @param $str This parameter is the data on database.
 * @return The data for display on a web browser.
 */
	function converterFromDBtoUser( $str )	{	}

/**
 * This method converts to the data for database from the data a user entered.
 * Requires to store to database.
 * @param $str The data a user entered.
 * @return The data for database
 */
	function converterFromUserToDB( $str )	{	}
}
?>
