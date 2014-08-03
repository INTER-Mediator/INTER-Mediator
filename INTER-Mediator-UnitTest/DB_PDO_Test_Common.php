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

abstract class DB_PDO_Test_Common extends PHPUnit_Framework_TestCase
{
    protected $db_proxy;
    protected $schemaName = "";

    abstract function dbProxySetupForAccess($contextName, $maxRecord);
    abstract function dbProxySetupForAuth();

    function setUp()
    {
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Asia/Tokyo');
    }

    public function testQuery1_singleRecord()
    {
        $this->dbProxySetupForAccess("person", 1);
        $result = $this->db_proxy->getFromDB("person");
        $recordCount = $this->db_proxy->countQueryResult("person");
        $this->assertTrue(count($result) == 1, "After the query, just one should be retrieved.");
        $this->assertTrue($recordCount == 3, "This table contanins 3 records");
        $this->assertTrue($result[0]["id"] == 1, "Field value is not same as the definition.");
//        var_export($this->db_proxy->logger->getAllErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessage());
    }

    public function testQuery2_multipleRecord()
    {
        $this->dbProxySetupForAccess("person", 1000000);
        $result = $this->db_proxy->getFromDB("person");
        $recordCount = $this->db_proxy->countQueryResult("person");
        $this->assertTrue(count($result) == 3, "After the query, some records should be retrieved.");
        $this->assertTrue($recordCount == 3, "This table contanins 3 records");
        $this->assertTrue($result[2]["name"] === 'Anyone', "Field value is not same as the definition.");
        $this->assertTrue($result[2]["id"] == 3, "Field value is not same as the definition.");

//        var_export($this->db_proxy->logger->getAllErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessage());
    }

    public function testInsertAndUpdateRecord()
    {
        $this->dbProxySetupForAccess("contact", 1000000);
        $this->db_proxy->requireUpdatedRecord(true);
        $newKeyValue = $this->db_proxy->newToDB("contact", true);
        $this->assertTrue($newKeyValue > 0, "If a record was created, it returns the new primary key value.");
        $createdRecord = $this->db_proxy->updatedRecord();
        $this->assertTrue($createdRecord != null, "Created record should be exists.");
        $this->assertTrue(count($createdRecord) == 1, "It should be just one record.");

        $this->dbProxySetupForAccess("person", 1000000);
        $this->db_proxy->requireUpdatedRecord(true);
        $newKeyValue = $this->db_proxy->newToDB("person", true);
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
        $result = $this->db_proxy->setToDB("person", true);
        $createdRecord = $this->db_proxy->updatedRecord();
        $this->assertTrue($createdRecord != null, "Update record should be exists.");
        $this->assertTrue(count($createdRecord) == 1, "It should be just one record.");
        $this->assertTrue($createdRecord[0]["name"] === $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($createdRecord[0]["address"] === $addressValue, "Field value is not same as the definition.");

        $this->dbProxySetupForAccess("person", 1000000);
        $this->db_proxy->dbSettings->addExtraCriteria("id", "=", $newKeyValue);
        $result = $this->db_proxy->getFromDB("person");
        $recordCount = $this->db_proxy->countQueryResult("person");
        $this->assertTrue(count($result) == 1, "It should be just one record.");
        $this->assertTrue($result[0]["name"] === $nameValue, "Field value is not same as the definition.");
        $this->assertTrue($result[0]["address"] === $addressValue, "Field value is not same as the definition.");

//        var_export($this->db_proxy->logger->getAllErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessage());

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
        $this->dbProxySetupForAuth();

        $testName = "Password Retrieving";
        $username = 'user1';
        $expectedPasswd = 'd83eefa0a9bd7190c94e7911688503737a99db0154455354';

        $retrievedPasswd = $this->db_proxy->dbClass->authSupportRetrieveHashedPassword($username);
//        echo var_export($this->db_proxy->logger->getDebugMessage(), true);
        $this->assertEquals($expectedPasswd, $retrievedPasswd, $testName);

//        var_export($this->db_proxy->logger->getAllErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessage());
    }

    public function testAuthUser3()
    {
        $this->dbProxySetupForAuth();

        $testName = "Salt retrieving";
        $username = 'user1';
        $retrievedSalt = $this->db_proxy->authSupportGetSalt($username);
        $this->assertEquals('54455354', $retrievedSalt, $testName);

//        var_export($this->db_proxy->logger->getAllErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessage());
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

//        var_export($this->db_proxy->logger->getAllErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessage());

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
//        var_export($this->db_proxy->logger->getAllErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessage());
    }

    public function testAuthUser6()
    {
        $this->dbProxySetupForAuth();

        $testName = "Create New User and Authenticate";
        $username = "testuser1";
        $password = "testuser1";

        $addUserResult = $this->db_proxy->addUser($username, $password);
//        var_export($this->db_proxy->logger->getAllErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessage());
        $this->assertTrue($addUserResult);

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
//        var_export($this->db_proxy->logger->getAllErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessage());
    }

    function testUserGroup()
    {
        $this->dbProxySetupForAuth();

        $testName = "Resolve containing group";
        $groupArray = $this->db_proxy->dbClass->authSupportGetGroupsOfUser('user1');
        echo var_export($groupArray);
        $this->assertTrue(count($groupArray) > 0, $testName);
    }

    public function testNativeUser()
    {
        $this->dbProxySetupForAuth();

        $testName = "Native User Challenge Check";
        $cliendId = "12345";

        $challenge = $this->db_proxy->generateChallenge();
        echo "\ngenerated=", $challenge;
        $this->db_proxy->dbClass->authSupportStoreChallenge(0, $challenge, $cliendId);

        $this->assertTrue(
            $this->db_proxy->checkChallenge($challenge, $cliendId), $testName);
    }

    public function testDefaultKey()
    {
        $this->dbProxySetupForAccess("person", 1);

        $testName = "The default key field name";
        $presetValue = "id";
        $value = $this->db_proxy->dbClass->getDefaultKey();
        $this->assertTrue($presetValue == $value, $testName);
        if (((int)phpversion()) >= 5.3) {
            $className = get_class($this->db_proxy->dbClass);
            eval("$value = $className::defaultKey();");
        }
        $this->assertTrue($presetValue == $value, $testName);
    }

    public function testMultiClientSyncTableExsistence()    {
        $testName = "Tables for storing the context and ids should be existing.";
        $this->dbProxySetupForAuth();
        $this->assertTrue($this->db_proxy->dbClass->isExistRequiredTable(), $testName);
    }

    public function testMultiClientSyncRegisterAndUnregister()    {
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
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 1, "Count table1");
        $this->assertTrue($recSet[0]["conditions"] == $condition, "the 'clientId' value in table1");
        $regId = $recSet[0]["id"];
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$regId));
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
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 1, "Count table1");
        $this->assertTrue($recSet[0]["conditions"] == $condition, "tha 'clientId' value in table1");
        $regId = $recSet[0]["id"];
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$regId));
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
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 1, "Count table1");
        $this->assertTrue($recSet[0]["conditions"] == $condition, "tha 'clientId' value in table1");
        $regId = $recSet[0]["id"];
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$regId));
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

    public function testMultiClientSyncRegisterAndUnregisterPartial()    {
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
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 3, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$registResult1));
        $this->assertTrue(count($recSet) == 4, "Count pk values");
        $this->assertTrue(count(array_diff(
                $pkArray,
                array($recSet[0]["pk"], $recSet[1]["pk"], $recSet[2]["pk"], $recSet[3]["pk"])
            )) == 0, "Stored pk values");

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId, array($registResult2)), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredcontext",
            array("clientid"=>$clientId, "entity"=>$entity));
        $this->assertTrue(count($recSet) == 2, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest(
            "registeredpks",
            array("context_id"=>$registResult2));
        $this->assertTrue(count($recSet) == 0, "Count pk values");

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId, null), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");

    }

    public function testMultiClientSyncMatching()    {
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

    public function testMultiClientSyncAppend()    {
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
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk"=>101));
        $this->assertTrue(count($recSet) == 2 , $testName);

        $result = $this->db_proxy->dbClass->appendIntoRegisterd($clientId2, $entity, array(102));
        $this->assertTrue($result[0] == $clientId1, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk"=>102));
        $this->assertTrue(count($recSet) == 2 , $testName);

        $result = $this->db_proxy->dbClass->appendIntoRegisterd($clientId3, "table2", array(103));
        $this->assertTrue(count($result) == 0, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk"=>103));
        $this->assertTrue(count($recSet) == 1 , $testName);

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId1, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId2, null) !== false, $testName);
        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId3, null) !== false, $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");

        //$reult = $this->db_proxy->dbClass->removeFromRegisterd($clientId, $entity, $pkArray);

    }

    public function testMultiClientSyncRemove()    {
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

        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks", array("pk"=>3003));
        $this->assertTrue(count($recSet) == 0 , $testName);

        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId1, null), $testName);
        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId2, null), $testName);
        $this->assertTrue($this->db_proxy->dbClass->unregister($clientId3, null), $testName);
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredcontext");
        $this->assertTrue(count($recSet) == 0, "Count table1");
        $recSet = $this->db_proxy->dbClass->queryForTest("registeredpks");
        $this->assertTrue(count($recSet) == 0, "Count pk values");
    }

}