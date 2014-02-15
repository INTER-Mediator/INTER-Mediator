<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

class INTERMediator_AllTests extends PHPUnit_Framework_TestCase
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite( 'all tests' );
        $folder = dirname( __FILE__ ) . '/';
        $suite->addTestFile($folder . 'DB_PDO-MySQL_Test.php');
        $suite->addTestFile($folder . 'DB_PDO-PostgreSQL_Test.php');
        $suite->addTestFile($folder . 'DB_PDO-SQLite_Test.php');
        $suite->addTestFile($folder . 'DB_Proxy_Test.php');
        $suite->addTestFile($folder . 'DB_Settings_Test.php');
        $suite->addTestFile($folder . 'DataConverter_AppendPrefix_Test.php');
        $suite->addTestFile($folder . 'DataConverter_AppendSuffix_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Currency_Test.php');
        $suite->addTestFile($folder . 'DataConverter_FMDateTime_Test.php');
        $suite->addTestFile($folder . 'DataConverter_HTMLString_Test.php');
        $suite->addTestFile($folder . 'DataConverter_MySQLDateTime_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Number_Test.php');
        $suite->addTestFile($folder . 'DataConverter_NumberBase_Test.php');
        $suite->addTestFile($folder . 'INTERMediator_Test.php');
        $suite->addTestFile($folder . 'MediaAccess_Test.php');
        $suite->addTestFile($folder . 'MessageStrings_Test.php');
        $suite->addTestFile($folder . 'MessageStrings_ja_Test.php');
        //$suite->addTestFile($folder . 'OME_Test.php');
        /*
         * TravisCI can't test the SMTP communication. Is that no wander?
         * The test case OME_Test.php should run on the my/your own environment.
         */
        $suite->addTestFile($folder . 'RSA_Test.php');
        return $suite;
    }
}