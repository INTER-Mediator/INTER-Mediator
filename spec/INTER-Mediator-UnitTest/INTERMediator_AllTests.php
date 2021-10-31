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

error_reporting(E_ALL);

use \PHPUnit\Framework\TestCase;
use \PHPUnit\Framework\TestSuite;

//$imRoot = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
//require "{$imRoot}" . DIRECTORY_SEPARATOR .'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

class INTERMediator_AllTests extends TestCase
{
    public static function suite()
    {
        $dontTestDB = false;
        $dontTestFileMaker = true;

        $suite = new TestSuite('all tests');
        $folder = dirname(__FILE__) . '/';
        $suite->addTestFile($folder . 'DataConverter_Currency_YenIM_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Currency_YenIntl_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Currency_DollerIM_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Currency_DollerIntl_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Currency_PoundIM_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Currency_PoundIntl_Test.php');
        $suite->addTestFile($folder . 'DataConverter_AppendPrefix_Test.php');
        $suite->addTestFile($folder . 'DataConverter_AppendSuffix_Test.php');
        $suite->addTestFile($folder . 'DataConverter_FMDateTime_Test.php');
        $suite->addTestFile($folder . 'DataConverter_HTMLString_Test.php');
        $suite->addTestFile($folder . 'DataConverter_NullZeroString_Test.php');
        $suite->addTestFile($folder . 'DataConverter_MySQLDateTime_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Number_Test.php');
        $suite->addTestFile($folder . 'DataConverter_NumberBase_Test.php');
        if (!$dontTestDB) {
            $suite->addTestFile($folder . 'DB_Formatters_Test.php');
            $suite->addTestFile($folder . 'DB_PDO_MySQL_Test.php');
            $suite->addTestFile($folder . 'DB_PDO_PostgreSQL_Test.php');
            $suite->addTestFile($folder . 'DB_PDO_SQLite_Test.php');
            if (!$dontTestFileMaker) {
                $suite->addTestFile($folder . 'DB_FMS_DataAPI_Test.php');
                $suite->addTestFile($folder . 'DB_FMS_FX_Test.php');
            }
            $suite->addTestFile($folder . 'DB_Proxy_MySQL_Test.php');
            $suite->addTestFile($folder . 'DB_Proxy_PostgreSQL_Test.php');
            $suite->addTestFile($folder . 'DB_Proxy_SQLite_Test.php');
            $suite->addTestFile($folder . 'DB_Settings_Test.php');
            $suite->addTestFile($folder . 'DB_ExtSupport_Test.php');
        }
        $suite->addTestFile($folder . 'GenerateJSCode_Test.php');
        $suite->addTestFile($folder . 'IMUtil_Test.php');
        $suite->addTestFile($folder . 'INTERMediator_Test.php');
        $suite->addTestFile($folder . 'LDAPAuth_Test.php');
        $suite->addTestFile($folder . 'MediaAccess_Test.php');
        $suite->addTestFile($folder . 'MessageStrings_Test.php');
        $suite->addTestFile($folder . 'MessageStrings_ja_Test.php');
        $suite->addTestFile($folder . 'Messaging_Test.php');
        $suite->addTestFile($folder . 'Line_Field_Divider_Test.php');
        $suite->addTestFile($folder . 'OME_Test.php');
        /*
         * CI envirionment can't test the SMTP communication. Is that no wander?
         * The test case OME_Test.php has tests to send mail but they are commented.
         * If you require to check to send mail, I'd like you to run on the your own environment.
         */
        $suite->addTestFile($folder . 'RSA_Test.php');
        $suite->addTestFile($folder . 'VM_Test.php');
        if (php_uname('n') !== 'inter-mediator-server') {
            $suite->addTestFile($folder . 'defedit_Test.php');
            $suite->addTestFile($folder . 'pageedit_Test.php');
        }
        return $suite;
    }
}
