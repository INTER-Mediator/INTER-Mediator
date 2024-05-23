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
use INTERMediator\DB\FileMaker_DataAPI;
use INTERMediator\IMUtil;

/**
 *
 */
class DB_Auth_Handler_FileMaker_DataAPI extends DB_Auth_Common
{
    /**
     * @var FileMaker_DataAPI
     */
    protected FileMaker_DataAPI $fmdb;

    /**
     * @param $parent
     */
    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->fmdb = $parent;
    }

    /**
     * @param string|null $uid
     * @param string $challenge
     * @param string $clientId
     * @param string $prefix
     * @return void
     * @throws Exception
     */
    public function authSupportStoreChallenge(?string $uid, string $challenge, string $clientId, string $prefix = ""): void
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);

        $conditions = array(array('user_id' => $uid, 'clienthost' => $clientId));
        $result = NULL;
        try {
            $result = $this->fmdb->fmDataAuth->{$hashTable}->query($conditions);
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential('Exception in Query: ' .
                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
        }

//        if ($result !== NULL) {
//            $this->logger->setDebugMessage(
//                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
//                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
//            return false;
//        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential(
                $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $className = is_object($result) ? get_class($result) : "NULL";
        if ($className === 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            foreach ($result as $record) {
                $recordId = $record->getRecordId();
                $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
                $this->fmdb->fmDataAuth->{$hashTable}->update($recordId, array(
                    'hash' => $challenge,
                    'expired' => $currentDTFormat,
                    'clienthost' => $clientId,
                    'user_id' => $uid,
                ));
                $result = $this->fmdb->fmDataAuth->{$hashTable}->getRecord($recordId);
                if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                    $this->logger->setDebugMessage(
                        $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                            $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
                    return;
                }
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(
                        $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
                return;
            }
        }
        $recordId = null;
        $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
        try {
            $recordId = $this->fmdb->fmDataAuth->{$hashTable}->create(array(
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
                $this->fmdb->stringWithoutCredential('RecordId is not numeric: ' .
                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
    }

    /**
     * @param string $uid
     * @return string|null
     * @throws Exception
     */
    public function authSupportCheckMediaToken(string $uid): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return null;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $result = null;
        $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
        $conditions = array(array('user_id' => $uid, 'clienthost' => '_im_media'));
        try {
            $result = $this->fmdb->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAuth->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return null;
        }

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return null;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
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

    /**
     * @param string $uid
     * @param string $clientId
     * @param bool $isDelete
     * @param string $prefix
     * @return string|null
     */
    public function authSupportRetrieveChallenge(string $uid, string $clientId, bool $isDelete = true, string $prefix = ""): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return null;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
        $conditions = array(array('user_id' => $uid, 'clienthost' => $clientId));
        $result = NULL;
        try {
            $result = $this->fmdb->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAuth->getSessionToken();
            }
            if (!is_null($result)) {
                foreach ($result as $record) {
                    $recordId = $record->getRecordId();
                    $hashValue = $record->hash;
                    if ($isDelete) {
                        $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
                        try {
                            $this->fmdb->fmDataAuth->{$hashTable}->delete($recordId);
                        } catch (Exception $e) {
                            $this->logger->setDebugMessage(
                                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
                            return null;
                        }
                    }
                    return $hashValue;
                }
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(
                    get_class($result) . ': ' . $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return null;
        }

        $className = is_object($result) ? get_class($result) : "NULL";
        if ($className !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($className . ': ' .
                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return null;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));

        return null;
    }

    /**
     * @return bool
     */
    public function authSupportRemoveOutdatedChallenges(): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }

        $currentDT = new DateTime();
        $timeValue = $currentDT->format("U");

        $this->fmdb->setupFMDataAPIforAuth($hashTable, 100000000);
        $conditions = array(
            array('expired' => '...' . date('m/d/Y H:i:s',
                    $timeValue - $this->dbSettings->getExpiringSeconds()),),
            array('clienthost' => '==', 'omit' => 'true'),
        );
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAuth->getSessionToken();
            }
            $className = is_object($result) ? get_class($result) : "NULL";
            if ($className !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(
                        $className . ': ' . $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
                return false;
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            if (!is_null($result)) {
                foreach ($result as $record) {
                    $recordId = $record->getRecordId();
                    $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
                    try {
                        $result = $this->fmdb->fmDataAuth->{$hashTable}->delete($recordId);
                    } catch (Exception $e) {
                        $this->logger->setDebugMessage(
                            $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                                $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
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
        if (is_null($userTable)) {
            return null;
        }

        $this->fmdb->setupFMDataAPIforDB($userTable, 1);
        $conditions = array(array('username' => str_replace('@', '\\@', $username)));
        $result = NULL;
        try {
            $result = $this->fmdb->fmData->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
            }
        } catch (Exception $e) {
            $className = is_object($result) ? get_class($result) : "NULL";
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(
                    $className . ': ' . $this->fmdb->fmData->{$userTable}->getDebugInfo()));
            return null;
        }

        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
        if (is_null($result)) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(
                    $this->fmdb->fmData->{$userTable}->getDebugInfo()));
            return null;
        }
        if ((get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation' ||
                $result->count() < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->fmdb->setupFMDataAPIforDB($userTable, 1);
            $conditions = array(array('email' => str_replace('@', '\\@', $username)));
            try {
                $result = $this->fmdb->fmData->{$userTable}->query($conditions);
            } catch (Exception $e) {
                return null;
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(
                    $this->fmdb->fmData->{$userTable}->getDebugInfo()));
        }

        foreach ($result as $record) {
            return $record->hashedpasswd;
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
     * @throws Exception
     */
    public function authSupportCreateUser(string  $username, string $hashedpassword, bool $isSAML = false,
                                          ?string $ldapPassword = null, ?array $attrs = null): bool
    {
        if ($this->authSupportRetrieveHashedPassword($username)) {
            $this->logger->setErrorMessage('User Already exist: ' . $username);
            return false;
        }
        $userTable = $this->dbSettings->getUserTable();
        $this->fmdb->setupFMDataAPIforDB($userTable, 1);
        $recordId = $this->fmdb->fmData->{$userTable}->create(array(
            'username' => $username,
            'hashedpasswd' => $hashedpassword,
        ));
        if (!is_numeric($recordId)) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(
                    'FileMakerLayout: ' . $this->fmdb->fmData->{$userTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential(
                $this->fmdb->fmData->{$userTable}->getDebugInfo()));
        return true;
    }

    /**
     * @param string $username
     * @param string $hashednewpassword
     * @return bool
     * @throws Exception
     */
    public function authSupportChangePassword(string $username, string $hashednewpassword): bool
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable)) {
            return false;
        }

        $this->fmdb->setupFMDataAPIforDB($userTable, 1);
        $username = $this->authSupportUnifyUsernameAndEmail($username);
        $conditions = array(array('username' => str_replace('@', '\\@', $username)));
        try {
            $result = $this->fmdb->fmData->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
            }
        } catch (Exception $e) {
            return false;
        }
        if ((!is_array($result) || count($result['data']) < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->fmdb->setupFMDataAPIforDB($userTable, 1);
            $conditions = array(array('email' => str_replace('@', '\\@', $username)));
            try {
                $result = $this->fmdb->fmData->{$userTable}->query($conditions);
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                return false;
            }
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
        foreach ($result as $record) {
            $recordId = $record->getRecordId();
            $this->fmdb->setupFMDataAPIforDB($userTable, 1);
            $this->fmdb->fmData->{$userTable}->update($recordId, array(
                'hashedpasswd' => $hashednewpassword,
            ));
            $result = $this->fmdb->fmData->{$userTable}->getRecord($recordId);
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                return false;
            }
            break;
        }
        return true;
    }

    /**
     * @param string|null $username
     * @return string|null
     */
    public function authSupportGetUserIdFromUsername(?string $username): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || !$username) {
            return null;
        }
        $username = $this->authSupportUnifyUsernameAndEmail($username);
        $this->fmdb->setupFMDataAPIforDB_Alt($userTable, 1);
        $conditions = array(array('username' => str_replace('@', '\\@', $username)));
        $result = NULL;
        try {
            $result = $this->fmdb->fmDataAlt->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAlt->getSessionToken();
            }
        } catch (Exception $e) {
            $className = is_object($result) ? get_class($result) : "NULL";
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($className . ': ' .
                    $this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
            return null;
        }
        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
            return null;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
        foreach ($result as $record) {
            return $record->id;
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
        if (is_null($userTable) || !$userid) {
            return null;
        }
        $this->fmdb->setupFMDataAPIforDB($userTable, 1);
        $conditions = array(array('id' => $userid));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmData->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmData->{$userTable}->getDebugInfo()));
            return null;
        }

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmData->{$userTable}->getDebugInfo()));
            return null;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
        foreach ($result as $record) {
            return $record->username;
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
        if (is_null($userTable) || !$email) {
            return null;
        }
        $this->fmdb->setupFMDataAPIforDB_Alt($userTable, 1);
        $conditions = array(array('email' => str_replace('@', '\\@', $email)));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmDataAlt->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAlt->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
            return null;
        }

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
            return null;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
        foreach ($result as $record) {
            return $record->id;
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
        if (is_null($userTable) || $username === 0 || $username === '') {
            return null;
        }
        $this->fmdb->setupFMDataAPIforDB_Alt($userTable, 55555);
        $conditions = array(
            array('username' => str_replace("@", "\\@", $username)),
            array('email' => str_replace("@", "\\@", $username))
        );
        $result = NULL;
        try {
            $result = $this->fmdb->fmDataAlt->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAlt->getSessionToken();
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
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
                $this->fmdb->stringWithoutCredential($className . ': ' .
                    $this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
            return null;
        }
        return $usernameCandidate;
    }

    public function authSupportEmailFromUnifiedUsername(?string $username): ?string
    {
        return null;
    }

    /**
     * @param $groupid
     * @return string|null
     */
    public function authSupportGetGroupNameFromGroupId($groupid): ?string
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable == null) {
            return null;
        }

        $this->fmdb->setupFMDataAPIforDB_Alt($groupTable, 1);
        $conditions = array(array('id' => $groupid));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmDataAlt->{$groupTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAlt->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAlt->{$groupTable}->getDebugInfo()));
            return null;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAlt->{$groupTable}->getDebugInfo()));
        foreach ($result as $record) {
            return $record->groupname;
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
     * @param string|null $groupid
     * @return void
     */
    private function resolveGroup(?string $groupid): void
    {
        $this->fmdb->setupFMDataAPIforDB_Alt($this->dbSettings->getCorrTable(), 1);
        if ($this->firstLevel) {
            $conditions = array(array('user_id' => $groupid));
            $this->firstLevel = false;
        } else {
            $conditions = array(array('group_id' => $groupid));
            $this->belongGroups[] = $groupid;
        }
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmDataAlt->{$this->dbSettings->getCorrTable()}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAlt->getSessionToken();
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(
                    $this->fmdb->fmDataAlt->{$this->dbSettings->getCorrTable()}->getDebugInfo()));
            if (!is_null($result)) {
                foreach ($result as $record) {
                    if (!in_array($record->dest_group_id, $this->belongGroups)) {
                        $this->resolveGroup($record->dest_group_id);
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAlt->{$this->dbSettings->getCorrTable()}->getDebugInfo()));
            return;
        }
    }

    /**
     * @param string $userid
     * @param string $clienthost
     * @param string $hash
     * @return bool
     * @throws Exception
     */
    public function authSupportStoreIssuedHashForResetPassword(string $userid, string $clienthost, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
        $recordId = $this->fmdb->fmDataAuth->{$hashTable}->create(array(
            'hash' => $hash,
            'expired' => $currentDTFormat,
            'clienthost' => $clienthost,
            'user_id' => $userid,
        ));

        if (!is_numeric($recordId)) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(
                    'FileMakerLayout: ' . $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
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
        if (is_null($hashTable)) {
            return false;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
        $conditions = array(array('user_id' => $userid, 'clienthost' => $randdata));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAuth->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
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

        $this->fmdb->setupFMDataAPIforAuth($tableName, 1);
        $conditions = array(array($userField => $user), array($keyField => $keyValue));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmDataAuth->{$tableName}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAuth->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAuth->{$tableName}->getDebugInfo()));
            return null;
        }
        if (!is_array($result)) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAuth->{$tableName}->getDebugInfo()));
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

    /**
     * @param string $userid
     * @param string $hash
     * @return bool
     * @throws Exception
     */
    public function authSupportUserEnrollmentStart(string $userid, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
        $recordId = $this->fmdb->fmDataAuth->{$hashTable}->create(array(
            'hash' => $hash,
            'expired' => IMUtil::currentDTStringFMS(),
            'user_id' => $userid,
        ));

        if (!is_numeric($recordId)) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(
                    'FileMakerLayout: ' . $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return false;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
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
        if (is_null($hashTable) || is_null($userTable)) {
            return null;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable, 1);
        $conditions = array(
            array('hasu' => $hash, 'clienthost' => '=', 'expired' => IMUtil::currentDTStringFMS(3600) . '...')
        );
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmDataAuth->{$hashTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAuth->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
            return null;
        }
        $this->logger->setDebugMessage(
            $this->fmdb->stringWithoutCredential($this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
        foreach ($result as $record) {
            return $record->user_id;
        }
        return null;
    }

    /**
     * @param string $userID
     * @param ?string $password
     * @param ?string $rawPWField
     * @param ?string $rawPW
     * @return string|null
     * @throws Exception
     */
    public function authSupportUserEnrollmentActivateUser(
        string $userID, ?string $password, ?string $rawPWField, ?string $rawPW): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($hashTable) || is_null($userTable)) {
            return null;
        }
        $this->fmdb->setupFMDataAPIforDB_Alt($userTable, 1);
        $conditions = array(array('id' => $userID));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmDataAlt->{$userTable}->query($conditions);
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmDataAlt->getSessionToken();
            }
        } catch (Exception $e) {
            $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                $this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
            return null;
        }
        $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential($result['URL']));
        foreach ($result as $record) {
            $recordId = $record->getRecordId();
            $this->fmdb->setupFMDataAPIforDB_Alt($userTable, 1);
            if (!is_null($rawPWField)) {
                $this->fmdb->fmDataAlt->{$userTable}->update($recordId, array(
                    'hashedpasswd' => $password,
                    $rawPWField => $rawPW,
                ));
            } else {
                $this->fmdb->fmDataAlt->{$userTable}->update($recordId, array(
                    'hashedpasswd' => $password,
                ));
            }
            $result = $this->fmdb->fmDataAlt->{$userTable}->getRecord($recordId);
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage($this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                    $this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
                return null;
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential(
                    $this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
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

    /**
     * @param array $keyValues
     * @return bool
     */
    public function authSupportOAuthUserHandling(array $keyValues): bool
    {
        $this->logger->setErrorMessage("DB_Auth_Handler_FileMaker_DataAPI doesn't support the authSupportOAuthUserHandling method.");
        return false;
    }
}
