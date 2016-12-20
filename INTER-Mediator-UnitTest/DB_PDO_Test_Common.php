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
require_once(dirname(__FILE__) . '/../DB_Interfaces.php');
require_once(dirname(__FILE__) . '/../DB_UseSharedObjects.php');
require_once(dirname(__FILE__) . '/../DB_AuthCommon.php');
require_once(dirname(__FILE__) . '/../DB_PDO.php');
require_once(dirname(__FILE__) . '/../DB_Settings.php');
require_once(dirname(__FILE__) . '/../DB_Formatters.php');
require_once(dirname(__FILE__) . '/../DB_Proxy.php');
require_once(dirname(__FILE__) . '/../DB_Logger.php');
require_once(dirname(__FILE__) . '/../MessageStrings.php');
require_once(dirname(__FILE__) . '/../INTER-Mediator.php');
require_once(dirname(__FILE__) . '/../OAuthAuth.php');

abstract class DB_PDO_Test_Common extends PHPUnit_Framework_TestCase
{
    protected $db_proxy;
    protected $schemaName = "";

    abstract function dbProxySetupForAccess($contextName, $maxRecord);

    abstract function dbProxySetupForAuth();

    abstract function dbProxySetupForAggregation();

    function setUp()
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');
    }

    public function testAggregation()
    {
        $this->dbProxySetupForAggregation();
        $result = $this->db_proxy->readFromDB("summary");
        $recordCount = $this->db_proxy->countQueryResult("summary");
        $this->assertEquals(is_array($result) ? count($result) : -1, 10, "After the query, 10 records should be retrieved.");
        $this->assertEquals($recordCount, 10, "The aggregation didn't count real record, and should match with records key");
        $cStr = "Onion";
        $this->assertEquals(substr($result[0]["item_name"], 0, strlen($cStr)), $cStr, "Field value is not same as the definition(1).");
        $this->assertEquals($result[0]["total"], 219510, "Field value is not same as the definition(2).");
        $cStr = "Broccoli";
        $this->assertEquals(substr($result[9]["item_name"], 0, strlen($cStr)), $cStr, "Field value is not same as the definition(3).");
        $this->assertEquals($result[9]["total"], 91225, "Field value is not same as the definition(4).");
        // the data in the name filed of the item_master table have trailing garbage. OMG
    }

    public function testQuery1_singleRecord()
    {
        $this->dbProxySetupForAccess("person", 1);
        $result = $this->db_proxy->readFromDB("person");
        $recordCount = $this->db_proxy->countQueryResult("person");
        $this->assertTrue((is_array($result) ? count($result) : -1) == 1, "After the query, just one should be retrieved.");
        $this->assertTrue($recordCount == 3, "This table contanins 3 records");
        $this->assertTrue($result[0]["id"] == 1, "Field value is not same as the definition.");
    }

    public function testQuery2_multipleRecord()
    {
        $this->dbProxySetupForAccess("person", 1000000);
        $result = $this->db_proxy->readFromDB("person");
        $recordCount = $this->db_proxy->countQueryResult("person");
        $this->assertTrue((is_array($result) ? count($result) : -1) == 3, "After the query, some records should be retrieved.");
        $this->assertTrue($recordCount == 3, "This table contanins 3 records");
        $this->assertTrue($result[2]["name"] === 'Anyone', "Field value is not same as the definition.");
        $this->assertTrue($result[2]["id"] == 3, "Field value is not same as the definition.");
    }

    public function testInsertAndUpdateRecord()
    {
        $this->dbProxySetupForAccess("contact", 1000000);
        $this->db_proxy->requireUpdatedRecord(true);
        $newKeyValue = $this->db_proxy->createInDB(true);
//        $msg = $this->db_proxy->logger->getErrorMessages();
//        var_dump($msg);
//        $msg = $this->db_proxy->logger->getDebugMessages();
//        var_dump($msg);
        $this->assertTrue($newKeyValue > 0, "If a record was created, it returns the new primary key value.");
        $createdRecord = $this->db_proxy->updatedRecord();
        $this->assertTrue($createdRecord != null, "Created record should be exists.");
        $this->assertTrue(count($createdRecord) == 1, "It should be just one record.");

        $this->dbProxySetupForAccess("person", 1000000);
        $this->db_proxy->requireUpdatedRecord(true);
        $newKeyValue = $this->db_proxy->createInDB(true);
        $this->assertTrue($newKeyValue > 0, "If a record was created, it returns the new primary key value.");
        $createdRecord = $this->db_proxy->updatedRecord();
        $this->assertTrue($createdRecord != null, "Created record should be exists.");
        $this->assertTrue(count($createdRecord) == 1, "It should be just one record.");

        $nameValue = "unknown, oh mygod!";
        $addressValue = "anyplace, who knows!";
        $this->dbProxySetupForAccess("person", 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $newKeyValue);
        $this->db_proxy->dbSettings->addTargetField("name");
        $this->db_proxy->dbSettings->addValue($nameValue);
        $this->db_proxy->dbSettings->addTargetField("address");
        $this->db_proxy->dbSettings->addValue($addressValue);
        $this->db_proxy->requireUpdatedRecord(true);
        $result = $this->db_proxy->updateDB("person", true);
        $createdRecord = $this->db_proxy->updatedRecord();
        $this->assertTrue($createdRecord != null, "Update record should be exists.");
        $this->assertTrue(count($createdRecord) == 1, "It should be just one record.");
        $this->assertTrue($createdRecord[0]["name"] === $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($createdRecord[0]["address"] === $addressValue, "Field value is not same as the definition.");

        $this->dbProxySetupForAccess("person", 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $newKeyValue);
        $result = $this->db_proxy->readFromDB("person");
        $recordCount = $this->db_proxy->countQueryResult("person");
        $this->assertTrue(count($result) == 1, "It should be just one record.");
        $this->assertTrue($result[0]["name"] === $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($result[0]["address"] === $addressValue, "Field value is not same as the definition.");
    }

    public function testCopySingleRecord()
    {
        $this->dbProxySetupForAccess("person", 1000000);
        $result = $this->db_proxy->readFromDB("person");
        $recordCount = $this->db_proxy->countQueryResult("person");

//        echo "===ckeckpoint1===";
//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());

        $parentId = $result[rand(0, $recordCount - 1)]["id"];
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $parentId);
        $this->db_proxy->copyInDB("person");

//        echo "===ckeckpoint2===";
//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());

        $this->dbProxySetupForAccess("person", 1000000, "contact");
        $result = $this->db_proxy->readFromDB("person");
        $recordCountAfter = $this->db_proxy->countQueryResult("person");
        $this->assertTrue($recordCount + 1 == $recordCountAfter,
            "After copy a record, the count of records should increase one.");
    }

    public function testCopyAssociatedRecords()
    {
        $this->dbProxySetupForAccess("person", 1000000);
        $result = $this->db_proxy->readFromDB("person");
        $recordCountPerson = $this->db_proxy->countQueryResult("person");

        $parentId = $result[rand(0, $recordCountPerson - 1)]["id"];

        $this->dbProxySetupForAccess("contact", 1000000);
        $result = $this->db_proxy->readFromDB("contact");
        $recordCountContact = $this->db_proxy->countQueryResult("contact");

        $this->dbProxySetupForAccess("contact", 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria("person_id", "=", $parentId);
        $result = $this->db_proxy->readFromDB("contact");
        $recordCountIncrease = $this->db_proxy->countQueryResult("contact");

        $this->dbProxySetupForAccess("person", 1000000, "contact");
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $parentId);
        $this->db_proxy->dbSettings->addAssociated("contact", "person_id", $parentId);
        $this->db_proxy->copyInDB("person");

//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());

        $this->dbProxySetupForAccess("person", 1000000);
        $result = $this->db_proxy->readFromDB("person");
        $recordCountPersonAfter = $this->db_proxy->countQueryResult("person");
        $this->assertTrue($recordCountPerson + 1 == $recordCountPersonAfter,
            "After copy a record, the count of records should increase one.");

        $this->dbProxySetupForAccess("contact", 1000000);
        $result = $this->db_proxy->readFromDB("contact");
        $recordCountContactAfter = $this->db_proxy->countQueryResult("contact");
        $this->assertTrue($recordCountContact + $recordCountIncrease == $recordCountContactAfter,
            "After copy a record, the count of associated records should increase one or more."
            . "[$recordCountContact, $recordCountIncrease, $recordCountContactAfter]");

    }

    public function testAuthUser1()
    {
        $testName = "Check time calc feature of PHP";
        $expiredDT = new DateTime('2012-02-13 11:32:40');
        $currentDate = new DateTime('2012-02-14 11:32:51');
        $calc = $currentDate->format('U') - $expiredDT->format('U');
        $this->assertTrue($calc === (11 + 3600 * 24), $testName);
    }

    public function testAuthUser2()
    {
        $this->dbProxySetupForAuth();

        $testName = "Password Retrieving";
        $username = 'user1';
        $expectedPasswd = 'd83eefa0a9bd7190c94e7911688503737a99db0154455354';

        $retrievedPasswd = $this->db_proxy->dbClass->authSupportRetrieveHashedPassword($username);
        $this->assertEquals($expectedPasswd, $retrievedPasswd, $testName);
    }

    public function testAuthUser3()
    {
        $this->dbProxySetupForAuth();

        $testName = "Salt retrieving";
        $username = 'user1';
        $retrievedSalt = $this->db_proxy->authSupportGetSalt($username);
        $this->assertEquals('54455354', $retrievedSalt, $testName);
    }

    public function testAuthUser4()
    {
        $this->dbProxySetupForAuth();

        $testName = "Generate Challenge and Retrieve it";
        $username = 'user1';
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authSupportStoreChallenge($username, $challenge, "TEST");
        $retrieved = $this->db_proxy->dbClass->authSupportRetrieveChallenge($username, "TEST");
        $this->assertEquals($challenge, $retrieved, $testName);

        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authSupportRetrieveChallenge($username, "TEST"), $testName);

        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authSupportStoreChallenge($username, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authSupportRetrieveChallenge($username, "TEST"), $testName);
    }

    public function testAuthUser5()
    {
        $this->dbProxySetupForAuth();

        $testName = "Simulation of Authentication";
        $username = 'user1';
        $password = 'user1'; //'d83eefa0a9bd7190c94e7911688503737a99db0154455354';
        $uid = $this->db_proxy->dbClass->authSupportGetUserIdFromUsername($username);

        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->dbClass->authSupportStoreChallenge($uid, $challenge, "TEST");

        //        $challenge = $this->db_pdo->authSupportRetrieveChallenge($username, "TEST");
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));

        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->assertTrue(
            $this->db_proxy->checkAuthorization($username, $calcuratedHash, "TEST"), $testName);
    }

    public function testAuthByValidUser()
    {
        $this->dbProxySetupForAuth();

        $testName = "Simulation of Authentication by Valid User";
        $username = 'user1';
        $password = 'user1'; //'d83eefa0a9bd7190c94e7911688503737a99db0154455354';
        $clientId = 'test1234test1234';

        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->db_proxy->dbSettings->setCurrentUser($username);
        $this->db_proxy->dbSettings->setDataSourceName("person");
        $this->db_proxy->paramAuthUser = $username;
        $this->db_proxy->clientId = $clientId;
        $this->db_proxy->paramResponse = $calcuratedHash;

        $this->db_proxy->processingRequest("read");
        $result = $this->db_proxy->getDatabaseResult();
        $this->assertTrue((is_array($result) ? count($result) : -1) == $this->db_proxy->getDatabaseResultCount(), $testName);

        //based on INSERT person SET id=2,name='Someone',address='Tokyo, Japan',mail='msyk@msyk.net';
        foreach ($result as $index => $record) {
            if ($record["id"] == 2) {
                $this->assertTrue($record["name"] == "Someone", $testName);
                $this->assertTrue($record["address"] == 'Tokyo, Japan', $testName);
            }
        }
    }

    public function testAuthByInvalidUsder()
    {
        $this->dbProxySetupForAuth();

        $testName = "Simulation of Authentication by Inalid User";
        $username = 'user2';
        $password = 'user2';
        $clientId = 'test1234test1234';

        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->db_proxy->dbSettings->setCurrentUser($username);
        $this->db_proxy->dbSettings->setDataSourceName("person");
        $this->db_proxy->paramAuthUser = $username;
        $this->db_proxy->clientId = $clientId;
        $this->db_proxy->paramResponse = $calcuratedHash;

        $this->db_proxy->processingRequest("read");
        $this->assertTrue(is_null($this->db_proxy->getDatabaseResult()), $testName);
        $this->assertTrue(is_null($this->db_proxy->getDatabaseResultCount()), $testName);
        $this->assertTrue(is_null($this->db_proxy->getDatabaseTotalCount()), $testName);
        $this->assertTrue(is_null($this->db_proxy->getDatabaseResult()), $testName);
        $this->assertTrue($this->db_proxy->dbSettings->getRequireAuthentication(), $testName);
    }

    public function testAuthUser6()
    {
        $this->dbProxySetupForAuth();

        $testName = "Create New User and Authenticate";
        $username = "testuser1";
        $password = "testuser1";

        $addUserResult = $this->db_proxy->addUser($username, $password);
        $this->assertTrue($addUserResult);

        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));

        $clientId = "TEST";
        $challenge = $this->db_proxy->generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);

        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);

        $this->assertTrue(
            $this->db_proxy->checkAuthorization($username, hash_hmac('sha256', $hashedvalue, $challenge), $clientId),
            $testName);
    }

    function testUserGroup()
    {
        $this->dbProxySetupForAuth();

        $testName = "Resolve containing group";
        $groupArray = $this->db_proxy->dbClass->authSupportGetGroupsOfUser('user1');
//        echo var_export($groupArray);
        $this->assertTrue(count($groupArray) > 0, $testName);
    }

    public function testNativeUser()
    {
        $this->dbProxySetupForAuth();

        $testName = "Native User Challenge Check";
        $cliendId = "12345";

        $challenge = $this->db_proxy->generateChallenge();
//        echo "\ngenerated=", $challenge;
        $this->db_proxy->dbClass->authSupportStoreChallenge(0, $challenge, $cliendId);

        $this->assertTrue(
            $this->db_proxy->checkChallenge($challenge, $cliendId), $testName);
    }

    public function testDefaultKey()
    {
        $this->dbProxySetupForAccess("person", 1);

        $className = get_class($this->db_proxy->dbClass);
        $this->assertEquals('id', call_user_func(array($className, 'defaultKey')));
    }

    public function testGetDefaultKey()
    {
        $this->dbProxySetupForAccess("person", 1);

        $value = $this->db_proxy->dbClass->getDefaultKey();
        $this->assertEquals('id', $value);
    }

    public function testMultiClientSyncTableExsistence()
    {
        $testName = "Tables for storing the context and ids should be existing.";
        $this->dbProxySetupForAuth();
        $this->assertTrue($this->db_proxy->dbClass->isExistRequiredTable(), $testName);
    }

    public function testMultiClientSyncRegisterAndUnregister()
    {
        $testName = "Register and Unregister.";
        $this->dbProxySetupForAuth();
        $this->db_proxy->dbClass->deleteForTest("registeredcontext");
        $this->db_proxy->dbClass->deleteForTest("registeredpks");
        $clientId = "123456789ABCDEF";
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray = array(1001, 2001, 3003, 4004);

        $entity = "table1";
        $registResult = $this->db_proxy->dbClass->register($clientId, $entity, $condition, $pkArray);
        //var_export($this->db_proxy->logger->getDebugMessage());
        $this->assertTrue($registResult !== false, "Register table1");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid" => $clientId, "entity" => $entity));
        $this->assertTrue(count($recSet) == 1, "Count table1");
        $this->assertTrue($recSet[0]["conditions"] == $condition, "the 'clientId' value in table1");
        $regId = $recSet[0]["id"];
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id" => $regId));
        $this->assertTrue(count($recSet) == 4, "Count pk values");
        $this->assertTrue(count(array_diff(
                $pkArray,
                array($recSet[0]["pk"], $recSet[1]["pk"], $recSet[2]["pk"], $recSet[3]["pk"])
            )) == 0, "Stored pk values");

        $entity = "table2";
        $this->assertTrue($this->db_proxy->dbClass->register($clientId, $entity, $condition, $pkArray) !== false,
            "Register table2");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid" => $clientId, "entity" => $entity));
        $this->assertTrue(count($recSet) == 1, "Count table1");
        $this->assertTrue($recSet[0]["conditions"] == $condition, "tha 'clientId' value in table1");
        $regId = $recSet[0]["id"];
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id" => $regId));
        $this->assertTrue(count($recSet) == 4, "Count pk values");
        $this->assertTrue(count(array_diff(
                $pkArray,
                array($recSet[0]["pk"], $recSet[1]["pk"], $recSet[2]["pk"], $recSet[3]["pk"])
            )) == 0, "Stored pk values");

        $entity = "table3";
        $this->assertTrue($this->db_proxy->dbClass->register($clientId, $entity, $condition, $pkArray) !== false,
            "Register table3");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid" => $clientId, "entity" => $entity));
        $this->assertTrue(count($recSet) == 1, "Count table1");
        $this->assertTrue($recSet[0]["conditions"] == $condition, "tha 'clientId' value in table1");
        $regId = $recSet[0]["id"];
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id" => $regId));
        $this->assertTrue(count($recSet) == 4, "Count pk values");
        $this->assertTrue(count(array_diff(
                $pkArray,
                array($recSet[0]["pk"], $recSet[1]["pk"], $recSet[2]["pk"], $recSet[3]["pk"])
            )) == 0, "Stored pk values");

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId, null), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");

    }

    public function testMultiClientSyncRegisterAndUnregisterPartial()
    {
        $testName = "Register and Unregister partically.";
        $this->dbProxySetupForAuth();
        $this->db_proxy->dbClass->deleteForTest("registeredcontext");
        $this->db_proxy->dbClass->deleteForTest("registeredpks");
        $clientId = "123456789ABCDEF";
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray = array(1001, 2001, 3003, 4004);

        $entity = "table1";
        $registResult1 = $this->db_proxy->dbClass->register($clientId, $entity, $condition, $pkArray);
        $registResult2 = $this->db_proxy->dbClass->register($clientId, $entity, $condition, $pkArray);
        $registResult3 = $this->db_proxy->dbClass->register($clientId, $entity, $condition, $pkArray);
        //var_export($this->db_proxy->logger->getDebugMessage());
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid" => $clientId, "entity" => $entity));
        $this->assertTrue(count($recSet) == 3, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id" => $registResult1));
        $this->assertTrue(count($recSet) == 4, "Count pk values");
        $this->assertTrue(count(array_diff(
                $pkArray,
                array($recSet[0]["pk"], $recSet[1]["pk"], $recSet[2]["pk"], $recSet[3]["pk"])
            )) == 0, "Stored pk values");

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId, array($registResult2)), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid" => $clientId, "entity" => $entity));
        $this->assertTrue(count($recSet) == 2, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id" => $registResult2));
        $this->assertTrue(count($recSet) == 0, "Count pk values");

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId, null), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");

    }

    public function testMultiClientSyncMatching()
    {
        $testName = "Match the sync info.";
        $this->dbProxySetupForAuth();
        $this->db_proxy->dbClass->deleteForTest("registeredcontext");
        $this->db_proxy->dbClass->deleteForTest("registeredpks");
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray1 = array(1001, 2001, 3003, 4004);
        $pkArray2 = array(9001, 8001, 3003, 4004);

        $entity = "table1";
        $clientId1 = "123456789ABCDEF";
        $this->assertTrue($this->db_proxy->dbClass->register($clientId1, $entity, $condition, $pkArray1) !== false, $testName);
        $clientId2 = "ZZYYEEDDFF39887";
        $this->assertTrue($this->db_proxy->dbClass->register($clientId2, $entity, $condition, $pkArray2) !== false, $testName);

        $result = $this->db_proxy->dbClass->matchInRegisterd($clientId2, $entity, array(3003));
        $this->assertTrue(count($result) == 1, "Count matching");
        $this->assertTrue($result[0] == $clientId1, "Matched client id");

        $result = $this->db_proxy->dbClass->matchInRegisterd($clientId2, $entity, array(2001));
        $this->assertTrue(count($result) == 1, "Count matching");
        $this->assertTrue($result[0] == $clientId1, "Matched client id");

        $result = $this->db_proxy->dbClass->matchInRegisterd($clientId2, $entity, array(4567));
        $this->assertTrue(count($result) == 0, "Count matching");

        $result = $this->db_proxy->dbClass->matchInRegisterd($clientId2, $entity, array(8001));
        $this->assertTrue(count($result) == 0, "Count matching");

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId1, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId2, null) !== false, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");
    }

    public function testMultiClientSyncAppend()
    {
        $testName = "Append Sync Info.";
        $this->dbProxySetupForAuth();
        $this->db_proxy->dbClass->deleteForTest("registeredcontext");
        $this->db_proxy->dbClass->deleteForTest("registeredpks");
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray1 = array(1001, 2001, 3003, 4004);
        $pkArray2 = array(9001, 8001, 3003, 4004);

        $entity = "table1";
        $clientId1 = "123456789ABCDEF";
        $this->assertTrue($this->db_proxy->dbClass->register($clientId1, $entity, $condition, $pkArray1) !== false, $testName);
        $clientId2 = "ZZYYEEDDFF39887";
        $this->assertTrue($this->db_proxy->dbClass->register($clientId2, $entity, $condition, $pkArray2) !== false, $testName);
        $clientId3 = "555588888DDDDDD";
        $this->assertTrue($this->db_proxy->dbClass->register($clientId3, "table2", $condition, $pkArray2) !== false, $testName);

        $result = $this->db_proxy->dbClass->appendIntoRegisterd($clientId1, $entity, array(101));
        $this->assertTrue($result[0] == $clientId2, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk" => 101));
        $this->assertTrue(count($recSet) == 2, $testName);

        $result = $this->db_proxy->dbClass->appendIntoRegisterd($clientId2, $entity, array(102));
        $this->assertTrue($result[0] == $clientId1, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk" => 102));
        $this->assertTrue(count($recSet) == 2, $testName);

        $result = $this->db_proxy->dbClass->appendIntoRegisterd($clientId3, "table2", array(103));
        $this->assertTrue(count($result) == 0, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk" => 103));
        $this->assertTrue(count($recSet) == 1, $testName);

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId1, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId2, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId3, null) !== false, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");

        //$reult = $this->db_proxy->dbClass->removeFromRegisterd($clientId, $entity, $pkArray);

    }

    public function testMultiClientSyncRemove()
    {
        $testName = "Remove Sync Info.";
        $this->dbProxySetupForAuth();
        $this->db_proxy->dbClass->deleteForTest("registeredcontext");
        $this->db_proxy->dbClass->deleteForTest("registeredpks");
        $condition = "WHERE id=1001 ORDER BY xdate LIMIT 10";
        $pkArray1 = array(1001, 2001, 3003, 4004);
        $pkArray2 = array(9001, 8001, 3003, 4004);

        $entity = "table1";
        $clientId1 = "123456789ABCDEF";
        $this->assertTrue($this->db_proxy->dbClass->register($clientId1, $entity, $condition, $pkArray1) !== false, $testName);
        $clientId2 = "ZZYYEEDDFF39887";
        $this->assertTrue($this->db_proxy->dbClass->register($clientId2, $entity, $condition, $pkArray2) !== false, $testName);
        $clientId3 = "555588888DDDDDD";

        $result = $this->db_proxy->dbClass->removeFromRegisterd($clientId1, $entity, array(3003));
        $this->assertTrue($result[0] == $clientId2, $testName);

        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk" => 3003));
        $this->assertTrue(count($recSet) == 0, $testName);

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId1, null), $testName);
        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId2, null), $testName);
        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId3, null), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");
    }

}
