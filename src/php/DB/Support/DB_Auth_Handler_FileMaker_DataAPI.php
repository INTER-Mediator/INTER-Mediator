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

class DB_Auth_Handler_FileMaker_DataAPI extends DB_Auth_Common implements Auth_Interface_DB
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
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);

        $conditions = array(array('user_id' => $uid, 'clienthost' => $clientId));
        $result = NULL;
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential('Exception in Query: ' .
                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
        }

//        if ($result !== NULL) {
//            $this->logger->setDebugMessage(
//                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
//                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
//            return false;
//        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential(
                $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $className = is_object($result) ? get_class($result) : "NULL";
        if ($className === 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            foreach ($result as $record) {
                $recordId = $record->getRecordId();
                $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
                $this->dbClass->fmDataAuth->{$hashTable}->update($recordId, array(
                    'hash' => $challenge,
                    'expired' => $currentDTFormat,
                    'clienthost' => $clientId,
                    'user_id' => $uid,
                ));
                $result = $this->dbClass->fmDataAuth->{$hashTable}->getRecord($recordId);
                if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                    $this->logger->setDebugMessage(
                        $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                            $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
                    return;
                }
                $this->logger->setDebugMessage(
                    $this->dbClass->stringWithoutCredential(
                        $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
                return;
            }
        }
        $recordId = null;
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        try {
            $recordId = $this->dbClass->fmDataAuth->{$hashTable}->create(array(
                'hash' => $challenge,
                'expired' => $currentDTFormat,
                'clienthost' => $clientId,
                'user_id' => $uid,
            ));
        } catch (Exception $e) {
            $this->logger->setErrorMessage($e->getMessage());
        }

        if (!is_numeric($recordId)) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential('RecordId is not numeric: ' .
                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
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
        $result = null;
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        $conditions = array(array('user_id' => $uid, 'clienthost' => '_im_media'));
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAuth->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return null;
        }

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
        foreach ($result as $record) {
            $expiredDT = new DateTime($record->expired);
            $hashValue = $record->hash;
            $currentDT = new DateTime();
            $seconds = $currentDT->format("U") - $expiredDT->format("U");
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return null;
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
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        $conditions = array(array('user_id' => $uid, 'clienthost' => $clientId));
        $result = NULL;
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAuth->getSessionToken();
            }
            if (!is_null($result)) {
                foreach ($result as $record) {
                    $recordId = $record->getRecordId();
                    $hashValue = $record->hash;
                    if ($isDelete) {
                        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
                        try {
                            $this->dbClass->fmDataAuth->{$hashTable}->delete($recordId);
                        } catch (Exception $e) {
                            $this->logger->setDebugMessage(
                                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
                            return null;
                        }
                    }
                    return $hashValue;
                }
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(
                    get_class($result) . ': ' . $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return null;
        }

        $className = is_object($result) ? get_class($result) : "NULL";
        if ($className !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential($className . ': ' .
                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return null;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));

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

        $this->dbClass->setupFMDataAPIforAuth($hashTable, 100000000);
        $conditions = array(
            array('expired' => '...' . date('m/d/Y H:i:s',
                    $timeValue - $this->dbSettings->getExpiringSeconds()),),
            array('clienthost' => '==', 'omit' => 'true'),
        );
        $result = null; // For PHPStan level 1
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAuth->getSessionToken();
            }
            $className = is_object($result) ? get_class($result) : "NULL";
            if ($className !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->dbClass->stringWithoutCredential(
                        $className . ': ' . $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
                return false;
            }
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            if (!is_null($result)) {
                foreach ($result as $record) {
                    $recordId = $record->getRecordId();
                    $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
                    try {
                        $result = $this->dbClass->fmDataAuth->{$hashTable}->delete($recordId);
                    } catch (Exception $e) {
                        $this->logger->setDebugMessage(
                            $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                                $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
        }

        return true;
    }

    public function authSupportRetrieveHashedPassword(string $username): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return null;
        }

        $this->dbClass->setupFMDataAPIforDB($userTable, 1);
        $conditions = array(array('username' => str_replace('@', '\\@', $username)));
        $result = NULL;
        try {
            $result = $this->dbClass->fmData->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmData->getSessionToken();
            }
        } catch (Exception $e) {
            $className = is_object($result) ? get_class($result) : "NULL";
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(
                    $className . ': ' . $this->dbClass->fmData->{$userTable}->getDebugInfo()));
            return null;
        }

        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmData->{$userTable}->getDebugInfo()));
        if (is_null($result)) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(
                    $this->dbClass->fmData->{$userTable}->getDebugInfo()));
            return null;
        }
        if ((get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation' ||
                $result->count() < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->dbClass->setupFMDataAPIforDB($userTable, 1);
            $conditions = array(array('email' => str_replace('@', '\\@', $username)));
            try {
                $result = $this->dbClass->fmData->{$userTable}->query($conditions);
            } catch (Exception $e) {
                return null;
            }
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(
                    $this->dbClass->fmData->{$userTable}->getDebugInfo()));
        }

        foreach ($result as $record) {
            return $record->hashedpasswd;
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
        $this->dbClass->setupFMDataAPIforDB($userTable, 1);
        $recordId = $this->dbClass->fmData->{$userTable}->create(array(
            'username' => $username,
            'hashedpasswd' => $hashedpassword,
        ));
        if (!is_numeric($recordId)) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($recordId) . ': ' .
                    $this->dbClass->fmData->{$userTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential(
                $this->dbClass->fmData->{$userTable}->getDebugInfo()));
        return true;
    }

    public function authSupportChangePassword(string $username, string $hashednewpassword): bool
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->dbClass->setupFMDataAPIforDB($userTable, 1);
        $username = $this->authSupportUnifyUsernameAndEmail($username);
        $conditions = array(array('username' => str_replace('@', '\\@', $username)));
        try {
            $result = $this->dbClass->fmData->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmData->getSessionToken();
            }
        } catch (Exception $e) {
            return false;
        }
        if ((!is_array($result) || count($result['data']) < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->dbClass->setupFMDataAPIforDB($userTable, 1);
            $conditions = array(array('email' => str_replace('@', '\\@', $username)));
            try {
                $result = $this->dbClass->fmData->{$userTable}->query($conditions);
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                        $this->dbClass->fmData->{$userTable}->getDebugInfo()));
                return false;
            }
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmData->{$userTable}->getDebugInfo()));
        foreach ($result as $record) {
            $recordId = $record->getRecordId();
            $this->dbClass->setupFMDataAPIforDB($userTable, 1);
            $this->dbClass->fmData->{$userTable}->update($recordId, array(
                'hashedpasswd' => $hashednewpassword,
            ));
            $result = $this->dbClass->fmData->{$userTable}->getRecord($recordId);
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                        $this->dbClass->fmData->{$userTable}->getDebugInfo()));
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

        $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 1);
        $conditions = array(array('username' => str_replace('@', '\\@', $username)));
        $result = NULL;
        try {
            $result = $this->dbClass->fmDataAlt->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAlt->getSessionToken();
            }
        } catch (Exception $e) {
            $className = is_object($result) ? get_class($result) : "NULL";
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential($className . ': ' .
                    $this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
            return false;
        }

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));

        foreach ($result as $record) {
            return $record->id;
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

        $this->dbClass->setupFMDataAPIforDB($userTable, 1);
        $conditions = array(array('id' => $userid));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->dbClass->fmData->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmData->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmData->{$userTable}->getDebugInfo()));
            return false;
        }

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmData->{$userTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmData->{$userTable}->getDebugInfo()));
        foreach ($result as $record) {
            return $record->username;
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

        $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 1);
        $conditions = array(array('email' => str_replace('@', '\\@', $email)));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->dbClass->fmDataAlt->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAlt->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
            return false;
        }

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
        foreach ($result as $record) {
            return $record->id;
        }
        return false;
    }

    public function authSupportUnifyUsernameAndEmail(?string $username): ?string
    {
        if (!$this->dbSettings->getEmailAsAccount() || $this->dbSettings->isDBNative()) {
            return $username;
        }

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null || $username == 0 || $username === '') {
            return false;
        }

        $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 55555);
        $conditions = array(
            array('username' => str_replace("@", "\\@", $username)),
            array('email' => str_replace("@", "\\@", $username))
        );
        $result = NULL;
        try {
            $result = $this->dbClass->fmDataAlt->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAlt->getSessionToken();
            }
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
            $usernameCandidate = '';
            foreach ($result as $record) {
                if ($record->username == $username) {
                    $usernameCandidate = $username;
                }
                if ($record->email == $username) {
                    $usernameCandidate = $record->username;
                }
            }
        } catch (Exception $e) {
            $className = is_object($result) ? get_class($result) : "NULL";
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential($className . ': ' .
                    $this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
            return false;
        }
        return $usernameCandidate;
    }

    public function authSupportGetGroupNameFromGroupId($groupid): ?string
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable == null) {
            return null;
        }

        $this->dbClass->setupFMDataAPIforDB_Alt($groupTable, 1);
        $conditions = array(array('id' => $groupid));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->dbClass->fmDataAlt->{$groupTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAlt->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAlt->{$groupTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAlt->{$groupTable}->getDebugInfo()));
        foreach ($result as $record) {
            return $record->groupname;
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

    private array $belongGroups;
    private bool $firstLevel;

    private function resolveGroup(string $groupid): void
    {
        $this->dbClass->setupFMDataAPIforDB_Alt($this->dbSettings->getCorrTable(), 1);
        if ($this->firstLevel) {
            $conditions = array(array('user_id' => $groupid));
            $this->firstLevel = false;
        } else {
            $conditions = array(array('group_id' => $groupid));
            $this->belongGroups[] = $groupid;
        }
        $result = null; // For PHPStan level 1
        try {
            $result = $this->dbClass->fmDataAlt->{$this->dbSettings->getCorrTable()}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAlt->getSessionToken();
            }
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(
                    $this->dbClass->fmDataAlt->{$this->dbSettings->getCorrTable()}->getDebugInfo()));
            if (!is_null($result)) {
                foreach ($result as $record) {
                    if (!in_array($record->dest_group_id, $this->belongGroups)) {
                        $this->resolveGroup($record->dest_group_id);
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAlt->{$this->dbSettings->getCorrTable()}->getDebugInfo()));
            return;
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
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        $recordId = $this->dbClass->fmDataAuth->{$hashTable}->create(array(
            'hash' => $hash,
            'expired' => $currentDTFormat,
            'clienthost' => $clienthost,
            'user_id' => $userid,
        ));

        if (!is_numeric($recordId)) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(
                    get_class($recordId) . ': ' . $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
        return true;
    }

    public function authSupportCheckIssuedHashForResetPassword(string $userid, string $randdata, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        $conditions = array(array('user_id' => $userid, 'clienthost' => $randdata));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAuth->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
        foreach ($result as $record) {
            $hashValue = $record->hash;
            $expiredDT = $record->expired;

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

    public function authSupportCheckMediaPrivilege(
        string $tableName, string $targeting, string $userField, string $user, string $keyField, string $keyValue): ?array
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

        $this->dbClass->setupFMDataAPIforAuth($tableName, 1);
        $conditions = array(array($userField => $user), array($keyField => $keyValue));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->dbClass->fmDataAuth->{$tableName}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAuth->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAuth->{$tableName}->getDebugInfo()));
            return null;
        }
        if (!is_array($result)) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAuth->{$tableName}->getDebugInfo()));
            return null;
        }
        $array = array();
        foreach ($result as $record) {
            $record = array('recordId' => $record->getRecordId(), 'modId' => $record->getModId());
            foreach ($record as $field => $value) {
                $record[$field] = $record->{$field};
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
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        $recordId = $this->dbClass->fmDataAuth->{$hashTable}->create(array(
            'hash' => $hash,
            'expired' => IMUtil::currentDTStringFMS(),
            'user_id' => $userid,
        ));

        if (!is_numeric($recordId)) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($recordId) . ': ' .
                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
        return true;
    }

    public function authSupportUserEnrollmentEnrollingUser(string $hash): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        $this->dbClass->setupFMDataAPIforAuth($hashTable, 1);
        $conditions = array(
            array('hasu' => $hash, 'clienthost' => '=', 'expired' => IMUtil::currentDTStringFMS(3600) . '...')
        );
        $result = null; // For PHPStan level 1
        try {
            $result = $this->dbClass->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAuth->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->dbClass->stringWithoutCredential($this->dbClass->fmDataAuth->{$hashTable}->getDebugInfo()));
        foreach ($result as $record) {
            return $record->user_id;
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
        $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 1);
        $conditions = array(array('id' => $userID));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->dbClass->fmDataAlt->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->dbClass->fmDataAlt->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                $this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential($result['URL']));
        foreach ($result as $record) {
            $recordId = $record->getRecordId();
            $this->dbClass->setupFMDataAPIforDB_Alt($userTable, 1);
            if ($rawPWField !== false) {
                $this->dbClass->fmDataAlt->{$userTable}->update($recordId, array(
                    'hashedpasswd' => $password,
                    $rawPWField => $rawPW,
                ));
            } else {
                $this->dbClass->fmDataAlt->{$userTable}->update($recordId, array(
                    'hashedpasswd' => $password,
                ));
            }
            $result = $this->dbClass->fmDataAlt->{$userTable}->getRecord($recordId);
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage($this->dbClass->stringWithoutCredential(get_class($result) . ': ' .
                    $this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
                return false;
            }
            $this->logger->setDebugMessage(
                $this->dbClass->stringWithoutCredential(
                    $this->dbClass->fmDataAlt->{$userTable}->getDebugInfo()));
            return $userID;
        }
        return null;
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
