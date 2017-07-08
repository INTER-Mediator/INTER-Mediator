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

class DB_FileMaker_FX_Auth_Handler extends DB_Auth_Common implements Auth_Interface_DB
{
    public function authSupportStoreChallenge($uid, $challenge, $clientId)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fxAuth->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $this->setupFXforAuth($hashTable, 1);
            $this->fxAuth->SetRecordID($recId);
            $this->fxAuth->AddDBParam('hash', $challenge);
            $this->fxAuth->AddDBParam('expired', $currentDTFormat);
            $this->fxAuth->AddDBParam('clienthost', $clientId);
            $this->fxAuth->AddDBParam('user_id', $uid);
            $result = $this->fxAuth->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
            $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
            return true;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('hash', $challenge);
        $this->fxAuth->AddDBParam('expired', $currentDTFormat);
        $this->fxAuth->AddDBParam('clienthost', $clientId);
        $this->fxAuth->AddDBParam('user_id', $uid);
        $result = $this->fxAuth->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportCheckMediaToken($uid)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fxAuth->AddDBParam('clienthost', '_im_media', 'eq');
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        return false;
    }

    public function authSupportRetrieveChallenge($uid, $clientId, $isDelete = true)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fxAuth->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $hashValue = $row['hash'][0];
            if ($isDelete) {
                $this->setupFXforAuth($hashTable, 1);
                $this->fxAuth->SetRecordID($recId);
                $result = $this->fxAuth->DoFxAction("delete", TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                    return false;
                }
            }
            return $hashValue;
        }
        return false;
    }

    public function authSupportRemoveOutdatedChallenges()
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }

        $currentDT = new DateTime();
        $timeValue = $currentDT->format("U");

        $this->setupFXforAuth($hashTable, 100000000);
        $this->fxAuth->AddDBParam('expired', date('m/d/Y H:i:s', $timeValue - $this->dbSettings->getExpiringSeconds()), 'lt');
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->setupFXforAuth($hashTable, 1);
            $this->fxAuth->SetRecordID($recId);
            $result = $this->fxAuth->DoFxAction("delete", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        return true;
    }

    public function authSupportRetrieveHashedPassword($username)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->setupFXforDB($userTable, 1);
        $this->fx->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        if ((!is_array($result) || $result['foundCount'] < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->setupFXforDB($userTable, 1);
            $this->fx->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        }
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        foreach ($result['data'] as $key => $row) {
            return $row['hashedpasswd'][0];
        }
        return false;
    }

    public function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null)
    {
        if ($this->authSupportRetrieveHashedPassword($username) !== false) {
            $this->logger->setErrorMessage('User Already exist: ' . $username);
            return false;
        }
        $userTable = $this->dbSettings->getUserTable();
        $this->setupFXforDB($userTable, 1);
        $this->fx->AddDBParam('username', $username);
        $this->fx->AddDBParam('hashedpasswd', $hashedpassword);
        $result = $this->fx->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportChangePassword($username, $hashednewpassword)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->setupFXforDB($userTable, 1);
        $username = $this->authSupportUnifyUsernameAndEmail($username);
        $this->fx->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if ((!is_array($result) || count($result['data']) < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->setupFXforDB($userTable, 1);
            $this->fx->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->setupFXforDB($userTable, 1);
            $this->fx->SetRecordID($recId);
            $this->fx->AddDBParam("hashedpasswd", $hashednewpassword);
            $result = $this->fx->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
            break;
        }
        return true;
    }

    public function authSupportGetUserIdFromUsername($username)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($username === 0) {
            return 0;
        }

        $username = $this->authSupportUnifyUsernameAndEmail($username);

        $this->setupFXforDB_Alt($userTable, 1);
        $this->fxAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['id'][0];
        }
        return false;
    }

    public function authSupportGetUsernameFromUserId($userid)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($userid === 0) {
            return 0;
        }

        $this->setupFXforDB($userTable, 1);
        $this->fx->AddDBParam('id', $userid, "eq");
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['username'][0];
        }
        return false;
    }

    public function authSupportGetUserIdFromEmail($email)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($email === 0) {
            return 0;
        }

        $this->setupFXforDB_Alt($userTable, 1);
        $this->fxAlt->AddDBParam('email', str_replace("@", "\\@", $email), "eq");
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['id'][0];
        }
        return false;
    }

    public function authSupportUnifyUsernameAndEmail($username)
    {
        if (!$this->dbSettings->getEmailAsAccount() || $this->dbSettings->isDBNative()) {
            return $username;
        }

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null || is_null($username) || $username === 0 || $username === '') {
            return false;
        }

        $this->setupFXforDB_Alt($userTable, 55555);
        $this->fxAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
        $this->fxAlt->AddDBParam('email', str_replace("@", "\\@", $username), "eq");
        $this->fxAlt->SetLogicalOR();
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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

    public function authSupportGetGroupNameFromGroupId($groupid)
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable == null) {
            return null;
        }

        $this->setupFXforDB_Alt($groupTable, 1);
        $this->fxAlt->AddDBParam('id', $groupid);
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            return $row['groupname'][0];
        }
        return false;
    }

    public function authSupportGetGroupsOfUser($user)
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
        $this->candidateGroups = array();
        foreach ($this->belongGroups as $groupid) {
            $this->candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
        }
        return $this->candidateGroups;
    }

    private $candidateGroups;
    private $belongGroups;
    private $firstLevel;

    private function resolveGroup($groupid)
    {
        $this->setupFXforDB_Alt($this->dbSettings->getCorrTable(), 1);
        if ($this->firstLevel) {
            $this->fxAlt->AddDBParam('user_id', $groupid);
            $this->firstLevel = false;
        } else {
            $this->fxAlt->AddDBParam('group_id', $groupid);
            $this->belongGroups[] = $groupid;
        }
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            if (!in_array($row['dest_group_id'][0], $this->belongGroups)) {
                if (!$this->resolveGroup($row['dest_group_id'][0])) {
                    return false;
                }
            }
        }
    }

    public function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('hash', $hash);
        $this->fxAuth->AddDBParam('expired', $currentDTFormat);
        $this->fxAuth->AddDBParam('clienthost', $clienthost);
        $this->fxAuth->AddDBParam('user_id', $userid);
        $result = $this->fxAuth->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('user_id', $userid, 'eq');
        $this->fxAuth->AddDBParam('clienthost', $randdata, 'eq');
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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

    public function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        $user = $this->authSupportUnifyUsernameAndEmail($user);

        $this->setupFXforAuth($tableName, 1);
        $this->fxAuth->AddDBParam($userField, $user, 'eq');
        $this->fxAuth->AddDBParam($keyField, $keyValue, 'eq');
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
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

    public function authSupportUserEnrollmentStart($userid, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam("hash", $hash);
        $this->fxAuth->AddDBParam("expired", $this->currentDTString());
        $this->fxAuth->AddDBParam("user_id", $userid);
        $result = $this->fxAuth->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        return true;
    }

    public
    function authSupportUserEnrollmentEnrollingUser($hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam("hash", $hash, "eq");
        $this->fxAuth->AddDBParam("clienthost", "", "eq");
        $this->fxAuth->AddDBParam("expired", $this->currentDTString(3600), "gt");
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $userID = $row['user_id'][0];
            return $userID;
        }
        return false;

    }

    public
    function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW)
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        $this->setupFXforDB_Alt($userTable, 1);
        $this->fxAlt->AddDBParam('id', $userID);
        $resultUser = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($resultUser)) {
            $this->logger->setDebugMessage(get_class($resultUser) . ': ' . $resultUser->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($resultUser['URL']));
        foreach ($resultUser['data'] as $ukey => $urow) {
            $recId = substr($ukey, 0, strpos($ukey, '.'));
            $this->setupFXforDB_Alt($userTable, 1);
            $this->fxAlt->SetRecordID($recId);
            $this->fxAlt->AddDBParam('hashedpasswd', $password);
            if ($rawPWField !== false) {
                $this->fxAlt->AddDBParam($rawPWField, $rawPW);
            }
            $result = $this->fxAlt->DoFxAction('update', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
                return false;
            }
            $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
            return $userID;
        }
    }

    private
    function currentDTString($addSeconds = 0)
    {
//        $currentDT = new DateTime();
//        $timeValue = $currentDT->format("U");
//        $currentDTStr = $this->link->quote($currentDT->format('m/d/Y H:i:s'));

        // For 5.2
        $timeValue = time();
        $currentDTStr = date('m/d/Y H:i:s', $timeValue - $addSeconds);
        // End of for 5.2
        return $currentDTStr;
    }

    public function isPossibleOperator($operator)
    {
        return !(FALSE === array_search(strtoupper($operator), array(
                'EQ', 'CN', 'BW', 'EW', 'GT', 'GTE', 'LT', 'LTE', 'NEQ', 'AND', 'OR',
            )));
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        return !(array_search(strtoupper($specifier), array('ASCEND', 'DESCEND', 'ASC', 'DESC')) === FALSE);
    }

    public function normalizedCondition($condition)
    {
        if (!isset($condition['field'])) {
            $condition['field'] = '';
        }
        if (!isset($condition['value'])) {
            $condition['value'] = '';
        }

        if (($condition['field'] === '-recid' && $condition['operator'] === 'undefined') ||
            ($condition['operator'] === '=')
        ) {
            return array(
                'field' => $condition['field'],
                'operator' => 'eq',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '!=') {
            return array(
                'field' => $condition['field'],
                'operator' => 'neq',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '<') {
            return array(
                'field' => $condition['field'],
                'operator' => 'lt',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '<=') {
            return array(
                'field' => $condition['field'],
                'operator' => 'lte',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '>') {
            return array(
                'field' => $condition['field'],
                'operator' => 'gt',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '>=') {
            return array(
                'field' => $condition['field'],
                'operator' => 'gte',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === 'match*') {
            return array(
                'field' => $condition['field'],
                'operator' => 'bw',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '*match') {
            return array(
                'field' => $condition['field'],
                'operator' => 'ew',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '*match*') {
            return array(
                'field' => $condition['field'],
                'operator' => 'cn',
                'value' => "{$condition['value']}",
            );
        } else {
            return $condition;
        }
    }

    protected function _adjustSortDirection($direction)
    {
        if (strtoupper($direction) == 'ASC') {
            $direction = 'ascend';
        } else if (strtoupper($direction) == 'DESC') {
            $direction = 'descend';
        }

        return $direction;
    }

    public function isContainingFieldName($fname, $fieldnames)
    {
        if (in_array($fname, $fieldnames)) {
            return true;
        }

        if (strpos($fname, "::") !== false) {
            $lastPeriodPosition = strrpos($fname, ".");
            if ($lastPeriodPosition !== false) {
                if (in_array(substr($fname, 0, $lastPeriodPosition), $fieldnames)) {
                    return true;
                }
            }
        }
        if ($fname == "-delete.related") {
            return true;
        }
        return false;
    }

    public function isNullAcceptable()
    {
        return false;
    }

    public function queryForTest($table, $conditions = null)
    {
        if ($table == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        $this->setupFXforAuth($table, 'all');
        if (count($conditions) > 0) {
            foreach ($conditions as $field => $value) {
                $this->fxAuth->AddDBParam($field, $value, 'eq');
            }
        }
        if (count($conditions) > 0) {
            $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        } else {
            $result = $this->fxAuth->DoFxAction('show_all', TRUE, TRUE, 'full');
        }
        if ($result === false) {
            return false;
        }
        $recordSet = array();
        foreach ($result['data'] as $key => $row) {
            $oneRecord = array();
            foreach ($row as $field => $value) {
                $oneRecord[$field] = $value[0];
            }
            $recordSet[] = $oneRecord;
        }
        return $recordSet;
    }

    function authSupportGetSalt($username)
    {
        // TODO: Implement authSupportGetSalt() method.
    }

    function removeOutdatedChallenges()
    {
        // TODO: Implement removeOutdatedChallenges() method.
    }

}