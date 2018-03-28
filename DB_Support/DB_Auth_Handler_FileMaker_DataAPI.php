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

class DB_Auth_Handler_FileMaker_DataAPI extends DB_Auth_Common implements Auth_Interface_DB
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
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);

        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam('user_id', $uid, 'eq');
        //$this->dbClass->fmDataAuth->AddDBParam('clienthost', $clientId, 'eq');
        //$result = $this->dbClass->fmDataAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('user_id' => $uid), array('clienthost' => $clientId));
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
        } catch (Exception $e) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }

        //if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            //return false;
        //}
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
            $this->dbClass->fmDataAuth->SetRecordID($recId);
            $this->dbClass->fmDataAuth->AddDBParam('hash', $challenge);
            $this->dbClass->fmDataAuth->AddDBParam('expired', $currentDTFormat);
            $this->dbClass->fmDataAuth->AddDBParam('clienthost', $clientId);
            $this->dbClass->fmDataAuth->AddDBParam('user_id', $uid);
            $result = $this->dbClass->fmDataAuth->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
            // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
            return true;
        }
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam('hash', $challenge);
        //$this->dbClass->fmDataAuth->AddDBParam('expired', $currentDTFormat);
        //$this->dbClass->fmDataAuth->AddDBParam('clienthost', $clientId);
        //$this->dbClass->fmDataAuth->AddDBParam('user_id', $uid);
        //$result = $this->dbClass->fmDataAuth->DoFxAction("new", TRUE, TRUE, 'full');
        $this->dbClass->fmDataAuth->{$hashTable}->create(array(
            'hash' => $challenge,
            'expired' => $currentDTFormat,
            'clienthost' => $clientId,
            'user_id' => $uid,
        ));

        if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
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
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam('user_id', $uid, 'eq');
        //$this->dbClass->fmDataAuth->AddDBParam('clienthost', '_im_media', 'eq');
        //$result = $this->dbClass->fmDataAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('user_id' => $uid), array('clienthost' => '_im_media'));
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
        } catch (Exception $e) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->dbClass->getDebugInfo());
            return false;
        }

        //if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->dbClass->getDebugInfo());
            //return false;
        //}
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
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
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam('user_id', $uid, 'eq');
        //$this->dbClass->fmDataAuth->AddDBParam('clienthost', $clientId, 'eq');
        //$result = $this->dbClass->fmDataAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('user_id' => $uid), array('clienthost' => $clientId));
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
        } catch (Exception $e) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }

        //if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            //return false;
        //}
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $hashValue = $row['hash'][0];
            if ($isDelete) {
                $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
                $this->dbClass->fmDataAuth->SetRecordID($recId);
                $result = $this->dbClass->fmDataAuth->DoFxAction("delete", TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
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

        $this->dbClass->setupFMDataAPIforAuth($hashTable, 100000000);
        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam('expired', date('m/d/Y H:i:s', $timeValue - $this->dbSettings->getExpiringSeconds()), 'lt');
        //$result = $this->dbClass->fmDataAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('expired' => '...' . date('m/d/Y H:i:s', $timeValue - $this->dbSettings->getExpiringSeconds())));
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
        } catch (Exception $e) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }

        //if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            //return false;
        //}
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
            $this->dbClass->fmDataAuth->SetRecordID($recId);
            $result = $this->dbClass->fmDataAuth->DoFxAction("delete", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
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

        // [WIP]
        $this->dbClass->setupFMDataAPIforDB($userTable, 1);
        //$this->dbClass->fmData->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
        //$result = $this->dbClass->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('username' => str_replace('@', '\\@', $username)));
        try {
            $result = $this->dbClass->fmData->{$userTable}->query($conditions);
        } catch (Exception $e) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }

        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        if ((!is_array($result) || $result['foundCount'] < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->dbClass->setupFMDataAPIforDB($userTable, 1);
            // [WIP]
            //$this->dbClass->fmData->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            //$result = $this->dbClass->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
            $conditions = array(array('email' => str_replace('@', '\\@', $username)));
            $result = $this->dbClass->fmData->{$userTable}->query($conditions);
            // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        }
        //if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            //return false;
        //}

        // [WIP]
        //foreach ($result['data'] as $key => $row) {
        //    return $row['hashedpasswd'][0];
        //}
        foreach ($result as $record) {
            return $record->hashedpasswd;
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
        $this->dbClass->setupFMDataAPIforDB($userTable, 1);
        // [WIP]
        //$this->dbClass->fmData->AddDBParam('username', $username);
        //$this->dbClass->fmData->AddDBParam('hashedpasswd', $hashedpassword);
        //$result = $this->dbClass->fmData->DoFxAction('new', TRUE, TRUE, 'full');
        $this->dbClass->fmData->{$userTable}->create(array(
            'username' => $username,
            'hashedpasswd' => $hashedpassword,
        ));

        if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportChangePassword($username, $hashednewpassword)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->dbClass->setupFMDataAPIforDB($userTable, 1);
        $username = $this->authSupportUnifyUsernameAndEmail($username);
        // [WIP]
        //$this->dbClass->fmData->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
        //$result = $this->dbClass->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('username' => str_replace('@', '\\@', $username)));
        $result = $this->dbClass->fmData->{$userTable}->query($conditions);
        if ((!is_array($result) || count($result['data']) < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->dbClass->setupFMDataAPIforDB($userTable, 1);
            // [WIP]
            //$this->dbClass->fmData->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            //$result = $this->dbClass->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
            $conditions = array(array('email' => str_replace('@', '\\@', $username)));
            $result = $this->dbClass->fmData->{$userTable}->query($conditions);    
            if (!is_array($result)) {
                // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->dbClass->setupFMDataAPIforDB($userTable, 1);
            $this->dbClass->fmData->SetRecordID($recId);
            $this->dbClass->fmData->AddDBParam("hashedpasswd", $hashednewpassword);
            $result = $this->dbClass->fmData->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
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

        $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
        //$result = $this->dbClass->fmDataAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('username' => str_replace('@', '\\@', $username)));
        try {
            $result = $this->dbClass->fmDataAlt->{$userTable}->query($conditions);
        } catch (Exception $e) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }

        //if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            //return false;
        //}
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));

        // [WIP]
        //foreach ($result['data'] as $row) {
        //    return $row['id'][0];
        //}
        foreach ($result as $record) {
            return $record->id;
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

        $this->dbClass->setupFMDataAPIforDB($userTable, 1);
        // [WIP]
        //$this->dbClass->fmData->AddDBParam('id', $userid, "eq");
        //$result = $this->dbClass->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('id' => $userid));
        try {
            $result = $this->dbClass->fmData->{$userTable}->query($conditions);
        } catch (Exception $e) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }

        //if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            //return false;
        //}
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
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

        $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAlt->AddDBParam('email', str_replace("@", "\\@", $email), "eq");
        //$result = $this->dbClass->fmDataAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('email' => str_replace('@', '\\@', $email)));
        try {
            $result = $this->dbClass->fmDataAlt->{$userTable}->query($conditions);
        } catch (Exception $e) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }

        //if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            //return false;
        //}
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
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

        $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 55555);
        $this->dbClass->fmDataAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
        $this->dbClass->fmDataAlt->AddDBParam('email', str_replace("@", "\\@", $username), "eq");
        $this->dbClass->fmDataAlt->SetLogicalOR();
        $result = $this->dbClass->fmDataAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
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

        $this->dbClass->setupFMDataAPIforDB_Alt($groupTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAlt->AddDBParam('id', $groupid);
        //$result = $this->dbClass->fmDataAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('id' => $groupid));
        $result = $this->dbClass->fmDataAlt->{$groupTable}->query($conditions);    
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
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
        $this->dbClass->setupFMDataAPIforDB_Alt($this->dbSettings->getCorrTable(), 1);
        // [WIP]
        if ($this->firstLevel) {
            //$this->dbClass->fmDataAlt->AddDBParam('user_id', $groupid);
            $conditions = array(array('user_id' => $groupid));
            $this->firstLevel = false;
        } else {
            //$this->dbClass->fmDataAlt->AddDBParam('group_id', $groupid);
            $conditions = array(array('group_id' => $groupid));
            $this->belongGroups[] = $groupid;
        }
        //$result = $this->dbClass->fmDataAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $result = $this->dbClass->fmDataAlt->{$this->dbSettings->getCorrTable()}->query($conditions);    

        if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
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
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam('hash', $hash);
        //$this->dbClass->fmDataAuth->AddDBParam('expired', $currentDTFormat);
        //$this->dbClass->fmDataAuth->AddDBParam('clienthost', $clienthost);
        //$this->dbClass->fmDataAuth->AddDBParam('user_id', $userid);
        //$result = $this->dbClass->fmDataAuth->DoFxAction("new", TRUE, TRUE, 'full');
        $this->dbClass->fmDataAuth->{$hashTable}->create(array(
            'hash' => $hash,
            'expired' => $currentDTFormat,
            'clienthost' => $clienthost,
            'user_id' => $userid,
        ));

        if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam('user_id', $userid, 'eq');
        //$this->dbClass->fmDataAuth->AddDBParam('clienthost', $randdata, 'eq');
        //$result = $this->dbClass->fmDataAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('user_id' => $userid), array('clienthost' => $randdata));
        $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);

        if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
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

        $this->dbClass->setupFMDataAPIforAuth($tableName, 1);
        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam($userField, $user, 'eq');
        //$this->dbClass->fmDataAuth->AddDBParam($keyField, $keyValue, 'eq');
        //$result = $this->dbClass->fmDataAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array($userField => $user), array($keyField => $keyValue));
        $result = $this->dbClass->fmDataAuth->{$tableName}->query($conditions);

        if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
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
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam("hash", $hash);
        //$this->dbClass->fmDataAuth->AddDBParam("expired", IMUtil::currentDTStringFMS());
        //$this->dbClass->fmDataAuth->AddDBParam("user_id", $userid);
        //$result = $this->dbClass->fmDataAuth->DoFxAction('new', TRUE, TRUE, 'full');
        $this->dbClass->fmDataAuth->{$hashTable}->create(array(
            'hash' => $hash,
            'expired' => IMUtil::currentDTStringFMS(),
            'user_id' => $userid,
        ));

        if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportUserEnrollmentEnrollingUser($hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAuth->AddDBParam("hash", $hash, "eq");
        //$this->dbClass->fmDataAuth->AddDBParam("clienthost", "", "eq");
        //$this->dbClass->fmDataAuth->AddDBParam("expired", IMUtil::currentDTStringFMS(3600), "gt");
        //$result = $this->dbClass->fmDataAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('hasu' => $hash), array('clienthost' => '='), array('expired' => IMUtil::currentDTStringFMS(3600) . '...'));
        $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);

        if (!is_array($result)) {
            // [WIP] $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $userID = $row['user_id'][0];
            return $userID;
        }
        return false;

    }

    public function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW)
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 1);
        // [WIP]
        //$this->dbClass->fmDataAlt->AddDBParam('id', $userID);
        //$resultUser = $this->dbClass->fmDataAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $conditions = array(array('id' => $userID));
        $result = $this->dbClass->fmDataAlt->{$userTable}->query($conditions);    

        if (!is_array($resultUser)) {
            $this->logger->setDebugMessage(get_class($resultUser) . ': ' . $resultUser->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($resultUser['URL']));
        foreach ($resultUser['data'] as $ukey => $urow) {
            $recId = substr($ukey, 0, strpos($ukey, '.'));
            $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 1);
            $this->dbClass->fmDataAlt->SetRecordID($recId);
            $this->dbClass->fmDataAlt->AddDBParam('hashedpasswd', $password);
            if ($rawPWField !== false) {
                $this->dbClass->fmDataAlt->AddDBParam($rawPWField, $rawPW);
            }
            $result = $this->dbClass->fmDataAlt->DoFxAction('update', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
                return false;
            }
            // [WIP] $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
            return $userID;
        }
    }
}
