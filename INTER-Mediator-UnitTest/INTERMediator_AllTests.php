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

class INTERMediator_AllTests extends PHPUnit_Framework_TestCase
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite( 'all tests' );
        $folder = dirname( __FILE__ ) . '/';
        $suite->addTestFile($folder . 'DB_Formatters_Test.php');
        $suite->addTestFile($folder . 'DB_PDO-MySQL_Test.php');
        $suite->addTestFile($folder . 'DB_PDO-PostgreSQL_Test.php');
        $suite->addTestFile($folder . 'DB_PDO-SQLite_Test.php');
        //$suite->addTestFile($folder . 'DB_FMS_FX_Test.php');
        $suite->addTestFile($folder . 'DB_Proxy_Test.php');
        $suite->addTestFile($folder . 'DB_Settings_Test.php');
        $suite->addTestFile($folder . 'DataConverter_AppendPrefix_Test.php');
        $suite->addTestFile($folder . 'DataConverter_AppendSuffix_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Currency_Test.php');
        $suite->addTestFile($folder . 'DataConverter_FMDateTime_Test.php');
        $suite->addTestFile($folder . 'DataConverter_HTMLString_Test.php');
        $suite->addTestFile($folder . 'DataConverter_NullZeroString_Test.php');
        $suite->addTestFile($folder . 'DataConverter_MySQLDateTime_Test.php');
        $suite->addTestFile($folder . 'DataConverter_Number_Test.php');
        $suite->addTestFile($folder . 'DataConverter_NumberBase_Test.php');
        $suite->addTestFile($folder . 'GenerateJSCode_Test.php');
        $suite->addTestFile($folder . 'IMUtil_Test.php');
        $suite->addTestFile($folder . 'INTERMediator_Test.php');
        $suite->addTestFile($folder . 'LDAPAuth_Test.php');
        $suite->addTestFile($folder . 'MediaAccess_Test.php');
        $suite->addTestFile($folder . 'MessageStrings_Test.php');
        $suite->addTestFile($folder . 'MessageStrings_ja_Test.php');
        //$suite->addTestFile($folder . 'OME_Test.php');
        /*
         * TravisCI can't test the SMTP communication. Is that no wander?
         * The test case OME_Test.php should run on the my/your own environment.
         */
        $suite->addTestFile($folder . 'RSA_Test.php');
        if (php_uname('n') !== 'inter-mediator-server') {
            $suite->addTestFile($folder . 'defedit_Test.php');
            $suite->addTestFile($folder . 'pageedit_Test.php');
        }
        return $suite;
    }
}
