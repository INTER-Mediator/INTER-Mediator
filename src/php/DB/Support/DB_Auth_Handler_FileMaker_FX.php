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

use Datetime;
use Exception;
use INTERMediator\DB\FileMaker_FX;
use INTERMediator\IMUtil;

/**
 *
 */
class DB_Auth_Handler_FileMaker_FX extends DB_Auth_Common implements Auth_Interface_DB
{
    /**
     * @var FileMaker_FX
     */
    protected FileMaker_FX $fmdb;

    /**
     * @param $parent
     */
    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->fmdb = $parent;
    }

    /**
     * @param string $uid
     * @param string $challenge
     * @param string $clientId
     * @return void
     */
    public function authSupportStoreChallenge(string $uid, string $challenge, string $clientId): void
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->fmdb->setupFXforAuth($hashTable, 1);
        $this->fmdb->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fmdb->fxAuth->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->fmdb->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $this->fmdb->setupFXforAuth($hashTable, 1);
            $this->fmdb->fxAuth->SetRecordID($recId);
            $this->fmdb->fxAuth->AddDBParam('hash', $challenge);
            $this->fmdb->fxAuth->AddDBParam('expired', $currentDTFormat);
            $this->fmdb->fxAuth->AddDBParam('clienthost', $clientId);
            $this->fmdb->fxAuth->AddDBParam('user_id', $uid);
            $result = $this->fmdb->fxAuth->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return;
            }
            $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
            return;
        }
        $this->fmdb->setupFXforAuth($hashTable, 1);
        $this->fmdb->fxAuth->AddDBParam('hash', $challenge);
        $this->fmdb->fxAuth->AddDBParam('expired', $currentDTFormat);
        $this->fmdb->fxAuth->AddDBParam('clienthost', $clientId);
        $this->fmdb->fxAuth->AddDBParam('user_id', $uid);
        $result = $this->fmdb->fxAuth->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
    }

    /**
     * @param string $uid
     * @return string|null
     * @throws Exception
     */
    public function authSupportCheckMediaToken(string $uid): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (!$hashTable || $uid < 1) {
            return null;
        }
        $this->fmdb->setupFXforAuth($hashTable, 1);
        $this->fmdb->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fmdb->fxAuth->AddDBParam('clienthost', '_im_media', 'eq');
        $result = $this->fmdb->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->dbClass->getDebugInfo());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            $expiredDT = new DateTime($row['expired'][0]);
            $hashValue = $row['hash'][0];
            $currentDT = new DateTime();
            $seconds = $currentDT->format("U") - $expiredDT->format("U");
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return null;
            }
            return $hashValue;
        }
        return null;
    }

    /**
     * @param string $uid
     * @param string $clientId
     * @param bool $isDelete
     * @return string|null
     */
    public function authSupportRetrieveChallenge(string $uid, string $clientId, bool $isDelete = true): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (!$hashTable || $uid < 1) {
            return null;
        }
        $this->fmdb->setupFXforAuth($hashTable, 1);
        $this->fmdb->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fmdb->fxAuth->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->fmdb->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $hashValue = $row['hash'][0];
            if ($isDelete) {
                $this->fmdb->setupFXforAuth($hashTable, 1);
                $this->fmdb->fxAuth->SetRecordID($recId);
                $result = $this->fmdb->fxAuth->DoFxAction("delete", TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                    return null;
                }
            }
            return $hashValue;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function authSupportRemoveOutdatedChallenges(): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }

        $currentDT = new DateTime();
        $timeValue = $currentDT->format("U");

        $this->fmdb->setupFXforAuth($hashTable, 100000000);
        $this->fmdb->fxAuth->AddDBParam('expired', date('m/d/Y H:i:s', $timeValue - $this->dbSettings->getExpiringSeconds()), 'lt');
        $this->fmdb->fxAuth->AddDBParam('clienthost', '', 'neq');
        $result = $this->fmdb->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->fmdb->setupFXforAuth($hashTable, 1);
            $this->fmdb->fxAuth->SetRecordID($recId);
            $result = $this->fmdb->fxAuth->DoFxAction("delete", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $username
     * @return string|null
     */
    public function authSupportRetrieveHashedPassword(string $username): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return null;
        }

        $this->fmdb->setupFXforDB($userTable, 1);
        $this->fmdb->fx->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
        $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if ((!is_array($result) || $result['foundCount'] < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->fmdb->setupFXforDB($userTable, 1);
            $this->fmdb->fx->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        }
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['hashedpasswd'][0];
        }
        return null;
    }

    /**
     * @param string $username
     * @param string $hashedpassword
     * @param bool $isSAML
     * @param string|null $ldapPassword
     * @param array|null $attrs
     * @return bool
     */
    public function authSupportCreateUser(string  $username, string $hashedpassword, bool $isSAML = false,
                                          ?string $ldapPassword = null, ?array $attrs = null): bool
    {
        if ($this->authSupportRetrieveHashedPassword($username)) {
            $this->logger->setErrorMessage('User Already exist: ' . $username);
            return false;
        }
        $userTable = $this->dbSettings->getUserTable();
        $this->fmdb->setupFXforDB($userTable, 1);
        $this->fmdb->fx->AddDBParam('username', $username);
        $this->fmdb->fx->AddDBParam('hashedpasswd', $hashedpassword);
        $result = $this->fmdb->fx->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        return true;
    }

    /**
     * @param string $username
     * @param string $hashednewpassword
     * @return bool
     */
    public function authSupportChangePassword(string $username, string $hashednewpassword): bool
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->fmdb->setupFXforDB($userTable, 1);
        $username = $this->authSupportUnifyUsernameAndEmail($username);
        $this->fmdb->fx->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
        $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if ((!is_array($result) || count($result['data']) < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->fmdb->setupFXforDB($userTable, 1);
            $this->fmdb->fx->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->fmdb->setupFXforDB($userTable, 1);
            $this->fmdb->fx->SetRecordID($recId);
            $this->fmdb->fx->AddDBParam("hashedpasswd", $hashednewpassword);
            $result = $this->fmdb->fx->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
            break;
        }
        return true;
    }

    /**
     * @param string $username
     * @return string|null
     */
    public function authSupportGetUserIdFromUsername(string $username): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (!$userTable || !$username) {
            return null;
        }

        $username = $this->authSupportUnifyUsernameAndEmail($username);

        $this->fmdb->setupFXforDB_Alt($userTable, 1);
        $this->fmdb->fxAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
        $result = $this->fmdb->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['id'][0];
        }
        return null;
    }

    /**
     * @param string $userid
     * @return string|null
     */
    public function authSupportGetUsernameFromUserId(string $userid): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (!$userTable || !$userid) {
            return null;
        }

        $this->fmdb->setupFXforDB($userTable, 1);
        $this->fmdb->fx->AddDBParam('id', $userid, "eq");
        $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['username'][0];
        }
        return null;
    }

    /**
     * @param string $email
     * @return string|null
     */
    public function authSupportGetUserIdFromEmail(string $email): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (!$userTable || !$email) {
            return null;
        }

        $this->fmdb->setupFXforDB_Alt($userTable, 1);
        $this->fmdb->fxAlt->AddDBParam('email', str_replace("@", "\\@", $email), "eq");
        $result = $this->fmdb->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['id'][0];
        }
        return null;
    }

    /**
     * @param string|null $username
     * @return string|null
     */
    public function authSupportUnifyUsernameAndEmail(?string $username): ?string
    {
        if (!$this->dbSettings->getEmailAsAccount() || $this->dbSettings->isDBNative()) {
            return $username;
        }
        $userTable = $this->dbSettings->getUserTable();
        if (!$userTable || !$username) {
            return null;
        }

        $this->fmdb->setupFXforDB_Alt($userTable, 55555);
        $this->fmdb->fxAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
        $this->fmdb->fxAlt->AddDBParam('email', str_replace("@", "\\@", $username), "eq");
        $this->fmdb->fxAlt->SetLogicalOR();
        $result = $this->fmdb->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        $usernameCandidate = '';
        foreach ($result['data'] as $row) {
            if ($row['username'][0] == $username) {
                $usernameCandidate = $username;
            }
            if ($row['email'][0] == $username) {
                $usernameCandidate = $row['username'][0];
            }
        }
        return $usernameCandidate;
    }

    /**
     * @param string $groupid
     * @return string|null
     */
    public function authSupportGetGroupNameFromGroupId(string $groupid): ?string
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if (!$groupTable || !$groupid) {
            return null;
        }

        $this->fmdb->setupFXforDB_Alt($groupTable, 1);
        $this->fmdb->fxAlt->AddDBParam('id', $groupid);
        $result = $this->fmdb->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['groupname'][0];
        }
        return null;
    }

    /**
     * @param string|null $user
     * @return array
     */
    public function authSupportGetGroupsOfUser(?string $user): array
    {
        $corrTable = $this->dbSettings->getCorrTable();
        if ($corrTable == null) {
            return array();
        }

        $userid = $this->authSupportGetUserIdFromUsername($user);
        if (is_null($userid)) {
            $userid = $this->authSupportGetUserIdFromEmail($user);
        }
        $this->firstLevel = true;
        $this->belongGroups = array();
        $this->resolveGroup($userid);
        $candidateGroups = array();
        foreach ($this->belongGroups as $groupid) {
            $candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
        }
        return $candidateGroups;
    }

    /**
     * @var array
     */
    private array $belongGroups;
    /**
     * @var bool
     */
    private bool $firstLevel;

    /**
     * @param string $groupid
     * @return void
     */
    private function resolveGroup(string $groupid): void
    {
        $this->fmdb->setupFXforDB_Alt($this->dbSettings->getCorrTable(), 1);
        if ($this->firstLevel) {
            $this->fmdb->fxAlt->AddDBParam('user_id', $groupid);
            $this->firstLevel = false;
        } else {
            $this->fmdb->fxAlt->AddDBParam('group_id', $groupid);
            $this->belongGroups[] = $groupid;
        }
        $result = $this->fmdb->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            if (!in_array($row['dest_group_id'][0], $this->belongGroups)) {
                $this->resolveGroup($row['dest_group_id'][0]);
            }
        }
    }

    /**
     * @param string $userid
     * @param string $clienthost
     * @param string $hash
     * @return bool
     */
    public function authSupportStoreIssuedHashForResetPassword(string $userid, string $clienthost, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->fmdb->setupFXforAuth($hashTable, 1);
        $this->fmdb->fxAuth->AddDBParam('hash', $hash);
        $this->fmdb->fxAuth->AddDBParam('expired', $currentDTFormat);
        $this->fmdb->fxAuth->AddDBParam('clienthost', $clienthost);
        $this->fmdb->fxAuth->AddDBParam('user_id', $userid);
        $result = $this->fmdb->fxAuth->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        return true;
    }

    /**
     * @param string $userid
     * @param string $randdata
     * @param string $hash
     * @return bool
     */
    public function authSupportCheckIssuedHashForResetPassword(string $userid, string $randdata, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->fmdb->setupFXforAuth($hashTable, 1);
        $this->fmdb->fxAuth->AddDBParam('user_id', $userid, 'eq');
        $this->fmdb->fxAuth->AddDBParam('clienthost', $randdata, 'eq');
        $result = $this->fmdb->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            $hashValue = $row['hash'][0];
            $expiredDT = $row['expired'][0];

            $expired = strptime($expiredDT, "%m/%d/%Y %H:%M:%S");
            $expiredValue = mktime($expired['tm_hour'], $expired['tm_min'], $expired['tm_sec'],
                $expired['tm_mon'] + 1, $expired['tm_mday'], $expired['tm_year'] + 1900);
            $currentDT = new DateTime();
            $timeValue = $currentDT->format("U");
            if ($timeValue > $expiredValue + 3600) {
                return false;
            }
            if ($hash == $hashValue) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param string $tableName
     * @param string $targeting
     * @param string $userField
     * @param string $user
     * @param string $keyField
     * @param string $keyValue
     * @return array|null
     * @throws Exception
     */
    public function authSupportCheckMediaPrivilege(string $tableName, string $targeting, string $userField,
                                                   string $user, string $keyField, string $keyValue): ?array
    {
        $user = $this->authSupportUnifyUsernameAndEmail($user);

        switch ($targeting) {
            case  'field_user':
                break;
            case  'field_group':
                throw new Exception('The authSupportCheckMediaPrivilege method has to modify for field-group targeting.');
            default: // 'context_auth' or 'no_auth'
                throw new Exception('Unexpected authSupportCheckMediaPrivilege method usage.');
        }

        $this->fmdb->setupFXforAuth($tableName, 1);
        $this->fmdb->fxAuth->AddDBParam($userField, $user, 'eq');
        $this->fmdb->fxAuth->AddDBParam($keyField, $keyValue, 'eq');
        $result = $this->fmdb->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return null;
        }
        $array = array();
        foreach ($result['data'] as $key => $row) {
            $keyExpode = explode(".", $key);
            $record = array("-recid" => $keyExpode[0], "-modid" => $keyExpode[1]);
            foreach ($row as $field => $value) {
                $record[$field] = implode("\n", $value);
            }
            $array[] = $record;
        }
        return $array;
    }

    /**
     * @param string $userid
     * @param string $hash
     * @return bool
     */
    public function authSupportUserEnrollmentStart(string $userid, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->fmdb->setupFXforAuth($hashTable, 1);
        $this->fmdb->fxAuth->AddDBParam("hash", $hash);
        $this->fmdb->fxAuth->AddDBParam("expired", IMUtil::currentDTStringFMS());
        $this->fmdb->fxAuth->AddDBParam("user_id", $userid);
        $result = $this->fmdb->fxAuth->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        return true;
    }

    /**
     * @param string $hash
     * @return string|null
     */
    public function authSupportUserEnrollmentEnrollingUser(string $hash): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if (!$hashTable || !$userTable || !$hash) {
            return null;
        }
        $this->fmdb->setupFXforAuth($hashTable, 1);
        $this->fmdb->fxAuth->AddDBParam("hash", $hash, "eq");
        $this->fmdb->fxAuth->AddDBParam("clienthost", "", "eq");
        $this->fmdb->fxAuth->AddDBParam("expired", IMUtil::currentDTStringFMS(3600), "gt");
        $result = $this->fmdb->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as  $row) {
            return $row['user_id'][0];
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
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if (!$hashTable || !$userTable) {
            return null;
        }
        $this->fmdb->setupFXforDB_Alt($userTable, 1);
        $this->fmdb->fxAlt->AddDBParam('id', $userID);
        $resultUser = $this->fmdb->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($resultUser)) {
            $this->logger->setDebugMessage(get_class($resultUser) . ': ' . $resultUser->toString());
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($resultUser['URL']));
        foreach ($resultUser['data'] as $ukey => $urow) {
            $recId = substr($ukey, 0, strpos($ukey, '.'));
            $this->fmdb->setupFXforDB_Alt($userTable, 1);
            $this->fmdb->fxAlt->SetRecordID($recId);
            $this->fmdb->fxAlt->AddDBParam('hashedpasswd', $password);
            if ($rawPWField !== false) {
                $this->fmdb->fxAlt->AddDBParam($rawPWField, $rawPW);
            }
            $result = $this->fmdb->fxAlt->DoFxAction('update', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
                return null;
            }
            $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
            return $userID;
        }
        return null;
    }

    /**
     * @param string $userID
     * @return bool
     */
    public function authSupportIsWithinSAMLLimit(string $userID): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function authSupportCanMigrateSHA256Hash(): bool  // authuser, issuedhash
    {
        return true;
    }
}
