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

class DB_PDO_Auth_Handler extends DB_Auth_Common implements Auth_Interface_DB
{
    /**
     * @param $username
     * @param $challenge
     * @param $clientId
     * @return bool
     *
     * Using 'issuedhash'
     */
    function authSupportStoreChallenge($uid, $challenge, $clientId)
    {
        $this->logger->setDebugMessage("[authSupportStoreChallenge] $uid, $challenge, $clientId");

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "select id from {$hashTable} where user_id={$uid} and clienthost=" . $this->link->quote($clientId);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
        $currentDTFormat = IMUtil::currentDTString();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $sql = "{$this->handler->sqlUPDATECommand()}{$hashTable} SET hash=" . $this->link->quote($challenge)
                . ",expired=" . $this->link->quote($currentDTFormat)
                . " WHERE id={$row['id']}";
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('UPDATE:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
            return true;
        }
        $sql = "{$this->handler->sqlINSERTCommand()}{$hashTable} (user_id, clienthost, hash, expired) "
            . "VALUES ({$uid},{$this->link->quote($clientId)},"
            . "{$this->link->quote($challenge)},{$this->link->quote($currentDTFormat)})";
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
    function authSupportCheckMediaToken($uid)
    {
        $this->logger->setDebugMessage("[authSupportCheckMediaToken] {$uid}", 2);

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 0) {
            $uid = 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}id,hash,expired FROM {$hashTable} "
            . "WHERE user_id={$uid} and clienthost=" . $this->link->quote('_im_media');
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
    function authSupportRetrieveChallenge($uid, $clientId, $isDelete = true)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}id,hash,expired FROM {$hashTable}"
            . " WHERE user_id={$uid} AND clienthost=" . $this->link->quote($clientId)
            . " ORDER BY expired DESC";
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportRetrieveChallenge] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hashValue = $row['hash'];
            $recordId = $row['id'];
            if ($isDelete) {
                $sql = "delete from {$hashTable} where id={$recordId}";
                $result = $this->link->query($sql);
                if ($result === false) {
                    $this->errorMessageStore('Delete:' . $sql);
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
    function authSupportRemoveOutdatedChallenges()
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTStr = $this->link->quote(IMUtil::currentDTString($this->dbSettings->getExpiringSeconds()));
        $sql = "delete from {$hashTable} where expired < {$currentDTStr}";
        $this->logger->setDebugMessage("[authSupportRemoveOutdatedChallenges] {$sql}");
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        return true;
    }

    /**
     * @param $username
     * @param $credential
     * @return bool(true: create user, false: reuse user)|null in error
     */
    function authSupportOAuthUserHandling($keyValues)
    {
        $user_id = $this->authSupportGetUserIdFromUsername($keyValues["username"]);

        $returnValue = null;
        $userTable = $this->dbSettings->getUserTable();
        if (!$this->setupConnection()) { //Establish the connection
            $this->errorMessageStore("PDO class can't set up a connection.");
            return $returnValue;
        }

        $currentDTFormat = $this->link->quote(IMUtil::currentDTString());
        $keys = array("limitdt");
        $values = array($currentDTFormat);
        $updates = array("limitdt=" . $currentDTFormat);
        if (is_array($keyValues)) {
            foreach ($keyValues as $key => $value) {
                $keys[] = $key;
                $values[] = $this->link->quote($value);
                $updates[] = "$key=" . $this->link->quote($value);
            }
        }
        if ($user_id > 0) {
            $returnValue = false;
            $sql = "{$this->handler->sqlUPDATECommand()}{$userTable} SET " . implode(",", $updates)
                . " WHERE id=" . $user_id;
        } else {
            $returnValue = true;
            $sql = "{$this->handler->sqlINSERTCommand()}{$userTable} (" . implode(",", $keys) . ") "
                . "VALUES (" . implode(",", $values) . ")";
        }
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('[authSupportOAuthUserHandling] ' . $sql);
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
    function authSupportRetrieveHashedPassword($username)
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}hashedpasswd FROM {$userTable} WHERE username=" . $this->link->quote($signedUser);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
    function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($isLDAP !== true) {
            if ($this->authSupportRetrieveHashedPassword($username) !== false) {
                $this->logger->setErrorMessage('User Already exist: ' . $username);
                return false;
            }
            if (!$this->setupConnection()) { //Establish the connection
                return false;
            }
            $sql = "{$this->handler->sqlINSERTCommand()}{$userTable} (username, hashedpasswd) "
                . "VALUES ({$this->link->quote($username)}, {$this->link->quote($hashedpassword)})";
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Insert:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
        } else {
            $user_id = -1;
            $timeUp = false;
            $hpw = null;
            if (!$this->setupConnection()) { //Establish the connection
                return false;
            }

            $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username=" . $this->link->quote($username);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
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
                $setClause = "limitdt=" . $this->link->quote($currentDTFormat);
                if ($timeUp) {
                    $hexSalt = substr($hpw, -8, 8);
                    $prevPwHash = sha1($ldapPassword . hex2bin($hexSalt)) . $hexSalt;
                    if ($prevPwHash != $hpw) {
                        $setClause .= ",hashedpasswd=" . $this->link->quote($hashedpassword);
                    }
                }
                $sql = "{$this->handler->sqlUPDATECommand()}{$userTable} SET {$setClause} WHERE id=" . $user_id;
                $result = $this->link->query($sql);
                $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
                if ($result === false) {
                    $this->errorMessageStore('Update:' . $sql);
                    return false;
                }
                if ($timeUp) {
                    $this->logger->setDebugMessage("LDAP cached account time over.");
                    return false;
                }
            } else {
                $sql = "{$this->handler->sqlINSERTCommand()}{$userTable} (username, hashedpasswd,limitdt) VALUES "
                    . "({$this->link->quote($username)},"
                    . " {$this->link->quote($hashedpassword)}, "
                    . " {$this->link->quote($currentDTFormat)})";
                $result = $this->link->query($sql);
                if ($result === false) {
                    $this->errorMessageStore('Insert:' . $sql);
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
    function authSupportChangePassword($username, $hashednewpassword)
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd=" . $this->link->quote($hashednewpassword)
            . " WHERE username=" . $this->link->quote($signedUser);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Update:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportChangePassword] {$sql}");
        return true;
    }

    function authTableGetUserIdFromUsername($username)
    {
        return $this->privateGetUserIdFromUsername($username, false);
    }

    function authSupportGetUserIdFromUsername($username)
    {
        return $this->privateGetUserIdFromUsername($username, true);
    }

    private $overLimitDTUser;

    private
    function privateGetUserIdFromUsername($username, $isCheckLimit)
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

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username=" . $this->link->quote($username);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
    function authSupportGetGroupNameFromGroupId($groupid)
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable === null) {
            return null;
        }

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}groupname FROM {$groupTable} WHERE id=" . $this->link->quote($groupid);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
    function authSupportGetGroupsOfUser($user)
    {
        $ldap = new LDAPAuth();
        $oAuth = new OAuthAuth();
        if ($ldap->isActive || $oAuth->isActive) {
            return $this->privateGetGroupsOfUser($user, true);
        } else {
            return $this->privateGetGroupsOfUser($user, false);
        }
    }

    function authTableGetGroupsOfUser($user)
    {
        return $this->privateGetGroupsOfUser($user, false);
    }

    private
    function privateGetGroupsOfUser($user, $isCheckLimit)
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

        if (!$this->setupConnection()) { //Establish the connection
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
    private
        $candidateGroups;
    /**
     * @var
     */
    private
        $belongGroups;
    /**
     * @var
     */
    private
        $firstLevel;

    /**
     * @param $groupid
     * @return bool
     *
     * Using 'authcor'
     */
    private
    function resolveGroup($groupid)
    {
        $corrTable = $this->dbSettings->getCorrTable();

        if ($this->firstLevel) {
            $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE user_id = " . $this->link->quote($groupid);
            $this->firstLevel = false;
        } else {
            $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE group_id = " . $this->link->quote($groupid);
            //    $this->belongGroups[] = $groupid;
        }
        $result = $this->link->query($sql);
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
    function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $user = $this->authSupportUnifyUsernameAndEmail($user);
        $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$tableName} WHERE {$userField}="
            . $this->link->quote($user) . " AND {$keyField}=" . $this->link->quote($keyValue);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
    function authSupportGetUserIdFromEmail($email)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($email === 0) {
            return 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}id FROM {$userTable} WHERE email=" . $this->link->quote($email);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
    function authSupportGetUsernameFromUserId($userid)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($userid === 0) {
            return 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}username FROM {$userTable} WHERE id=" . $this->link->quote($userid);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
    function authSupportUnifyUsernameAndEmail($username)
    {
        if (!$this->dbSettings->getEmailAsAccount() || strlen($username) == 0) {
            return $username;
        }
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}username,email FROM {$userTable} WHERE username=" .
            $this->link->quote($username) . " or email=" . $this->link->quote($username);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
    function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $sql = "{$this->handler->sqlINSERTCommand()}{$hashTable} (hash,expired,clienthost,user_id) VALUES("
            . implode(',', array($this->link->quote($hash), $this->link->quote($currentDTFormat),
                $this->link->quote($clienthost), $this->link->quote($userid))) . ')';
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Insert:' . $sql);
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
    public
    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}hash,expired FROM {$hashTable} WHERE"
            . " user_id=" . $this->link->quote($userid)
            . " AND clienthost=" . $this->link->quote($randdata);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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

    public
    function authSupportUserEnrollmentStart($userid, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $sql = "{$this->handler->sqlINSERTCommand()}{$hashTable} (hash,expired,user_id) VALUES(" . implode(',', array(
                $this->link->quote($hash),
                $this->link->quote($currentDTFormat),
                $this->link->quote($userid))) . ')';
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentStart] {$sql}");
        return true;
    }

    public
    function authSupportUserEnrollmentEnrollingUser($hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString(3600);
        $sql = "{$this->handler->sqlSELECTCommand()}user_id FROM {$hashTable} WHERE hash = " . $this->link->quote($hash) .
            " AND clienthost IS NULL AND expired > " . $this->link->quote($currentDTFormat);
        $resultHash = $this->link->query($sql);
        if ($resultHash === false) {
            $this->errorMessageStore('Select:' . $sql);
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

    public
    function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd=" . $this->link->quote($password)
            . (($rawPWField !== false) ? "," . $rawPWField . "=" . $this->link->quote($rawPW) : "")
            . " WHERE id=" . $this->link->quote($userID);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Update:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentActivateUser] {$sql}");
        return $userID;
    }

}