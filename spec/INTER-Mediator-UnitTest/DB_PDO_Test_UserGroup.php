<?php

use PHPUnit\Framework\TestCase;
use INTERMediator\IMUtil;
use INTERMediator\DB\Proxy;

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

        foreach ($result as $record) {
            $this->assertIsString($record["name"], $testName);
            $this->assertIsString($record["address"], $testName);
        }
    }

    public function testAuthByInvalidUser()
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

    public function testAddUser1()
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

    public function testAddUser2()
    {
        $this->dbProxySetupForAuth();

        $testName = "Create New User and Authenticate";
        $username = "testuser2";
        $password = "testuser2";

        [$addUserResult, $hashedpw] = $this->db_proxy->addUser($username, $password, false, ['realname' => 'test123']);
        $this->assertTrue($addUserResult);

        $db = new Proxy(true);
        $db->ignoringPost();

        // ユーザー名からユーザidを取得
        $db->initialize([['name' => 'authuser', 'record' => 1, 'key' => 'id']],
            [], ['db-class' => 'PDO',], 2, 'authuser');
        $db->dbSettings->addExtraCriteria("username", "=", $username);
        $db->processingRequest('read', true);
        $userResult = $db->getDatabaseResult();

        $this->assertEquals('test123', $userResult[0]['realname'], 'The realname is supplied with parameter.');
        $this->assertEquals($username, $userResult[0]['username'], 'The username has to be keep.');
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

        $db = new Proxy(true);
        $db->ignoringPost();

        // ユーザー名からユーザidを取得
        $db->initialize([['name' => 'authuser', 'record' => 1, 'key' => 'id']],
            [], ['db-class' => 'PDO',], 2, 'authuser');
        $db->dbSettings->addExtraCriteria("username", "=", $username);
        $db->processingRequest('read', true);
        $userResult = $db->getDatabaseResult();

        $this->assertEquals('test123', $userResult[0]['realname'], 'The realname is supplied with parameter.');
        $this->assertEquals($username, $userResult[0]['username'], 'The username has to be keep.');
    }

    function testUserGroup()
    {
        $this->dbProxySetupForAuth();

        $testName = "Resolve containing group";
        $groupArray = $this->db_proxy->dbClass->authHandler->authSupportGetGroupsOfUser('user1');
//        var_export($this->db_proxy->logger->getErrorMessages());
//        var_export($this->db_proxy->logger->getDebugMessages());
        $this->assertTrue(count($groupArray) > 0, $testName);
        $this->assertTrue(in_array("group1", $groupArray), $testName);
        $this->assertFalse(in_array("group2", $groupArray), $testName);
        $this->assertTrue(in_array("group3", $groupArray), $testName);
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

}