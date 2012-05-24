<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/23
 * Time: 17:57
 * To change this template use File | Settings | File Templates.
 */
class DB_AuthCommon extends DB_UseSharedObjects implements Auth_Interface_Communication, Auth_Interface_CommonDB
{

    var $dbClass = null;
    /*
    *  Implementation of the Auth_Interface_Communication interface.
    */

    /* Authentication support */
    function generateClientId($prefix)
    {
        return sha1(uniqid($prefix, true));
    }

    function generateChallenge()
    {
        $str = '';
        for ($i = 0; $i < 12; $i++) {
            $n = rand(1, 255);
            $str .= ($n < 16 ? '0' : '') . dechex($n);
        }
        return $str;
    }

    function generateSalt()
    {
        $str = '';
        for ($i = 0; $i < 4; $i++) {
            $n = rand(1, 255);
            $str .= chr($n);
        }
        return $str;
    }

    /* returns user's hash salt.

    */
    function saveChallenge($username, $challenge, $clientId)
    {
        $this->dbClass->authSupportStoreChallenge($username, $challenge, $clientId);
        return $username === 0 ? "" : $this->dbClass->authSupportGetSalt($username);
    }

    function checkAuthorization($username, $hashedvalue, $clientId)
    {
        $returnValue = false;

        $this->dbClass->removeOutdatedChallenges();

        $storedChalenge = $this->dbClass->authSupportRetrieveChallenge($username, $clientId);
        $this->logger->setDebugMessage("[checkAuthorization]storedChalenge={$storedChalenge}", 2);

        if (strlen($storedChalenge) == 24) { // ex.fc0d54312ce33c2fac19d758
            $hashedPassword = $this->dbClass->authSupportRetrieveHashedPassword($username);
            $this->logger->setDebugMessage("[checkAuthorization]hashedPassword={$hashedPassword}", 2);
            if (strlen($hashedPassword) > 0) {
                if ($hashedvalue == sha1($storedChalenge . $hashedPassword)) {
                    $returnValue = true;
                }
            }
        }
        return $returnValue;
    }

    // This method is just used to authenticate with database user
    function checkChallenge($challenge, $clientId)
    {
        $returnValue = false;
        $this->dbClass->removeOutdatedChallenges();
        // Database user mode is user_id=0
        $storedChallenge = $this->dbClass->authSupportRetrieveChallenge(0, $clientId);
        if (strlen($storedChallenge) == 24 && $storedChallenge == $challenge) { // ex.fc0d54312ce33c2fac19d758
            $returnValue = true;
        }
        return $returnValue;
    }

    function addUser($username, $password)
    {
        $salt = $this->generateSalt();
        $hexSalt = bin2hex($salt);
        $returnValue = $this->dbClass->authSupportCreateUser($username, sha1($password . $salt) . $hexSalt);
        return $returnValue;
    }

    /*
    *  Implementation of the Auth_Interface_CommonDB interface.
    */
    function getFieldForAuthorization($operation)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $authInfoField = null;
        if (isset($tableInfo['authentication']['all']['field'])) {
            $authInfoField = $tableInfo['authentication']['all']['field'];
        }
        if (isset($tableInfo['authentication'][$operation]['field'])) {
            $authInfoField = $tableInfo['authentication'][$operation]['field'];
        }
        return $authInfoField;
    }

    function getTargetForAuthorization($operation)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $authInfoTarget = null;
        if (isset($tableInfo['authentication']['all']['target'])) {
            $authInfoTarget = $tableInfo['authentication']['all']['target'];
        }
        if (isset($tableInfo['authentication'][$operation]['target'])) {
            $authInfoTarget = $tableInfo['authentication'][$operation]['target'];
        }
        return $authInfoTarget;
    }

    function getAuthorizedUsers($operation = null)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $usersArray = array();
        if (isset($this->dbSettings->authentication['user'])) {
            $usersArray = array_merge($usersArray, $this->dbSettings->authentication['user']);
        }
        if (isset($tableInfo['authentication']['all']['user'])) {
            $usersArray = array_merge($usersArray, $tableInfo['authentication']['all']['user']);
        }
        if (isset($tableInfo['authentication'][$operation]['user'])) {
            $usersArray = array_merge($usersArray, $tableInfo['authentication'][$operation]['user']);
        }
        return $usersArray;
    }

    function getAuthorizedGroups($operation = null)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $groupsArray = array();
        if (isset($this->dbSettings->authentication['group'])) {
            $groupsArray = array_merge($groupsArray, $this->dbSettings->authentication['group']);
        }
        if (isset($tableInfo['authentication']['all']['group'])) {
            $groupsArray = array_merge($groupsArray, $tableInfo['authentication']['all']['group']);
        }
        if (isset($tableInfo['authentication'][$operation]['group'])) {
            $groupsArray = array_merge($groupsArray, $tableInfo['authentication'][$operation]['group']);
        }
        return $groupsArray;
    }

    function changePassword($username, $oldpassword, $newpassword)
    {
        $returnValue = $this->dbClass->authSupportChangePassword($username, sha1($oldpassword), sha1($newpassword));
        return $returnValue;
    }
}
