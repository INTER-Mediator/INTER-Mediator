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
class DataConverter_template
{
    // "Data Converter Class" name must have the prefix "DataConverter_".

    /**
     * This method converts to the data on a web browser from the data on database.
     * Requires to show the data on database.
     * @param $str This parameter is the data on database.
     * @return The data for display on a web browser.
     */
    function converterFromDBtoUser($str)
    {
    }

    /**
     * This method converts to the data for database from the data a user entered.
     * Requires to store to database.
     * @param $str The data a user entered.
     * @return The data for database
     */
    function converterFromUserToDB($str)
    {
    }
}
