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
class DB_Auth_Handler_PDO extends DB_Auth_Common implements Auth_Interface_DB
{
    /**
     * @param $username
     * @param $challenge
     * @param $clientId
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportStoreChallenge($uid, $challenge, $clientId)
    {
        $this->logger->setDebugMessage("[authSupportStoreChallenge] $uid, $challenge, $clientId");

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "select id from {$hashTable} where user_id={$uid} and clienthost=" . $this->dbClass->link->quote($clientId);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
        $currentDTFormat = IMUtil::currentDTString();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $sql = "{$this->dbClass->handler->sqlUPDATECommand()}{$hashTable} SET hash=" . $this->dbClass->link->quote($challenge)
                . ",expired=" . $this->dbClass->link->quote($currentDTFormat)
                . " WHERE id={$row['id']}";
            $result = $this->dbClass->link->query($sql);
            if ($result === false) {
                $this->dbClass->errorMessageStore('UPDATE:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
            return true;
        }
        $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$hashTable} (user_id, clienthost, hash, expired) "
            . "VALUES ({$uid},{$this->dbClass->link->quote($clientId)},"
            . "{$this->dbClass->link->quote($challenge)},{$this->dbClass->link->quote($currentDTFormat)})";
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
        return true;
    }

    /**
     * @param $user
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportCheckMediaToken($uid)
    {
        $this->logger->setDebugMessage("[authSupportCheckMediaToken] {$uid}", 2);

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 0) {
            $uid = 0;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}id,hash,expired FROM {$hashTable} "
            . "WHERE user_id={$uid} and clienthost=" . $this->dbClass->link->quote('_im_media');
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportCheckMediaToken] {$sql}");

        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hashValue = $row['hash'];
            $seconds = IMUtil::secondsFromNow($row['expired']);
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return false;
            }
            return $hashValue;
        }
        return false;
    }

    /**
     * @param $username
     * @param $clientId
     * @param bool $isDelete
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportRetrieveChallenge($uid, $clientId, $isDelete = true)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}id,hash,expired FROM {$hashTable}"
            . " WHERE user_id={$uid} AND clienthost=" . $this->dbClass->link->quote($clientId)
            . " ORDER BY expired DESC";
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportRetrieveChallenge] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hashValue = $row['hash'];
            $recordId = $row['id'];
            if ($isDelete) {
                $sql = "delete from {$hashTable} where id={$recordId}";
                $result = $this->dbClass->link->query($sql);
                if ($result === false) {
                    $this->dbClass->errorMessageStore('Delete:' . $sql);
                    return false;
                }
                $this->logger->setDebugMessage("[authSupportRetrieveChallenge] {$sql}");
            }
            $seconds = IMUtil::secondsFromNow($row['expired']);
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return false;
            }
            return $hashValue;
        }
        return false;
    }

    /**
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportRemoveOutdatedChallenges()
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTStr = $this->dbClass->link->quote(IMUtil::currentDTString($this->dbSettings->getExpiringSeconds()));
        $sql = "delete from {$hashTable} where expired < {$currentDTStr}";
        $this->logger->setDebugMessage("[authSupportRemoveOutdatedChallenges] {$sql}");
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        return true;
    }

    /**
     * @param $username
     * @param $credential
     * @return bool(true: create user, false: reuse user)|null in error
     */
    public function authSupportOAuthUserHandling($keyValues)
    {
        $user_id = $this->authSupportGetUserIdFromUsername($keyValues["username"]);

        $returnValue = null;
        $userTable = $this->dbSettings->getUserTable();
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            $this->dbClass->errorMessageStore("PDO class can't set up a connection.");
            return $returnValue;
        }

        $currentDTFormat = $this->dbClass->link->quote(IMUtil::currentDTString());
        $keys = array("limitdt");
        $values = array($currentDTFormat);
        $updates = array("limitdt=" . $currentDTFormat);
        if (is_array($keyValues)) {
            foreach ($keyValues as $key => $value) {
                $keys[] = $key;
                $values[] = $this->dbClass->link->quote($value);
                $updates[] = "$key=" . $this->dbClass->link->quote($value);
            }
        }
        if ($user_id > 0) {
            $returnValue = false;
            $sql = "{$this->dbClass->handler->sqlUPDATECommand()}{$userTable} SET " . implode(",", $updates)
                . " WHERE id=" . $user_id;
        } else {
            $returnValue = true;
            $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$userTable} (" . implode(",", $keys) . ") "
                . "VALUES (" . implode(",", $values) . ")";
        }
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('[authSupportOAuthUserHandling] ' . $sql);
            return $returnValue;
        }
        $this->logger->setDebugMessage("[authSupportOAuthUserHandling] {$sql}");
        return $returnValue;
    }

    /**
     * @param $username
     * @return bool
     *
     * Using 'authuser'
     */
    public function authSupportRetrieveHashedPassword($username)
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}hashedpasswd FROM {$userTable} WHERE username=" . $this->dbClass->link->quote($signedUser);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportRetrieveHashedPassword] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $limitSeconds = $this->dbSettings->getLDAPExpiringSeconds();
            if (isset($row['limitdt']) && !is_null($row['limitdt'])
                && IMUtil::secondsFromNow($row['limitdt']) < $limitSeconds
            ) {
                return false;
            }
            return $row['hashedpasswd'];
        }
        return false;
    }

    /**
     * @param $username
     * @param $hashedpassword
     * @param $isLDAP
     * @return bool
     *
     * Using 'authuser'
     */
    public function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($isLDAP !== true) {
            if ($this->authSupportRetrieveHashedPassword($username) !== false) {
                $this->logger->setErrorMessage('User Already exist: ' . $username);
                return false;
            }
            if (!$this->dbClass->setupConnection()) { //Establish the connection
                return false;
            }
            $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$userTable} (username, hashedpasswd) "
                . "VALUES ({$this->dbClass->link->quote($username)}, {$this->dbClass->link->quote($hashedpassword)})";
            $result = $this->dbClass->link->query($sql);
            if ($result === false) {
                $this->dbClass->errorMessageStore('Insert:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
        } else {
            $user_id = -1;
            $timeUp = false;
            $hpw = null;
            if (!$this->dbClass->setupConnection()) { //Establish the connection
                return false;
            }

            $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username=" . $this->dbClass->link->quote($username);
            $result = $this->dbClass->link->query($sql);
            if ($result === false) {
                $this->dbClass->errorMessageStore('Select:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (isset($row['limitdt']) && !is_null($row['limitdt'])) {
                    if (time() - strtotime($row['limitdt']) > $this->dbSettings->getLDAPExpiringSeconds()) {
                        $timeUp = true;
                        $hpw = $row['hashedpasswd'];
                    }
                }
                $user_id = $row['id'];
            }
            $currentDTFormat = IMUtil::currentDTString();
            if ($user_id > 0) {
                $setClause = "limitdt=" . $this->dbClass->link->quote($currentDTFormat);
                if ($timeUp) {
                    $hexSalt = substr($hpw, -8, 8);
                    $prevPwHash = sha1($ldapPassword . hex2bin($hexSalt)) . $hexSalt;
                    if ($prevPwHash != $hpw) {
                        $setClause .= ",hashedpasswd=" . $this->dbClass->link->quote($hashedpassword);
                    }
                }
                $sql = "{$this->dbClass->handler->sqlUPDATECommand()}{$userTable} SET {$setClause} WHERE id=" . $user_id;
                $result = $this->dbClass->link->query($sql);
                $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
                if ($result === false) {
                    $this->dbClass->errorMessageStore('Update:' . $sql);
                    return false;
                }
                if ($timeUp) {
                    $this->logger->setDebugMessage("LDAP cached account time over.");
                    return false;
                }
            } else {
                $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$userTable} (username, hashedpasswd,limitdt) VALUES "
                    . "({$this->dbClass->link->quote($username)},"
                    . " {$this->dbClass->link->quote($hashedpassword)}, "
                    . " {$this->dbClass->link->quote($currentDTFormat)})";
                $result = $this->dbClass->link->query($sql);
                if ($result === false) {
                    $this->dbClass->errorMessageStore('Insert:' . $sql);
                    return false;
                }
                $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
            }
        }
        return true;
    }

    /**
     * @param $username
     * @param $hashednewpassword
     * @return bool
     *
     * Using 'authuser'
     */
    public function authSupportChangePassword($username, $hashednewpassword)
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd=" . $this->dbClass->link->quote($hashednewpassword)
            . " WHERE username=" . $this->dbClass->link->quote($signedUser);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Update:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportChangePassword] {$sql}");
        return true;
    }

    public function authTableGetUserIdFromUsername($username)
    {
        return $this->privateGetUserIdFromUsername($username, false);
    }

    public function authSupportGetUserIdFromUsername($username)
    {
        return $this->privateGetUserIdFromUsername($username, true);
    }

    private $overLimitDTUser;

    private function privateGetUserIdFromUsername($username, $isCheckLimit)
    {
        $this->logger->setDebugMessage("[authSupportGetUserIdFromUsername]username={$username}", 2);

        $this->overLimitDTUser = false;
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($username === 0) {
            return 0;
        }

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username=" . $this->dbClass->link->quote($username);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[privateGetUserIdFromUsername] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($isCheckLimit && isset($row['limitdt']) && !is_null($row['limitdt'])) {
                if (time() - strtotime($row['limitdt']) > $this->dbSettings->getLDAPExpiringSeconds()) {
                    $this->overLimitDTUser = false;
                }
            }
            return $row['id'];
        }
        return false;
    }

    /**
     * @param $groupid
     * @return bool|null
     *
     * Using 'authgroup'
     */
    public function authSupportGetGroupNameFromGroupId($groupid)
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable === null) {
            return null;
        }

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}groupname FROM {$groupTable} WHERE id=" . $this->dbClass->link->quote($groupid);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportGetGroupNameFromGroupId] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row['groupname'];
        }
        return false;
    }

    /**
     * @param $user
     * @return array|bool
     *
     * Using 'authcor'
     */
    public function authSupportGetGroupsOfUser($user)
    {
        $ldap = new LDAPAuth();
        $oAuth = new OAuthAuth();
        if ($ldap->isActive || $oAuth->isActive) {
            return $this->privateGetGroupsOfUser($user, true);
        } else {
            return $this->privateGetGroupsOfUser($user, false);
        }
    }

    public function authTableGetGroupsOfUser($user)
    {
        return $this->privateGetGroupsOfUser($user, false);
    }

    private function privateGetGroupsOfUser($user, $isCheckLimit)
    {
        $corrTable = $this->dbSettings->getCorrTable();
        if ($corrTable == null) {
            return array();
        }

        $userid = $this->privateGetUserIdFromUsername($user, $isCheckLimit);
        if ($userid === false && $this->dbSettings->getEmailAsAccount()) {
            $userid = $this->authSupportGetUserIdFromEmail($user);
        }

        $this->logger->setDebugMessage("[authSupportGetGroupsOfUser]user={$user}, userid={$userid}");

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $this->firstLevel = true;
        $this->belongGroups = array();
        $this->resolveGroup($userid);

        $this->candidateGroups = array();
        foreach ($this->belongGroups as $groupid) {
            $this->candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
        }
        return $this->candidateGroups;
    }

    /**
     * @var
     */
    private $candidateGroups;
    /**
     * @var
     */
    private $belongGroups;
    /**
     * @var
     */
    private $firstLevel;

    /**
     * @param $groupid
     * @return bool
     *
     * Using 'authcor'
     */
    private function resolveGroup($groupid)
    {
        $corrTable = $this->dbSettings->getCorrTable();

        if ($this->firstLevel) {
            $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE user_id = " . $this->dbClass->link->quote($groupid);
            $this->firstLevel = false;
        } else {
            $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE group_id = " . $this->dbClass->link->quote($groupid);
            //    $this->belongGroups[] = $groupid;
        }
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->logger->setDebugMessage('Select:' . $sql);
            return false;
        }
        if ($result->columnCount() === 0) {
            return false;
        }
        $this->logger->setDebugMessage("[resolveGroup] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (!in_array($row['dest_group_id'], $this->belongGroups)) {
                $this->belongGroups[] = $row['dest_group_id'];
                $this->resolveGroup($row['dest_group_id']);
            }
        }
    }

    /**
     * @param $tableName
     * @param $userField
     * @param $user
     * @param $keyField
     * @param $keyValue
     * @return bool
     *
     * Using any table.
     */
    public function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $user = $this->authSupportUnifyUsernameAndEmail($user);
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$tableName} WHERE {$userField}="
            . $this->dbClass->link->quote($user) . " AND {$keyField}=" . $this->dbClass->link->quote($keyValue);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportCheckMediaPrivilege] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row;
        }
        return false;
    }

    /**
     * @param $email
     * @return bool|int
     *
     * Using 'authuser'
     */
    public function authSupportGetUserIdFromEmail($email)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($email === 0) {
            return 0;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}id FROM {$userTable} WHERE email=" . $this->dbClass->link->quote($email);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportGetUserIdFromEmail] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row['id'];
        }
        return false;
    }

    /**
     * @param $userid
     * @return bool|int
     *
     * Using 'authuser'
     */
    public function authSupportGetUsernameFromUserId($userid)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($userid === 0) {
            return 0;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}username FROM {$userTable} WHERE id=" . $this->dbClass->link->quote($userid);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportGetUsernameFromUserId] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row['username'];
        }
        return false;
    }

    /**
     * @param $username
     * @return bool|string
     *
     * Using 'authuser'
     */
    public function authSupportUnifyUsernameAndEmail($username)
    {
        if (!$this->dbSettings->getEmailAsAccount() || strlen($username) == 0) {
            return $username;
        }
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}username,email FROM {$userTable} WHERE username=" .
            $this->dbClass->link->quote($username) . " or email=" . $this->dbClass->link->quote($username);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUnifyUsernameAndEmail] {$sql}");
        $usernameCandidate = '';
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['username'] == $username) {
                $usernameCandidate = $username;
            }
            if ($row['email'] == $username) {
                $usernameCandidate = $row['username'];
            }
//            $limitSeconds = $this->dbSettings->getLDAPExpiringSeconds();
//            if (isset($row['limitdt']) && !is_null($row['limitdt'])
//                && IMUtil::secondsFromNow($row['limitdt']) < $limitSeconds) {
//                return "_im_auth_failed_";
//            }
        }
        return $usernameCandidate;
    }

    /**
     * @param $userid
     * @param $clienthost
     * @param $hash
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$hashTable} (hash,expired,clienthost,user_id) VALUES("
            . implode(',', array($this->dbClass->link->quote($hash), $this->dbClass->link->quote($currentDTFormat),
                $this->dbClass->link->quote($clienthost), $this->dbClass->link->quote($userid))) . ')';
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportStoreIssuedHashForResetPassword] {$sql}");
        return true;
    }


    /**
     * @param $userid
     * @param $randdata
     * @param $hash
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}hash,expired FROM {$hashTable} WHERE"
            . " user_id=" . $this->dbClass->link->quote($userid)
            . " AND clienthost=" . $this->dbClass->link->quote($randdata);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportCheckIssuedHashForResetPassword] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hashValue = $row['hash'];
            if (IMUtil::secondsFromNow($row['expired']) > 3600) {
                return false;
            }
            if ($hash == $hashValue) {
                return true;
            }
        }
        return false;
    }

    public function authSupportUserEnrollmentStart($userid, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$hashTable} (hash,expired,user_id) VALUES(" . implode(',', array(
                $this->dbClass->link->quote($hash),
                $this->dbClass->link->quote($currentDTFormat),
                $this->dbClass->link->quote($userid))) . ')';
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentStart] {$sql}");
        return true;
    }

    public function authSupportUserEnrollmentEnrollingUser($hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString(3600);
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}user_id FROM {$hashTable} WHERE hash = " . $this->dbClass->link->quote($hash) .
            " AND clienthost IS NULL AND expired > " . $this->dbClass->link->quote($currentDTFormat);
        $resultHash = $this->dbClass->link->query($sql);
        if ($resultHash === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentEnrollingUser] {$sql}");
        foreach ($resultHash->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $userID = $row['user_id'];
            if ($userID < 1) {
                return false;
            }
            return $userID;
        }
        return false;
    }

    public function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd=" . $this->dbClass->link->quote($password)
            . (($rawPWField !== false) ? "," . $rawPWField . "=" . $this->dbClass->link->quote($rawPW) : "")
            . " WHERE id=" . $this->dbClass->link->quote($userID);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Update:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentActivateUser] {$sql}");
        return $userID;
    }

}