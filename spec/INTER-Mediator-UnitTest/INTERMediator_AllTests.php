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

/*
 * The test of INTER-Mediator can do with 'composer test', but macOS might report the error as like
 * "Failed to open stream: Too many open files in ...". (Masayuki Nii, 2022-07-29)
 * In that case, the following command resolve this issue.
 * https://magento.stackexchange.com/questions/314894/composer-installation-fails-with-failed-to-open-stream-too-many-open-files
 *
 * ulimit -n 10000
 */
error_reporting(E_ALL);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;

class INTERMediator_AllTests extends TestCase
{
    public static function suite()
    {
        $my = new INTERMediator_AllTests();
        return $my->testSuiteSetup();
    }

    public function testSuiteSetup()
    {
        $dontTestDB = false;
        $dontTestMySQL = false;
        $dontTestPostgreSQL = false;
        $dontTestSQLite = false;
        $dontTestDataConv = false;
        $dontTestFileMaker = true;

        $version = explode('.', phpversion());
        $versionNumber = floatval($version[0] . "." . $version[1] . $version[2]);
        if ($versionNumber < 8.1) {
            $suite = new TestSuite('all tests');
        } else {
            $suite = TestSuite::empty('all_tests');
        }
        $folder = dirname(__FILE__) . '/';

        if (!$dontTestDataConv) {
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
        }
        if (!$dontTestDB) {
            $suite->addTestFile($folder . 'DB_PDO_Test_Conditions.php');
            $suite->addTestFile($folder . 'DB_PDO_Test_UserGroup.php');
            $suite->addTestFile($folder . 'DB_PDO_Test_LocalContextConditions.php');
            $suite->addTestFile($folder . 'DB_Formatters_Test.php');
            if (!$dontTestMySQL) {
                $suite->addTestFile($folder . 'DB_PDO_MySQL_Test.php');
            }
            if (!$dontTestPostgreSQL) {
                $suite->addTestFile($folder . 'DB_PDO_PostgreSQL_Test.php');
            }
            if (!$dontTestSQLite) {
                $suite->addTestFile($folder . 'DB_PDO_SQLite_Test.php');
            }
            if (!$dontTestFileMaker) {
                $suite->addTestFile($folder . 'DB_FMS_DataAPI_Test.php');
                $suite->addTestFile($folder . 'DB_FMS_FX_Test.php');
            }
            if (!$dontTestMySQL) {
                $suite->addTestFile($folder . 'DB_Proxy_MySQL_Test.php');
            }
            if (!$dontTestPostgreSQL) {
                $suite->addTestFile($folder . 'DB_Proxy_PostgreSQL_Test.php');
            }
            if (!$dontTestSQLite) {
                $suite->addTestFile($folder . 'DB_Proxy_SQLite_Test.php');
            }
            $suite->addTestFile($folder . 'DB_Settings_Test.php');
            $suite->addTestFile($folder . 'DB_ExtSupport_Test.php');
        }
        $suite->addTestFile($folder . 'GenerateJSCode_Test.php');
        $suite->addTestFile($folder . 'IMUtil_Test.php');
        $suite->addTestFile($folder . 'INTERMediator_Test.php');
        // $suite->addTestFile($folder . 'LDAPAuth_Test.php');
        $suite->addTestFile($folder . 'MediaAccess_Test.php');
        $suite->addTestFile($folder . 'MessageStrings_Test.php');
        $suite->addTestFile($folder . 'MessageStrings_ja_Test.php');
        $suite->addTestFile($folder . 'Messaging_Test.php');
        $suite->addTestFile($folder . 'Line_Field_Divider_Test.php');
        $suite->addTestFile($folder . 'OME_Test.php');
        $suite->addTestFile($folder . 'Params_Test.php');
        /*
         * CI envirionment can't test the SMTP communication. Is that no wander?
         * The test case OME_Test.php has tests to send mail but they are commented.
         * If you require to check to send mail, I'd like you to run on the your own environment.
         */
        $suite->addTestFile($folder . 'VM_Test.php');
        if (php_uname('n') !== 'inter-mediator-server') {
            $suite->addTestFile($folder . 'defedit_Test.php');
            $suite->addTestFile($folder . 'pageedit_Test.php');
        }
        if ($versionNumber >= 8.1) {
            $suite->run();
        }
        $this->assertTrue(true, "Dummy test case.");

        return $suite;
    }
}
