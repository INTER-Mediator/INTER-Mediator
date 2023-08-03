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

namespace INTERMediator\DB\Support;

use INTERMediator\IMUtil;
use INTERMediator\OAuthAuth;
use INTERMediator\Params;
use PDO;
use Exception;

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
    public function authSupportStoreChallenge(string $uid, string $challenge, string $clientId): void
    {
        $this->logger->setDebugMessage("[authSupportStoreChallenge] $uid, $challenge, $clientId");

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return;
        }
        $sql = "select id from {$hashTable} where user_id={$uid} and clienthost=" . $this->dbClass->link->quote($clientId);
        $result = $this->dbClass->link->query($sql);
        if (!$result) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return;
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
                return;
            }
            $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
            return;
        }
        $tableRef = "{$hashTable} (user_id, clienthost, hash, expired)";
        $setClause = "VALUES ({$uid},{$this->dbClass->link->quote($clientId)},"
            . "{$this->dbClass->link->quote($challenge)},{$this->dbClass->link->quote($currentDTFormat)})";
        $sql = $this->dbClass->handler->sqlINSERTCommand($tableRef, $setClause);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return;
        }
        $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
        return;
    }

    /**
     * @param $user
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportCheckMediaToken(string $uid): bool
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
    public function authSupportRetrieveChallenge(string $uid, string $clientId, bool $isDelete = true): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return null;
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
            return null;
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
                    return null;
                }
                $this->logger->setDebugMessage("[authSupportRetrieveChallenge] {$sql}");
            }
            $seconds = IMUtil::secondsFromNow($row['expired']);
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return null;
            }
            return $hashValue;
        }
        return null;
    }

    /**
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportRemoveOutdatedChallenges(): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $expireSeconds = $this->dbSettings->getExpiringSeconds();
        $currentDTStr = $this->dbClass->link->quote(IMUtil::currentDTString($expireSeconds));
        $longBeforeDTStr = $this->dbClass->link->quote(IMUtil::currentDTString(3600 * 24 * 3));
        $sql = "{$this->dbClass->handler->sqlDELETECommand()}{$hashTable} WHERE" .
            " (clienthost IS NOT NULL AND expired < {$currentDTStr}) OR (expired < {$longBeforeDTStr})";
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportRemoveOutdatedChallenges] {$sql}");

        return true;
    }

    /**
     * @param $username
     * @param $credential
     * @return bool(true: create user, false: reuse user)|null in error
     */
    public function authSupportOAuthUserHandling($keyValues): bool
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
            $sql = $this->dbClass->handler->sqlINSERTCommand(
                "{$userTable} (" . implode(",", $keys) . ")",
                "VALUES (" . implode(",", $values) . ")");
        }
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('[authSupportOAuthUserHandling] ' . $sql);
            return $returnValue;
        }
        $this->logger->setDebugMessage("[authSupportOAuthUserHandling] {
                $sql}");
        return $returnValue;
    }

    /**
     * @param $username
     * @return bool
     *
     * Using 'authuser'
     */
    public function authSupportRetrieveHashedPassword(string $username): ?string
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return null;
        }

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return null;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}hashedpasswd FROM {$userTable} WHERE username = "
            . $this->dbClass->link->quote($signedUser);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportRetrieveHashedPassword] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $limitSeconds = $this->dbSettings->getSAMLExpiringSeconds();
            if (isset($row['limitdt']) && !is_null($row['limitdt'])
                && IMUtil::secondsFromNow($row['limitdt']) < $limitSeconds
            ) {
                $this->logger->setDebugMessage("[authSupportRetrieveHashedPassword] returns null");
                return null;
            }
            $this->logger->setDebugMessage("[authSupportRetrieveHashedPassword] {$row['hashedpasswd']}");
            return $row['hashedpasswd'];
        }
        $this->logger->setDebugMessage("[authSupportRetrieveHashedPassword] returns null");
        return null;
    }

    /**
     * @param $username
     * @param $hashedpassword
     * @param $isSAML
     * @return bool
     *
     * Using 'authuser'
     */
    public function authSupportCreateUser(string  $username, string $hashedpassword, bool $isSAML = false,
                                          ?string $ldapPassword = null, ?array $attrs = null): bool
    {
        $this->logger->setDebugMessage("[authSupportCreateUser] username ={$username}, isSAML ={$isSAML}", 2);

        $userTable = $this->dbSettings->getUserTable();
        if ($isSAML !== true) {
            if ($this->authSupportRetrieveHashedPassword($username)) {
                $this->logger->setErrorMessage('User Already exist: ' . $username);
                return false;
            }
            if (!$this->dbClass->setupConnection()) { //Establish the connection
                return false;
            }
            $fieldArray = ['username', 'hashedpasswd'];
            $valueArray = [$username, $hashedpassword];
            if (is_array($attrs)) {
                foreach ($attrs as $field => $value) {
                    if (!in_array($field, $fieldArray)) {
                        $fieldArray[] = $field;
                        $valueArray[] = $value;
                    }
                }
            }
            $tableRef = "{$userTable} (" . implode(',', $fieldArray) . ")";
            $setArray = implode(',', array_map(function ($e) {
                return $this->dbClass->link->quote($e);
            }, $valueArray));
            $sql = $this->dbClass->handler->sqlINSERTCommand($tableRef, "VALUES({$setArray})");
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

            $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username = "
                . $this->dbClass->link->quote($username);
            $result = $this->dbClass->link->query($sql);
            if ($result === false) {
                $this->dbClass->errorMessageStore('Select:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage(
                "[authSupportCreateUser - SAML] {$sql}, SAML expiring ={$this->dbSettings->getSAMLExpiringSeconds()}");
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (isset($row['limitdt']) && !is_null($row['limitdt'])) {
                    if (IMUtil::secondsFromNow($row['limitdt']) > $this->dbSettings->getSAMLExpiringSeconds()) {
                        $this->logger->setDebugMessage("[authSupportCreateUser - SAML] Over Limit Datetime . ");
                        $timeUp = true;
                        $hpw = $row['hashedpasswd'];
                        $this->logger->setDebugMessage("[authSupportCreateUser - SAML] Detect hashedpasswd ={$hpw}");
                    }
                }
                $user_id = $row['id'];
                $this->logger->setDebugMessage("[authSupportCreateUser - SAML] Detect user id ={$user_id}");
            }
            $currentDTFormat = IMUtil::currentDTString();
            if ($user_id > 0) {
                $setClause = "limitdt = " . $this->dbClass->link->quote($currentDTFormat);
                //if ($timeUp) {
                $hexSalt = substr($hpw, -8, 8);
                $prevPwHash = sha1($ldapPassword . hex2bin($hexSalt)) . $hexSalt;
                if ($prevPwHash != $hpw) {
                    $setClause .= ",hashedpasswd = " . $this->dbClass->link->quote($hashedpassword);
                }
                //}
                $sql = "{$this->dbClass->handler->sqlUPDATECommand()}{$userTable} SET {$setClause} WHERE id = {$user_id}";
                $result = $this->dbClass->link->query($sql);
                $this->logger->setDebugMessage("[authSupportCreateUser - SAML] {$sql}");
                if ($result === false) {
                    $this->dbClass->errorMessageStore('Update:' . $sql);
                    return false;
                }
                if ($timeUp) {
                    $this->logger->setDebugMessage("LDAP cached account time over, but it's updated.");
                    return true; // This case should be handled as succeed.
                }
            } else {
                $fieldArray = ['username', 'hashedpasswd', 'limitdt'];
                $valueArray = [$username, $hashedpassword, $currentDTFormat];
                if (is_array($attrs)) {
                    foreach ($attrs as $field => $value) {
                        if (!in_array($field, $fieldArray)) {
                            $fieldArray[] = $field;
                            $valueArray[] = $value;
                        }
                    }
                }
                $tableRef = "{$userTable} (" . implode(',', $fieldArray) . ")";
                $setArray = implode(',', array_map(function ($e) {
                    return $this->dbClass->link->quote($e);
                }, $valueArray));
                $sql = $this->dbClass->handler->sqlINSERTCommand($tableRef, "VALUES({$setArray})");
                $result = $this->dbClass->link->query($sql);
                if ($result === false) {
                    $this->dbClass->errorMessageStore('Insert:' . $sql);
                    return false;
                }
                $this->logger->setDebugMessage("[authSupportCreateUser - SAML] {$sql}");
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
    public function authSupportChangePassword(string $username, string $hashednewpassword): bool
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd = "
            . $this->dbClass->link->quote($hashednewpassword)
            . " WHERE username = " . $this->dbClass->link->quote($signedUser);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Update:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportChangePassword] {$sql}");
        return true;
    }

    public function authTableGetUserIdFromUsername(string $username): string
    {
        return $this->privateGetUserIdFromUsername($username, false);
    }

    public function authSupportGetUserIdFromUsername(string $username): string
    {
        return $this->privateGetUserIdFromUsername($username, true);
    }

    private function privateGetUserIdFromUsername(?string $username, bool $isCheckLimit): ?string
    {
        $this->logger->setDebugMessage("[privateGetUserIdFromUsername]username ={$username}", 2);

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
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username = "
            . $this->dbClass->link->quote($username ?? "");
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[privateGetUserIdFromUsername] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (!$isCheckLimit || is_null($row['limitdt'])) {
                return $row['id'];
            }
            if ($isCheckLimit && isset($row['limitdt']) && !is_null($row['limitdt'])) {
                if (time() - strtotime($row['limitdt']) <= $this->dbSettings->getSAMLExpiringSeconds()) {
                    return $row['id'];
                }
            }
        }
        return false;
    }

    /**
     * @param $groupid
     * @return bool|null
     *
     * Using 'authgroup'
     */
    public function authSupportGetGroupNameFromGroupId(string $groupid): ?string
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable === null) {
            return null;
        }

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}groupname FROM {$groupTable} WHERE id = "
            . $this->dbClass->link->quote($groupid);
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
    public function authSupportGetGroupsOfUser(?string $user): array
    {
        $oAuth = new OAuthAuth();
        if ($oAuth->isActive) {
            return $this->privateGetGroupsOfUser($user, true);
        } else {
            return $this->privateGetGroupsOfUser($user, false);
        }
    }

    public function authTableGetGroupsOfUser(string $user): ?array
    {
        return $this->privateGetGroupsOfUser($user, false);
    }

    private function privateGetGroupsOfUser(?string $user, bool $isCheckLimit): ?array
    {
        $corrTable = $this->dbSettings->getCorrTable();
        if ($corrTable == null) {
            return array();
        }

        $userid = $this->privateGetUserIdFromUsername($user, $isCheckLimit);
        if ($userid === false && $this->dbSettings->getEmailAsAccount()) {
            $userid = $this->authSupportGetUserIdFromEmail($user);
        }

        $this->logger->setDebugMessage("[authSupportGetGroupsOfUser]user ={$user}, userid ={$userid}");

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return null;
        }
        $this->firstLevel = true;
        $this->belongGroups = array();
        $this->resolveGroup($userid);

        $candidateGroups = array();
        foreach ($this->belongGroups as $groupid) {
            $candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
        }
        if (count($candidateGroups) == 0) {
            $defaultGroup = Params::getParameterValue("defaultGroupName", false);
            if ($defaultGroup) {
                $candidateGroups = [$defaultGroup];
            }
        }
        return $candidateGroups;
    }


    /**
     * @var
     */
    private array $belongGroups;
    /**
     * @var
     */
    private bool $firstLevel;

    /**
     * @param $groupid
     * @return bool
     *
     * Using 'authcor'
     */
    private function resolveGroup(string $groupid): void
    {
        if (strlen($groupid) < 1) {
            return;
        }
        $corrTable = $this->dbSettings->getCorrTable();

        if ($this->firstLevel) {
            $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE user_id = "
                . $this->dbClass->link->quote($groupid) . " ORDER BY id";
            $this->firstLevel = false;
        } else {
            $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE group_id = "
                . $this->dbClass->link->quote($groupid) . " ORDER BY id";
            //    $this->belongGroups[] = $groupid;
        }
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->logger->setDebugMessage('Select:' . $sql);
            return;
        }
        $this->logger->setDebugMessage("[resolveGroup] {$sql}");
        if ($result->columnCount() === 0) {
            return;
        }
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
    public function authSupportCheckMediaPrivilege(
        string $tableName, string $targeting, string $userField, string $user, string $keyField, string $keyValue): ?array
    {
        if (strlen($user) == 0 || is_null($user)) {
            return null;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return null;
        }

        $user = $this->authSupportUnifyUsernameAndEmail($user);
        switch ($targeting) {
            case  'field_user':
                $queryClause = "{$userField}=" . $this->dbClass->link->quote($user)
                    . " AND {$keyField}=" . $this->dbClass->link->quote($keyValue);
                break;
            case  'field_group':
                $belongGroups = $this->authSupportGetGroupsOfUser($user);
                $groupCriteria = array();
                foreach ($belongGroups as $oneGroup) {
                    $groupCriteria[] = "{$userField}=" . $this->dbClass->link->quote($oneGroup);
                }
                if (count($groupCriteria) == 0) {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = "(" . implode(' OR ', $groupCriteria) . ")"
                        . " AND {$keyField}=" . $this->dbClass->link->quote($keyValue);
                }
                break;
            default: // 'context_auth' or 'no_auth'
                throw new Exception('Unexpected authSupportCheckMediaPrivilege method usage.');
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$tableName} WHERE {$queryClause}";
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore("[authSupportCheckMediaPrivilege] {$sql}");
            return null;
        }
        $this->logger->setDebugMessage("[authSupportCheckMediaPrivilege] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row;
        }
        return null;
    }

    private $userCache = []; // Cache for authSupportGetUserIdFromEmail method.

    /**
     * @param $email
     * @return bool|int
     *
     * Using 'authuser'
     */
    public function authSupportGetUserIdFromEmail(string $email): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return null;
        }
        if ($email === 0) {
            return 0;
        }
        if (is_null($email)) {
            return null;
        }
        if (isset($this->userCache[$email])) {
            return $this->userCache[$email];
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return null;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}id FROM {$userTable} WHERE email = "
            . $this->dbClass->link->quote($email);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportGetUserIdFromEmail] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->userCache[$email] = $row['id'];
            return $row['id'];
        }
        return null;
    }

    /**
     * @param $userid
     * @return bool|int
     *
     * Using 'authuser'
     */
    public function authSupportGetUsernameFromUserId(string $userid): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return null;
        }
        if ($userid === 0) {
            return 0;
        }
        if (is_null($userid)) {
            return null;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return null;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}username FROM {$userTable} WHERE id = "
            . $this->dbClass->link->quote($userid);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportGetUsernameFromUserId] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row['username'];
        }
        return null;
    }

    /**
     * @param $username
     * @return bool|string
     *
     * Using 'authuser'
     */
    public function authSupportUnifyUsernameAndEmail(?string $username): ?string
    {
        if (!$this->dbSettings->getEmailAsAccount() || strlen($username) == 0 || is_null($username)) {
            return $username;
        }
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return null;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return null;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}username,email FROM {$userTable} WHERE username = " .
            $this->dbClass->link->quote($username) . " or email = " . $this->dbClass->link->quote($username);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return null;
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
//            $limitSeconds = $this->dbSettings->getSAMLExpiringSeconds();
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
    public function authSupportStoreIssuedHashForResetPassword(string $userid, string $clienthost, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $tableRef = "{$hashTable} (hash,expired,clienthost,user_id)";
        $setArray = implode(',', array_map(function ($e) {
            return $this->dbClass->link->quote($e);
        }, [$hash, $currentDTFormat, $clienthost, $userid]));
        $sql = $this->dbClass->handler->sqlINSERTCommand($tableRef, "VALUES({$setArray})");
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
    public function authSupportCheckIssuedHashForResetPassword(string $userid, string $randdata, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}hash,expired FROM {$hashTable} WHERE"
            . " user_id = " . $this->dbClass->link->quote($userid)
            . " and clienthost = " . $this->dbClass->link->quote($randdata);
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

    public function authSupportUserEnrollmentStart(string $userid, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $tableRef = "{$hashTable} (hash,expired,user_id)";
        $setArray = implode(',', array_map(function ($e) {
            return $this->dbClass->link->quote($e);
        }, [$hash, $currentDTFormat, $userid]));
        $sql = $this->dbClass->handler->sqlINSERTCommand($tableRef, "VALUES({$setArray})");
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentStart] {$sql}");
        return true;
    }

    public function authSupportUserEnrollmentEnrollingUser(string $hash): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return null;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return null;
        }
        $currentDTFormat = IMUtil::currentDTString(3600);
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}user_id FROM {$hashTable} WHERE hash = "
            . $this->dbClass->link->quote($hash) .
            " and clienthost IS NULL and expired > " . $this->dbClass->link->quote($currentDTFormat);
        $resultHash = $this->dbClass->link->query($sql);
        if ($resultHash === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentEnrollingUser] {$sql}");
        foreach ($resultHash->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $userID = $row['user_id'];
            if ($userID < 1) {
                return null;
            }
            return $userID;
        }
        return null;
    }

    public function authSupportUserEnrollmentActivateUser(
        string $userID, string $password, string $rawPWField, string $rawPW): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return null;
        }
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return null;
        }

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return null;
        }
        $sql = "{$this->dbClass->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd = "
            . $this->dbClass->link->quote($password)
            . (($rawPWField !== false) ? "," . $rawPWField . " = " . $this->dbClass->link->quote($rawPW) : "")
            . " WHERE id = " . $this->dbClass->link->quote($userID);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Update:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentActivateUser] {$sql}");

        $sql = "{$this->dbClass->handler->sqlDELETECommand()}{$hashTable} "
            . " WHERE user_id = " . $this->dbClass->link->quote($userID);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Delete:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentActivateUser] {$sql}");

        return $userID;
    }

    public function authSupportIsWithinSAMLLimit(string $userID): bool
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->dbClass->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE id = " . $userID;
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportIsWithinSAMLLimit] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->logger->setDebugMessage("[authSupportIsWithinSAMLLimit] " . var_export($row, true));
            $this->logger->setDebugMessage("[authSupportIsWithinSAMLLimit] "
                . "ldapLimit ={$this->dbSettings->getSAMLExpiringSeconds()}");
            if (isset($row['limitdt']) && !is_null($row['limitdt'])) {
                if (time() - strtotime($row['limitdt']) > $this->dbSettings->getSAMLExpiringSeconds()) {
                    $this->logger->setDebugMessage("[authSupportIsWithinSAMLLimit] returns false ");
                    return false;
                } else {
                    $this->logger->setDebugMessage("[authSupportIsWithinSAMLLimit] returns true ");
                    return true;
                }
            } else {
                $this->logger->setDebugMessage("[authSupportIsWithinSAMLLimit] returns true for limitdt is null");
                return true;
            }
        }
        return false;
    }

    public function authSupportCanMigrateSHA256Hash(): bool // authuser, issuedhash
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $messages = $this->dbClass->handler->authSupportCanMigrateSHA256Hash($userTable, $hashTable);
        if (count($messages) > 0) {
            $this->logger->setErrorMessages($messages);
            return false;
        }
        return true;
    }
}
