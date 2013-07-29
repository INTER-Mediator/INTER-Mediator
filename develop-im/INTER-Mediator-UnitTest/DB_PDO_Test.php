<?php
/*
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 11/12/14
 * Time: 14:21
 * Unit Test by PHPUnit (http://phpunit.de)
 *
 */

//require_once('PHPUnit/Framework/TestCase.php');
require_once('../INTER-Mediator/DB_Interfaces.php');
require_once('../INTER-Mediator/DB_UseSharedObjects.php');
require_once('../INTER-Mediator/DB_AuthCommon.php');
require_once('../INTER-Mediator/DB_PDO.php');
require_once('../INTER-Mediator/DB_Settings.php');
require_once('../INTER-Mediator/DB_Formatters.php');
require_once('../INTER-Mediator/DB_Proxy.php');
require_once('../INTER-Mediator/DB_Logger.php');
require_once('../INTER-Mediator/MessageStrings.php');
require_once('../INTER-Mediator/INTER-Mediator.php');

class DB_PDO_Test extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');

        $this->db_proxy = new DB_Proxy(true);
        $this->db_proxy->initialize(array(
                array(
                    'records' => 1,
                    'paging' => true,
                    'name' => 'person',
                    'key' => 'id',
                    'query' => array( /* array( 'field'=>'id', 'value'=>'5', 'operator'=>'eq' ),*/),
                    'sort' => array(array('field' => 'id', 'direction' => 'asc'),),
                    'sequence' => 'im_sample.serial',
                )
            ),
            array(
                'authentication' => array( // table only, for all operations
                    'user' => array('user1'), // Itemize permitted users
                    'group' => array('group2'), // Itemize permitted groups
                    'privilege' => array(), // Itemize permitted privileges
                    'user-table' => 'authuser', // Default value
                    'group-table' => 'authgroup',
                    'corresponding-table' => 'authcor',
                    'challenge-table' => 'issuedhash',
                    'authexpired' => '300', // Set as seconds.
                    'storing' => 'cookie-domainwide', // 'cookie'(default), 'cookie-domainwide', 'none'
                ),
            ),
            array(
                'db-class' => 'PDO',
                'dsn' => 'mysql:dbname=test_db;host=127.0.0.1',
                'user' => 'web',
                'password' => 'password',
            ),
            false);
    }

    public function testQuery1_singleRecord()
    {
        $contexts = array(
            array(
                'records' => 1,
                'name' => 'person',
                'key' => 'id',
                'sequence' => 'im_sample.serial',
            )
        );
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => 'mysql:dbname=test_db;host=127.0.0.1',
            'user' => 'web',
            'password' => 'password',
        );
        $db_proxy = new DB_Proxy(true);
        $db_proxy->initialize($contexts, $options, $dbSettings, 2, "person");
        $result = $db_proxy->getFromDB("person");
        $recordCount = $db_proxy->countQueryResult("person");
//        var_export($db_proxy->logger->errorMessage);
//        var_export($db_proxy->logger->debugMessage);
//        var_export($result);
        $this->assertTrue(count($result) == 1, "After the query, just one should be retrieved.");
        $this->assertTrue($recordCount == 3, "This table contanins 3 records");
        $this->assertTrue($result[0]["id"] == 1, "Field value is not same as the definition.");
    }

    public function testQuery2_multipleRecord()
    {
        $contexts = array(
            array(
                'records' => 100000,
                'name' => 'person',
                'key' => 'id',
            )
        );
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => 'mysql:dbname=test_db;host=127.0.0.1',
            'user' => 'web',
            'password' => 'password',
        );
        $db_proxy = new DB_Proxy(true);
        $db_proxy->initialize($contexts, $options, $dbSettings, 2, "person");
        $result = $db_proxy->getFromDB("person");
        $recordCount = $db_proxy->countQueryResult("person");
//        var_export($db_proxy->logger->errorMessage);
//        var_export($db_proxy->logger->debugMessage);
//        var_export($result);
        $this->assertTrue(count($result) == 3, "After the query, some records should be retrieved.");
        $this->assertTrue($recordCount == 3, "This table contanins 3 records");
        $this->assertTrue($result[2]["name"] === 'Anyone', "Field value is not same as the definition.");
        $this->assertTrue($result[2]["id"] == 3, "Field value is not same as the definition.");

        // INSERT person SET id=3,name='Anyone',address='Osaka, Japan',mail='msyk@msyk.net';

    }

    public function testInsertAndUpdateRecord()
    {
        $contexts = array(
            array(
                'records' => 100000,
                'name' => 'person',
                'key' => 'id',
            )
        );
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => 'mysql:dbname=test_db;host=127.0.0.1',
            'user' => 'web',
            'password' => 'password',
        );
        $db_proxy = new DB_Proxy(true);
        $db_proxy->initialize($contexts, $options, $dbSettings, 2, "person");
        $newKeyValue = $db_proxy->newToDB("person", true);
//        var_export($db_proxy->logger->errorMessage);
//        var_export($db_proxy->logger->debugMessage);
//        var_export($newKeyValue);
        $this->assertTrue($newKeyValue > 0, "If a record was created, it returns the new primary key value.");

        $contexts = array(
            array(
                'records' => 100000,
                'name' => 'person',
                'key' => 'id',
                'query' => array(
                    'field' => 'id', 'value' => $newKeyValue, 'operator' => '='
                )
            )
        );
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => 'mysql:dbname=test_db;host=127.0.0.1',
            'user' => 'web',
            'password' => 'password',
        );
        $nameValue = "unknown, oh mygod!";
        $addressValue = "anyplace, who knows!";
        $db_proxy = new DB_Proxy(true);
        $db_proxy->initialize($contexts, $options, $dbSettings, 2, "person");
        $db_proxy->dbSettings->addTargetField("name");
        $db_proxy->dbSettings->addValue($nameValue);
        $db_proxy->dbSettings->addTargetField("address");
        $db_proxy->dbSettings->addValue($addressValue);
        $result = $db_proxy->setToDB("person", true);
//        var_export($db_proxy->logger->errorMessage);
//        var_export($db_proxy->logger->debugMessage);
//        var_export($result);
        $this->assertTrue($result, "Update should be successful.");

        $contexts = array(
            array(
                'records' => 1,
                'name' => 'person',
                'key' => 'id',
                'query' => array(
                    'field' => 'id', 'value' => $newKeyValue, 'operator' => '='
                )
            )
        );
        $options = null;
        $dbSettings = array(
            'db-class' => 'PDO',
            'dsn' => 'mysql:dbname=test_db;host=127.0.0.1',
            'user' => 'web',
            'password' => 'password',
        );
        $nameValue = "unknown, oh mygod!";
        $addressValue = "anyplace, who knows!";
        $db_proxy = new DB_Proxy(true);
        $db_proxy->initialize($contexts, $options, $dbSettings, 2, "person");
        $result = $db_proxy->getFromDB("person");
        $recordCount = $db_proxy->countQueryResult("person");
//        var_export($db_proxy->logger->errorMessage);
//        var_export($db_proxy->logger->debugMessage);
//        var_export($result);
        $this->assertTrue(count($result) == 1, "It should be just one record.");
        $this->assertTrue($result[0]["name"] === $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($result[0]["address"] === $addressValue, "Field value is not same as the definition.");

    }

    public function testAuthUser1()
    {
        $testName = "Check time calc feature of PHP";
        $expiredDT = new DateTime('2012-02-13 11:32:40');
        $currentDate = new DateTime('2012-02-14 11:32:51');
        //    $expiredDT = new DateTime('2012-02-13 00:00:00');
        //    $currentDate = new DateTime('2013-04-13 01:02:03');
        $calc = $currentDate->format('U') - $expiredDT->format('U');
        $this->assertTrue($calc === (11 + 3600 * 24), $testName);
    }

    public function testAuthUser2()
    {
        $testName = "Password Retrieving";
        $username = 'user1';
        $expectedPasswd = 'd83eefa0a9bd7190c94e7911688503737a99db0154455354';

        $retrievedPasswd = $this->db_proxy->dbClass->authSupportRetrieveHashedPassword($username);
        echo var_export($this->db_proxy->logger->getDebugMessage(), true);
        $this->assertEquals($expectedPasswd, $retrievedPasswd, $testName);

    }

    public function testAuthUser3()
    {
        $testName = "Salt retrieving";
        $username = 'user1';
        $retrievedSalt = $this->db_proxy->authSupportGetSalt($username);
        $this->assertEquals('54455354', $retrievedSalt, $testName);

    }

    public function testAuthUser4()
    {
        $testName = "Generate Challenge and Retrieve it";
        $username = 'user1';
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authSupportRetrieveChallenge($username, "TEST"), $testName);
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authSupportRetrieveChallenge($username, "TEST"), $testName);
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authSupportRetrieveChallenge($username, "TEST"), $testName);

    }

    public function testAuthUser5()
    {
        $testName = "Simulation of Authentication";
        $username = 'user1';
        $password = 'user1'; //'d83eefa0a9bd7190c94e7911688503737a99db0154455354';

        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authSupportStoreChallenge($username, $challenge, "TEST");

        //        $challenge = $this->db_pdo->authSupportRetrieveChallenge($username, "TEST");
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));

        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->assertTrue(
            $this->db_proxy->checkAuthorization($username, $calcuratedHash, "TEST"), $testName);
    }

    public function testAuthUser6()
    {
        $testName = "Create New User and Authenticate";
        $username = "testuser3";
        $password = "testuser3";

        $this->assertTrue($this->db_proxy->addUser($username, $password));

        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));

        $clientId = "TEST";
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);

        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        echo $hashedvalue;

        $this->assertTrue(
            $this->db_proxy->checkAuthorization($username, hash_hmac('sha256', $hashedvalue, $challenge), $clientId),
            $testName);
    }

    function testUserGroup()
    {
        $testName = "Resolve containing group";
        $groupArray = $this->db_proxy->dbClass->authSupportGetGroupsOfUser('user1');
        echo var_export($groupArray);
        $this->assertTrue(count($groupArray) > 0, $testName);
    }

    public function testNativeUser()
    {
        $testName = "Native User Challenge Check";
        $cliendId = "12345";

        $challenge = $this->db_proxy->generateChallenge();
        echo "\ngenerated=", $challenge;
        $this->db_proxy->dbClass->authSupportStoreChallenge(0, $challenge, $cliendId);

        $this->assertTrue(
            $this->db_proxy->checkChallenge($challenge, $cliendId), $testName);
    }

}