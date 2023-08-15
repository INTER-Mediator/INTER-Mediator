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
use INTERMediator\FileUploader;
use INTERMediator\IMUtil;
use INTERMediator\SAMLAuth;
use INTERMediator\Locale\IMLocale;
use INTERMediator\Messaging\MessagingProxy;
use INTERMediator\NotifyServer;
use INTERMediator\ServiceServerProxy;
use INTERMediator\Params;

/**
 *
 */
class Proxy extends UseSharedObjects implements Proxy_Interface
{
//    public ?string $dbClass = null; // declared in UseSharedObjects
    /**
     * @var DBClass|null
     */
    private ?DBClass $authDbClass = null; // for issuedhash context
    /**
     * @var object|null
     */
    private ?object $userExpanded = null;
    /**
     * @var array|null
     */
    public ?array $outputOfProcessing = null;
    /**
     * @var string|null
     */
    public ?string $paramAuthUser = null;
    /**
     * @var string|null
     */
    public ?string $hashedPassword = null;

    /**
     * @var string|null
     */
    private ?string $paramResponse = null;
    /**
     * @var string|null
     */
    private ?string $paramResponse2m = null;
    /**
     * @var string|null
     */
    private ?string $paramResponse2 = null;
    /**
     * @var string|null
     */
    private ?string $credential = null;
    /**
     * @var bool
     */
    private bool $authSucceed = false;
    /**
     * @var string|null
     */
    private ?string $clientId;
    /**
     * @var string|null
     */
    private ?string $passwordHash;
    /**
     * @var bool
     */
    private bool $alwaysGenSHA2;
    /**
     * @var string|null
     */
    private ?string $originalAccess;
    /**
     * @var bool
     */
    private bool $clientSyncAvailable;

    /**
     * @var bool
     */
    private bool $ignorePost = false;
    /**
     * @var array|null
     */
    private ?array $PostData;

    /**
     * @var int
     */
    private int $accessLogLevel;
    /**
     * @var array
     */
    private array $result4Log = [];
    /**
     * @var bool
     */
    private bool $isStopNotifyAndMessaging = false;
    /**
     * @var bool
     */
    private bool $suppressMediaToken = false;
    /**
     * @var bool
     */
    private bool $migrateSHA1to2;
    /**
     * @var string|null
     */
    private ?string $credentialCookieDomain;

    /**
     * @param string $cid
     * @return void
     */
    public function setClientId_forTest(string $cid): void // For testing
    {
        $this->clientId = $cid;
    }

    /**
     * @param string $hpw
     * @return void
     */
    public function setHashedPassword_forTest(string $hpw): void // For testing
    {
        $this->hashedPassword = $hpw;
    }

    /**
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
     * @return string|null
     */
    public static function defaultKey(): ?string
    {
        trigger_error("Don't call the static method defaultKey of Proxy class.");
        return null;
    }

    /**
     * @return string|null
     */
    public function getDefaultKey(): ?string
    {
        return $this->dbClass->specHandler->getDefaultKey();
    }

    /**
     * @return void
     */
    public function setStopNotifyAndMessaging(): void
    {
        $this->isStopNotifyAndMessaging = true;
    }

    /**
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
     * @return void
     */
    public function exportOutputDataAsJason(): void
    {
        $this->exportOutputDataAsJSON();
    }

    /**
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
     * @return ?array
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
                    throw new Exception("[Proxy::readFromDB] The method 'doBeforeReadFromDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return null; // Silent stop
                    }
                    throw new Exception($returnBefore); // Just message as error.
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
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[1] {$e->getMessage()} ###{$e->getTraceAsString()}");
            return null;
        }
        return $result;
    }

    /**
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
                    throw new Exception("[Proxy::updateDB] The method 'doBeforeUpdateDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return false; // Silent stop
                    }
                    throw new Exception($returnBefore); // Just message as error.
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
                try {
                    $this->dbSettings->notifyServer->updated(
                        $this->PostData['notifyid'] ?? null,
                        $this->dbClass->notifyHandler->queriedEntity(),
                        $this->dbClass->notifyHandler->queriedPrimaryKeys(),
                        $this->dbSettings->getFieldsRequired(),
                        $this->dbSettings->getValue(),
                        strpos(strtolower($currentDataSource['sync-control']), 'update-notify') !== false
                    );
                } catch (Exception $ex) {
                    throw $ex;
                }
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

        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[2] {$e->getMessage()}");
            return false;
        }
        return $resultOfUpdate;
    }

    /**
     * @param bool $isReplace
     * @return ?string
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
                    throw new Exception("[Proxy::createInDB] The method 'doBeforeCreateToDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return null; // Silent stop
                    }
                    throw new Exception($returnBefore); // Just message as error.
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
                    try {
                        $this->dbSettings->notifyServer->created(
                            $this->PostData['notifyid'] ?? null,
                            $this->dbClass->notifyHandler->queriedEntity(),
                            $this->dbClass->notifyHandler->queriedPrimaryKeys(),
                            $currentDataSource['key'],
                            $result,
                            strpos(strtolower($currentDataSource['sync-control']), 'create-notify') !== false
                        );
                    } catch (Exception $ex) {
                        throw $ex;
                    }
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
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[3] {$e->getMessage()}");
            return null;
        }
        return $resultOfCreate;

    }

    /**
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
                    throw new Exception("[Proxy::deleteFromDB] The method 'doBeforeDeleteFromDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return false; // Silent stop
                    }
                    throw new Exception($returnBefore); // Just message as error.
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
                try {
                    $this->dbSettings->notifyServer->deleted(
                        $this->PostData['notifyid'] ?? null,
                        $this->dbClass->notifyHandler->queriedEntity(),
                        $this->dbClass->notifyHandler->queriedPrimaryKeys()
                    );
                } catch (Exception $ex) {
                    throw $ex;
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[4] {$e->getMessage()}");
            return false;
        }
        return $result;

    }

    /**
     * @return ?string
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
                    throw new Exception("[Proxy::copyInDB] The method 'doBeforeCopyInDB' reports an error.");
                    //} else if (is_null($returnBefore)) {
                    // Pass through for 'return' doesn't exist.
                } else if (is_string($returnBefore)) {
                    if (strlen($returnBefore) === 0) {
                        return null; // Silent stop
                    }
                    throw new Exception($returnBefore); // Just message as error.
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
                try {
                    $this->dbSettings->notifyServer->created(
                        $this->PostData['notifyid'] ?? null,
                        $this->dbClass->notifyHandler->queriedEntity(),
                        $this->dbClass->notifyHandler->queriedPrimaryKeys(),
                        $currentDataSource['key'],
                        $result,
                        strpos(strtolower($currentDataSource['sync-control']), 'create-notify') !== false
                    );
                } catch (Exception $ex) {
                    throw $ex;
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[5] {$e->getMessage()}");
            return null;
        }
        return $resultOfCopy;
    }

    /**
     * @param string $dataSourceName
     * @return ?array
     */
    public function getFieldInfo(string $dataSourceName): ?array
    {
        return $this->dbClass ? $this->dbClass->getFieldInfo($dataSourceName) : null;
    }

    /**
     * @return void
     */
    public function ignoringPost()
    {
        $this->ignorePost = true;
    }

    /**
     * @return void
     */
    public function ignorePost()
    {
        $this->ignorePost = true;
    }

    /**
     * @param ?array $datasource
     * @param ?array $options
     * @param ?array $dbspec
     * @param ?int $debug
     * @param ?string $target
     * @return bool
     */
    function initialize(?array $datasource, ?array $options, ?array $dbspec, ?int $debug, ?string $target = null): bool
    {
        $this->PostData = $this->ignorePost ? array() : $_POST;
        $this->setUpSharedObjects();
        $this->logger->setDebugMessage("Start to initialize the DB\Proxy class instance.", 2);
        $this->dbSettings->setSAMLExpiringSeconds(Params::getParameterValue('ldapExpiringSeconds', 600));
        $this->dbSettings->setSAMLExpiringSeconds(Params::getParameterValue('samlExpiringSeconds', 600));
        $this->credentialCookieDomain = Params::getParameterValue('credentialCookieDomain', "");

        $this->accessLogLevel = intval(Params::getParameterValue('accessLogLevel', false));
        $this->clientSyncAvailable = boolval(Params::getParameterValue("activateClientService", false));
        $this->passwordHash = Params::getParameterValue('passwordHash', 1);
        $this->alwaysGenSHA2 = boolval(Params::getParameterValue('alwaysGenSHA2', false));
        $this->migrateSHA1to2 = boolval(Params::getParameterValue('migrateSHA1to2', false));
        $emailAsAliasOfUserName = Params::getParameterValue('emailAsAliasOfUserName', false);

        $this->dbSettings->setDataSource($datasource);
        $this->dbSettings->setOptions($options);
        IMLocale::$options = $options;
        $this->dbSettings->setDbSpec($dbspec);

        $this->dbSettings->setSeparator($options['separator'] ?? '@');
        $this->formatter->setFormatter($options['formatter'] ?? null);
        $this->dbSettings->setDataSourceName(!is_null($target) ? $target : ($this->PostData['name'] ?? "_im_auth"));
        $context = $this->dbSettings->getDataSourceTargetArray();
        if (count($_FILES) > 0) {
            $this->dbSettings->setAttachedFiles($context['name'], $_FILES);
        }

        $dbClassName = '\\INTERMediator\\DB\\' .
            ($context['db-class'] ?? ($dbspec['db-class'] ?? Params::getParameterValue('dbClass', '')));
        $this->dbSettings->setDbSpecServer(
            $context['server'] ?? ($dbspec['server'] ?? Params::getParameterValue('dbServer', '')));
        $this->dbSettings->setDbSpecPort(
            $context['port'] ?? ($dbspec['port'] ?? Params::getParameterValue('dbPort', '')));
        $this->dbSettings->setDbSpecUser(
            $context['user'] ?? ($dbspec['user'] ?? Params::getParameterValue('dbUser', '')));
        $this->dbSettings->setDbSpecPassword(
            $context['password'] ?? ($dbspec['password'] ?? Params::getParameterValue('dbPassword', '')));
        $this->dbSettings->setDbSpecDataType(
            $context['datatype'] ?? ($dbspec['datatype'] ?? Params::getParameterValue('dbDataType', '')));
        $this->dbSettings->setDbSpecDatabase(
            $context['database'] ?? ($dbspec['database'] ?? Params::getParameterValue('dbDatabase', '')));
        $this->dbSettings->setDbSpecProtocol(
            $context['protocol'] ?? ($dbspec['protocol'] ?? Params::getParameterValue('dbProtocol', '')));
        $this->dbSettings->setDbSpecOption(
            $context['option'] ?? ($dbspec['option'] ?? Params::getParameterValue('dbOption', '')));
        $this->dbSettings->setCertVerifying(
            $context['cert-verifying'] ?? ($dbspec['cert-verifying'] ?? Params::getParameterValue('certVerifying', true)));
        if (isset($options['authentication']['issuedhash-dsn'])) {
            $this->dbSettings->setDbSpecDSN($options['authentication']['issuedhash-dsn']);
        } else {
            $this->dbSettings->setDbSpecDSN(
                $context['dsn'] ?? ($dbspec['dsn'] ?? Params::getParameterValue('dbDSN', '')));
        }

        /* Setup Database Class's Object */
        $isDBClassNull = is_null($this->dbClass);
        if ($isDBClassNull) {
            $this->dbClass = new $dbClassName();
            if ($this->dbClass == null) {
                $this->logger->setErrorMessage("The database class [{$dbClassName}] that you specify is not valid.");
                echo implode('', $this->logger->getMessagesForJS());
                return false;
            }
            $this->logger->setDebugMessage("The class '{$dbClassName}' was instanciated.", 2);
        }
        $this->dbClass->setUpSharedObjects($this);
        if ($isDBClassNull) {
            if (!$this->dbClass->setupConnection()) {
                return false;
            }
            $this->dbClass->setupHandlers();
        }
        if (!Params::getParameterValue('prohibitDebugMode', false) && $debug) {
            $this->logger->setDebugMode($debug);
        }
        $this->dbSettings->setAggregationSelect(
            $context['aggregation-select'] ?? null);
        $this->dbSettings->setAggregationFrom(
            $context['aggregation-from'] ?? null);
        $this->dbSettings->setAggregationGroupBy(
            $context['aggregation-group-by'] ?? null);

        /* Authentication and Authorization Judgement */
        $challengeDSN = $options['authentication']['issuedhash-dsn'] ?? Params::getParameterValue('issuedHashDSN', null);
        if (!is_null($challengeDSN)) {
            $this->authDbClass = new PDO();
            $this->authDbClass->setUpSharedObjects($this);
            $this->authDbClass->setupWithDSN($challengeDSN);
            $this->authDbClass->setupHandlers($challengeDSN);
            $this->logger->setDebugMessage(
                "The class 'PDO' was instanciated for issuedhash with {$challengeDSN}.", 2);
        } else {
            $this->authDbClass = $this->dbClass;
        }

        $this->dbSettings->notifyServer = null;
        if ($this->clientSyncAvailable) {
            $this->dbSettings->notifyServer = new NotifyServer();
            $nid = $this->PostData['notifyid'] ?? null;
            if ($this->dbSettings->notifyServer->initialize($this->authDbClass, $nid)) {
                $this->logger->setDebugMessage("The NotifyServer was instanciated.", 2);
            } else {
                $this->logger->setDebugMessage("The NotifyServer failed to initialize.", 2);
                $this->dbSettings->notifyServer = null;
            }
        }

        $this->dbSettings->setCurrentDataAccess($this->dbClass);

        if (isset($context['extending-class'])) {
            $className = $context['extending-class'];
            try {
                $this->userExpanded = new $className();
                $this->logger->setDebugMessage("The class '{$className}' was instanciated.", 2);
            } catch (Exception $e) {
                $this->logger->setErrorMessage("The class '{$className}' wasn't instanciated.");
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
        if (isset($options['authentication']['email-as-username'])) {
            $this->dbSettings->setEmailAsAccount($options['authentication']['email-as-username']);
        } else if (isset($emailAsAliasOfUserName) && $emailAsAliasOfUserName) {
            $this->dbSettings->setEmailAsAccount($emailAsAliasOfUserName);
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

        $this->paramAuthUser = $this->PostData['authuser'] ?? "";
        $this->paramResponse = $this->PostData['response'] ?? "";
        $this->paramResponse2m = $this->PostData['response2m'] ?? "";
        $this->paramResponse2 = $this->PostData['response2'] ?? "";
        $this->credential = $_COOKIE['_im_credential_token'] ?? "";
        $this->clientId = $this->PostData['clientid'] ?? ($_SERVER['REMOTE_ADDR'] ?? "Non-browser-client");

        $this->dbSettings->setMediaRoot($options['media-root-dir']
            ?? Params::getParameterValue('mediaRootDir', null) ?? null);

        $this->logger->setDebugMessage("Server side locale: " . setlocale(LC_ALL, "0"), 2);

        if (isset($options['authentication']['is-saml'])) {
            $this->dbSettings->setIsSAML($options['authentication']['is-saml']);
        } else {
            $this->dbSettings->setIsSAML(Params::getParameterValue('isSAML', false));
        }

        $this->dbSettings->setSAMLAuthSource(Params::getParameterValue('samlAuthSource', null));
        $this->dbSettings->setSAMLAttrRules(Params::getParameterValue("samlAttrRules", null));
        $this->dbSettings->setSAMLAdditionalRules(Params::getParameterValue("samlAdditionalRules", null));
        return true;
    }

    /*
    * POST
    * ?access=select
    * &name=<table name>
    * &start=<record number to start>
    * &records=<how many records should it return>
    * &field_<N>=<field name>
    * &value_<N>=<value of the field>
    * &condition<N>field=<Extra criteria's field name>
    * &condition<N>operator=<Extra criteria's operator>
    * &condition<N>value=<Extra criteria's value>
    * &parent_keyval=<value of the foreign key field>
    */

    /**
     * @param ?string $access
     * @param bool $bypassAuth
     * @param bool $ignoreFiles
     */
    public function processingRequest(?string $access = null, bool $bypassAuth = false, bool $ignoreFiles = false): void
    {
        $this->logger->setDebugMessage("[processingRequest]", 2);
        $authOptions = $this->dbSettings->getAuthentication();
        $messageClass = IMUtil::getMessageClassInstance();
        /* Aggregation Judgement */
        $isSelect = $this->dbSettings->getAggregationSelect();
        $isFrom = $this->dbSettings->getAggregationFrom();
        $isGroupBy = $this->dbSettings->getAggregationGroupBy();
        $isDBSupport = false;
        if ($this->dbClass->specHandler) {
            $isDBSupport = $this->dbClass->specHandler->isSupportAggregation();
        }
        if (!$isDBSupport && ($isSelect || $isFrom || $isGroupBy)) {
            $this->logger->setErrorMessage($messageClass->getMessageAs(1042));
            $access = "do nothing";
        } else if ($isDBSupport && (($isSelect && !$isFrom) || (!$isSelect && $isFrom))) {
            $this->logger->setErrorMessage($messageClass->getMessageAs(1043));
            $access = "do nothing";
        } else if ($isDBSupport && $isSelect && $isFrom
            && in_array($access, array("update", "new", "create", "delete", "copy"))
        ) {
            $this->logger->setErrorMessage($messageClass->getMessageAs(1044));
            $access = "do nothing";
        }

        // Authentication and Authorization
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $access = is_null($access) ? $this->PostData['access'] : $access;
        $access = (($access == "select") || ($access == "load")) ? "read" : $access;

        $this->dbSettings->setRequireAuthentication(false);
        $this->dbSettings->setRequireAuthorization(false);
        $this->dbSettings->setDBNative(false);
        if (!is_null($authOptions)
            || $access == 'challenge' || $access == 'changepassword' || $access == 'credential'
            || (isset($tableInfo['authentication'])
                && (isset($tableInfo['authentication']['all']) || isset($tableInfo['authentication'][$access])))
        ) {
            if ($this->logger->getDebugLevel()
                && ($this->passwordHash != '1' || $this->alwaysGenSHA2)) {
                $this->dbClass->authHandler->authSupportCanMigrateSHA256Hash();
            }
            $this->dbSettings->setRequireAuthorization(true);
        }

        $this->originalAccess = $access;
        $this->authSucceed = false;
        if (!$bypassAuth && $this->dbSettings->getRequireAuthorization()) { // Authentication required
            if (strlen($this->paramAuthUser) == 0
                || (strlen($this->paramResponse) == 0
                    && strlen($this->paramResponse2m) == 0
                    && strlen($this->paramResponse2) == 0
                    && strlen($this->credential) == 0)
            ) { // No username or password
                $access = "do nothing";
                $this->dbSettings->setRequireAuthentication(true);
            }
            // User and Password are suppried but...
            if ($access != 'challenge') { // Not accessing getting a challenge.
                $noAuthorization = true;
                $authorizedGroups = $this->dbClass->authHandler->getAuthorizedGroups($access);
                $authorizedUsers = $this->dbClass->authHandler->getAuthorizedUsers($access);

                $this->logger->setDebugMessage(str_replace("\n", "",
                        ("contextName={$this->dbSettings->getDataSourceName()}/access={$access}/"
                            . "authorizedUsers=" . var_export($authorizedUsers, true)
                            . "/authorizedGroups=" . var_export($authorizedGroups, true)))
                    , 2);
                if ((count($authorizedUsers) == 0 && count($authorizedGroups) == 0)) {
                    $noAuthorization = false;
                } else {
                    $signedUser = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    if (in_array($signedUser, $authorizedUsers)) {
                        $noAuthorization = false;
                    } else {
                        if (count($authorizedGroups) > 0) {
                            $belongGroups = $this->dbClass->authHandler->authSupportGetGroupsOfUser($signedUser);
                            $this->logger->setDebugMessage($signedUser . "=belongGroups=" . var_export($belongGroups, true), 2);
                            if (count(array_intersect($belongGroups, $authorizedGroups)) != 0) {
                                $noAuthorization = false;
                            }
                        }
                    }
                }
                if ($noAuthorization) {
                    $this->logger->setDebugMessage("Authorization doesn't meet the settings.");
                    $access = "do nothing";
                    $this->dbSettings->setRequireAuthentication(true);
                }
                $signedUser = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($this->paramAuthUser);
                $this->hashedPassword = $this->dbClass->authHandler->authSupportRetrieveHashedPassword($signedUser);
                if ($this->dbSettings->getIsSAML()) { // Set up as SAML
                    if ($this->checkAuthorization($signedUser, true)) {
                        $this->dbSettings->setCurrentUser($signedUser);
                        $this->logger->setDebugMessage("IM-built-in Authentication for SAML user succeed.");
                        $this->authSucceed = true;
                    } else { // Timeout with SAML
                        $SAMLAuth = new SAMLAuth($this->dbSettings->getSAMLAuthSource());
                        $SAMLAuth->setSAMLAttrRules($this->dbSettings->getSAMLAttrRules());
                        $SAMLAuth->setSAMLAdditionalRules($this->dbSettings->getSAMLAdditionalRules());
                        [$additional, $signedUser] = $SAMLAuth->samlLoginCheck();
                        $this->logger->setDebugMessage("SAML Auth result: user={$signedUser}, additional={$additional}, attributes="
                            . var_export($SAMLAuth->getAttributes(), true));
                        $this->outputOfProcessing['samlloginurl'] = $SAMLAuth->samlLoginURL($_SERVER['HTTP_REFERER']);
                        $this->outputOfProcessing['samllogouturl'] = $SAMLAuth->samlLogoutURL($_SERVER['HTTP_REFERER']);
                        if (!$additional) {
                            $this->outputOfProcessing['samladditionalfail'] = $SAMLAuth->samlLogoutURL($_SERVER['HTTP_REFERER']);
                        }
                        $this->paramAuthUser = $signedUser;
                        if ($signedUser) {
                            $attrs = $SAMLAuth->getValuesFromAttributes();
                            $this->logger->setDebugMessage(
                                "SAML Authentication succeed. Attributes=" . var_export($attrs, true));
                            $this->authSucceed = true;
                            $password = IMUtil::generateRandomPW();
                            [$addResult, $hashedpw] = $this->addUser($signedUser, $password, true, $attrs);
                            if ($addResult) {
                                $this->dbSettings->setRequireAuthentication(false);
                                $this->dbSettings->setCurrentUser($signedUser);
                                $access = $this->originalAccess;
                                $this->outputOfProcessing['samluser'] = $signedUser;
                                $this->outputOfProcessing['temppw'] = $hashedpw;
                            }
                        }
                    }
                } else { // Normal Login process
                    if ($this->checkAuthorization($signedUser, false)) {
                        $this->dbSettings->setCurrentUser($signedUser);
                        $this->logger->setDebugMessage("IM-built-in Authentication succeed.");
                        $this->authSucceed = true;
                    }
                }

                if (!$this->authSucceed) {
                    $this->logger->setDebugMessage(
                        "Authentication doesn't meet valid.{$signedUser}/{$this->paramResponse}/{$this->clientId}");
                    // Not Authenticated!
                    $access = "do nothing";
                    $this->dbSettings->setRequireAuthentication(true);
                }
            }
        }
        $this->suppressMediaToken = true;
        // Come here access=challenge or authenticated access
        switch ($access) {
            case 'describe':
                $this->logger->setDebugMessage("[processingRequest] start describe processing", 2);
                $result = $this->dbClass->getSchema($this->dbSettings->getDataSourceName());
                $this->outputOfProcessing['dbresult'] = $result;
                $this->outputOfProcessing['resultCount'] = 0;
                $this->outputOfProcessing['totalCount'] = 0;
                break;
            case 'read':
            case 'select':
                $this->logger->setDebugMessage("[processingRequest] start read processing", 2);
                $result = $this->readFromDB();
                if (isset($tableInfo['protect-reading']) && is_array($tableInfo['protect-reading'])) {
                    $recordCount = count($result);
                    for ($index = 0; $index < $recordCount; $index++) {
                        foreach ($result[$index] as $field => $value) {
                            if (in_array($field, $tableInfo['protect-reading'])) {
                                $result[$index][$field] = "[protected]";
                            }
                        }
                    }
                }
                $this->outputOfProcessing['dbresult'] = $result;
                $this->outputOfProcessing['resultCount'] = $this->countQueryResult();
                $this->outputOfProcessing['totalCount'] = $this->getTotalCount();
                $this->suppressMediaToken = false;
                break;
            case
            'update':
                $this->logger->setDebugMessage("[processingRequest] start update processing", 2);
                if ($this->checkValidation()) {
                    if (isset($tableInfo['protect-writing']) && is_array($tableInfo['protect-writing'])) {
                        $fieldArray = array();
                        $valueArray = array();
                        $counter = 0;
                        $fieldValues = $this->dbSettings->getValue();
                        foreach ($this->dbSettings->getFieldsRequired() as $field) {
                            if (!in_array($field, $tableInfo['protect-writing'])) {
                                $fieldArray[] = $field;
                                $valueArray[] = $fieldValues[$counter];
                            }
                            $counter++;
                        }
                        $this->dbSettings->setFieldsRequired($fieldArray);
                        $this->dbSettings->setValue($valueArray);
                    }
                    $this->dbClass->requireUpdatedRecord(true);
                    $this->updateDB($bypassAuth);
                    $this->outputOfProcessing['dbresult'] = $this->getUpdatedRecord();
                } else {
                    $this->logger->setErrorMessage("Invalid data. Any validation rule was violated.");
                }
                break;
            case 'new':
            case 'create':
            case 'replace':
                $this->logger->setDebugMessage("[processingRequest] start create processing", 2);
                $attachedFields = $this->dbSettings->getAttachedFields();
                if (!$ignoreFiles && isset($attachedFields) && $attachedFields[0] == '_im_csv_upload') {
                    $this->logger->setDebugMessage("CSV File importing operation gets stated.", 2);
                    $uploadFiles = $this->dbSettings->getAttachedFiles($tableInfo['name']);
                    if ($uploadFiles && count($tableInfo) > 0) {
                        $fileUploader = new FileUploader();
                        if (IMUtil::guessFileUploadError()) {
                            $fileUploader->processingAsError(
                                $this->dbSettings->getDataSource(),
                                $this->dbSettings->getOptions(),
                                $this->dbSettings->getDbSpec(), true,
                                $this->dbSettings->getDataSourceName(), true);
                        } else {
                            $fileUploader->processingWithParameters(
                                $this->dbSettings->getDataSource(),
                                $this->dbSettings->getOptions(),
                                $this->dbSettings->getDbSpec(),
                                $this->logger->getDebugLevel(),
                                $tableInfo['name'], $tableInfo['key'], null,
                                $this->dbSettings->getAttachedFields(), $uploadFiles, true
                            );
                            $this->outputOfProcessing['dbresult'] = $fileUploader->dbresult;
                        }
                    }
                } else {
                    if ($this->checkValidation()) {
                        $uploadFiles = $this->dbSettings->getAttachedFiles($tableInfo['name']);
                        if ($ignoreFiles || !$uploadFiles || count($tableInfo) < 1) { // No attached file.
                            $result = $this->createInDB($access == 'replace');
                            $this->outputOfProcessing['newRecordKeyValue'] = $result;
                            $this->outputOfProcessing['dbresult'] = $this->getUpdatedRecord();
                        } else { // Some files are attached.
                            $fileUploader = new FileUploader();
                            if (IMUtil::guessFileUploadError()) { // Detect file upload error.
                                $fileUploader->processingAsError(
                                    $this->dbSettings->getDataSource(),
                                    $this->dbSettings->getOptions(),
                                    $this->dbSettings->getDbSpec(), true,
                                    $this->dbSettings->getDataSourceName(), true);
                            } else { // No file upload error.
                                $dbresult = [];
                                $result = $this->createInDB($access == 'replace');
                                $this->outputOfProcessing['newRecordKeyValue'] = $result;
                                $counter = 0;
                                foreach ($uploadFiles as $oneFile) {
                                    $dbresult[] = $this->getUpdatedRecord()[0];
                                    if ($result) {
                                        $fileUploader->processingWithParameters(
                                            $this->dbSettings->getDataSource(),
                                            $this->dbSettings->getOptions(),
                                            $this->dbSettings->getDbSpec(),
                                            $this->logger->getDebugLevel(),
                                            $tableInfo['name'], $tableInfo['key'], $result,
                                            [$attachedFields[$counter]], [$oneFile], true
                                        );
                                    }
                                    $this->outputOfProcessing['dbresult'] = $dbresult;
                                    $counter += 1;
                                }
                            }
                        }
                    } else {
                        $this->logger->setErrorMessage("Invalid data. Any validation rule was violated.");
                    }
                }
                break;
            case 'delete':
                $this->logger->setDebugMessage("[processingRequest] start delete processing", 2);
                $this->deleteFromDB();
                break;
            case 'copy':
                $this->logger->setDebugMessage("[processingRequest] start copy processing", 2);
                if ($this->checkValidation()) {
                    $result = $this->copyInDB();
                    $this->outputOfProcessing['newRecordKeyValue'] = $result;
                    $this->outputOfProcessing['dbresult'] = $this->getUpdatedRecord();
                } else {
                    $this->logger->setErrorMessage("Invalid data. Any validation rule was violated.");
                }
                break;
            case 'challenge':
                break;
            case 'changepassword':
                $this->logger->setDebugMessage("[processingRequest] start changepassword processing", 2);
                if (isset($this->PostData['newpass'])) {
                    $changeResult = $this->changePassword($this->paramAuthUser, $this->PostData['newpass']);
                    $this->outputOfProcessing['changePasswordResult'] = $changeResult;
                } else {
                    $this->outputOfProcessing['changePasswordResult'] = false;
                }
                break;
            case 'unregister':
                $this->logger->setDebugMessage("[processingRequest] start unregister processing", 2);
                if (!is_null($this->dbSettings->notifyServer) && $this->clientSyncAvailable) {
                    $tableKeys = null;
                    if (isset($this->PostData['pks'])) {
                        $tableKeys = json_decode($this->PostData['pks'], true);
                    }
                    $this->dbSettings->notifyServer->unregister($this->PostData['notifyid'], $tableKeys);
                }
                break;
        }
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
                            $this->logger->setErrorMessage($messageClass->getMessageAs(1033, array($fieldName)));
                        }
                    }
                }
            }
        }
    }

    /**
     * @param bool $notFinish
     */
    function finishCommunication(bool $notFinish = false): void
    {
        $this->logger->setDebugMessage(
            "[finishCommunication]getRequireAuthorization={$this->dbSettings->getRequireAuthorization()}", 2);
        $this->outputOfProcessing['usenull'] = false;
        if (!$notFinish && $this->dbSettings->getRequireAuthorization()) {
            $generatedChallenge = IMUtil::generateChallenge();
            $generatedUID = IMUtil::generateClientId('', $this->passwordHash);
            $this->logger->setDebugMessage("generatedChallenge = $generatedChallenge", 2);
            $userSalt = $this->saveChallenge(
                $this->dbSettings->isDBNative() ? 0 : $this->paramAuthUser, $generatedChallenge, $generatedUID);
            $authStoring = $this->dbSettings->getAuthenticationItem('storing');
            if ($authStoring == 'credential') {
                if ($this->authSucceed) {
                    setcookie('_im_credential_token',
                        $this->generateCredential($generatedChallenge, $generatedUID, $this->hashedPassword),
                        time() + $this->dbSettings->getAuthenticationItem('authexpired'), '/',
                        $this->credentialCookieDomain, false, true);
                } else {
                    setcookie("_im_credential_token", "", time() - 3600); // Should be removed.
                }
                if ($this->originalAccess == 'challenge') {
                    $this->outputOfProcessing['challenge'] = "{$generatedChallenge}{$userSalt}";
                }
            } else {
                $this->outputOfProcessing['challenge'] = "{$generatedChallenge}{$userSalt}";
            }
            $this->outputOfProcessing['clientid'] = $generatedUID;
            if ($this->dbSettings->getRequireAuthentication()) {
                $this->outputOfProcessing['requireAuth'] = true;
            }
            $tableInfo = $this->dbSettings->getDataSourceTargetArray();
            if (isset($tableInfo['authentication']['media-handling']) && $tableInfo['authentication']['media-handling'] === true && !$this->suppressMediaToken
            ) {
                $generatedChallenge = IMUtil::generateChallenge();
                $this->saveChallenge($this->paramAuthUser, $generatedChallenge, "_im_media");
                //$this->outputOfProcessing['mediatoken'] = $generatedChallenge;
                $cookieNameUser = '_im_username';
                $cookieNameToken = '_im_mediatoken';
                $realm = $this->dbSettings->getAuthenticationItem('realm');
                if ($realm) {
                    $realm = str_replace(" ", "_", str_replace(".", "_", $realm));
                    $cookieNameUser .= ('_' . $realm);
                    $cookieNameToken .= ('_' . $realm);
                }
                setcookie($cookieNameToken, $generatedChallenge,
                    time() + $this->dbSettings->getAuthenticationItem('authexpired'), '/',
                    $this->credentialCookieDomain, false, true);
                setcookie($cookieNameUser, $this->paramAuthUser,
                    time() + $this->dbSettings->getAuthenticationItem('authexpired'), '/',
                    $this->credentialCookieDomain, false, false);
                $this->logger->setDebugMessage("mediatoken stored", 2);
            }
        }
        $this->addOutputData('errorMessages', $this->logger->getErrorMessages());
        $this->addOutputData('warningMessages', $this->logger->getWarningMessages());
        $this->addOutputData('debugMessages', $this->logger->getDebugMessages());
    }

    /**
     * @param string $generatedChallenge
     * @param string $generatedUID
     * @param string $pwHash
     * @return string
     */
    private function generateCredential(string $generatedChallenge, string $generatedUID, string $pwHash): string
    {
        return hash("sha256", $generatedChallenge . $generatedUID . $pwHash);
    }

    /**
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
     * @return string|null
     */
    public function getDatabaseNewRecordKey(): ?string
    {
        if (isset($this->outputOfProcessing['newRecordKeyValue'])) {
            return $this->outputOfProcessing['newRecordKeyValue'];
        }
        return null;
    }

    /* Authentication support */
    /**
     * @param string $username
     * @return string
     */
    function authSupportGetSalt(string $username): ?string
    {
        $hashedpw = $this->hashedPassword ?? $this->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
        if ($hashedpw) {
            return substr($hashedpw, -8);
        }
        return null;
    }

    /* returns user's hash salt.*/
    /**
     * @param string $username
     * @param string $challenge
     * @param string $clientId
     * @return string
     */
    function saveChallenge(string $username, string $challenge, string $clientId): ?string
    {
        $this->logger->setDebugMessage(
            "[saveChallenge]user={$username}, challenge={$challenge}, clientid={$clientId}", 2);
        $username = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($username);
        $uid = $this->dbClass->authHandler->authSupportGetUserIdFromUsername($username);
        $this->authDbClass->authHandler->authSupportStoreChallenge($uid, $challenge, $clientId);
        return $username === 0 ? "" : $this->authSupportGetSalt($username);
    }

    /**
     * @param string $username
     * @param bool $isSAML
     * @return bool
     */
    function checkAuthorization(string $username, bool $isSAML = false): bool
    {
        $falseHash = hash("sha256", uniqid("", true)); // for failing auth.
        $hashedvalue = $this->paramResponse ?? $falseHash;
        $hashedvalue2m = $this->paramResponse2m ?? $falseHash;
        $hashedvalue2 = $this->paramResponse2 ?? $falseHash;
        $this->logger->setDebugMessage("[checkAuthorization]user={$username}, paramResponse={$hashedvalue}, "
            . "paramResponse2m={$hashedvalue2m}, paramResponse2={$hashedvalue2}, clientid={$this->clientId}", 2);

        $returnValue = false;
        $this->authDbClass->authHandler->authSupportRemoveOutdatedChallenges();
        $signedUser = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($username);
        $uid = $this->dbClass->authHandler->authSupportGetUserIdFromUsername($signedUser);
        $this->logger->setDebugMessage("[checkAuthorization]uid={$uid}", 2);
        if ($uid <= 0) {
            return $returnValue;
        }
        if ($isSAML && !$this->dbClass->authHandler->authSupportIsWithinSAMLLimit($uid)) {
            return $returnValue;
        }
        $storedChallenge = $this->authDbClass->authHandler->authSupportRetrieveChallenge($uid, $this->clientId);
        $this->logger->setDebugMessage("[checkAuthorization]storedChallenge={$storedChallenge}/{$this->credential}", 2);
        if ($storedChallenge && strlen($storedChallenge) == 48) { // ex.fc0d54312ce33c2fac19d758
            if ($this->credential == $this->generateCredential($storedChallenge, $this->clientId, $this->hashedPassword)) {
                // Credential Auth passed
                $this->logger->setDebugMessage("[checkAuthorization]Credential auth passed.", 2);
                $returnValue = true;
            } else { // Hash Auth checking
                $hmacValue = $this->hashedPassword ? hash_hmac('sha256', $this->hashedPassword, $storedChallenge) : 'no-value';
                $hmacValue2m = $this->hashedPassword ? hash_hmac('sha256', $this->hashedPassword, $storedChallenge) : 'no-value';
                $this->logger->setDebugMessage(
                    "[checkAuthorization]hashedPassword={$this->hashedPassword}/hmac_value={$hmacValue}", 2);
                if ($this->hashedPassword && strlen($this->hashedPassword) > 0) {
                    if ($hashedvalue == $hmacValue) {
                        $this->logger->setDebugMessage("[checkAuthorization]sha1 hash used.", 2);
                        $returnValue = true;
                        if ($this->migrateSHA1to2) {
                            $salt = hex2bin(substr($this->hashedPassword, -8));
                            $hashedPw = IMUtil::convertHashedPassword($this->hashedPassword, $this->passwordHash, true, $salt);
                            $this->dbClass->authHandler->authSupportChangePassword($signedUser, $hashedPw);
                        }
                    } else if ($hashedvalue2m == $hmacValue2m) {
                        $this->logger->setDebugMessage("[checkAuthorization]sha2 hash from sha1 hash used.", 2);
                        $returnValue = true;
                    } else if ($hashedvalue2 == $hmacValue) {
                        $this->logger->setDebugMessage("[checkAuthorization]sha2 hash used.", 2);
                        $returnValue = true;
                    } else {
                        $this->logger->setDebugMessage("[checkAuthorization]Built-in authorization fail.", 2);
                    }
                }
            }
        }
        return $returnValue;
    }

    // This method is just used to authenticate with database user

    /**
     * @param string $challenge
     * @param string $clientId
     * @return bool
     */
    function checkChallenge(string $challenge, string $clientId): bool
    {
        $returnValue = false;
        $this->authDbClass->authHandler->authSupportRemoveOutdatedChallenges();
        // Database user mode is user_id=0
        $storedChallenge = $this->authDbClass->authHandler->authSupportRetrieveChallenge(0, $clientId);
        if ($storedChallenge && strlen($storedChallenge) == 48 && $storedChallenge == $challenge) { // ex.fc0d54312ce33c2fac19d758
            $returnValue = true;
        }
        return $returnValue;
    }

    /**
     * @param string $user
     * @param string $token
     * @return bool
     */
    public function checkMediaToken(string $user, string $token): bool
    {
        $this->logger->setDebugMessage("[checkMediaToken] user={$user}, token={$token}", 2);
        $returnValue = false;
        $this->authDbClass->authHandler->authSupportRemoveOutdatedChallenges();
        // Database user mode is user_id=0
        $user = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($user);
        $uid = $this->dbClass->authHandler->authSupportGetUserIdFromUsername($user);
        $storedChallenge = $this->authDbClass->authHandler->authSupportCheckMediaToken($uid);
        if (strlen($storedChallenge) == 48 && $storedChallenge == $token) { // ex.fc0d54312ce33c2fac19d758
            $returnValue = true;
        }
        return $returnValue;
    }


    /**
     * @param string $username
     * @param string $password
     * @param bool $isSAML
     * @param ?array $attrs
     * @return array
     */
    function addUser(string $username, string $password, bool $isSAML = false, ?array $attrs = null): array
    {
        $this->logger->setDebugMessage("[addUser] username={$username}, isSAML={$isSAML}", 2);
        $hashedPw = IMUtil::convertHashedPassword($password, $this->passwordHash, $this->alwaysGenSHA2);
        $returnValue = $this->dbClass->authHandler->authSupportCreateUser($username, $hashedPw, $isSAML, $password, $attrs);
        $this->logger->setDebugMessage("[addUser] authSupportCreateUser returns: {$returnValue}", 2);
        return [$returnValue, $hashedPw];
    }

    /**
     * @param string $username
     * @param string $newpassword
     * @return bool
     */
    function changePassword(string $username, string $newpassword): bool
    {
        return $this->dbClass->authHandler->authSupportChangePassword($username, $newpassword);
    }

    /**
     * @param string $email
     * @return ?array
     */
    function resetPasswordSequenceStart(string $email): ?array
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
     * @param ?string $username
     * @param ?string $email
     * @param string $randdata
     * @param string $newpassword
     * @return bool
     *
     * Using
     */
    function resetPasswordSequenceReturnBack(?string $username, ?string $email, string $randdata, string $newpassword): bool
    {
        $userid = null;
        $username = null;
        if (is_null($username) && !is_null($email)) {
            $userid = $this->dbClass->authHandler->authSupportGetUserIdFromEmail($email);
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
     * @param string $userID
     * @return string
     */
    function userEnrollmentStart(string $userID): string
    {
        $hash = IMUtil::generateChallenge();
        $this->authDbClass->authHandler->authSupportUserEnrollmentStart($userID, $hash);
        return $hash;
    }

    /**
     * @param string $challenge
     * @param string $password
     * @param bool $rawPWField
     * @return string|null
     */
    function userEnrollmentActivateUser(string $challenge, string $password, bool $rawPWField = false): ?string
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
     * @return bool
     */
    private
    function checkValidation(): bool
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
     * @return bool
     */
    public function setupConnection(): bool
    {
        return false;
    }

    /**
     * @param string|null $dsn
     * @return void
     */
    public function setupHandlers(?string $dsn = null): void
    {
        // TODO: Implement setupHandlers() method.
    }

    /**
     * @param string $field
     * @param string $value
     * @return void
     */
    function softDeleteActivate(string $field, string $value): void
    {
        if ($this->dbClass) {
            $this->dbClass->softDeleteActivate($field, $value);
        }
    }

    /**
     * @param bool $value
     * @return void
     */
    public function requireUpdatedRecord(bool $value): void
    {
        if ($this->dbClass) {
            $this->dbClass->requireUpdatedRecord($value);
        }
    }

    /**
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
     * @return array|null
     */
    public function updatedRecord(): ?array
    {
        return $this->getUpdatedRecord();
    }

    /**
     * @param array $record
     * @return void
     */
    public function setUpdatedRecord(array $record): void
    {
        $this->dbClass->setUpdatedRecord($record);
    }

    /**
     * @param string $field
     * @param string $value
     * @param int $index
     * @return void
     */
    public function setDataToUpdatedRecord(string $field, string $value, int $index = 0): void
    {
        $this->dbClass->setDataToUpdatedRecord($field, $value, $index);
    }

    /**
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
     * @return void
     */
    public function clearUseSetDataToUpdatedRecord(): void
    {
        if ($this->dbClass) {
            $this->dbClass->clearUseSetDataToUpdatedRecord();
        }
    }

    /**
     * @param string $table
     * @param array|null $conditions
     * @return array|null
     */
    public function queryForTest(string $table, ?array $conditions = null): ?array
    {
        return null;
    }

    /**
     * @param string $table
     * @param array|null $conditions
     * @return bool
     */
    public function deleteForTest(string $table, ?array $conditions = null): bool
    {
        return false;
    }

    /*
     * Transaction
     */
    /**
     * @return bool
     */
    public function hasTransaction(): bool
    {
        return $this->dbClass->hasTransaction();
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->dbClass->inTransaction();
    }

    /**
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->dbClass->beginTransaction();
    }

    /**
     * @return void
     */
    public function commitTransaction(): void
    {
        $this->dbClass->commitTransaction();
    }

    /**
     * @return void
     */
    public function rollbackTransaction(): void
    {
        $this->dbClass->rollbackTransaction();
    }

    /**
     * @return void
     */
    public function closeDBOperation(): void
    {
        $this->dbClass->closeDBOperation();
    }

    /**
     * @param array $condition
     * @return mixed
     * @throws Exception
     */
    public function normalizedCondition(array $condition)
    {
        throw new Exception("Don't use normalizedCondition method on DBClass instance without FileMaker ones.");
    }
}
