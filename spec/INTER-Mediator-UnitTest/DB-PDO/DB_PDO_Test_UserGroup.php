<?php

use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\IMUtil;

trait DB_PDO_Test_UserGroup
{
    public function testAuthUser1()
    {
        $testName = "Check time calc feature of PHP";
        $expiredDT = new DateTime('2012-02-13 11:32:40');
        $currentDate = new DateTime('2012-02-14 11:32:51');
        $calc = $currentDate->format('U') - $expiredDT->format('U');
        $this->assertTrue($calc === (11 + 3600 * 24), $testName);
    }


//    public function testAuthUser5()
//    {
//        $this->dbProxySetupForAuth();
//
////        $this->db_proxy->logger->clearLogs();
//
//        $testName = "Simulation of Authentication";
//        $username = 'user1';
//        $password = 'user1';
//        $uid = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromUsername($username);
//        $hpw = $this->db_proxy->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
//
//        $clientId = "TEST";
//        $challenge = IMUtil::generateChallenge();
//        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($uid, $challenge, "TEST", "#");
//        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
//        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
//        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
//
////        $this->db_proxy->logger->setDebugMessage("challenge=$challenge clientId=$clientId hashedvalue=$hashedvalue",2);
//
//        $this->db_proxy->credential = hash("sha256", $challenge . $clientId . $hashedvalue);
//        $this->db_proxy->setClientId_forTest("TEST");
//        $this->db_proxy->setHashedPassword_forTest($hpw);
//        $this->db_proxy->dbSettings->setCurrentUser($username);
//        $this->db_proxy->access = "read";
//        $this->db_proxy->authUser = $username;
//        $visitorClasName = IMUtil::getVisitorClassName($this->db_proxy->access);
//        $visitor = new $visitorClasName($this->db_proxy);
//        $process = new CheckAuthenticationElement();
//        $process->acceptCheckAuthentication($visitor);
//        $resultAuth = $process->resultOfCheckAuthentication;
//
////        var_export($this->db_proxy->logger->getErrorMessages());
////        var_export($this->db_proxy->logger->getDebugMessages());
//
//        $this->assertTrue($resultAuth, $testName);
//    }

//    public function testAuthByValidUser()
//    {
//        $this->dbProxySetupForAuth();
//
////        $this->db_proxy->logger->clearLogs();
//
//        $testName = "Simulation of Authentication by Valid User";
//        $username = 'user1';
//        $password = 'user1'; //'d83eefa0a9bd7190c94e7911688503737a99db0154455354';
//        $clientId = 'test1234test1234';
//
//        $challenge = IMUtil::generateChallenge();
//        $this->db_proxy->saveChallenge($username, $challenge, $clientId,"#");
//        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
//        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
//        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
//
//        $this->db_proxy->credential = hash("sha256", $challenge . $clientId . $hashedvalue);
//        $this->db_proxy->dbSettings->setCurrentUser($username);
//        $this->db_proxy->dbSettings->setDataSourceName("person");
//        $this->db_proxy->paramAuthUser = $username;
//        $this->db_proxy->setClientId_forTest($clientId);
//
//        $this->db_proxy->processingRequest("read");
//        $result = $this->db_proxy->getDatabaseResult();
//
////        var_export($this->db_proxy->logger->getErrorMessages());
////        var_export($this->db_proxy->logger->getDebugMessages());
//
//        $this->assertEquals(is_array($result) ? count($result) : -1, $this->db_proxy->getDatabaseResultCount(), $testName);
//
//        foreach ($result as $record) {
//            $this->assertIsString($record["name"], $testName);
//            $this->assertIsString($record["address"], $testName);
//        }
//    }

//    public function testAuthByInvalidUser()
//    {
//        $this->dbProxySetupForAuth();
//
//        $testName = "Simulation of Authentication by Inalid User";
//        $username = 'user2';
//        $password = 'user2';
//        $clientId = 'test1234test1234';
//
//        $challenge = IMUtil::generateChallenge();
//        $this->db_proxy->saveChallenge($username, $challenge, $clientId);
//        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
//        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
//        $hashedvalue = sha1($password . $retrievedSalt) . bin2hex($retrievedSalt);
//        $calcuratedHash = hash_hmac('sha256', $hashedvalue, $challenge);
//
//        $this->db_proxy->credential = hash("sha256", $challenge . $clientId . $hashedvalue);
//        $this->db_proxy->dbSettings->setCurrentUser($username);
//        $this->db_proxy->dbSettings->setDataSourceName("person");
//        $this->db_proxy->paramAuthUser = $username;
//        $this->db_proxy->setClientId_forTest($clientId);
//        $this->db_proxy->setParamResponse([$calcuratedHash]);
//
//        $this->db_proxy->processingRequest("read");
//        $this->assertTrue(is_null($this->db_proxy->getDatabaseResult()), $testName);
//        $this->assertTrue(is_null($this->db_proxy->getDatabaseResultCount()) ||
//            $this->db_proxy->getDatabaseResultCount() == 0, $testName);
//        $this->assertTrue(is_null($this->db_proxy->getDatabaseTotalCount()) ||
//            $this->db_proxy->getDatabaseTotalCount() == 0, $testName);
//        $this->assertTrue(is_null($this->db_proxy->getDatabaseResult()), $testName);
//        $this->assertTrue($this->db_proxy->dbSettings->getRequireAuthentication(), $testName);
//    }
//
//    public function testAddUser1()
//    {
//        $this->dbProxySetupForAuth();
//
////        $this->db_proxy->logger->clearLogs();
//
//        $testName = "Create New User and Authenticate";
//        $username = "testuser1";
//        $password = "testuser1";
//
//        [$addUserResult, $hashedpw] = $this->db_proxy->addUser($username, $password);
//
////        var_export($this->db_proxy->logger->getErrorMessages());
////        var_export($this->db_proxy->logger->getDebugMessages());
//
//        $this->assertTrue($addUserResult, $testName);
//
//        $hpw = $this->db_proxy->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
//        $this->assertTrue($hpw == $hashedpw, $testName);
//
//        $retrievedHexSalt = $this->db_proxy->authSupportGetSalt($username);
//        $retrievedSalt = pack('N', hexdec($retrievedHexSalt));
//
//        $clientId = "TEST";
//        $challenge = IMUtil::generateChallenge();
//        $this->db_proxy->saveChallenge($username, $challenge, $clientId);
//
//        $hashedvalue = hash('sha1', $password . $retrievedSalt) . $retrievedHexSalt;
//        $value = $password . $retrievedSalt;
//        for ($i = 0; $i < 4999; $i++) {
//            $value = hash("sha256", $value, true);
//        }
//        $hashedvalue256 = hash("sha256", $value, false) . $retrievedHexSalt;
//        $this->db_proxy->setParamResponse([
//            hash_hmac('sha256', $hashedvalue, $challenge),
//            hash_hmac('sha256', $hashedvalue256, $challenge),
//            hash_hmac('sha256', $hashedvalue256, $challenge),
//        ]);
//        $this->db_proxy->setClientId_forTest($clientId);
//        $this->db_proxy->setHashedPassword_forTest($hpw);
//
//        $visitorClasName = IMUtil::getVisitorClassName("read");
//        $visitor = new $visitorClasName($this->db_proxy);
//        $process = new CheckAuthenticationElement();
//        $this->db_proxy->signedUser = $username;
//        $process->acceptCheckAuthentication($visitor);
//        $checkResult = $process->resultOfCheckAuthentication;
//
////        $checkResult = $this->db_proxy->checkAuthorization($username);
//
//        $this->assertTrue($checkResult, $testName);
//    }

    public function testAddUser2()
    {
        $this->dbProxySetupForAuth();

        $username = "testuser2";
        $password = "testuser2";

//        $this->db_proxy->logger->clearLogs();

        [$addUserResult, $hashedpw] = $this->db_proxy->addUser($username, $password, false, ['realname' => 'test123']);
        $this->assertTrue($addUserResult);

        $this->setTestMode();
        $this->dbInit(
            [['name' => 'authuser', 'view' => "{$this->schemaName}authuser", 'table' => "{$this->schemaName}authuser",
                'records' => 1, 'key' => 'id']], null,
            ['db-class' => 'PDO', 'dsn' => $this->dsn, 'user' => 'web', 'password' => 'password']);
        $readResult = $this->dbRead('authuser', ["username" => $username]);
        $this->assertEquals('test123', $readResult[0]['realname'], 'The realname is supplied with parameter.');
        $this->assertEquals($username, $readResult[0]['username'], 'The username has to be keep.');

    }

    public function testAddUser3()
    {
        $this->dbProxySetupForAuth();

        $testName = "Create New User and Authenticate";
        $username = "testuser3";
        $password = "testuser3";

        [$addUserResult, $hashedpw] = $this->db_proxy->addUser($username, $password, false,
            ['username' => 'mycat', 'realname' => 'test123']);
        $this->assertTrue($addUserResult);

        $this->setTestMode();
        $this->dbInit(
            [['name' => 'authuser', 'view' => "{$this->schemaName}authuser", 'table' => "{$this->schemaName}authuser",
                'records' => 1, 'key' => 'id']], null,
            ['db-class' => 'PDO', 'dsn' => $this->dsn, 'user' => 'web', 'password' => 'password']);
        $readResult = $this->dbRead('authuser', ["username" => $username]);
        $this->assertEquals('test123', $readResult[0]['realname'], 'The realname is supplied with parameter.');
        $this->assertEquals($username, $readResult[0]['username'], 'The username has to be keep.');
    }

//    public function testNativeUser()
//    {
//        $this->dbProxySetupForAuth();
//
//        $testName = "Native User Challenge Check";
//        $cliendId = "12345";
//
//        $challenge = IMUtil::generateChallenge();
//        //echo "\ngenerated=", $challenge;
//        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge(0, $challenge, $cliendId);
//
//        $result = $this->db_proxy->checkChallenge($challenge, $cliendId);
//        $this->assertTrue($result, $testName);
//
//    }

}