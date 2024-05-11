<?php

use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\IMUtil;
use PHPUnit\Framework\Attributes\Test;

trait DB_PDO_Test_AuthHandler
{
    /* Follwing methods don't have any unit test code.
    public function getFieldForAuthorization(string $operation): ?string;
   public function getTargetForAuthorization(string $operation): ?string;
   public function getNoSetForAuthorization(string $operation): ?string;
     public function authSupportCheckMediaToken(string $uid): ?string;
    public function authSupportChangePassword(string $username, string $hashednewpassword): bool;
    public function authSupportCheckMediaPrivilege(string $tableName, string $targeting, string $userField,
                                                   string $user, string $keyField, string $keyValue): ?array;
*/

    #[Test]
    function getAuthorizedGroups_Test()
    {
        $this->dbProxySetupForAuth();
        $aGroup = $this->db_proxy->dbClass->authHandler->getAuthorizedGroups("read");
        $this->assertNotContains('group1', $aGroup);
        $this->assertContains('group2', $aGroup);
        $this->assertNotContains('group3', $aGroup);
    }

    #[Test]
    function getAuthorizedUsers_Test()
    {
        $this->dbProxySetupForAuth();
        $aGroup = $this->db_proxy->dbClass->authHandler->getAuthorizedUsers("read");
        $this->assertContains('user1', $aGroup);
        $this->assertNotContains('user2', $aGroup);
        $this->assertNotContains('user3', $aGroup);
        $this->assertNotContains('user4', $aGroup);
        $this->assertNotContains('user5', $aGroup);
    }

    #[Test]
    public function removeOutdatedChallenges_Test()
    {
        $this->dbProxySetupForAuth();
        $result = $this->db_proxy->dbClass->authHandler->authSupportRemoveOutdatedChallenges();
        $this->assertTrue($result, "Some sql commands have to be executed to remove outdated challenges.");
    }

    #[Test]
    public function createUser_Test()
    {
        $this->dbProxySetupForAuth();

        $username = 'testuser';
        $hashedPasswd = 'd83eefa0a9bd7190c94e7911688503737a99db0154455354';

        $result = $this->db_proxy->dbClass->authHandler->authSupportCreateUser($username, $hashedPasswd);
        $this->assertTrue($result, "Any user has to be created.");

        $retrievedPasswd = $this->db_proxy->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
        $this->assertEquals($hashedPasswd, $retrievedPasswd, "Password hash has to be retrieved.");
    }

    #[Test]
    public function retrieveHashedPassword_Test()
    {
        $this->dbProxySetupForAuth();

        $testName = "Password Retrieving";
        $username = 'user1';
        $expectedPasswd = 'd83eefa0a9bd7190c94e7911688503737a99db0154455354';

        $retrievedPasswd = $this->db_proxy->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
        $this->assertEquals($expectedPasswd, $retrievedPasswd, $testName);
    }

    public function getSalt_Test()
    {
        $this->dbProxySetupForAuth();

        $testName = "Salt retrieving";
        $username = 'user1';
        $retrievedSalt = $this->db_proxy->authSupportGetSalt($username);
        $this->assertEquals('54455354', $retrievedSalt, $testName);
    }

    #[Test]
    public function storeRetrieveChallenge_Test()
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
        $retrieved = $this->db_proxy->dbClass->authHandler->authSupportRetrieveChallenge($uid, "TEST");
        $this->assertEquals($challenge, $retrieved, $testName);

        $challenge = IMUtil::generateChallenge();
        $this->db_proxy->dbClass->authHandler->authSupportStoreChallenge($uid, $challenge, "TEST");
        $retrieved = $this->db_proxy->dbClass->authHandler->authSupportRetrieveChallenge($uid, "TEST");
        $this->assertEquals($challenge, $retrieved, $testName);
    }

    #[Test]
    public function getUserIdFromEmail_Test()
    {
        $testName = "Test for the authSupportGetUserIdFromEmail method in AuthHandler.";
        $this->dbProxySetupForAuth();
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromEmail('user1@msyk.net');
        $this->assertEquals(1, $result, $testName);
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromEmail('user2@msyk.net');
        $this->assertEquals(2, $result, $testName);
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromEmail('user3@msyk.net');
        $this->assertEquals(3, $result, $testName);
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromEmail('user3');
        $this->assertEquals('', $result, $testName);
    }

    #[Test]
    public function getUserIdFromUsername_Test()
    {
        $testName = "Test for the authSupportGetUserIdFromUsername method in AuthHandler.";
        $this->dbProxySetupForAuth();
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromUsername('user1');
        $this->assertEquals(1, $result, $testName);
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromUsername('user2');
        $this->assertEquals(2, $result, $testName);
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUserIdFromUsername('user3');
        $this->assertEquals(3, $result, $testName);
    }

    #[Test]
    public function getUsernameFromUserId_Test()
    {
        $testName = "Test for the authSupportGetUsernameFromUserId method in AuthHandler.";
        $this->dbProxySetupForAuth();
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUsernameFromUserId(1);
        $this->assertEquals('user1', $result, $testName);
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUsernameFromUserId(2);
        $this->assertEquals('user2', $result, $testName);
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetUsernameFromUserId(3);
        $this->assertEquals('user3', $result, $testName);
    }

    #[Test]
    public function getGroupNameFromGroupId_Test()
    {
        $testName = "Test for the authSupportGetGroupNameFromGroupId method in AuthHandler.";
        $this->dbProxySetupForAuth();
        $result = $this->db_proxy->dbClass->authHandler->authSupportGetGroupNameFromGroupId(1);
        $this->assertEquals('group1', $result, $testName);
    }

    #[Test]
    function getGroupsOfUser_Test()
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


    #[Test]
    public function unifyUsernameAndEmail_Test()
    {
        $testName = "Test for the authSupportUnifyUsernameAndEmail method in AuthHandler.";
        $this->dbProxySetupForAuth();
        $result = $this->db_proxy->dbClass->authHandler->authSupportUnifyUsernameAndEmail('user1');
        $this->assertEquals('user1', $result, $testName);
        $result = $this->db_proxy->dbClass->authHandler->authSupportUnifyUsernameAndEmail('user1@msyk.net');
        $this->assertEquals('user1', $result, $testName);

    }

    #[Test]
    public function emailFromUnifiedUsername_Test()
    {
        $testName = "Test for the authSupportEmailFromUnifiedUsername method in AuthHandler.";
        $this->dbProxySetupForAuth();
        $result = $this->db_proxy->dbClass->authHandler->authSupportEmailFromUnifiedUsername('user1');
        $this->assertEquals('user1@msyk.net', $result, $testName);
        $result = $this->db_proxy->dbClass->authHandler->authSupportEmailFromUnifiedUsername('xxxxxxx@msyk.net');
        $this->assertEquals('', $result, $testName);

    }

    #[Test]
    public function canMigrateSHA256Hash_Test()
    {
        $testName = "Test for the authSupportCanMigrateSHA256Hash method in AuthHandler.";
        $this->dbProxySetupForAuth();
        $result = $this->db_proxy->dbClass->authHandler->authSupportCanMigrateSHA256Hash();
        $this->assertTrue($result, $testName);
    }

}