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

use DateInterval;
use Datetime;
use Exception;
use INTERMediator\DB\FileMaker_DataAPI;
use INTERMediator\IMUtil;
use INTERMediator\Params;

/**
 * Handles authentication support for FileMaker Data API.
 * Provides challenge/response, password management, authorization, group resolution, and user enrollment
 * using FileMaker as backend. Implements INTER-Mediator's authentication logic for the FileMaker Data API.
 *
 * @property FileMaker_DataAPI $fmdb FileMaker Data API handler instance.
 * @property mixed $dbSettings Settings object for DB configuration (inherited).
 * @property mixed $dbClass Parent DBClass instance (inherited).
 * @property mixed $logger Logger instance for debug and error messages (inherited).
 */
class DB_Auth_Handler_FileMaker_DataAPI extends DB_Auth_Common
{
    /** FileMaker Data API handler instance.
     * @var FileMaker_DataAPI
     */
    protected FileMaker_DataAPI $fmdb;

    /** Array of group IDs the user belongs to (used for group resolution).
     * @var array
     */
    private array $belongGroups;

    /** Indicates if currently at the first level of group resolution.
     * @var bool
     */
    private bool $firstLevel;

    /** Constructor for the handler.
     * @param FileMaker_DataAPI $parent Parent FileMaker_DataAPI instance.
     */
    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->fmdb = $parent;
    }

    /** Stores a challenge for authentication.
     * @param string|null|int $uid User ID.
     * @param string $challenge Challenge string.
     * @param string $clientId Client ID.
     * @param string $prefix Prefix for the challenge.
     * @param bool $alwaysInsert Always insert a new challenge, even if one exists.
     * @return void
     * @throws Exception
     */
    public function authSupportStoreChallenge(string|null|int $uid,
                                              string          $challenge,
                                              string          $clientId,
                                              string          $prefix = "",
                                              bool            $alwaysInsert = false): void
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable);

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
                $this->fmdb->setupFMDataAPIforAuth($hashTable);
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
        $this->fmdb->setupFMDataAPIforAuth($hashTable);
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

    /** Checks a media token for authentication.
     * @param string $uid User ID.
     * @return string|null Media token or null if not found.
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
        $this->fmdb->setupFMDataAPIforAuth($hashTable);
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
            $expiredDT = new DateTime($record->expired); // @phpstan-ignore property.notFound
            $hashValue = $record->hash;  // @phpstan-ignore property.notFound
            $currentDT = new DateTime();
            $seconds = $currentDT->format("U") - $expiredDT->format("U");
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return null;
            }
            return $hashValue;
        }
        return null;
    }

    /** Retrieves a challenge for authentication.
     * @param null|string|int $uid User ID.
     * @param string $clientId Client ID.
     * @param bool $isDelete Delete the challenge after retrieval.
     * @param string $prefix Prefix for the challenge.
     * @param bool $isMulti Allow multiple challenges.
     * @return string|null|array Challenge string or null if not found.
     */
    public function authSupportRetrieveChallenge(null|string|int $uid,
                                                 string          $clientId,
                                                 bool            $isDelete = true,
                                                 string          $prefix = "",
                                                                 $isMulti = false): null|string|array
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return null;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable);
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
                        $this->fmdb->setupFMDataAPIforAuth($hashTable);
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

    /** Removes outdated authentication challenges from the backend.
     * @return bool True if successful, false otherwise.
     */
    public function authSupportRemoveOutdatedChallenges(): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }

        $currentDT = new DateTime();
        $timeValue = $currentDT->format("U");

        $this->fmdb->setupFMDataAPIforAuth($hashTable);
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
            foreach ($result as $record) {
                $recordId = $record->getRecordId();
                $this->fmdb->setupFMDataAPIforAuth($hashTable);
                try {
                    $this->fmdb->fmDataAuth->{$hashTable}->delete($recordId);
                } catch (Exception $e) {
                    $this->logger->setDebugMessage(
                        $this->fmdb->stringWithoutCredential('Delete pperation: ' .
                            $this->fmdb->fmDataAuth->{$hashTable}->getDebugInfo()));
                    return false;
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

    /** Retrieves a hashed password for a user.
     * @param string $username Username.
     * @return string|null Hashed password or null if not found.
     */
    public function authSupportRetrieveHashedPassword(string $username): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable)) {
            return null;
        }

        $this->fmdb->setupFMDataAPIforDB($userTable);
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
            $this->fmdb->setupFMDataAPIforDB($userTable);
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

    /** Creates a new user in the authentication system.
     * @param string $username Username.
     * @param string $hashedpassword Hashed password.
     * @param bool $isSAML SAML authentication flag.
     * @param string|null $ldapPassword LDAP password.
     * @param array|null $attrs Additional attributes.
     * @return bool True if successful, false otherwise.
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
        $this->fmdb->setupFMDataAPIforDB($userTable);
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

    /** Changes a user's password.
     * @param string $username Username.
     * @param string $hashednewpassword New hashed password.
     * @return bool True if successful, false otherwise.
     * @throws Exception
     */
    public function authSupportChangePassword(string $username, string $hashednewpassword): bool
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable)) {
            return false;
        }

        $this->fmdb->setupFMDataAPIforDB($userTable);
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
            $this->fmdb->setupFMDataAPIforDB($userTable);
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
            $this->fmdb->setupFMDataAPIforDB($userTable);
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

    /** Retrieves a user ID from a username.
     * @param string|null $username Username.
     * @return string|null User ID or null if not found.
     */
    public function authSupportGetUserIdFromUsername(?string $username): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || !$username) {
            return null;
        }
        $username = $this->authSupportUnifyUsernameAndEmail($username);
        $this->fmdb->setupFMDataAPIforDB_Alt($userTable);
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
            return $record->id; // @phpstan-ignore property.notFound
        }
        return null;
    }

    /** Retrieves a username from a user ID.
     * @param string|int $userid User ID.
     * @return string|null Username or null if not found.
     */
    public function authSupportGetUsernameFromUserId(string|int $userid): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || !$userid) {
            return null;
        }
        $this->fmdb->setupFMDataAPIforDB($userTable);
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
            return $record->username; // @phpstan-ignore property.notFound
        }
        return null;
    }

    /** Retrieves a user ID from an email address.
     * @param string $email Email address.
     * @return string|null User ID or null if not found.
     */
    public function authSupportGetUserIdFromEmail(string $email): ?string
    {
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || !$email) {
            return null;
        }
        $this->fmdb->setupFMDataAPIforDB_Alt($userTable);
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
            return $record->id; // @phpstan-ignore property.notFound
        }
        return null;
    }

    /** Unifies a username and email (returns unified username or null).
     * @param string|null $username Username.
     * @return string|null Unified username or null if not found.
     */
    public function authSupportUnifyUsernameAndEmail(?string $username): ?string
    {
        if (!$this->dbSettings->getEmailAsAccount() || $this->dbSettings->isDBNative()) {
            return $username;
        }
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable) || $username === '') {
            return null;
        }
        $this->fmdb->setupFMDataAPIforDB_Alt($userTable);
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

    /** Resolves a group and its child groups recursively.
     * @param string|null $groupid Group ID.
     * @return void
     */
    private function resolveGroup(?string $groupid): void
    {
        $this->fmdb->setupFMDataAPIforDB_Alt($this->dbSettings->getCorrTable());
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

    /** Retrieves a group name from a group ID.
     * @param string|int $groupid Group ID.
     * @return string|null Group name or null if not found.
     */
    public function authSupportGetGroupNameFromGroupId(string|int $groupid): ?string
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable == null) {
            return null;
        }

        $this->fmdb->setupFMDataAPIforDB_Alt($groupTable);
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

    /** Retrieves all groups for a user.
     * @param string|null $user User ID or username.
     * @return array Groups for the user.
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
            if ($groupid) {
                $candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
            }
        }
        return $candidateGroups;
    }

    /** @param string $userid
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
        $this->fmdb->setupFMDataAPIforAuth($hashTable);
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

    /** Checks an issued hash for password reset.
     * @param string $userid User ID.
     * @param string $randdata Random data.
     * @param string $hash Hash string.
     * @return bool True if successful, false otherwise.
     */
    public function authSupportCheckIssuedHashForResetPassword(string $userid, string $randdata, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable);
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
            if ($timeValue > $expiredValue + Params::getParameterValue('limitPwChangeSecond', 3600)) {
                return false;
            }
            if ($hash == $hashValue) {
                return true;
            }
            return false;
        }
        return false;
    }

    /** Checks the media privilege for a user in a given table.
     * @param string $tableName Table name.
     * @param string $targeting Targeting value.
     * @param string $userField User field name.
     * @param string $user User value.
     * @param string $keyField Key field name.
     * @param string $keyValue Key value.
     * @return array|null The media privilege data, or null if not found.
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

        $this->fmdb->setupFMDataAPIforAuth($tableName);
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
                $record[$field] = $record->{$field}; // @phpstan-ignore-line
            }
            $array[] = $record;
        }
        return $array;
    }

    /** Starts user enrollment for a user.
     * @param string $userid User ID.
     * @param string $hash Hash string.
     * @return bool True if successful, false otherwise.
     * @throws Exception
     */
    public function authSupportUserEnrollmentStart(string $userid, string $hash): bool
    {
        $hashTable = $this->dbSettings->getHashTable();
        if (is_null($hashTable)) {
            return false;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable);
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

    /** Retrieves the enrolling user for a given hash.
     * @param string $hash Hash string.
     * @return string|null User ID or null if not found.
     */
    public function authSupportUserEnrollmentEnrollingUser(string $hash): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($hashTable) || is_null($userTable)) {
            return null;
        }
        $this->fmdb->setupFMDataAPIforAuth($hashTable);
        $conditions = array(array(
            'hash' => $hash,
            'clienthost' => '=',
            'expired' => IMUtil::currentDTStringFMS(Params::getParameterValue('limitEnrollSecond', 3600)) . '...'
        )
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

    /** Activates a user after enrollment.
     * @param string $userID User ID.
     * @param string|null $password Password.
     * @param string|null $rawPWField Raw password field.
     * @param string|null $rawPW Raw password value.
     * @return string|null User ID or null if not found.
     * @throws Exception
     */
    public function authSupportUserEnrollmentActivateUser(
        string $userID, ?string $password = null, ?string $rawPWField = null, ?string $rawPW = null): ?string
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($hashTable) || is_null($userTable)) {
            return null;
        }
        $this->fmdb->setupFMDataAPIforDB_Alt($userTable);
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
            $this->fmdb->setupFMDataAPIforDB_Alt($userTable);
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

    /** Checks if a user is within the SAML authentication limit.
     * @param string $userID User ID.
     * @return bool True if within the limit, false otherwise.
     */
    public function authSupportIsWithinSAMLLimit(string $userID): bool
    {
        return false;
    }

    /** Checks if the system can migrate SHA256 hash.
     * @return bool True if migration is possible, false otherwise.
     */
    public function authSupportCanMigrateSHA256Hash(): bool  // authuser, issuedhash
    {
        return true;
    }

    /** Handles OAuth user registration or update.
     * @param array $keyValues Key-value pairs for OAuth user handling.
     * @return bool True if successful, false otherwise.
     */
    public function authSupportOAuthUserHandling(array $keyValues): bool
    {
        $this->logger->setErrorMessage("DB_Auth_Handler_FileMaker_DataAPI doesn't support the authSupportOAuthUserHandling method.");
        return false;
    }

    /** Retrieves unified username and email information (array: [UserID, username, hashedpasswd]).
     * @param null|string $userID User ID.
     * @return array Array with three elements: [UserID, username, hashedpasswd].
     */
    public function authSupportUnifyUsernameAndEmailAndGetInfo(?string $userID): array
    {
        if (!$userID) {
            return [null, null, null];
        }
        $userTable = $this->dbSettings->getUserTable();
        if (is_null($userTable)) {
            return [null, null, null];
        }
        $this->fmdb->setupFMDataAPIforDB_Alt($userTable);
        $conditions = [];
        $conditions[] = ['username' => str_replace("@", "\\@", $userID)];
        if ($this->dbSettings->getEmailAsAccount()) {
            $conditions[] = ['email' => str_replace("@", "\\@", $userID)];
        }
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
                if ($record->username == $userID) {
                    $usernameCandidate = $userID;
                }
                if ($record->email == $userID) {
                    $usernameCandidate = $record->username;
                }
                return [$record->id, $usernameCandidate, $record->hashedpasswd];
            }
        } catch (Exception $e) {
            $className = is_object($result) ? get_class($result) : "NULL";
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($className . ': ' .
                    $this->fmdb->fmDataAlt->{$userTable}->getDebugInfo()));
        }
        return [null, null, null];
    }

    /** Retrieves login user information from the authuser table.
     * @param string $userID User ID or username.
     * @return array Array containing [user ID, real name, email, public key, secret].
     * @throws Exception If the user table is not configured, connection fails, or multiple/no users are found.
     */
    public function getLoginUserInfo(string $userID): array
    {
        try {
            if (!$userID) {
                throw new Exception("Invalid user ID: {$userID}");
            }
            $userTable = $this->dbSettings->getUserTable();
            if (is_null($userTable)) {
                throw new Exception("Usertable setting up failed.");
            }
            $user = $this->authSupportUnifyUsernameAndEmail($userID);
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $conditions = array(array('username' => str_replace('@', '\\@', $user)));
            $result = null;
            try {
                $result = $this->fmdb->fmData->{$userTable}->query($conditions);
                if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                    $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
                }
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
            $counter = 0;
            $returnValue = null;
            foreach ($result as $record) {
                $returnValue = [
                    $record->id, // @phpstan-ignore property.notFound
                    $record->realname ?? '', // @phpstan-ignore property.notFound
                    $record->email ?? '', // @phpstan-ignore property.notFound
                    $record->pubkey ?? '', // @phpstan-ignore property.notFound
                    $record->secret ?? '', // @phpstan-ignore property.notFound
                ];
                $counter++;
            }
            if ($counter === 0) {
                throw new Exception("No Information Detected for the Log-in User {$userID}");
            } else if ($counter > 1) {
                throw new Exception("Multiple Users Detected for the Log-in User {$userID}");
            }
            return $returnValue;
        } catch (\Exception $e) {
            $this->fmdb->errorMessageStore("[getLoginUserInfo] ERROR: {$e->getMessage()}");
        }
        return ['', '', '', '', ''];
    }

    /** Stores a public key and its credential ID for a user in the authuser table.
     * @param string $uid The user ID.
     * @param string $publicKey The public key to store.
     * @param string $publicKeyCredentialId The credential ID associated with the public key.
     * @return void
     * @throws Exception If the user ID is invalid, the public key or credential ID is empty, the user table is not configured, or the user record is not found.
     */
    public function authSupportStorePublicKey(string $uid, string $publicKey, string $publicKeyCredentialId): void
    {
        try {
            if (!$uid) {
                throw new Exception("Invalid user ID: {$uid}");
            }
            if (!$publicKey) {
                throw new Exception("The public key is empty.");
            }
            if (!$publicKeyCredentialId) {
                throw new Exception("The credential ID of the public key is empty.");
            }
            $userTable = $this->dbSettings->getUserTable();
            if (is_null($userTable)) {
                throw new Exception("Usertable setting up failed.");
            }
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $conditions = array(array('id' => $uid));
            $result = null;
            try {
                $result = $this->fmdb->fmData->{$userTable}->query($conditions);
                if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                    $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
                }
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
            $counter = 0;
            $recordId = null;
            foreach ($result as $record) {
                $recordId = $record->getRecordId();
                $counter++;
            }
            if ($counter === 0) {
                throw new Exception("No user record for {$uid}");
            } else if ($counter > 1) {
                throw new Exception("Multiple Users Detected from the authuser table {$uid}");
            }
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $this->fmdb->fmData->{$userTable}->update($recordId, array(
                'publicKey' => $publicKey,
                'publicKeyCredentialId' => $publicKeyCredentialId,
            ));
            $result = $this->fmdb->fmData->{$userTable}->getRecord($recordId);
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in UPDATE");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
        } catch (\Exception $e) {
            $this->fmdb->errorMessageStore("[authSupportStorePublicKey] ERROR: {$e->getMessage()}");
        }
    }

    /** Removes the public key and credential ID for a user from the authuser table.
     * @param string $uid The user ID.
     * @return void
     * @throws Exception If the user ID is invalid, the user table is not configured, or the user record is not found.
     */
    public function authSupportRemovePublicKey(string $uid): void
    {
        try {
            if (!$uid) {
                throw new Exception("Invalid user ID: {$uid}");
            }
            $userTable = $this->dbSettings->getUserTable();
            if (is_null($userTable)) {
                throw new Exception("Usertable setting up failed.");
            }
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $conditions = array(array('id' => $uid));
            $result = null;
            try {
                $result = $this->fmdb->fmData->{$userTable}->query($conditions);
                if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                    $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
                }
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
            $counter = 0;
            $recordId = null;
            foreach ($result as $record) {
                $recordId = $record->getRecordId();
                $counter++;
            }
            if ($counter === 0) {
                throw new Exception("No user record for {$uid}");
            } else if ($counter > 1) {
                throw new Exception("Multiple Users Detected from the authuser table {$uid}");
            }
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $this->fmdb->fmData->{$userTable}->update($recordId, array(
                'publicKey' => '',
                'publicKeyCredentialId' => '',
            ));
            $result = $this->fmdb->fmData->{$userTable}->getRecord($recordId);
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in UPDATE");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
        } catch (\Exception $e) {
            $this->fmdb->errorMessageStore("[authSupportRemovePublicKey] ERROR: {$e->getMessage()}");
        }
    }

    /** Retrieves user information from the authuser table by public key credential ID.
     * @param string $pkid The public key credential ID.
     * @return array Array of user information, or empty array if not found.
     * @throws Exception If the public key ID is invalid, the user table is not configured, or multiple users are found.
     */
    public function authSupportUserInfoFromPublickeyId(string $pkid): array
    {
        try {
            if (!$pkid) {
                throw new Exception("Invalid Public Key ID: {$pkid}");
            }
            $userTable = $this->dbSettings->getUserTable();
            if (is_null($userTable)) {
                throw new Exception("Usertable setting up failed.");
            }
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $conditions = array(array('publicKeyCredentialId' => $pkid));
            $result = null;
            try {
                $result = $this->fmdb->fmData->{$userTable}->query($conditions);
                if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                    $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
                }
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
            $counter = 0;
            $resultRow = [];
            foreach ($result as $record) {
                $resultRow = [
                    'id' => $record->id, // @phpstan-ignore property.notFound
                    'username' => $record->username, // @phpstan-ignore property.notFound
                    'realname' => $record->realname, // @phpstan-ignore property.notFound
                    'hashedpasswd' => $record->hashedpasswd, // @phpstan-ignore property.notFound
                    'publicKey' => $record->publicKey, // @phpstan-ignore property.notFound
                ];
                $counter++;
            }
            if ($counter === 0) {
                return [];
            } else if ($counter > 1) {
                throw new Exception("Multiple Users Detected from the authuser table {$pkid}");
            }
            return $resultRow;
        } catch (\Exception $e) {
            $this->fmdb->errorMessageStore("[authSupportUserInfoFromPublickeyId] ERROR: {$e->getMessage()}");
        }
        return [];
    }

    /** Stores or clears the 2FA secret for a user in the authuser table.
     * @param string $uid The user ID.
     * @param string|null $secret The 2FA secret to store, or null to clear it.
     * @return void
     * @throws Exception If the user ID is invalid, the user table is not configured, or the user record is not found.
     */
    public function authSupportStore2FASecret(string $uid, string|null $secret): void
    {
        try {
            if (!$uid) {
                throw new Exception("Invalid user ID: {$uid}");
            }
            $userTable = $this->dbSettings->getUserTable();
            if (is_null($userTable)) {
                throw new Exception("Usertable setting up failed.");
            }
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $conditions = array(array('id' => $uid));
            $result = null;
            try {
                $result = $this->fmdb->fmData->{$userTable}->query($conditions);
                if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                    $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
                }
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
            $counter = 0;
            $recordId = null;
            foreach ($result as $record) {
                $recordId = $record->getRecordId();
                $counter++;
            }
            if ($counter === 0) {
                throw new Exception("No user record for {$uid}");
            } else if ($counter > 1) {
                throw new Exception("Multiple Users Detected from the authuser table {$uid}");
            }
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $this->fmdb->fmData->{$userTable}->update($recordId, array(
                'secret' => $secret ?? '',
            ));
            $result = $this->fmdb->fmData->{$userTable}->getRecord($recordId);
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in Update");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
        } catch (\Exception $e) {
            $this->fmdb->errorMessageStore("[authSupportStore2FASecret] ERROR: {$e->getMessage()}");
        }
    }

    /** Counts authentication failures within a specified time period from the authfail table.
     * @param string $ip The client IP address.
     * @param string|null $username The username, or null to count all failures for the IP.
     * @param int $seconds The time period in seconds to look back.
     * @return int The number of authentication failures within the period.
     * @throws Exception If the database connection fails or the query errors.
     */
    public function authSupportCheckAuthFailCount(string $ip, string|null $username, int $seconds): int
    {
        $counter = 0;
        $failTable = "authfail";
        try {
            $periodBefore = (new DateTime())->modify("-{$seconds} seconds")->format('m/d/Y H:i:s');
            $this->fmdb->setupFMDataAPIforDB($failTable);
            $conditions = array(array('dt' => $periodBefore . '...', 'ip' => $ip));
            if (!is_null($username)) {
                $conditions[0]['username'] = $username;
            }
            $result = null;
            try {
                $result = $this->fmdb->fmData->{$failTable}->query($conditions);// @phpstan-ignore property.notFound
                if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                    $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
                }
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$failTable}->getDebugInfo()));// @phpstan-ignore property.notFound
                throw new Exception("ERROR in SELECT");
            }
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$failTable}->getDebugInfo()));// @phpstan-ignore property.notFound
                throw new Exception("ERROR in SELECT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$failTable}->getDebugInfo()));// @phpstan-ignore property.notFound
            foreach ($result as $record) {
                $counter++;
            }
            $this->logger->setDebugMessage("[authSupportCheckAuthFailCount] counter = {$counter}");
        } catch (\Exception $e) {
            $this->fmdb->errorMessageStore("[authSupportCheckAuthFailCount] ERROR: {$e->getMessage()}");
        }
        return $counter;
    }

    /** Records an authentication failure in the authfail table and removes outdated entries older than one day.
     * @param string $ip The client IP address.
     * @param string $username The username that failed authentication.
     * @return void
     * @throws Exception If the database connection fails or the query errors.
     */
    public function authSupportAddAuthFail(string $ip, string $username): void
    {
        $failTable = "authfail";
        try {
            $this->fmdb->setupFMDataAPIforDB($failTable);
            $oneDayBefore = (new DateTime())->sub(new DateInterval('P1D'))->format('m/d/Y H:i:s');
            $conditions = array(array('dt' => '...' . $oneDayBefore));
            $result = null;
            try {
                $result = $this->fmdb->fmData->{$failTable}->query($conditions);// @phpstan-ignore property.notFound
                if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                    $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
                }
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$failTable}->getDebugInfo()));// @phpstan-ignore property.notFound
                throw new Exception("ERROR in SELECT");
            }
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$failTable}->getDebugInfo()));// @phpstan-ignore property.notFound
                throw new Exception("ERROR in SELECT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$failTable}->getDebugInfo()));// @phpstan-ignore property.notFound
            foreach ($result as $record) {
                $recordId = $record->getRecordId();
                $this->fmdb->setupFMDataAPIforDB($failTable);
                try {
                    $this->fmdb->fmData->{$failTable}->delete($recordId);// @phpstan-ignore property.notFound
                } catch (Exception $e) {
                    $this->logger->setDebugMessage(
                        $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                            $this->fmdb->fmData->{$failTable}->getDebugInfo()));// @phpstan-ignore property.notFound
                    throw new Exception("ERROR in DELETE");
                }
            }
            $currentDT = new DateTime();
            $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
            $this->fmdb->setupFMDataAPIforDB($failTable);
            $recordId = $this->fmdb->fmData->{$failTable}->create(array(// @phpstan-ignore property.notFound
                'ip' => $ip,
                'username' => $username,
                'dt' => $currentDTFormat,
            ));
            if (!is_numeric($recordId)) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(
                        'FileMakerLayout: ' . $this->fmdb->fmData->{$failTable}->getDebugInfo()));// @phpstan-ignore property.notFound
                throw new Exception("ERROR in INSERT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$failTable}->getDebugInfo()));// @phpstan-ignore property.notFound
        } catch (\Exception $e) {
            $this->fmdb->errorMessageStore("[authSupportAddAuthFail] ERROR: {$e->getMessage()}");
        }
    }

    /** Checks if a user is marked as inactive in the authuser table.
     * @param string $uid The user ID.
     * @return bool True if the user is inactive, false otherwise.
     * @throws Exception If the user table is not configured, the database connection fails, or the query errors.
     */
    public function authSupportIsInactive(string $uid): bool
    {
        $returnValue = false;
        try {
            $userTable = $this->dbSettings->getUserTable();
            if (is_null($userTable)) {
                throw new Exception("ERROR in no user table.");
            }
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $conditions = array(array('id' => $uid));
            $result = null;
            try {
                $result = $this->fmdb->fmData->{$userTable}->query($conditions);
                if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                    $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
                }
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
            foreach ($result as $record) {
                $returnValue = $record->inactive; // @phpstan-ignore property.notFound
            }
        } catch (\Exception $e) {
            $this->fmdb->errorMessageStore("[authSupportIsInactive] ERROR: {$e->getMessage()}");
        }
        return $returnValue;
    }

    /** Sets the inactive status for a user in the authuser table.
     * @param string $uid The user ID.
     * @param bool $value The inactive status to set.
     * @return void
     * @throws Exception If the user table is not configured, the database connection fails, or the query errors.
     */
    public function authSupportSetInactive(string $uid, bool $value): void
    {
        try {
            $authUser = $this->authSupportUnifyUsernameAndEmail($uid) ?? "";
            $userTable = $this->dbSettings->getUserTable();
            if (is_null($userTable)) {
                throw new Exception("ERROR in no user table.");
            }
            $userId = $this->authSupportGetUserIdFromUsername($authUser);
            if (is_null($userId)) {
                throw new Exception("User not found: {$uid}");
            }
            $this->fmdb->setupFMDataAPIforDB($userTable);
            $conditions = array(array('id' => $userId));
            $result = null;
            try {
                $result = $this->fmdb->fmData->{$userTable}->query($conditions);
                if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                    $_SESSION['X-FM-Data-Access-Token'] = $this->fmdb->fmData->getSessionToken();
                }
            } catch (Exception $e) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                        $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                throw new Exception("ERROR in SELECT");
            }
            $this->logger->setDebugMessage(
                $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
            foreach ($result as $record) {
                $recordId = $record->getRecordId();
                $this->fmdb->setupFMDataAPIforDB($userTable);
                $this->fmdb->fmData->{$userTable}->update($recordId, array(
                    'inactive' => $value ? 1 : 0,
                ));
                $result = $this->fmdb->fmData->{$userTable}->getRecord($recordId);
                if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                    $this->logger->setDebugMessage(
                        $this->fmdb->stringWithoutCredential(get_class($result) . ': ' .
                            $this->fmdb->fmData->{$userTable}->getDebugInfo()));
                    throw new Exception("ERROR in UPDATE");
                }
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential($this->fmdb->fmData->{$userTable}->getDebugInfo()));
            }
        } catch (\Exception $e) {
            $this->fmdb->errorMessageStore("[authSupportSetInactive] ERROR: {$e->getMessage()}");
        }
    }
}
