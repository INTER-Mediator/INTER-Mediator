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

use DateTime;
use INTERMediator\DB\PDO;
use INTERMediator\IMUtil;
use INTERMediator\OAuthAuth;
use INTERMediator\Params;
use Exception;

/**
 *
 */
class DB_Auth_Handler_PDO extends DB_Auth_Common
{
    /**
     * @var PDO
     */
    protected PDO $pdoDB;

    /**
     * @param PDO $parent
     */
    public function __construct(PDO $parent)
    {
        parent::__construct($parent);
        $this->pdoDB = $parent;
    }

    /**
     * @param string|null $uid
     * @param string $challenge
     * @param string $clientId
     * @param string $prefix
     * @return void
     *
     * Using 'issuedhash'
     */
    public function authSupportStoreChallenge(?string $uid, string $challenge, string $clientId, string $prefix = ""): void
    {
        $this->logger->setDebugMessage("[authSupportStoreChallenge] $uid, $challenge, $clientId");

        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return;
        }
        $expSeconds = $prefix === "" ? $this->dbSettings->getExpiringSeconds() :
            ($prefix === "#" ? $this->dbSettings->getExpiringSeconds() :
                ($prefix === "+" ? $this->dbSettings->getExpiringSeconds() :
                    ($prefix === "=" ? $this->dbSettings->getExpiringSeconds2FA() :
                        $this->dbSettings->getExpiringSeconds())));

        // Retrieving issuedhash records that are same user_id and clientID.
        $sql = "SELECT id FROM {$hashTable} WHERE user_id={$uid} AND clienthost={$this->pdoDB->link->quote($clientId)}";
        $result = $this->pdoDB->link->query($sql);
        if (!$result) {
            $this->pdoDB->errorMessageStore('ERROR in SELECT:' . $sql);
            return;
        }
        $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
        // Calculating expiring date and time.
        $expiringDT = IMUtil::currentDTString(-$expSeconds);
        // Checking wheather here are any records that are same user_id and ClientID
        $didUpdate = false;
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            // if it exists, updateging the record with hash and expired fields.
            if (substr($row['hash'] ?? "", 0, 1) === $prefix) {
                $didUpdate = true;
                $sql = "{$this->pdoDB->handler->sqlUPDATECommand()}{$hashTable}"
                    . " SET hash={$this->pdoDB->link->quote($challenge)}"
                    . ",expired={$this->pdoDB->link->quote($expiringDT)}"
                    . " WHERE id={$row['id']}";
                $result = $this->pdoDB->link->query($sql);
                if ($result === false) {
                    $this->pdoDB->errorMessageStore('Error in UPDATE:' . $sql);
                } else {
                    $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
                }
            }
        }
        if (!$didUpdate) { // If here is no record with user_id and clientID, it creates with parameters.
            $tableRef = "{$hashTable} (user_id, clienthost, hash, expired)";
            $setClause = "VALUES ({$uid},{$this->pdoDB->link->quote($clientId)},"
                . "{$this->pdoDB->link->quote($prefix.$challenge)},{$this->pdoDB->link->quote($expiringDT)})";
            $sql = $this->pdoDB->handler->sqlINSERTCommand($tableRef, $setClause);
            $result = $this->pdoDB->link->query($sql);
            if ($result === false) {
                $this->pdoDB->errorMessageStore('ERROR in CREATE:' . $sql);
            } else {
                $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
            }
        }
    }

    /**
     * @param string $uid
     * @return ?string
     *
     * Using 'issuedhash'
     * @throws Exception
     */
    public
    function authSupportCheckMediaToken(string $uid): ?string
    {
        $this->logger->setDebugMessage("[authSupportCheckMediaToken] {$uid}", 2);

        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return null;
        }
        if ($uid < 0) {
            $uid = 0;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}id,hash,expired FROM {$hashTable} "
            . "WHERE user_id={$uid} and clienthost={$this->pdoDB->link->quote('_im_media')}";
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportCheckMediaToken] {$sql}");
        $justNow = new DateTime();
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if ((new DateTime($row['expired']))->diff($justNow)->invert != 1) { // Judge timeout.
                return null;
            }
            return $row['hash'];
        }
        return null;
    }

    /**
     * @param string $uid
     * @param string $clientId
     * @param bool $isDelete
     * @param string $prefix
     * @return ?string
     *
     * Using 'issuedhash'
     * @throws Exception
     */
    public function authSupportRetrieveChallenge(
        string $uid, string $clientId, bool $isDelete = true, string $prefix = ""): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return null;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}id,hash,expired FROM {$hashTable}"
            . " WHERE user_id={$uid} AND clienthost={$this->pdoDB->link->quote($clientId)}"
            . ($prefix === "" ? "" : " AND hash like '{$prefix}%'")
            . " ORDER BY expired DESC";
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('ERROR in SELECT:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportRetrieveChallenge] {$sql}");
        $justNow = new DateTime();
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if ($isDelete) {
                $sql = "delete from {$hashTable} where id={$row['id']}";
                $result = $this->pdoDB->link->query($sql);
                if ($result === false) {
                    $this->pdoDB->errorMessageStore('ERROR in DELETE:' . $sql);
                    return null;
                }
                $this->logger->setDebugMessage("[authSupportRetrieveChallenge] {$sql}");
            }
            if ((new DateTime($row['expired']))->diff($justNow)->invert != 1) { // Judge timeout.
                return null;
            }
            $hashValue = $prefix === "" ? $row['hash'] : substr($row['hash'], strlen($prefix));
            $this->logger->setDebugMessage("[authSupportRetrieveChallenge] returns hash value: {$hashValue}");
            return $hashValue;
        }
        return null;
    }

    /**
     * @return bool
     *
     * Using 'issuedhash'
     */
    public
    function authSupportRemoveOutdatedChallenges(): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }

        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTStr = $this->pdoDB->link->quote(IMUtil::currentDTString());
        $longBeforeDTStr = $this->pdoDB->link->quote(IMUtil::currentDTString(3600 * 24 * 3));
        $sql = "{$this->pdoDB->handler->sqlDELETECommand()}{$hashTable} WHERE" .
            " (clienthost IS NOT NULL AND expired < {$currentDTStr}) OR (expired < {$longBeforeDTStr})";
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportRemoveOutdatedChallenges] {$sql}");

        return true;
    }

    /**
     * @param array $keyValues
     * @return bool(true: create user, false: reuse user)|null in error
     */
    public
    function authSupportOAuthUserHandling(array $keyValues): bool
    {
        $user_id = $this->authSupportGetUserIdFromUsername($keyValues["username"]);

        $userTable = $this->dbSettings->getUserTable();
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            $this->pdoDB->errorMessageStore("PDO class can't set up a connection.");
            return false;
        }

        $currentDTFormat = $this->pdoDB->link->quote(IMUtil::currentDTString());
        $keys = array("limitdt");
        $values = array($currentDTFormat);
        $updates = array("limitdt=" . $currentDTFormat);
        foreach ($keyValues as $key => $value) {
            $keys[] = $key;
            $values[] = $this->pdoDB->link->quote($value);
            $updates[] = "$key=" . $this->pdoDB->link->quote($value);
        }
        if ($user_id > 0) {
            $returnValue = false;
            $sql = "{$this->pdoDB->handler->sqlUPDATECommand()}{$userTable} SET " . implode(",", $updates)
                . " WHERE id=" . $user_id;
        } else {
            $returnValue = true;
            $sql = $this->pdoDB->handler->sqlINSERTCommand(
                "{$userTable} (" . implode(",", $keys) . ")",
                "VALUES (" . implode(",", $values) . ")");
        }
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('[authSupportOAuthUserHandling] ' . $sql);
            return $returnValue;
        }
        $this->logger->setDebugMessage("[authSupportOAuthUserHandling] {
                $sql}");
        return $returnValue;
    }

    /**
     * @param string $username
     * @return ?string
     *
     * Using 'authuser'
     */
    public function authSupportRetrieveHashedPassword(string $username): ?string
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);
        if(is_null($signedUser)) {
            $signedUser = "";
        }

        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable)) {
            return null;
        }

        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}hashedpasswd FROM {$userTable} WHERE username = "
            . $this->pdoDB->link->quote($signedUser);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportRetrieveHashedPassword] {$sql}");
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $limitSeconds = $this->dbSettings->getSAMLExpiringSeconds();
            if (isset($row['limitdt']) && IMUtil::secondsFromNow($row['limitdt']) < $limitSeconds) {
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
     * @param string $username
     * @param string $hashedpassword
     * @param bool $isSAML
     * @param ?string $ldapPassword
     * @param ?array $attrs
     * @return bool
     *
     * Using 'authuser'
     */
    public
    function authSupportCreateUser(string  $username, string $hashedpassword, bool $isSAML = false,
                                   ?string $ldapPassword = null, ?array $attrs = null): bool
    {
        $this->logger->setDebugMessage("[authSupportCreateUser] username ={$username}, isSAML ={$isSAML}", 2);

        $userTable = $this->dbSettings->getUserTable();
        if ($isSAML !== true) {
            if ($this->authSupportRetrieveHashedPassword($username)) {
                $this->logger->setErrorMessage('User Already exist: ' . $username);
                return false;
            }
            if (!$this->pdoDB->setupConnection()) { //Establish the connection
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
                return $this->pdoDB->link->quote($e);
            }, $valueArray));
            $sql = $this->pdoDB->handler->sqlINSERTCommand($tableRef, "VALUES({$setArray})");
            $result = $this->pdoDB->link->query($sql);
            if ($result === false) {
                $this->pdoDB->errorMessageStore('Insert:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
        } else {
            $user_id = -1;
            $timeUp = false;
            $hpw = null;
            if (!$this->pdoDB->setupConnection()) { //Establish the connection
                return false;
            }

            $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username = "
                . $this->pdoDB->link->quote($username);
            $result = $this->pdoDB->link->query($sql);
            if ($result === false) {
                $this->pdoDB->errorMessageStore('Select:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage(
                "[authSupportCreateUser - SAML] {$sql}, SAML expiring ={$this->dbSettings->getSAMLExpiringSeconds()}");
            foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                if (isset($row['limitdt'])) {
                    if (IMUtil::secondsFromNow($row['limitdt']) > $this->dbSettings->getSAMLExpiringSeconds()) {
                        $this->logger->setDebugMessage("[authSupportCreateUser - SAML] Over Limit Datetime.");
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
                $setClause = "limitdt = " . $this->pdoDB->link->quote($currentDTFormat);
                //if ($timeUp) {
                $hexSalt = substr($hpw, -8, 8);
                $prevPwHash = sha1($ldapPassword . hex2bin($hexSalt)) . $hexSalt;
                if ($prevPwHash != $hpw) {
                    $setClause .= ",hashedpasswd = " . $this->pdoDB->link->quote($hashedpassword);
                }
                //}
                $sql = "{$this->pdoDB->handler->sqlUPDATECommand()}{$userTable} SET {$setClause} WHERE id = {$user_id}";
                $result = $this->pdoDB->link->query($sql);
                $this->logger->setDebugMessage("[authSupportCreateUser - SAML] {$sql}");
                if ($result === false) {
                    $this->pdoDB->errorMessageStore('Update:' . $sql);
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
                    return $this->pdoDB->link->quote($e);
                }, $valueArray));
                $sql = $this->pdoDB->handler->sqlINSERTCommand($tableRef, "VALUES({$setArray})");
                $result = $this->pdoDB->link->query($sql);
                if ($result === false) {
                    $this->pdoDB->errorMessageStore('Insert:' . $sql);
                    return false;
                }
                $this->logger->setDebugMessage("[authSupportCreateUser - SAML] {$sql}");
            }
        }
        return true;
    }

    /**
     * @param string $username
     * @param string $hashednewpassword
     * @return bool
     *
     * Using 'authuser'
     */
    public
    function authSupportChangePassword(string $username, string $hashednewpassword): bool
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);
        if(is_null($signedUser)) {
            $signedUser = "";
        }

        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable)) {
            return false;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->pdoDB->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd = "
            . $this->pdoDB->link->quote($hashednewpassword)
            . " WHERE username = " . $this->pdoDB->link->quote($signedUser);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Update:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportChangePassword] {$sql}");
        return true;
    }

    /**
     * @param string $username
     * @return string
     */
    public
    function authTableGetUserIdFromUsername(string $username): string
    {
        return $this->privateGetUserIdFromUsername($username, false);
    }

    /**
     * @param string|null $username
     * @return string
     */
    public
    function authSupportGetUserIdFromUsername(?string $username): ?string
    {
        return $this->privateGetUserIdFromUsername($username, true);
    }

    /**
     * @param string|null $username
     * @param bool $isCheckLimit
     * @return string|null
     */
    private
    function privateGetUserIdFromUsername(?string $username, bool $isCheckLimit): ?string
    {
        $this->logger->setDebugMessage("[privateGetUserIdFromUsername]username ={$username}", 2);

        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || is_null($username) || !$this->pdoDB->setupConnection()) {
            return null;
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username = "
            . $this->pdoDB->link->quote($username);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[privateGetUserIdFromUsername] {$sql}");
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if (!$isCheckLimit || is_null($row['limitdt'])) {
                return $row['id'];
            }
            if (time() - strtotime($row['limitdt']) <= $this->dbSettings->getSAMLExpiringSeconds()) {
                return $row['id'];
            }
        }
        return null;
    }

    /**
     * @param string $groupid
     * @return ?string
     *
     * Using 'authgroup'
     */
    public
    function authSupportGetGroupNameFromGroupId(string $groupid): ?string
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if (is_null($groupTable) || !$this->pdoDB->setupConnection()) {
            return null;
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}groupname FROM {$groupTable} WHERE id = "
            . $this->pdoDB->link->quote($groupid);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportGetGroupNameFromGroupId] {$sql}");
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            return $row['groupname'];
        }
        return null;
    }

    /**
     * @param ?string $user
     * @return array
     *
     * Using 'authcor'
     */
    public
    function authSupportGetGroupsOfUser(?string $user): array
    {
        $oAuth = new OAuthAuth();
        if ($oAuth->isActive) {
            return $this->privateGetGroupsOfUser($user, true);
        } else {
            return $this->privateGetGroupsOfUser($user, false);
        }
    }

    /**
     * @param string $user
     * @return array|null
     */
    public
    function authTableGetGroupsOfUser(string $user): ?array
    {
        return $this->privateGetGroupsOfUser($user, false);
    }

    /**
     * @param string|null $user
     * @param bool $isCheckLimit
     * @return array|null
     */
    private
    function privateGetGroupsOfUser(?string $user, bool $isCheckLimit): ?array
    {
        $corrTable = $this->dbSettings->getCorrTable();
        if (is_null($corrTable)) {
            return array();
        }

        $userid = $this->privateGetUserIdFromUsername($user, $isCheckLimit);
        if (is_null($userid) && $this->dbSettings->getEmailAsAccount()) {
            $userid = $this->authSupportGetUserIdFromEmail($user);
        }

        $this->logger->setDebugMessage("[authSupportGetGroupsOfUser]user ={$user}, userid ={$userid}");

        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }
        $this->firstLevel = true;
        $this->belongGroups = array();
        $this->resolveGroup($userid);

        $candidateGroups = array();
        foreach ($this->belongGroups as $groupid) {
            if ($groupid) {
                $candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
            }
        }
        if (count($candidateGroups) === 0) {
            $defaultGroup = Params::getParameterValue("defaultGroupName", false);
            if ($defaultGroup) {
                $candidateGroups = [$defaultGroup];
            }
        }
        return $candidateGroups;
    }


    /**
     * @var array
     */
    private
    array $belongGroups;
    /**
     * @var bool
     */
    private
    bool $firstLevel;

    /**
     * @param string|null $groupid
     * @return void
     *
     * Using 'authcor'
     */
    private function resolveGroup(?string $groupid): void
    {
        if (!$groupid || strlen($groupid) < 1) {
            return;
        }
        $corrTable = $this->dbSettings->getCorrTable();

        if ($this->firstLevel) {
            $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE user_id = "
                . $this->pdoDB->link->quote($groupid) . " ORDER BY id";
            $this->firstLevel = false;
        } else {
            $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE group_id = "
                . $this->pdoDB->link->quote($groupid) . " ORDER BY id";
            //    $this->belongGroups[] = $groupid;
        }
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->logger->setDebugMessage('Select:' . $sql);
            return;
        }
        $this->logger->setDebugMessage("[resolveGroup] {$sql}");
        if ($result->columnCount() === 0) {
            return;
        }
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if (!in_array($row['dest_group_id'], $this->belongGroups)) {
                $this->belongGroups[] = $row['dest_group_id'];
                $this->resolveGroup($row['dest_group_id']);
            }
        }
    }

    /**
     * @param string $tableName
     * @param string $targeting
     * @param string $userField
     * @param string $user
     * @param string $keyField
     * @param string $keyValue
     * @return array|null Using any table.
     *
     * Using any table.
     * @throws Exception
     */
    public function authSupportCheckMediaPrivilege(
        string $tableName, string $targeting, string $userField, string $user, string $keyField, string $keyValue): ?array
    {
        if (strlen($user) === 0) {
            return null;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }

        $user = $this->authSupportUnifyUsernameAndEmail($user);
        switch ($targeting) {
            case  'field_user':
                $queryClause = "{$userField}=" . $this->pdoDB->link->quote($user)
                    . " AND {$keyField}=" . $this->pdoDB->link->quote($keyValue);
                break;
            case  'field_group':
                $belongGroups = $this->authSupportGetGroupsOfUser($user);
                $groupCriteria = array();
                foreach ($belongGroups as $oneGroup) {
                    $groupCriteria[] = "{$userField}=" . $this->pdoDB->link->quote($oneGroup);
                }
                if (count($groupCriteria) === 0) {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = "(" . implode(' OR ', $groupCriteria) . ")"
                        . " AND {$keyField}=" . $this->pdoDB->link->quote($keyValue);
                }
                break;
            default: // 'context_auth' or 'no_auth'
                throw new Exception('Unexpected authSupportCheckMediaPrivilege method usage.');
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}* FROM {$tableName} WHERE {$queryClause}";
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore("[authSupportCheckMediaPrivilege] {$sql}");
            return null;
        }
        $this->logger->setDebugMessage("[authSupportCheckMediaPrivilege] {$sql}");
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            return $row;
        }
        return null;
    }

    /**
     * @var array
     */
    private array $userCache = []; // Cache for authSupportGetUserIdFromEmail method.

    /**
     * @param string $email
     * @return ?string
     *
     * Using 'authuser'
     */
    public function authSupportGetUserIdFromEmail(string $email): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || !$email || !$this->pdoDB->setupConnection()) {
            return null;
        }
        if (isset($this->userCache[$email])) {
            return $this->userCache[$email];
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}id FROM {$userTable} WHERE email = "
            . $this->pdoDB->link->quote($email);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportGetUserIdFromEmail] {$sql}");
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $this->userCache[$email] = $row['id'];
            return $row['id'];
        }
        return null;
    }

    /**
     * @param string $userid
     * @return ?string
     *
     * Using 'authuser'
     */
    public function authSupportGetUsernameFromUserId(string $userid): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || !$userid || !$this->pdoDB->setupConnection()) {
            return null;
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}username FROM {$userTable} WHERE id = "
            . $this->pdoDB->link->quote($userid);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportGetUsernameFromUserId] {$sql}");
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            return $row['username'];
        }
        return null;
    }

    /**
     * @param string|null $username
     * @return ?string
     *
     * Using 'authuser'
     */
    public function authSupportUnifyUsernameAndEmail(?string $username): ?string
    {
        if (!$username) {
            return null;
        }
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || !$this->pdoDB->setupConnection()) {
            return null;
        }

        if (!$this->dbSettings->getEmailAsAccount()) { // In case of $this->dbSettings->getEmailAsAccount() is false
            $sql = $this->pdoDB->handler->sqlSELECTCommand() . " username FROM {$userTable} WHERE username = "
                . $this->pdoDB->link->quote($username);
            $result = $this->pdoDB->link->query($sql);
            if ($result === false) {
                $this->pdoDB->errorMessageStore('Select:' . $sql);
                return null;
            }
            $this->logger->setDebugMessage("[authSupportUnifyUsernameAndEmail] {$sql}");
            foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                return $row['username'];
            }
            return null;
        }
        // In case of $this->dbSettings->getEmailAsAccount() is true
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}username,email FROM {$userTable} WHERE username = " .
            $this->pdoDB->link->quote($username) . " or email = " . $this->pdoDB->link->quote($username);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportUnifyUsernameAndEmail] {$sql}");
        $usernameCandidate = '';
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if ($row['username'] === $username) {
                $usernameCandidate = $username;
            }
            if ($row['email'] === $username) {
                $usernameCandidate = $row['username'];
            }
        }
        return $usernameCandidate;
    }

    /**
     * @param string|null $username
     * @return string|null
     */
    public function authSupportEmailFromUnifiedUsername(?string $username): ?string
    {
        if (!$username) {
            return null;
        }
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || !$this->pdoDB->setupConnection()) {
            return null;
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}email FROM {$userTable} WHERE username = " .
            $this->pdoDB->link->quote($username);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportEmailFromUnifiedUsername] {$sql}");
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            return $row['email'];
        }
        return null;
    }

    /**
     * @param string $userid
     * @param string $clienthost
     * @param string $hash
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportStoreIssuedHashForResetPassword(string $userid, string $clienthost, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $tableRef = "{$hashTable} (hash,expired,clienthost,user_id)";
        $setArray = implode(',', array_map(function ($e) {
            return $this->pdoDB->link->quote($e);
        }, [$hash, $currentDTFormat, $clienthost, $userid]));
        $sql = $this->pdoDB->handler->sqlINSERTCommand($tableRef, "VALUES({$setArray})");
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportStoreIssuedHashForResetPassword] {$sql}");
        return true;
    }


    /**
     * @param string $userid
     * @param string $randdata
     * @param string $hash
     * @return bool
     *
     * Using 'issuedhash'
     */
    public function authSupportCheckIssuedHashForResetPassword(string $userid, string $randdata, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}hash,expired FROM {$hashTable} WHERE"
            . " user_id = " . $this->pdoDB->link->quote($userid)
            . " and clienthost = " . $this->pdoDB->link->quote($randdata);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportCheckIssuedHashForResetPassword] {$sql}");
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $hashValue = $row['hash'];
            if (IMUtil::secondsFromNow($row['expired']) > Params::getParameterValue('limitPwChangeSecond', 3600)) {
                return false;
            }
            if ($hash === $hashValue) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $userid
     * @param string $hash
     * @return bool
     */
    public function authSupportUserEnrollmentStart(string $userid, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $tableRef = "{$hashTable} (hash,expired,user_id)";
        $setArray = implode(',', array_map(function ($e) {
            return $this->pdoDB->link->quote($e);
        }, [$hash, $currentDTFormat, $userid]));
        $sql = $this->pdoDB->handler->sqlINSERTCommand($tableRef, "VALUES({$setArray})");
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentStart] {$sql}");
        return true;
    }

    /**
     * @param string $hash
     * @return string|null
     */
    public function authSupportUserEnrollmentEnrollingUser(string $hash): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return null;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }

        $currentDTFormat = IMUtil::currentDTString(Params::getParameterValue('limitEnrollSecond', 3600));
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}user_id FROM {$hashTable} WHERE hash = "
            . $this->pdoDB->link->quote($hash) .
            " and clienthost IS NULL and expired > " . $this->pdoDB->link->quote($currentDTFormat);
        $resultHash = $this->pdoDB->link->query($sql);
        if ($resultHash === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentEnrollingUser] {$sql}");
        foreach ($resultHash->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $userID = $row['user_id'];
            if ($userID < 1) {
                return null;
            }
            return $userID;
        }
        return null;
    }

    /**
     * @param string $userID
     * @param string|null $password
     * @param string|null $rawPWField
     * @param string|null $rawPW
     * @return string|null
     */
    public function authSupportUserEnrollmentActivateUser(
        string $userID, ?string $password, ?string $rawPWField, ?string $rawPW): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable)) {
            return null;
        }
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return null;
        }

        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }
        if ($rawPWField) {
            $sql = "{$this->pdoDB->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd = "
                . $this->pdoDB->link->quote($password) . "," . $rawPWField . " = " . $this->pdoDB->link->quote($rawPW)
                . " WHERE id = " . $this->pdoDB->link->quote($userID);
        } else {
            $sql = "{$this->pdoDB->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd = "
                . $this->pdoDB->link->quote($password) . " WHERE id = " . $this->pdoDB->link->quote($userID);
        }
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Update:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentActivateUser] {$sql}");

        $sql = "{$this->pdoDB->handler->sqlDELETECommand()}{$hashTable} "
            . " WHERE user_id = " . $this->pdoDB->link->quote($userID);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Delete:' . $sql);
            return null;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentActivateUser] {$sql}");

        return $userID;
    }

    /**
     * @param string $userID
     * @return bool
     */
    public function authSupportIsWithinSAMLLimit(string $userID): bool
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable)) {
            return false;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE id = " . $userID;
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportIsWithinSAMLLimit] {$sql}");
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $this->logger->setDebugMessage("[authSupportIsWithinSAMLLimit] " . var_export($row, true));
            $this->logger->setDebugMessage("[authSupportIsWithinSAMLLimit] "
                . "ldapLimit ={$this->dbSettings->getSAMLExpiringSeconds()}");
            if (isset($row['limitdt'])) {
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

    /**
     * @return bool
     */
    public function authSupportCanMigrateSHA256Hash(): bool // authuser, issuedhash
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable)) {
            return false;
        }
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }
        $messages = $this->pdoDB->handler->authSupportCanMigrateSHA256Hash($userTable, $hashTable);
        if (count($messages) > 0) {
            $this->logger->setErrorMessages($messages);
            return false;
        }
        return true;
    }

    /**
     * @param null|string $userID
     * @return array 3 elements array as like: [UserID, username, hashedpasswd].
     */
    public function authSupportUnifyUsernameAndEmailAndGetInfo(?string $userID): array
    {
        if (!$userID) {
            return [null, null, null];
        }
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || !$this->pdoDB->setupConnection()) {
            return [null, null, null];
        }

        if (!$this->dbSettings->getEmailAsAccount()) { // In the case of $this->dbSettings->getEmailAsAccount() is false
            $sql = $this->pdoDB->handler->sqlSELECTCommand() . " id,username,hashedpasswd FROM {$userTable} WHERE username = "
                . $this->pdoDB->link->quote($userID);
            $result = $this->pdoDB->link->query($sql);
            if ($result === false) {
                $this->pdoDB->errorMessageStore('Select:' . $sql);
                return [null, null, null];
            }
            $this->logger->setDebugMessage("[authSupportUnifyUsernameAndEmail] {$sql}");
            foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                return [$row['id'], $row['username'], $row['hashedpasswd']];
            }
            return [null, null, null];
        }
        // In the case of $this->dbSettings->getEmailAsAccount() is true
        $sql = "{$this->pdoDB->handler->sqlSELECTCommand()}id,username,email,hashedpasswd FROM {$userTable} WHERE username = " .
            $this->pdoDB->link->quote($userID) . " or email = " . $this->pdoDB->link->quote($userID);
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore('Select:' . $sql);
            return [null, null, null];
        }
        $this->logger->setDebugMessage("[authSupportUnifyUsernameAndEmail] {$sql}");
        $usernameCandidate = '';
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            if ($row['username'] === $userID) {
                $usernameCandidate = $userID;
            }
            if ($row['email'] === $userID) {
                $usernameCandidate = $row['username'];
            }
            return [$row['id'], $usernameCandidate, $row['hashedpasswd']];
        }
        return [null, null, null];
    }
}
