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

namespace INTERMediator\DB;

use Exception;
use DateTime;
use DateInterval;
use INTERMediator\DB\Support\Proxy_Auth;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\DB\Support\ProxyVisitors\OperationVisitor;
use INTERMediator\DB\Support\StopOperationException;
use INTERMediator\IMUtil;
use INTERMediator\Locale\IMLocale;
use INTERMediator\Messaging\MessagingProxy;
use INTERMediator\NotifyServer;
use INTERMediator\ServiceServerProxy;
use INTERMediator\Params;

/**
 * Proxy class for INTER-Mediator DB operations.
 * Handles authentication, credential management, and database operation orchestration.
 */
class Proxy extends UseSharedObjects implements Proxy_Interface
{
    use Proxy_Auth;

    /**
     * DBClass instance for authentication DB context.
     * @var DBClass|null
     */
    public ?DBClass $authDbClass = null; // for issuedhash context
    /**
     * User expanded object.
     * @var object|null
     */
    private ?object $userExpanded = null;
    /**
     * Output of processing.
     * @var array|null
     */
    public ?array $outputOfProcessing = null;
    /**
     * Auth user parameter.
     * @var string|null
     */
    public ?string $paramAuthUser = null;
    /**
     * Hashed password.
     * @var string|null
     */
    public ?string $hashedPassword = null;
    /**
     * Response parameter.
     * @var string|null
     */
    public ?string $paramResponse = null;
    /**
     * Response2m parameter.
     * @var string|null
     */
    public ?string $paramResponse2m = null;
    /**
     * Response2 parameter.
     * @var string|null
     */
    public ?string $paramResponse2 = null;
    /**
     * Credential string.
     * @var string|null
     */
    public ?string $credential = null;
    /**
     * Credential for 2FA.
     * @var string|null
     */
    public ?string $credential2FA = null;
    /**
     * Authentication success flag.
     * @var bool
     */
    public bool $authSucceed = false;
    /**
     * Client ID.
     * @var string|null
     */
    public ?string $clientId;
    /**
     * Password hash.
     * @var string|null
     */
    public ?string $passwordHash;
    /**
     * Always generate SHA2 flag.
     * @var bool
     */
    private bool $alwaysGenSHA2;
    /**
     * Original access string.
     * @var string
     */
    private string $originalAccess;
    /**
     * Client sync available flag.
     * @var bool
     */
    public bool $clientSyncAvailable;
    /**
     * Ignore post flag.
     * @var bool
     */
    private bool $ignorePost = false;
    /**
     * Post data.
     * @var array|null
     */
    public ?array $PostData;
    /**
     * Access string.
     * @var string
     */
    public string $access;
    /**
     * Access log level.
     * @var int
     */
    private int $accessLogLevel;
    /**
     * Result for log.
     * @var array
     */
    private array $result4Log = [];
    /**
     * Stop notify and messaging flag.
     * @var bool
     */
    private bool $isStopNotifyAndMessaging = false;
    /**
     * Suppress media token flag.
     * @var bool
     */
    public bool $suppressMediaToken = false;
    /**
     * Migrate SHA1 to 2 flag.
     * @var bool
     */
    public bool $migrateSHA1to2;
    /**
     * Credential cookie domain.
     * @var string|null
     */
    public ?string $credentialCookieDomain;
    /**
     * Activate generator flag.
     * @var bool
     */
    public bool $activateGenerator;
    /**
     * Auth storing string.
     * @var string
     */
    public string $authStoring;
    /**
     * Required 2FA flag.
     * @var bool
     */
    public bool $required2FA;
    /**
     * Digits of 2FA code.
     * @var int
     */
    public int $digitsOf2FACode;
    /**
     * Mail context for 2FA.
     * @var string
     */
    public string $mailContext2FA;
    /**
     * Code for 2FA.
     * @var string
     */
    public string $code2FA = "";
    /**
     * Operation visitor.
     * @var OperationVisitor
     */
    private OperationVisitor $visitor;
    /**
     * Bypass auth flag.
     * @var bool
     */
    public bool $bypassAuth;
    /**
     * Ignore files flag.
     * @var bool
     */
    public bool $ignoreFiles;
    /**
     * Signed user.
     * @var string|null
     */
    public ?string $signedUser = "";
    /**
     * Generated client ID.
     * @var string|null
     */
    public ?string $generatedClientID = null;

    /**
     * Constructor.
     * @param bool $testmode
     * @param bool $noCache
     */
    function __construct(bool $testmode = false, bool $noCache = true)
    {
        $this->PostData = $_POST;
        $this->outputOfProcessing = [];
        if (!$testmode) {
            $cacheMediaAccess = Params::getParameterValue("cacheMediaAccess", false);
            header('Content-Type: text/javascript;charset="UTF-8"');
            if (!$noCache && $cacheMediaAccess) {
                $dt = (new DateTime('UTC'))->add(DateInterval::createFromDateString('1 month'));
                header("Expires: {$dt->format('D, d M Y H:i:s \G\M\T')}");
            } else {
                header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
                header('Expires: 0');
            }
            header('X-Content-Type-Options: nosniff');
            $util = new IMUtil();
            $util->outputSecurityHeaders();
        }
    }

    /**
     * Set client ID for test.
     * @param string $cid
     * @return void
     */
    public function setClientId_forTest(string $cid): void // For testing
    {
        $this->clientId = $cid;
    }

    /**
     * Set hashed password for test.
     * @param string $hpw
     * @return void
     */
    public function setHashedPassword_forTest(string $hpw): void // For testing
    {
        $this->hashedPassword = $hpw;
    }

    /**
     * Set param response for test.
     * @param $res
     * @return void
     */
    public function setParamResponse($res): void // For testing, $res could be an array or a string
    {
        if (is_array($res)) {
            $this->paramResponse = $res[0] ?? null;
            $this->paramResponse2m = $res[1] ?? null;
            $this->paramResponse2 = $res[2] ?? null;
        } else {
            $this->paramResponse = $res;
        }
    }

    /**
     * Get default key.
     * @return string|null
     */
    public static function defaultKey(): ?string
    {
        trigger_error("Don't call the static method defaultKey of Proxy class.");
        return null;
    }

    /**
     * Get default key.
     * @return string|null
     */
    public function getDefaultKey(): ?string
    {
        return $this->dbClass->specHandler->getDefaultKey();
    }

    /**
     * Set stop notify and messaging.
     * @return void
     */
    public function setStopNotifyAndMessaging(): void
    {
        $this->isStopNotifyAndMessaging = true;
    }

    /**
     * Add output data.
     * @param string $key
     * @param $value
     * @return void
     */
    public function addOutputData(string $key, $value): void // $value could be an array or a string.
    {
        if (!isset($this->outputOfProcessing[$key])) {
            $this->outputOfProcessing[$key] = $value;
        } else if (is_array($this->outputOfProcessing[$key])) {
            if (is_array($value)) {
                $this->outputOfProcessing[$key] = array_merge($this->outputOfProcessing[$key], $value);
            } else {
                $this->outputOfProcessing[$key][] = $value;
            }
        } else if (is_string($this->outputOfProcessing[$key])) {
            $this->outputOfProcessing[$key] .= $value;
        } else {
            $this->outputOfProcessing[$key] = $this->outputOfProcessing[$key] . $value;
        }
    }

    /**
     * Export output data as JSON.
     * @return void
     */
    public function exportOutputDataAsJSON(): void
    {
        $jsonOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
        if (IMUtil::phpVersion() >= 7.2) {
            $jsonOptions |= JSON_INVALID_UTF8_IGNORE;
        }
        $jsonString = json_encode($this->outputOfProcessing, $jsonOptions);
        if ($jsonString === false) {
            echo json_encode(['errorMessages' => ['json_encode function failed by: ' . json_last_error_msg()]]);
            return;
        }
        echo $jsonString;
    }

    /**
     * Export output data as Jason.
     * @return void
     */
    public function exportOutputDataAsJason(): void
    {
        $this->exportOutputDataAsJSON();
    }

    /**
     * Get result for log.
     * @return array|null
     */
    public function getResultForLog(): ?array
    {
        if ($this->accessLogLevel < 1) {
            return [];
        }
        $setToArray = function ($k, $v = null) {
            if (!is_null($v)) {
                $this->result4Log[$k] = $v;
            } else if (isset($this->outputOfProcessing[$k])) {
                $this->result4Log[$k] = $this->outputOfProcessing[$k];
            }
        };
        if (isset($this->outputOfProcessing['dbresult']) && is_array($this->outputOfProcessing['dbresult'])) {
            $count = count($this->outputOfProcessing['dbresult']);
            $setToArray('dbresult', "Query result includes {$count} records.");
        }
        $setToArray('resultCount');
        $setToArray('totalCount');
        $setToArray('newRecordKeyValue');
        $setToArray('changePasswordResult');
        $setToArray('getRequireAuthorization', $this->dbSettings->getRequireAuthorization());
        $setToArray('challenge');
        $setToArray('clientid');
        $setToArray('requireAuth', $this->dbSettings->getRequireAuthentication());
        return $this->result4Log;
    }

    /**
     * Read from DB.
     * @return array|null
     */
    public function readFromDB(): ?array
    {
        $result = null;
        $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
        try {
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeReadFromDB")) {
                $className = get_class((object)$this->userExpanded);
                $this->logger->setDebugMessage("The method 'doBeforeReadFromDB' of the class '{$className}' is calling.", 2);
                $returnBefore = $this->userExpanded->doBeforeReadFromDB();
                if ($returnBefore === false) {
                    throw new StopOperationException("[Proxy::readFromDB] The method 'doBeforeReadFromDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return null; // Silent stop
                    }
                    throw new StopOperationException($returnBefore); // Just message as error.
                }
            }

            if ($this->dbClass) {
                $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                if (isset($tableInfo['soft-delete'])) {
                    $delFlagField = 'delete';
                    if (is_string($tableInfo['soft-delete'])) {
                        $delFlagField = $tableInfo['soft-delete'];
                    }
                    $this->softDeleteActivate($delFlagField, 1);
                    $this->logger->setDebugMessage(
                        "The soft-delete applies to this query with '{$delFlagField}' field.", 2);
                }
                $result = $this->dbClass->readFromDB();
                if (is_null($result)) {
                    throw new Exception('[Proxy::readFromDB] Read operation failed.');
                }
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterReadFromDB")) {
                $className = get_class($this->userExpanded);
                $this->logger->setDebugMessage("The method 'doAfterReadFromDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterReadFromDB($result);
            }

            if ($this->dbSettings->notifyServer
                && $this->clientSyncAvailable
                && isset($currentDataSource['sync-control'])) {
                $this->outputOfProcessing['registeredid'] = $this->dbSettings->notifyServer->register(
                    $this->dbClass->notifyHandler->queriedEntity(),
                    $this->dbClass->notifyHandler->queriedCondition(),
                    $this->dbClass->notifyHandler->queriedPrimaryKeys()
                );
            }
            // Messaging
            $msgEntry = $currentDataSource['send-mail'] ?? ($currentDataSource['messaging'] ?? null);
            if ($msgEntry) {
                $msgArray = $msgEntry['load'] ?? ($msgEntry['read'] ?? null);
                if ($msgArray) {
                    $this->logger->setDebugMessage("Try to send a message.", 2);
                    $driver = $msgEntry['driver'] ?? "mail";
                    $msgProxy = new MessagingProxy($driver);
                    $msgProxy->processing($this, $msgArray, $result);
                }
            }
        } catch (StopOperationException $e) {
            $this->logger->setWarningMessage($e->getMessage());
            return null;
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[1] {$e->getMessage()} \nTrace:{$e->getTraceAsString()}");
            return null;
        }
        return $result;
    }

    /**
     * Count query result.
     * @return int
     */
    public function countQueryResult(): int
    {
        $result = null;
        if ($this->userExpanded && method_exists($this->userExpanded, "countQueryResult")) {
            $className = get_class($this->userExpanded);
            $this->logger->setDebugMessage("The method 'countQueryResult' of the class '{$className}' is calling.", 2);
            $result = $this->userExpanded->countQueryResult();
        }
        if ($this->dbClass) {
            $result = $this->dbClass->countQueryResult();
        }
        return $result;
    }

    /**
     * Get total count.
     * @return int
     */
    public function getTotalCount(): int
    {
        $result = null;
        if ($this->userExpanded && method_exists($this->userExpanded, "getTotalCount")) {
            $className = get_class($this->userExpanded);
            $this->logger->setDebugMessage("The method 'getTotalCount' of the class '{$className}' is calling.", 2);
            $result = $this->userExpanded->getTotalCount();
        }
        if ($this->dbClass) {
            $result = $this->dbClass->getTotalCount();
        }
        return $result;
    }

    /**
     * Update DB.
     * @param bool $bypassAuth
     * @return bool
     */
    public function updateDB(bool $bypassAuth): bool
    {
        $result = null;
        $resultOfUpdate = null;
        $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
        try {
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeUpdateDB")) {
                $className = get_class((object)$this->userExpanded);
                $this->logger->setDebugMessage(
                    "[Proxy::updateDB] The method 'doBeforeUpdateDB' of the class '{$className}' is calling.", 2);
                $returnBefore = $this->userExpanded->doBeforeUpdateDB(false);
                if ($returnBefore === false) {
                    throw new StopOperationException("[Proxy::updateDB] The method 'doBeforeUpdateDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return false; // Silent stop
                    }
                    throw new StopOperationException($returnBefore); // Just message as error.
                }
            }
            if ($this->dbClass) {
                $this->dbClass->requireUpdatedRecord(true); // Always Get Updated Record
                $resultOfUpdate = $this->dbClass->updateDB($bypassAuth);
                if (!$resultOfUpdate) {
                    throw new Exception('[Proxy::updateDB] Update operation failed.');
                }
                $result = $this->getUpdatedRecord();
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterUpdateToDB")) {
                $className = get_class((object)$this->userExpanded);
                $this->logger->setDebugMessage(
                    "[Proxy::updateDB] The method 'doAfterUpdateToDB' of the class '{$className}' is calling.", 2);
                $this->clearUseSetDataToUpdatedRecord();
                $result = $this->userExpanded->doAfterUpdateToDB($result);
                if (!$this->getUseSetDataToUpdatedRecord()) {
                    $this->setUpdatedRecord($result);
                }
            }
//            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterUpdateToDBMod")) {
//                $this->logger->setDebugMessage(
//                    "[Proxy::updateDB] The method 'doAfterUpdateToDBMod' of the class '{$className}' is calling.", 2);
//                $result = $this->userExpanded->doAfterUpdateToDBMod($result);
//                $this->setUpdatedRecord($result);
//            }
            if ($this->dbSettings->notifyServer
                && $this->clientSyncAvailable
                && isset($currentDataSource['sync-control'])
                && strpos(strtolower($currentDataSource['sync-control']), 'update') !== false) {
                $this->dbSettings->notifyServer->updated(
                    $this->PostData['notifyid'] ?? null,
                    $this->dbClass->notifyHandler->queriedEntity(),
                    $this->dbClass->notifyHandler->queriedPrimaryKeys(),
                    $this->dbSettings->getFieldsRequired(),
                    $this->dbSettings->getValue(),
                    strpos(strtolower($currentDataSource['sync-control']), 'update-notify') !== false
                );
            }
            // Messaging
            $msgEntry = $currentDataSource['send-mail'] ?? ($currentDataSource['messaging'] ?? null);
            if ($msgEntry) {
                $msgArray = $msgEntry['edit'] ?? ($msgEntry['update'] ?? null);
                if ($msgArray) {
                    $this->logger->setDebugMessage("Try to send a message.", 2);
                    $driver = $msgEntry['driver'] ?? "mail";
                    $msgProxy = new MessagingProxy($driver);
                    $msgProxy->processing($this, $msgArray, $this->dbClass->getUpdatedRecord());
                }
            }
        } catch (StopOperationException $e) {
            $this->logger->setWarningMessage($e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[2] {$e->getMessage()}");
            return false;
        }
        return $resultOfUpdate;
    }

    /**
     * Create in DB.
     * @param bool $isReplace
     * @return string|null
     */
    public function createInDB(bool $isReplace = false): ?string
    {
        $result = null;
        $resultOfCreate = null;
        $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
        try {
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeCreateToDB")) {
                $className = get_class((object)$this->userExpanded);
                $this->logger->setDebugMessage(
                    "[Proxy::createInDB] The method 'doBeforeCreateToDB' of the class '{$className}' is calling.", 2);
                $returnBefore = $this->userExpanded->doBeforeCreateToDB();
                if ($returnBefore === false) {
                    throw new StopOperationException("[Proxy::createInDB] The method 'doBeforeCreateToDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return null; // Silent stop
                    }
                    throw new StopOperationException($returnBefore); // Just message as error.
                }
            }
            if ($this->dbClass) {
                $this->dbClass->requireUpdatedRecord(true); // Always Requred Created Record
                $resultOfCreate = $this->dbClass->createInDB($isReplace);
                if (!$resultOfCreate) {
                    throw new Exception('[Proxy::createInDB] Create operation failed.');
                }
                $result = $this->getUpdatedRecord();
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterCreateToDB")) {
                $className = get_class((object)$this->userExpanded);
                $this->logger->setDebugMessage(
                    "[Proxy::createInDB] The method 'doAfterCreateToDB' of the class '{$className}' is calling.", 2);
                $this->clearUseSetDataToUpdatedRecord();
                $result = $this->userExpanded->doAfterCreateToDB($result);
                if (!$this->getUseSetDataToUpdatedRecord()) {
                    $this->setUpdatedRecord($result);
                }
            }
            if (!$this->isStopNotifyAndMessaging) {
                if ($this->dbSettings->notifyServer
                    && $this->clientSyncAvailable
                    && isset($currentDataSource['sync-control'])
                    && strpos(strtolower($currentDataSource['sync-control']), 'create') !== false) {
                    $this->dbSettings->notifyServer->created(
                        $this->PostData['notifyid'] ?? null,
                        $this->dbClass->notifyHandler->queriedEntity(),
                        $this->dbClass->notifyHandler->queriedPrimaryKeys(),
                        $currentDataSource['key'],
                        $result,
                        strpos(strtolower($currentDataSource['sync-control']), 'create-notify') !== false
                    );
                }
                // Messaging
                $msgEntry = $currentDataSource['send-mail'] ?? ($currentDataSource['messaging'] ?? null);
                if ($msgEntry) {
                    $msgArray = $msgEntry['new'] ?? ($msgEntry['create'] ?? null);
                    if ($msgArray) {
                        $this->logger->setDebugMessage("Try to send a message.", 2);
                        $driver = $msgEntry['driver'] ?? "mail";
                        $msgProxy = new MessagingProxy($driver);
                        $msgProxy->processing($this, $msgArray, $result);
                    }
                }
            }
        } catch (StopOperationException $e) {
            $this->logger->setWarningMessage($e->getMessage());
            return null;
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[3] {$e->getMessage()}");
            return null;
        }
        return $resultOfCreate;
    }

    /**
     * Delete from DB.
     * @return bool
     */
    public function deleteFromDB(): bool
    {
        $result = null;
        try {
            $className = is_null($this->userExpanded) ? "" : get_class((object)$this->userExpanded);
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeDeleteFromDB")) {
                $this->logger->setDebugMessage("[Proxy::deleteFromDB] The method 'doBeforeDeleteFromDB' of the class '{$className}' is calling.", 2);
                $returnBefore = $this->userExpanded->doBeforeDeleteFromDB();
                if ($returnBefore === false) {
                    throw new StopOperationException("[Proxy::deleteFromDB] The method 'doBeforeDeleteFromDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return false; // Silent stop
                    }
                    throw new StopOperationException($returnBefore); // Just message as error.
                }
            }
            if ($this->dbClass) {
                $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                if (isset($tableInfo['soft-delete'])) {
                    $delFlagField = is_string($tableInfo['soft-delete']) ? $tableInfo['soft-delete'] : 'delete';
                    $this->logger->setDebugMessage(
                        "[Proxy::deleteFromDB] The soft-delete applies to this delete operation with '{$delFlagField}' field.", 2);
                    $this->dbSettings->addValueWithField($delFlagField, 1);
                    $result = $this->dbClass->updateDB(false);
                } else {
                    $result = $this->dbClass->deleteFromDB();
                }
                if (!$result) {
                    throw new Exception('[Proxy::deleteFromDB] Delete operation failed.');
                }
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterDeleteFromDB")) {
                $this->logger->setDebugMessage("[Proxy::deleteFromDB] The method 'doAfterDeleteFromDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterDeleteFromDB($result);
            }
            $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
            if ($this->dbSettings->notifyServer
                && $this->clientSyncAvailable
                && isset($currentDataSource['sync-control'])
                && strpos(strtolower($currentDataSource['sync-control']), 'delete') !== false) {
                $this->dbSettings->notifyServer->deleted(
                    $this->PostData['notifyid'] ?? null,
                    $this->dbClass->notifyHandler->queriedEntity(),
                    $this->dbClass->notifyHandler->queriedPrimaryKeys()
                );
            }
        } catch (StopOperationException $e) {
            $this->logger->setWarningMessage($e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[4] {$e->getMessage()}");
            return false;
        }
        return $result;
    }

    /**
     * Copy in DB.
     * @return string|null
     */
    public function copyInDB(): ?string
    {
        $result = null;
        $resultOfCopy = null;
        $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
        try {
            $className = is_null($this->userExpanded) ? "" : get_class($this->userExpanded);
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeCopyInDB")) {
                $this->logger->setDebugMessage("[Proxy::copyInDB] The method 'doBeforeCopyInDB' of the class '{$className}' is calling.", 2);
                $returnBefore = $this->userExpanded->doBeforeCopyInDB();
                if ($returnBefore === false) {
                    throw new StopOperationException("[Proxy::copyInDB] The method 'doBeforeCopyInDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return null; // Silent stop
                    }
                    throw new StopOperationException($returnBefore); // Just message as error.
                }
            }
            if ($this->dbClass) {
                $this->dbClass->requireUpdatedRecord(true); // Always Requred Copied Record
                $resultOfCopy = $this->dbClass->copyInDB();
                if (!$resultOfCopy) {
                    throw new Exception('[Proxy::copyInDB] Copy operation failed.');
                }
                $result = $this->getUpdatedRecord();
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterCopyInDB")) {
                $this->logger->setDebugMessage("[Proxy::copyInDB] The method 'doAfterCopyInDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterCopyInDB($result);
            }
            if ($this->dbSettings->notifyServer
                && $this->clientSyncAvailable
                && isset($currentDataSource['sync-control'])
                && strpos(strtolower($currentDataSource['sync-control']), 'create') !== false) {
                $this->dbSettings->notifyServer->created(
                    $this->PostData['notifyid'] ?? null,
                    $this->dbClass->notifyHandler->queriedEntity(),
                    $this->dbClass->notifyHandler->queriedPrimaryKeys(),
                    $currentDataSource['key'],
                    $result,
                    strpos(strtolower($currentDataSource['sync-control']), 'create-notify') !== false
                );
            }
        } catch (StopOperationException $e) {
            $this->logger->setWarningMessage($e->getMessage());
            return null;
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[5] {$e->getMessage()}");
            return null;
        }
        return $resultOfCopy;
    }

    /**
     * Get field info.
     * @param string $dataSourceName
     * @return array|null
     */
    public function getFieldInfo(string $dataSourceName): ?array
    {
        return $this->dbClass ? $this->dbClass->getFieldInfo($dataSourceName) : null;
    }

    /**
     * Ignore post.
     * @return void
     */
    public function ignoringPost()
    {
        $this->ignorePost = true;
    }

    /**
     * Ignore post.
     * @return void
     */
    public function ignorePost()
    {
        $this->ignorePost = true;
    }

    /**
     * Initialize.
     * @param array|null $dataSource
     * @param array|null $options
     * @param array|null $dbSpec
     * @param int|null $debug
     * @param string|null $target
     * @return bool
     * @throws Exception
     */
    public function initialize(?array $dataSource, ?array $options, ?array $dbSpec, ?int $debug, ?string $target = null): bool
    {
        $this->PostData = $this->ignorePost ? array() : $_POST;
        $this->setUpSharedObjects();
        $this->logger->setDebugMessage("Start to initialize the DB\Proxy class instance.", 2);
        $this->accessLogLevel = intval(Params::getParameterValue('accessLogLevel', false));
        $this->clientSyncAvailable = boolval(Params::getParameterValue("activateClientService", false));
        $this->activateGenerator = Params::getParameterValue('activateGenerator', false);

        $this->dbSettings->setDataSource($dataSource);
        $this->dbSettings->setOptions($options);
        IMLocale::$options = $options;
        $this->dbSettings->setDbSpec($dbSpec);

        $this->dbSettings->setSeparator($options['separator'] ?? '@');
        $this->formatter->setFormatter($options['formatter'] ?? null);
        $this->dbSettings->setDataSourceName(!is_null($target) ? $target : ($this->PostData['name'] ?? "_im_auth"));
        $context = $this->dbSettings->getDataSourceTargetArray();
        if (count($_FILES) > 0) {
            $this->dbSettings->setAttachedFiles($context['name'], $_FILES);
        }

        $dbClassName = '\\INTERMediator\\DB\\' .
            ($context['db-class'] ?? ($dbSpec['db-class'] ?? Params::getParameterValue('dbClass', '')));
        $this->dbSettings->setDbSpecServer(
            $context['server'] ?? ($dbSpec['server'] ?? Params::getParameterValue('dbServer', '')));
        $this->dbSettings->setDbSpecPort(
            $context['port'] ?? ($dbSpec['port'] ?? Params::getParameterValue('dbPort', '')));
        $this->dbSettings->setDbSpecUser(IMUtil::getFromProfileIfAvailable(
            $context['user'] ?? ($dbSpec['user'] ?? Params::getParameterValue('dbUser', ''))));
        $this->dbSettings->setDbSpecPassword(IMUtil::getFromProfileIfAvailable(
            $context['password'] ?? ($dbSpec['password'] ?? Params::getParameterValue('dbPassword', ''))));
        $this->dbSettings->setDbSpecDataType(
            $context['datatype'] ?? ($dbSpec['datatype'] ?? Params::getParameterValue('dbDataType', '')));
        $this->dbSettings->setDbSpecDatabase(
            $context['database'] ?? ($dbSpec['database'] ?? Params::getParameterValue('dbDatabase', '')));
        $this->dbSettings->setDbSpecProtocol(
            $context['protocol'] ?? ($dbSpec['protocol'] ?? Params::getParameterValue('dbProtocol', '')));
        $this->dbSettings->setDbSpecOption(
            $context['option'] ?? ($dbSpec['option'] ?? Params::getParameterValue('dbOption', '')));
        $this->dbSettings->setCertVerifying(
            $context['cert-verifying'] ?? ($dbSpec['cert-verifying'] ?? Params::getParameterValue('certVerifying', true)));
        if (isset($options['authentication']['issuedhash-dsn'])) {
            $this->dbSettings->setDbSpecDSN($options['authentication']['issuedhash-dsn']);
        } else {
            $this->dbSettings->setDbSpecDSN(
                $context['dsn'] ?? ($dbSpec['dsn'] ?? Params::getParameterValue('dbDSN', '')));
        }

        /* Setup Database Class's Object */
        $isDBClassNull = is_null($this->dbClass);
        if ($isDBClassNull) {
            $this->dbClass = new $dbClassName();
            if ($this->dbClass == null) {
                $this->logger->setErrorMessage("The database class [{
                $dbClassName}] that you specify is not valid.");
                echo implode('', $this->logger->getMessagesForJS());
                return false;
            }
            $this->logger->setDebugMessage("The class '{$dbClassName}' was instantiated . ", 2);
        }

        $this->dbClass->setUpSharedObjects($this);
        if ($isDBClassNull) {
            if ($this->activateGenerator) { // In case of Generator activated
                try {
                    $originalDSN = $this->dbSettings->getDbSpecDSN();
                    $this->logger->setDebugMessage("[Schema Generator]originalDSN " . var_export($originalDSN, true), 2);
                    $generator = new Generator($this);
                    $this->dbSettings->setDbSpecDSN($generator->generateDSN(true));
                    if (!$this->dbClass->setupConnection()) { // Connection without dbname
                        return false;
                    }
                    $this->dbClass->setupHandlers();
                    $generator->prepareDatabase(); // If the database doesn't exist, it's going to create here.
                    $this->dbSettings->setDbSpecDSN($originalDSN);
                    if (!$this->dbClass->setupConnection()) { // Recreating database connection
                        return false;
                    }
                } catch (Exception $ex) { // Catching the exception within Generator class.
                    return false;
                }
            } else { // Here is normal operations for Database Class.
                if (!$this->dbClass->setupConnection()) {
                    return false;
                }
            }
            $this->dbClass->setupHandlers();
        }
        if (!Params::getParameterValue('prohibitDebugMode', false) && $debug) {
            $this->logger->setDebugMode($debug);
        }
        $this->dbSettings->setAggregationSelect($context['aggregation-select'] ?? null);
        $this->dbSettings->setAggregationFrom($context['aggregation-from'] ?? null);
        $this->dbSettings->setAggregationGroupBy($context['aggregation-group-by'] ?? null);

        $this->collectAuthInfo($options); // Calling the method in the Support\Proxy_Auth trait.

        $this->dbSettings->notifyServer = null;
        if ($this->clientSyncAvailable) {
            $this->dbSettings->notifyServer = new NotifyServer();
            $nid = $this->PostData['notifyid'] ?? null;
            if ($this->dbSettings->notifyServer->initialize($this->authDbClass, $nid)) {
                $this->logger->setDebugMessage("The NotifyServer was instantiated . ", 2);
            } else {
                $this->logger->setDebugMessage("The NotifyServer failed to initialize . ", 2);
                $this->dbSettings->notifyServer = null;
            }
        }

        $this->dbSettings->setCurrentDataAccess($this->dbClass);

        if (isset($context['extending-class'])) {
            $className = $context['extending-class'];
            try {
                $this->userExpanded = new $className();
                $this->logger->setDebugMessage("The class '{$className}' was instantiated . ", 2);
            } catch (Exception $e) {
                $this->logger->setErrorMessage("The class '{$className}' wasn't instantiated.");
            }
            if (is_subclass_of($this->userExpanded, '\INTERMediator\DB\UseSharedObjects')) {
                $this->userExpanded->setUpSharedObjects($this);
            }
        }
        $this->dbSettings->setPrimaryKeyOnly(isset($this->PostData['pkeyonly']));

        $this->dbSettings->setCurrentUser($this->PostData['authuser'] ?? null);
        $this->dbSettings->setAuthentication($options['authentication'] ?? null);

        $this->dbSettings->setStart($this->PostData['start'] ?? 0);
        $this->dbSettings->setRecordCount($this->PostData['records'] ?? 10000000);

        for ($count = 0; $count < 10000; $count++) {
            if (isset($this->PostData["condition{$count}field"])) {
                $this->dbSettings->addExtraCriteria(
                    $this->PostData["condition{$count}field"],
                    $this->PostData["condition{$count}operator"] ?? '=',
                    $this->PostData["condition{$count}value"] ?? null);
            } else {
                break;
            }
        }
        for ($count = 0; $count < 10000; $count++) {
            if (isset($this->PostData["sortkey{$count}field"])) {
                $this->dbSettings->addExtraSortKey($this->PostData["sortkey{$count}field"], $this->PostData["sortkey{$count}direction"]);
            } else {
                break;
            }
        }
        for ($count = 0; $count < 10000; $count++) {
            if (!isset($this->PostData["foreign{$count}field"])) {
                break;
            }
            $this->dbSettings->addForeignValue(
                $this->PostData["foreign{$count}field"],
                $this->PostData["foreign{$count}value"]);
        }

        for ($i = 0; $i < 1000; $i++) {
            if (!isset($this->PostData["field_{$i}"])) {
                break;
            }
            $this->dbSettings->addTargetField($this->PostData["field_{$i}"]);
        }
        for ($i = 0; $i < 1000; $i++) {
            if (!isset($this->PostData["value_{$i}"])) {
                break;
            }
            $value = IMUtil::removeNull(filter_var($this->PostData["value_{$i}"]));
            $this->dbSettings->addValue($value);
        }
        for ($i = 0; $i < 1000; $i++) {
            if (!isset($this->PostData["assoc{$i}"])) {
                break;
            }
            $this->dbSettings->addAssociated($this->PostData["assoc{$i}"], $this->PostData["asfield{$i}"], $this->PostData["asvalue{$i}"]);
        }

        if (isset($options['smtp'])) {
            $this->dbSettings->setSmtpConfiguration($options['smtp']);
        } else {
            $this->dbSettings->setSmtpConfiguration(Params::getParameterValue('sendMailSMTP', null));
        }

        $this->dbSettings->setClientTZOffset($this->PostData['tzoffset'] ?? 0);
        $this->dbSettings->setParentOfTarget($this->PostData['parent'] ?? '');

        return true;
    }

    /**
     * Processing request.
     * @param string|null $access
     * @param bool $bypassAuth
     * @param bool $ignoreFiles
     * @throws Exception
     */
    public function processingRequest(?string $access = null, bool $bypassAuth = false, bool $ignoreFiles = false): void
    {
        $this->logger->setDebugMessage("[processingRequest]", 2);
        $this->bypassAuth = $bypassAuth;
        $this->ignoreFiles = $ignoreFiles;
        $this->suppressMediaToken = true;

        $this->access = is_null($access) ? $this->PostData['access'] : $access;
        $this->access = (($this->access == "select") || ($this->access == "load")) ? "read" : $this->access;
        $this->originalAccess = $this->access;
        $this->logger->setDebugMessage("[processingRequest] decided access={$this->access}", 2);
        $this->access = $this->aggregationJudgement($this->access);

        $visitorClasName = IMUtil::getVisitorClassName($this->access);
        $this->visitor = new $visitorClasName($this);
        $this->authenticationAndAuthorization(); // Calling the method in the Support\Proxy_Auth trait.
        (new DataOperationElement())->acceptDataOperation($this->visitor);
        $this->checkIrrelevantFields($this->access); // Working only for debug mode.
    }

    /**
     * Finish communication.
     * @param bool $notFinish
     */
    function finishCommunication(bool $notFinish = false): void
    {
        $this->logger->setDebugMessage(
            "[finishCommunication]getRequireAuthorization={$this->dbSettings->getRequireAuthorization()}", 2);
        $this->outputOfProcessing['usenull'] = false;
        if ($this->dbClass->getSortKeys()) {
            $this->addOutputData('sortKeys', $this->dbClass->getSortKeys());
        }
        if (!$notFinish && $this->dbSettings->getRequireAuthorization()) {
            (new HandleChallengeElement())->acceptHandleChallenge($this->visitor);
            $this->handleMediaToken(); // Calling the method in the Support\Proxy_Auth trait.
        }
        $this->outputOfProcessing['clientid'] = $this->generatedClientID;
        $this->outputOfProcessing['requireAuth'] = $this->dbSettings->getRequireAuthentication();
        $this->outputOfProcessing['authUser'] = $this->dbSettings->getCurrentUser();
        $this->addOutputData('errorMessages', $this->logger->getErrorMessages());
        $this->addOutputData('warningMessages', $this->logger->getWarningMessages());
        $this->addOutputData('debugMessages', $this->logger->getDebugMessages());
        $this->logger->clearLogs();
    }

    /**
     * Get database result.
     * @return array|null
     */
    public function getDatabaseResult(): ?array
    {
        if (isset($this->outputOfProcessing['dbresult'])) {
            return $this->outputOfProcessing['dbresult'];
        }
        return null;
    }

    /**
     * Get database result count.
     * @return int
     */
    public function getDatabaseResultCount(): int
    {
        if (isset($this->outputOfProcessing['resultCount'])) {
            return $this->outputOfProcessing['resultCount'];
        }
        return 0;
    }

    /**
     * Get database total count.
     * @return int
     */
    public function getDatabaseTotalCount(): int
    {
        if (isset($this->outputOfProcessing['totalCount'])) {
            return $this->outputOfProcessing['totalCount'];
        }
        return 0;
    }

    /**
     * Get database new record key.
     * @return string|null
     */
    public function getDatabaseNewRecordKey(): ?string
    {
        if (isset($this->outputOfProcessing['newRecordKeyValue'])) {
            return $this->outputOfProcessing['newRecordKeyValue'];
        }
        return null;
    }

    /**
     * Change password.
     * @param string $username
     * @param string $newpassword
     * @return bool
     */
    function changePassword(string $username, string $newpassword): bool
    {
        return $this->dbClass->authHandler->authSupportChangePassword($username, $newpassword);
    }

    /**
     * Reset password sequence start.
     * @param string $email
     * @return array|null
     */
    public function resetPasswordSequenceStart(string $email): ?array
    {
        if ($email == '') { // checked also is null or is false
            return null;
        }
        $userid = $this->dbClass->authHandler->authSupportGetUserIdFromEmail($email);
        $username = $this->dbClass->authHandler->authSupportGetUsernameFromUserId($userid);
        if (is_null($userid) || is_null($username)) { // checked also is null or is false
            return null;
        }
        $clienthost = IMUtil::generateChallenge();
        $hash = sha1($clienthost . $email . $username);
        if ($this->authDbClass->authHandler->authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)) {
            return array('randdata' => $clienthost, 'username' => $username);
        }
        return null;
    }

    /**
     * Reset password sequence return back.
     * @param string|null $username
     * @param string|null $email
     * @param string $randdata
     * @param string $newpassword
     * @return bool
     */
    public function resetPasswordSequenceReturnBack(?string $username, ?string $email, string $randdata, string $newpassword): bool
    {
        $userid = null;
        if (is_null($username) && !is_null($email)) {
            $userid = $this->dbClass->authHandler->authSupportGetUserIdFromEmail($email);
            if (!$userid) {
                return false;
            }
            $username = $this->dbClass->authHandler->authSupportGetUsernameFromUserId($userid);
        }
        if ($email == '' || is_null($userid) || is_null($username)) {
            return false;
        }
        $userid = $this->dbClass->authHandler->authSupportGetUserIdFromUsername($username);
        $hash = sha1($randdata . $email . $username);
        if ($this->authDbClass->authHandler->authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)) {
            if ($this->changePassword($username, $newpassword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * User enrollment start.
     * @param string $userID
     * @return string
     */
    public function userEnrollmentStart(string $userID): string
    {
        $hash = IMUtil::generateChallenge();
        $this->authDbClass->authHandler->authSupportUserEnrollmentStart($userID, $hash);
        return $hash;
    }

    /**
     * User enrollment activate user.
     * @param string $challenge
     * @param string $password
     * @param string|null $rawPWField
     * @return string|null
     */
    public function userEnrollmentActivateUser(string $challenge, string $password, ?string $rawPWField = null): ?string
    {
        $userID = $this->authDbClass->authHandler->authSupportUserEnrollmentEnrollingUser($challenge);
        if (!$userID) {
            return null;
        }
        return $this->dbClass->authHandler->authSupportUserEnrollmentActivateUser(
            $userID, IMUtil::convertHashedPassword($password, $this->passwordHash, $this->alwaysGenSHA2),
            $rawPWField, $password);
    }

    /**
     * Check irrelevant fields.
     * @param string $access
     */
    public function checkIrrelevantFields(string $access): void
    {
        if ($this->logger->getDebugLevel() !== false) {
            $fInfo = $this->getFieldInfo($this->dbSettings->getDataSourceName());
            if ($fInfo != null) {
                $ds = $this->dbSettings->getDataSourceTargetArray();
                if (isset($ds["calculation"])) {
                    foreach ($ds["calculation"] as $def) {
                        $fInfo[] = $def["field"];
                    }
                }
                $calcFields = [];
                if (isset($this->dbSettings->getDataSourceTargetArray()['calculation'])) {
                    foreach ($this->dbSettings->getDataSourceTargetArray()['calculation'] as $entry) {
                        $calcFields[] = $entry['field'];
                    }
                }
                $ignoringField = isset($this->dbSettings->getDataSourceTargetArray()['ignoring-field']) ?
                    $this->dbSettings->getDataSourceTargetArray()['ignoring-field'] : [];
                if ($access == 'read' || $access == 'select') {
                    foreach ($this->dbSettings->getFieldsRequired() as $fieldName) {
                        if (!$this->dbClass->specHandler->isContainingFieldName($fieldName, $fInfo)
                            && !in_array($fieldName, $calcFields)
                            && !in_array($fieldName, $ignoringField)) {
                            $this->logger->setErrorMessage(IMUtil::getMessageClassInstance()->getMessageAs(1033, array($fieldName)));
                        }
                    }
                }
            }
        }
    }

    /**
     * Check validation.
     * @return bool
     */
    public function checkValidation(): bool
    {
        $inValid = false;
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        if (isset($tableInfo['validation'])) {
            $requestedFieldValue = [];
            $counter = 0;
            $fieldValues = $this->dbSettings->getValue();
            foreach ($this->dbSettings->getFieldsRequired() as $field) {
                $value = $fieldValues[$counter];
                $requestedFieldValue[$field] = (is_array($value)) ? implode("\n", $value) : $value;
                $counter++;
            }

            $serviceServer = ServiceServerProxy::instance();
            foreach ($tableInfo['validation'] as $entry) {
                if (array_key_exists($entry['field'], $requestedFieldValue)) {
                    $this->logger->setDebugMessage("Validation: field={$entry['field']}, rule={$entry['rule']}:", 2);
                    if (!$serviceServer->validate($entry['rule'], ["value" => $requestedFieldValue[$entry['field']]])) {
                        $inValid = true;
                    }
                }
            }
            $this->logger->setDebugMessages($serviceServer->getMessages(), 2);
            $this->logger->setErrorMessages($serviceServer->getErrors());
            $serviceServer->clearMessages();
            $serviceServer->clearErrors();
        }
        return !$inValid;
    }

    /**
     * Setup connection.
     * @return bool
     */
    public function setupConnection(): bool
    {
        return false;
    }

    /**
     * Setup handlers.
     * @param string|null $dsn
     */
    public function setupHandlers(?string $dsn = null): void
    {
        // TODO: Implement setupHandlers() method.
    }

    /**
     * Soft delete activate.
     * @param string $field
     * @param string $value
     */
    function softDeleteActivate(string $field, string $value): void
    {
        if ($this->dbClass) {
            $this->dbClass->softDeleteActivate($field, $value);
        }
    }

    /**
     * Require updated record.
     * @param bool $value
     */
    public function requireUpdatedRecord(bool $value): void
    {
        if ($this->dbClass) {
            $this->dbClass->requireUpdatedRecord($value);
        }
    }

    /**
     * Get updated record.
     * @return array|null
     */
    public function getUpdatedRecord(): ?array
    {
        if ($this->dbClass) {
            return $this->dbClass->getUpdatedRecord();
        }
        return null;
    }

    /**
     * Updated record.
     * @return array|null
     */
    public function updatedRecord(): ?array
    {
        return $this->getUpdatedRecord();
    }

    /**
     * Set updated record.
     * @param array $record
     */
    public function setUpdatedRecord(array $record): void
    {
        $this->dbClass->setUpdatedRecord($record);
    }

    /**
     * Set data to updated record.
     * @param string $field
     * @param string|null $value
     * @param int $index
     */
    public function setDataToUpdatedRecord(string $field, ?string $value, int $index = 0): void
    {
        $this->dbClass->setDataToUpdatedRecord($field, $value, $index);
    }

    /**
     * Get use set data to updated record.
     * @return bool
     */
    public function getUseSetDataToUpdatedRecord(): bool
    {
        if ($this->dbClass) {
            return $this->dbClass->getUseSetDataToUpdatedRecord();
        }
        return false;
    }

    /**
     * Clear use set data to updated record.
     */
    public function clearUseSetDataToUpdatedRecord(): void
    {
        if ($this->dbClass) {
            $this->dbClass->clearUseSetDataToUpdatedRecord();
        }
    }

    /**
     * Query for test.
     * @param string $table
     * @param array|null $conditions
     * @return array|null
     */
    public function queryForTest(string $table, ?array $conditions = null): ?array
    {
        return null;
    }

    /**
     * Delete for test.
     * @param string $table
     * @param array|null $conditions
     * @return bool
     */
    public function deleteForTest(string $table, ?array $conditions = null): bool
    {
        return false;
    }

    /**
     * Has transaction.
     * @return bool
     */
    public function hasTransaction(): bool
    {
        return $this->dbClass->hasTransaction();
    }

    /**
     * In transaction.
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->dbClass->inTransaction();
    }

    /**
     * Begin transaction.
     */
    public function beginTransaction(): void
    {
        $this->dbClass->beginTransaction();
    }

    /**
     * Commit transaction.
     */
    public function commitTransaction(): void
    {
        $this->dbClass->commitTransaction();
    }

    /**
     * Rollback transaction.
     */
    public function rollbackTransaction(): void
    {
        $this->dbClass->rollbackTransaction();
    }

    /**
     * Close DB operation.
     */
    public function closeDBOperation(): void
    {
        $this->dbClass->closeDBOperation();
    }

    /**
     * Normalized condition.
     * @param array $condition
     * @return null|array
     * @throws Exception
     */
    public function normalizedCondition(array $condition): null|array
    {
        throw new Exception("Don't use normalizedCondition method on DBClass instance without FileMaker ones.");
    }

    /**
     * Aggregation judgement.
     * @param string $access
     * @return string
     */
    private function aggregationJudgement(string $access): string
    {
        $isSelect = $this->dbSettings->getAggregationSelect();
        $isFrom = $this->dbSettings->getAggregationFrom();
        $isGroupBy = $this->dbSettings->getAggregationGroupBy();
        $isDBSupport = false;
        if ($this->dbClass->specHandler) {
            $isDBSupport = $this->dbClass->specHandler->isSupportAggregation();
        }
        if (!$isDBSupport && ($isSelect || $isFrom || $isGroupBy)) {
            $this->logger->setErrorMessage(IMUtil::getMessageClassInstance()->getMessageAs(1042));
            $access = "nothing";
        } else if ($isDBSupport && (($isSelect && !$isFrom) || (!$isSelect && $isFrom))) {
            $this->logger->setErrorMessage(IMUtil::getMessageClassInstance()->getMessageAs(1043));
            $access = "nothing";
        } else if ($isDBSupport && $isSelect && $isFrom
            && in_array($access, array("update", "new", "create", "delete", "copy"))
        ) {
            $this->logger->setErrorMessage(IMUtil::getMessageClassInstance()->getMessageAs(1044));
            $access = "nothing";
        }
        return $access;
    }

    public function getSortKeys(): array
    {
        return $this->dbClass->getSortKeys();
    }
}
