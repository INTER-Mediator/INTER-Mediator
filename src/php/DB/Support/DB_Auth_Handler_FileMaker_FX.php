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
use INTERMediator\IMUtil;

class DB_Auth_Handler_FileMaker_FX extends DB_Auth_Common implements Auth_Interface_DB
{
    public function authSupportStoreChallenge(string $uid, string $challenge, string $clientId): void
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->dbClass->setupFXforAuth($hashTable, 1);
        $this->dbClass->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->dbClass->fxAuth->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->dbClass->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $this->dbClass->setupFXforAuth($hashTable, 1);
            $this->dbClass->fxAuth->SetRecordID($recId);
            $this->dbClass->fxAuth->AddDBParam('hash', $challenge);
            $this->dbClass->fxAuth->AddDBParam('expired', $currentDTFormat);
            $this->dbClass->fxAuth->AddDBParam('clienthost', $clientId);
            $this->dbClass->fxAuth->AddDBParam('user_id', $uid);
            $result = $this->dbClass->fxAuth->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return;
            }
            $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
            return;
        }
        $this->dbClass->setupFXforAuth($hashTable, 1);
        $this->dbClass->fxAuth->AddDBParam('hash', $challenge);
        $this->dbClass->fxAuth->AddDBParam('expired', $currentDTFormat);
        $this->dbClass->fxAuth->AddDBParam('clienthost', $clientId);
        $this->dbClass->fxAuth->AddDBParam('user_id', $uid);
        $result = $this->dbClass->fxAuth->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        return;
    }

    public function authSupportCheckMediaToken(string $uid): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return null;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->dbClass->setupFXforAuth($hashTable, 1);
        $this->dbClass->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->dbClass->fxAuth->AddDBParam('clienthost', '_im_media', 'eq');
        $result = $this->dbClass->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->dbClass->getDebugInfo());
            return null;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $expiredDT = new DateTime($row['expired'][0]);
            $hashValue = $row['hash'][0];
            $currentDT = new DateTime();
            $seconds = $currentDT->format("U") - $expiredDT->format("U");
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return false;
            }
            return $hashValue;
        }
        return null;
    }

    public function authSupportRetrieveChallenge(string $uid, string $clientId, bool $isDelete = true): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return null;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->dbClass->setupFXforAuth($hashTable, 1);
        $this->dbClass->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->dbClass->fxAuth->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->dbClass->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return null;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $hashValue = $row['hash'][0];
            if ($isDelete) {
                $this->dbClass->setupFXforAuth($hashTable, 1);
                $this->dbClass->fxAuth->SetRecordID($recId);
                $result = $this->dbClass->fxAuth->DoFxAction("delete", TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                    return null;
                }
            }
            return $hashValue;
        }
        return null;
    }

    public function authSupportRemoveOutdatedChallenges(): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }

        $currentDT = new DateTime();
        $timeValue = $currentDT->format("U");

        $this->dbClass->setupFXforAuth($hashTable, 100000000);
        $this->dbClass->fxAuth->AddDBParam('expired', date('m/d/Y H:i:s', $timeValue - $this->dbSettings->getExpiringSeconds()), 'lt');
        $this->dbClass->fxAuth->AddDBParam('clienthost', '', 'neq');
        $result = $this->dbClass->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->dbClass->setupFXforAuth($hashTable, 1);
            $this->dbClass->fxAuth->SetRecordID($recId);
            $result = $this->dbClass->fxAuth->DoFxAction("delete", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        return true;
    }

    public function authSupportRetrieveHashedPassword(string $username): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return null;
        }

        $this->dbClass->setupFXforDB($userTable, 1);
        $this->dbClass->fx->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
        $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if ((!is_array($result) || $result['foundCount'] < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->dbClass->setupFXforDB($userTable, 1);
            $this->dbClass->fx->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        }
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return null;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            return $row['hashedpasswd'][0];
        }
        return null;
    }

    public function authSupportCreateUser(string  $username, string $hashedpassword, bool $isSAML = false,
                                          ?string $ldapPassword = null, ?array $attrs = null): bool
    {
        if ($this->authSupportRetrieveHashedPassword($username)) {
            $this->logger->setErrorMessage('User Already exist: ' . $username);
            return false;
        }
        $userTable = $this->dbSettings->getUserTable();
        $this->dbClass->setupFXforDB($userTable, 1);
        $this->dbClass->fx->AddDBParam('username', $username);
        $this->dbClass->fx->AddDBParam('hashedpasswd', $hashedpassword);
        $result = $this->dbClass->fx->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportChangePassword(string $username, string $hashednewpassword): bool
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->dbClass->setupFXforDB($userTable, 1);
        $username = $this->authSupportUnifyUsernameAndEmail($username);
        $this->dbClass->fx->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
        $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if ((!is_array($result) || count($result['data']) < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->dbClass->setupFXforDB($userTable, 1);
            $this->dbClass->fx->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->dbClass->setupFXforDB($userTable, 1);
            $this->dbClass->fx->SetRecordID($recId);
            $this->dbClass->fx->AddDBParam("hashedpasswd", $hashednewpassword);
            $result = $this->dbClass->fx->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
            break;
        }
        return true;
    }

    public function authSupportGetUserIdFromUsername(string $username): string
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($username === 0) {
            return 0;
        }

        $username = $this->authSupportUnifyUsernameAndEmail($username);

        $this->dbClass->setupFXforDB_Alt($userTable, 1);
        $this->dbClass->fxAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
        $result = $this->dbClass->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['id'][0];
        }
        return false;
    }

    public function authSupportGetUsernameFromUserId(string $userid): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($userid === 0) {
            return 0;
        }

        $this->dbClass->setupFXforDB($userTable, 1);
        $this->dbClass->fx->AddDBParam('id', $userid, "eq");
        $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['username'][0];
        }
        return false;
    }

    public function authSupportGetUserIdFromEmail(string $email): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($email === 0) {
            return 0;
        }

        $this->dbClass->setupFXforDB_Alt($userTable, 1);
        $this->dbClass->fxAlt->AddDBParam('email', str_replace("@", "\\@", $email), "eq");
        $result = $this->dbClass->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['id'][0];
        }
        return false;
    }

    public function authSupportUnifyUsernameAndEmail(?string $username): ?string
    {
        if (!$this->dbSettings->getEmailAsAccount() || $this->dbSettings->isDBNative()) {
            return $username;
        }

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null || is_null($username) || $username === 0 || $username === '') {
            return false;
        }

        $this->dbClass->setupFXforDB_Alt($userTable, 55555);
        $this->dbClass->fxAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
        $this->dbClass->fxAlt->AddDBParam('email', str_replace("@", "\\@", $username), "eq");
        $this->dbClass->fxAlt->SetLogicalOR();
        $result = $this->dbClass->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
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

    public function authSupportGetGroupNameFromGroupId(string $groupid): ?string
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable == null) {
            return null;
        }

        $this->dbClass->setupFXforDB_Alt($groupTable, 1);
        $this->dbClass->fxAlt->AddDBParam('id', $groupid);
        $result = $this->dbClass->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            return $row['groupname'][0];
        }
        return false;
    }

    public function authSupportGetGroupsOfUser(?string $user): array
    {
        $corrTable = $this->dbSettings->getCorrTable();
        if ($corrTable == null) {
            return array();
        }

        $userid = $this->authSupportGetUserIdFromUsername($user);
        if ($userid === false) {
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

    private $belongGroups;
    private $firstLevel;

    private function resolveGroup(string $groupid): void
    {
        $this->dbClass->setupFXforDB_Alt($this->dbSettings->getCorrTable(), 1);
        if ($this->firstLevel) {
            $this->dbClass->fxAlt->AddDBParam('user_id', $groupid);
            $this->firstLevel = false;
        } else {
            $this->dbClass->fxAlt->AddDBParam('group_id', $groupid);
            $this->belongGroups[] = $groupid;
        }
        $result = $this->dbClass->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            if (!in_array($row['dest_group_id'][0], $this->belongGroups)) {
                $this->resolveGroup($row['dest_group_id'][0]);
            }
        }
    }

    public function authSupportStoreIssuedHashForResetPassword(string $userid, string $clienthost, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->dbClass->setupFXforAuth($hashTable, 1);
        $this->dbClass->fxAuth->AddDBParam('hash', $hash);
        $this->dbClass->fxAuth->AddDBParam('expired', $currentDTFormat);
        $this->dbClass->fxAuth->AddDBParam('clienthost', $clienthost);
        $this->dbClass->fxAuth->AddDBParam('user_id', $userid);
        $result = $this->dbClass->fxAuth->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportCheckIssuedHashForResetPassword(string $userid, string $randdata, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->dbClass->setupFXforAuth($hashTable, 1);
        $this->dbClass->fxAuth->AddDBParam('user_id', $userid, 'eq');
        $this->dbClass->fxAuth->AddDBParam('clienthost', $randdata, 'eq');
        $result = $this->dbClass->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
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

        $this->dbClass->setupFXforAuth($tableName, 1);
        $this->dbClass->fxAuth->AddDBParam($userField, $user, 'eq');
        $this->dbClass->fxAuth->AddDBParam($keyField, $keyValue, 'eq');
        $result = $this->dbClass->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
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

    public function authSupportUserEnrollmentStart(string $userid, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->dbClass->setupFXforAuth($hashTable, 1);
        $this->dbClass->fxAuth->AddDBParam("hash", $hash);
        $this->dbClass->fxAuth->AddDBParam("expired", IMUtil::currentDTStringFMS());
        $this->dbClass->fxAuth->AddDBParam("user_id", $userid);
        $result = $this->dbClass->fxAuth->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportUserEnrollmentEnrollingUser(string $hash): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        $this->dbClass->setupFXforAuth($hashTable, 1);
        $this->dbClass->fxAuth->AddDBParam("hash", $hash, "eq");
        $this->dbClass->fxAuth->AddDBParam("clienthost", "", "eq");
        $this->dbClass->fxAuth->AddDBParam("expired", IMUtil::currentDTStringFMS(3600), "gt");
        $result = $this->dbClass->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            return $row['user_id'][0];
        }
        return false;

    }

    public function authSupportUserEnrollmentActivateUser(
        string $userID, string $password, string $rawPWField, string $rawPW): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        $this->dbClass->setupFXforDB_Alt($userTable, 1);
        $this->dbClass->fxAlt->AddDBParam('id', $userID);
        $resultUser = $this->dbClass->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($resultUser)) {
            $this->logger->setDebugMessage(get_class($resultUser) . ': ' . $resultUser->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($resultUser['URL']));
        foreach ($resultUser['data'] as $ukey => $urow) {
            $recId = substr($ukey, 0, strpos($ukey, '.'));
            $this->dbClass->setupFXforDB_Alt($userTable, 1);
            $this->dbClass->fxAlt->SetRecordID($recId);
            $this->dbClass->fxAlt->AddDBParam('hashedpasswd', $password);
            if ($rawPWField !== false) {
                $this->dbClass->fxAlt->AddDBParam($rawPWField, $rawPW);
            }
            $result = $this->dbClass->fxAlt->DoFxAction('update', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
                return false;
            }
            $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
            return $userID;
        }
        return false;
    }

    public function authSupportIsWithinSAMLLimit(string $userID): bool
    {
        return false;
    }

    public function authSupportCanMigrateSHA256Hash(): bool  // authuser, issuedhash
    {
        return true;
    }
}
