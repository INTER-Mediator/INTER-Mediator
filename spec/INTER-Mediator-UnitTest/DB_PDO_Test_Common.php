<?php
/*
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 11/12/14
 * Time: 14:21
 * Unit Test by PHPUnit (http://phpunit.de)
 *
 */

use PHPUnit\Framework\TestCase;

//use \DateTime;
use INTERMediator\IMUtil;

abstract class DB_PDO_Test_Common extends TestCase
{
    protected $db_proxy;
    protected $schemaName = "";

    abstract function dbProxySetupForAccess($contextName, $maxRecord);

    abstract function dbProxySetupForAuth();

    abstract function dbProxySetupForAggregation();

    function setUp(): void
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');
    }

    public function testAggregation()
    {
        $this->dbProxySetupForAggregation();

        //$this->db_proxy->logger->clearLogs();

        $result = $this->db_proxy->readFromDB();
        $recordCount = $this->db_proxy->countQueryResult();

//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());

        $this->assertTrue(is_array($result), "After the query, any array should be retrieved.");
        $this->assertEquals(count($result), 10, "After the query, 10 records should be retrieved.");
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
        $result = $this->db_proxy->readFromDB();
        $recordCount = $this->db_proxy->countQueryResult();
        $this->assertTrue((is_array($result) ? count($result) : -1) == 1, "After the query, just one should be retrieved.");
        $this->assertTrue($recordCount == 3, "This table contanins 3 records");
        $this->assertTrue($result[0]["id"] == 1, "Field value is not same as the definition.");
    }

    public function testQuery2_multipleRecord()
    {
        $this->dbProxySetupForAccess("person", 1000000);
        $result = $this->db_proxy->readFromDB();
        $recordCount = $this->db_proxy->countQueryResult();
        $this->assertTrue((is_array($result) ? count($result) : -1) == 3, "After the query, some records should be retrieved.");
        $this->assertTrue($recordCount == 3, "This table contanins 3 records");
        $this->assertTrue($result[2]["name"] === 'Anyone', "Field value is not same as the definition.");
        $this->assertTrue($result[2]["id"] == 3, "Field value is not same as the definition.");
    }

    public function testInsertAndUpdateRecord()
    {
        $this->dbProxySetupForAccess("contact", 1000000);
        $this->db_proxy->requireUpdatedRecord(true);
        $newKeyValue = $this->db_proxy->createInDB();
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
        $newKeyValue = $this->db_proxy->createInDB();
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
        $result = $this->db_proxy->updateDB(false);
        $createdRecord = $this->db_proxy->updatedRecord();
        $this->assertTrue($createdRecord != null, "Update record should be exists.");
        $this->assertTrue(count($createdRecord) == 1, "It should be just one record.");
        $this->assertTrue($createdRecord[0]["name"] === $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($createdRecord[0]["address"] === $addressValue, "Field value is not same as the definition.");

        $this->dbProxySetupForAccess("person", 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $newKeyValue);
        $result = $this->db_proxy->readFromDB();
        $recordCount = $this->db_proxy->countQueryResult();
        $this->assertTrue(count($result) == 1, "It should be just one record.");
        $this->assertTrue($result[0]["name"] === $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($result[0]["address"] === $addressValue, "Field value is not same as the definition.");
    }

    public function testCopySingleRecord()
    {
        $this->dbProxySetupForAccess("person", 1000000);
//        $this->db_proxy->logger->clearLogs();
        $result = $this->db_proxy->readFromDB();
        $recordCount = $this->db_proxy->countQueryResult();

//        echo "===ckeckpoint1===";
//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());

        $parentId = $result[random_int(0, $recordCount - 1)]["id"];
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $parentId);
        $this->db_proxy->copyInDB();

//        echo "===ckeckpoint2===";
//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());

        $this->dbProxySetupForAccess("person", 1000000, "contact");
        $result = $this->db_proxy->readFromDB();
        $recordCountAfter = $this->db_proxy->countQueryResult();
        $this->assertTrue($recordCount + 1 == $recordCountAfter,
            "After copy a record, the count of records should increase one.");
    }

    public function testCopyAssociatedRecords()
    {
        $this->dbProxySetupForAccess("person", 1000000);
        $result = $this->db_proxy->readFromDB();
        $recordCountPerson = $this->db_proxy->countQueryResult();

        $parentId = $result[random_int(0, $recordCountPerson - 1)]["id"];

        $this->dbProxySetupForAccess("contact", 1000000);
        $result = $this->db_proxy->readFromDB();
        $recordCountContact = $this->db_proxy->countQueryResult();

        $this->dbProxySetupForAccess("contact", 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria("person_id", "=", $parentId);
        $result = $this->db_proxy->readFromDB();
        $recordCountIncrease = $this->db_proxy->countQueryResult();

        $this->dbProxySetupForAccess("person", 1000000, "contact");
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $parentId);
        $this->db_proxy->dbSettings->addAssociated("contact", "person_id", $parentId);
        $this->db_proxy->copyInDB();

//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());

        $this->dbProxySetupForAccess("person", 1000000);
        $result = $this->db_proxy->readFromDB();
        $recordCountPersonAfter = $this->db_proxy->countQueryResult();
        $this->assertTrue($recordCountPerson + 1 == $recordCountPersonAfter,
            "After copy a record, the count of records should increase one.");

        $this->dbProxySetupForAccess("contact", 1000000);
        $result = $this->db_proxy->readFromDB();
        $recordCountContactAfter = $this->db_proxy->countQueryResult();
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

        $retrievedPasswd = $this->db_proxy->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
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

//        $this->db_proxy->logger->clearLogs();

        $testName = "Generate Challenge and Retrieve it";
        $uid = 1;
        $challenge = IMUtil::generateChallenge();
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($uid, $challenge, "TEST");
        $retrieved = $this->db_proxy->dbClass->authHandler->authSupportRetrieveChallenge($uid, "TEST");

//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());

        $this->assertEquals($challenge, $retrieved, $testName);

        $challenge = IMUtil::generateChallenge();
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($uid, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authHandler->authSupportRetrieveChallenge($uid, "TEST"), $testName);

        $challenge = IMUtil::generateChallenge();
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($uid, $challenge, "TEST");
        $this->assertEquals($challenge, $this->db_proxy->dbClass->authHandler->authSupportRetrieveChallenge($uid, "TEST"), $testName);
    }

    public function testAuthUser5()
    {
        $this->dbProxySetupForAuth();

        $testName = "Simulation of Authentication";
        $username = 'user1';
        $password = 'user1'; //'d83eefa0a9bd7190c94e7911688503737a99db0154455354';
        $uid = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromUsername($username);

        $challenge = IMUtil::generateChallenge();
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($uid, $challenge, "TEST");

        //        $challenge = $this->db_pdo->authHandler->authSupportRetrieveChallenge($username, "TEST");
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));

        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->db_proxy->setParamResponse([$calcuratedHash]);
        $this->db_proxy->setClientId("TEST");
        $this->assertTrue(
            $this->db_proxy->checkAuthorization($username), $testName);
    }

    public function testAuthByValidUser()
    {
        $this->dbProxySetupForAuth();

        $testName = "Simulation of Authentication by Valid User";
        $username = 'user1';
        $password = 'user1'; //'d83eefa0a9bd7190c94e7911688503737a99db0154455354';
        $clientId = 'test1234test1234';

        $challenge = IMUtil::generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->db_proxy->dbSettings->setCurrentUser($username);
        $this->db_proxy->dbSettings->setDataSourceName("person");
        $this->db_proxy->paramAuthUser = $username;
        $this->db_proxy->setClientId($clientId);
        $this->db_proxy->setParamResponse([$calcuratedHash]);

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

        $challenge = IMUtil::generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);
        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);

        $this->db_proxy->dbSettings->setCurrentUser($username);
        $this->db_proxy->dbSettings->setDataSourceName("person");
        $this->db_proxy->paramAuthUser = $username;
        $this->db_proxy->setClientId($clientId);
        $this->db_proxy->setParamResponse([$calcuratedHash]);

        $this->db_proxy->processingRequest("read");
        $this->assertTrue(is_null($this->db_proxy->getDatabaseResult()), $testName);
        $this->assertTrue(is_null($this->db_proxy->getDatabaseResultCount()) ||
            $this->db_proxy->getDatabaseResultCount() == 0, $testName);
        $this->assertTrue(is_null($this->db_proxy->getDatabaseTotalCount()) ||
            $this->db_proxy->getDatabaseTotalCount() == 0, $testName);
        $this->assertTrue(is_null($this->db_proxy->getDatabaseResult()), $testName);
        $this->assertTrue($this->db_proxy->dbSettings->getRequireAuthentication(), $testName);
    }

    public function testAuthUser6()
    {
        $this->dbProxySetupForAuth();

        $testName = "Create New User and Authenticate";
        $username = "testuser1";
        $password = "testuser1";

        [$addUserResult, $hashedpw] = $this->db_proxy->addUser($username, $password);
        $this->assertTrue($addUserResult);

        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));

        $clientId = "TEST";
        $challenge = IMUtil::generateChallenge();
        $this->db_proxy->saveChallenge($username, $challenge, $clientId);

        $hashedvalue = hash('sha1', $password . $retrievedSalt) . $retrievedHexSalt;
        $value = $password . $retrievedSalt;
        for ($i = 0; $i < 4999; $i++) {
            $value = hash("sha256", $value, true);
        }
        $hashedvalue256 = hash("sha256", $value, false) . $retrievedHexSalt;
        $this->db_proxy->setParamResponse([
            hash_hmac('sha256', $hashedvalue, $challenge),
            hash_hmac('sha256', $hashedvalue256, $challenge),
            hash_hmac('sha256', $hashedvalue256, $challenge),
        ]);
        $this->db_proxy->setClientId($clientId);
        $checkResult = $this->db_proxy->checkAuthorization($username);

//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());

        $this->assertTrue($checkResult, $testName);
    }

    function testUserGroup()
    {
        $this->dbProxySetupForAuth();

        $testName = "Resolve containing group";
        $groupArray = $this->db_proxy->dbClass->authHandler->authSupportGetGroupsOfUser('user1');
//        echo var_export($groupArray);
        $this->assertTrue(count($groupArray) > 0, $testName);
    }

    public function testNativeUser()
    {
        $this->dbProxySetupForAuth();

        $testName = "Native User Challenge Check";
        $cliendId = "12345";

        $challenge = IMUtil::generateChallenge();
        //echo "\ngenerated=", $challenge;
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge(0, $challenge, $cliendId);

        $result = $this->db_proxy->checkChallenge($challenge, $cliendId);
        $this->assertTrue($result, $testName);

    }

    public function testDefaultKey()
    {
        $this->dbProxySetupForAccess("person", 1);

        $className = get_class($this->db_proxy->dbClass->specHandler);
        $this->assertEquals('id', call_user_func(array($className, 'defaultKey')));
    }

    public function testGetDefaultKey()
    {
        $this->dbProxySetupForAccess("person", 1);

        $value = $this->db_proxy->dbClass->specHandler->getDefaultKey();
        $this->assertEquals('id', $value);
    }

    public function testMultiClientSyncTableExsistence()
    {
        $testName = "Tables for storing the context and ids should be existing.";
        $this->dbProxySetupForAuth();
        //$this->db_proxy->logger->clearLogs();
        $result = $this->db_proxy->dbClass->notifyHandler->isExistRequiredTable();
        //var_export($this->db_proxy->logger->getErrorMessages());
        //var_export($this->db_proxy->logger->getDebugMessages());
        $this->assertTrue($result, $testName);
    }

    protected function getSampleComdition()
    {
        return "WHERE id=1001 ORDER BY xdate LIMIT 10";
    }

    public function testMultiClientSyncRegisterAndUnregister()
    {
        $testName = "Register and Unregister.";
        $this->dbProxySetupForAuth();
        $this->db_proxy->dbClass->deleteForTest("registeredcontext");
        $this->db_proxy->dbClass->deleteForTest("registeredpks");
//               $this->db_proxy->logger->clearLogs();
        $clientId = "123456789ABCDEF";
        $condition = $this->getSampleComdition();
        $pkArray = array(1001, 2001, 3003, 4004);

        $entity = "table1";
        $registResult = $this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray);
//        var_export($this->db_proxy->logger->getDebugMessages());
//        var_export($this->db_proxy->logger->getErrorMessages());
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
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray) !== false,
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
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray) !== false,
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

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId, null), $testName);
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
        $condition = $this->getSampleComdition();
        $pkArray = array(1001, 2001, 3003, 4004);

        $entity = "table1";
        $registResult1 = $this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray);
        $registResult2 = $this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray);
        $registResult3 = $this->db_proxy->dbClass->notifyHandler->register($clientId, $entity, $condition, $pkArray);
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

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId, array($registResult2)), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid" => $clientId, "entity" => $entity));
        $this->assertTrue(count($recSet) == 2, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id" => $registResult2));
        $this->assertTrue(count($recSet) == 0, "Count pk values");

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId, null), $testName);
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
        $condition = $this->getSampleComdition();
        $pkArray1 = array(1001, 2001, 3003, 4004);
        $pkArray2 = array(9001, 8001, 3003, 4004);

        $entity = "table1";
        $clientId1 = "123456789ABCDEF";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId1, $entity, $condition, $pkArray1) !== false, $testName);
        $clientId2 = "ZZYYEEDDFF39887";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId2, $entity, $condition, $pkArray2) !== false, $testName);

        $result = $this->db_proxy->dbClass->notifyHandler->matchInRegistered($clientId2, $entity, array(3003));
        $this->assertTrue(count($result) == 1, "Count matching");
        $this->assertTrue($result[0] == $clientId1, "Matched client id");

        $result = $this->db_proxy->dbClass->notifyHandler->matchInRegistered($clientId2, $entity, array(2001));
        $this->assertTrue(count($result) == 1, "Count matching");
        $this->assertTrue($result[0] == $clientId1, "Matched client id");

        $result = $this->db_proxy->dbClass->notifyHandler->matchInRegistered($clientId2, $entity, array(4567));
        $this->assertTrue(count($result) == 0, "Count matching");

        $result = $this->db_proxy->dbClass->notifyHandler->matchInRegistered($clientId2, $entity, array(8001));
        $this->assertTrue(count($result) == 0, "Count matching");

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId1, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId2, null) !== false, $testName);
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
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId1, $entity, $condition, $pkArray1) !== false, $testName);
        $clientId2 = "ZZYYEEDDFF39887";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId2, $entity, $condition, $pkArray2) !== false, $testName);
        $clientId3 = "555588888DDDDDD";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId3, "table2", $condition, $pkArray2) !== false, $testName);

        $result = $this->db_proxy->dbClass->notifyHandler->appendIntoRegistered($clientId1, $entity, array(101));
        $this->assertTrue($result[0] == $clientId2, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk" => 101));
        $this->assertTrue(count($recSet) == 2, $testName);

        $result = $this->db_proxy->dbClass->notifyHandler->appendIntoRegistered($clientId2, $entity, array(102));
        $this->assertTrue($result[0] == $clientId1, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk" => 102));
        $this->assertTrue(count($recSet) == 2, $testName);

        $result = $this->db_proxy->dbClass->notifyHandler->appendIntoRegistered($clientId3, "table2", array(103));
        $this->assertTrue(count($result) == 0, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk" => 103));
        $this->assertTrue(count($recSet) == 1, $testName);

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId1, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId2, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId3, null) !== false, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");

        //$reult = $this->db_proxy->dbClass->notifyHandler->removeFromRegistered($clientId, $entity, $pkArray);

    }

    public function testMultiClientSyncRemove()
    {
        $testName = "Remove Sync Info.";
        $this->dbProxySetupForAuth();
        $this->db_proxy->dbClass->deleteForTest("registeredcontext");
        $this->db_proxy->dbClass->deleteForTest("registeredpks");
        $condition = $this->getSampleComdition();
        $pkArray1 = array(1001, 2001, 3003, 4004);
        $pkArray2 = array(9001, 8001, 3003, 4004);

        $entity = "table1";
        $clientId1 = "123456789ABCDEF";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId1, $entity, $condition, $pkArray1) !== false, $testName);
        $clientId2 = "ZZYYEEDDFF39887";
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->register($clientId2, $entity, $condition, $pkArray2) !== false, $testName);
        $clientId3 = "555588888DDDDDD";

        $result = $this->db_proxy->dbClass->notifyHandler->removeFromRegistered($clientId1, $entity, array(3003));
        $this->assertTrue($result[0] == $clientId2, $testName);

        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk" => 3003));
        $this->assertTrue(count($recSet) == 0, $testName);

        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId1, null), $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId2, null), $testName);
        $this->assertTrue($this->db_proxy->dbClass->notifyHandler->unregister($clientId3, null), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");
    }

    public function testIgnoreValuesForSpecificOperators()
    {
        $this->dbProxySetupForAccess("person", 1);
        $result = $this->db_proxy->readFromDB();
        $aName = $result[0]['name'];
        $this->assertEquals(count($result), 1, "Just 1 records should be retrieved.");

        $this->dbProxySetupForAccess("person", 1);
        $this->db_proxy->dbSettings->addExtraCriteria("id", "IS NOT NULL", "3");
        $result = $this->db_proxy->readFromDB();
        $this->assertEquals(is_array($result), true, "The retrieved data has to be array.");
        $this->assertEquals(count($result), 1, "Just 1 records should be retrieved.");
        $this->assertEquals($result[0]['name'], $aName, "Same record should be retrieved.");
    }
}
