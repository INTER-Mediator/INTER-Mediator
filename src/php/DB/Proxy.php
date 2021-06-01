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
use INTERMediator\FileUploader;
use INTERMediator\IMUtil;
use INTERMediator\LDAPAuth;
use INTERMediator\SAMLAuth;
use INTERMediator\Locale\IMLocale;
use INTERMediator\Messaging\MessagingProxy;
use INTERMediator\NotifyServer;
use INTERMediator\ServiceServerProxy;
use phpseclib\Crypt\RSA;

class Proxy extends UseSharedObjects implements Proxy_Interface
{
    public $dbClass = null; // for Default context
    public $authDbClass = null; // for issuedhash context
    private $userExpanded = null;
    public $outputOfProcessing = null;
    public $paramAuthUser = null;

    private $paramResponse = null;
    private $paramResponse2m = null;
    private $paramResponse2 = null;
    private $paramCryptResponse = null;
    private $credential = null;
    private $authSucceed = false;
    private $clientId;
    private $passwordHash;
    private $alwaysGenSHA2;
    private $originalAccess;
    private $clientSyncAvailable;

    private $ignorePost = false;
    private $PostData = null;

    private $accessLogLevel;
    private $result4Log = [];
    private $isStopNotifyAndMessaging = false;
    private $suppressMediaToken = false;
    private $migrateSHA1to2;

    public function setClientId($cid) // For testing
    {
        $this->clientId = $cid;
    }

    public function setParamResponse($res) // For testing
    {
        if (is_array($res)) {
            $this->paramResponse = isset($res[0]) ? $res[0] : null;
            $this->paramResponse2m = isset($res[1]) ? $res[1] : null;
            $this->paramResponse2 = isset($res[2]) ? $res[2] : null;
        } else {
            $this->paramResponse = $res;
        }
    }

    public static function defaultKey()
    {
        trigger_error("Don't call the static method defaultKey of Proxy class.");
        return null;
    }

    public function getDefaultKey()
    {
        return $this->dbClass->specHandler->getDefaultKey();
    }

    public function setStopNotifyAndMessaging()
    {
        $this->isStopNotifyAndMessaging = true;
    }

    public function addOutputData($key, $value)
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
            $this->outputOfProcessing[$key] = (string)$this->outputOfProcessing[$key] . (string)$value;
        }
    }

    public function exportOutputDataAsJSON()
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

    public function exportOutputDataAsJason()
    {
        $this->exportOutputDataAsJSON();
    }

    public function getResultForLog()
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
     */
    function __construct($testmode = false)
    {
        $this->PostData = $_POST;
        $this->outputOfProcessing = [];
        if (!$testmode) {
            header('Content-Type: text/javascript;charset="UTF-8"');
            header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
            header('Expires: 0');
            header('X-Content-Type-Options: nosniff');
            $util = new IMUtil();
            $util->outputSecurityHeaders();
        }
    }

    /**
     * @param $dataSourceName
     * @return mixed
     */
    function readFromDB()
    {
        $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
        try {
            $className = is_null($this->userExpanded) ? null : get_class($this->userExpanded);
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeReadFromDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeReadFromDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeReadFromDB();
            }

            if ($this->dbClass) {
                $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                if (isset($tableInfo['soft-delete'])) {
                    $delFlagField = 'delete';
                    if (is_string($tableInfo['soft-delete'])) {
                        $delFlagField = $tableInfo['soft-delete'];
                    }
                    $this->dbClass->softDeleteActivate($delFlagField, 1);
                    $this->logger->setDebugMessage(
                        "The soft-delete applies to this query with '{$delFlagField}' field.", 2);
                }
                $result = $this->dbClass->readFromDB();
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterReadFromDB")) {
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
            $msgEntry = isset($currentDataSource['send-mail']) ? $currentDataSource['send-mail'] :
                (isset($currentDataSource['messaging']) ? $currentDataSource['messaging'] : null);
            if ($msgEntry) {
                $msgArray = isset($msgEntry['load']) ? $msgEntry['load'] :
                    (isset($msgEntry['read']) ? $msgEntry['read'] : null);
                if ($msgArray) {
                    $this->logger->setDebugMessage("Try to send a message.", 2);
                    $driver = isset($msgEntry['driver']) ? $msgEntry['driver'] : "mail";
                    $msgProxy = new MessagingProxy($driver);
                    $msgResult = $msgProxy->processing($this, $msgArray, $result);
                    if ($msgResult !== true) {
                        $this->logger->setErrorMessage("Mail sending error: $msgResult");
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[1] {$e->getMessage()}");
            return false;
        }
        return $result;
    }

    /**
     * @param $dataSourceName
     * @return mixed
     */
    function countQueryResult()
    {
        $result = null;
        $className = is_null($this->userExpanded) ? null : get_class($this->userExpanded);
        if ($this->userExpanded && method_exists($this->userExpanded, "countQueryResult")) {
            $this->logger->setDebugMessage("The method 'countQueryResult' of the class '{$className}' is calling.", 2);
            $result = $this->userExpanded->countQueryResult();
        }
        if ($this->dbClass) {
            $result = $this->dbClass->countQueryResult();
        }
        return $result;
    }

    /**
     * @param $dataSourceName
     * @return mixed
     */
    function getTotalCount()
    {
        $result = null;
        $className = is_null($this->userExpanded) ? null : get_class($this->userExpanded);
        if ($this->userExpanded && method_exists($this->userExpanded, "getTotalCount")) {
            $this->logger->setDebugMessage("The method 'getTotalCount' of the class '{$className}' is calling.", 2);
            $result = $this->userExpanded->getTotalCount();
        }
        if ($this->dbClass) {
            $result = $this->dbClass->getTotalCount();
        }
        return $result;
    }

    /**
     * @param $dataSourceName
     * @return mixed
     */
    function updateDB($bypassAuth)
    {
        $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
        try {
            $className = is_null($this->userExpanded) ? "" : get_class($this->userExpanded);
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeUpdateDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeUpdateDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeUpdateDB(false);
            }
            if ($this->dbClass) {
                $this->dbClass->requireUpdatedRecord(true); // Always Get Updated Record
                $result = $this->dbClass->updateDB($bypassAuth);
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterUpdateToDB")) {
                $this->logger->setDebugMessage("The method 'doAfterUpdateToDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterUpdateToDB($result);
            }
            if ($this->dbSettings->notifyServer
                && $this->clientSyncAvailable
                && isset($currentDataSource['sync-control'])
                && strpos(strtolower($currentDataSource['sync-control']), 'update') !== false) {
                try {
                    $this->dbSettings->notifyServer->updated(
                        $this->PostData['notifyid'],
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
            $msgEntry = isset($currentDataSource['send-mail']) ? $currentDataSource['send-mail'] :
                (isset($currentDataSource['messaging']) ? $currentDataSource['messaging'] : null);
            if ($msgEntry) {
                $msgArray = isset($msgEntry['edit']) ? $msgEntry['edit'] :
                    (isset($msgEntry['update']) ? $msgEntry['update'] : null);
                if ($msgArray) {
                    $this->logger->setDebugMessage("Try to send a message.", 2);
                    $driver = isset($msgEntry['driver']) ? $msgEntry['driver'] : "mail";
                    $msgProxy = new MessagingProxy($driver);
                    $msgResult = $msgProxy->processing($this, $msgArray, $this->dbClass->updatedRecord());
                    if ($msgResult !== true) {
                        $this->logger->setErrorMessage("Mail sending error: $msgResult");
                    }
                }
            }

        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[2] {$e->getMessage()}");
            return false;
        }
        return $result;
    }

    /**
     * @param $dataSourceName
     * @param $bypassAuth
     * @return mixed
     */
    public function createInDB($isReplace = false)
    {
        $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
        try {
            $className = is_null($this->userExpanded) ? "" : get_class($this->userExpanded);
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeCreateToDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeCreateToDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeCreateToDB();
            }
            if ($this->dbClass) {
                $this->dbClass->requireUpdatedRecord(true); // Always Requred Created Record
                $resultOfCreate = $this->dbClass->createInDB($isReplace);
                $result = $this->dbClass->updatedRecord();
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterCreateToDB")) {
                $this->logger->setDebugMessage("The method 'doAfterCreateToDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterCreateToDB($result);
            }
            if (!$this->isStopNotifyAndMessaging) {
                if ($this->dbSettings->notifyServer
                    && $this->clientSyncAvailable
                    && isset($currentDataSource['sync-control'])
                    && strpos(strtolower($currentDataSource['sync-control']), 'create') !== false) {
                    try {
                        $this->dbSettings->notifyServer->created(
                            $this->PostData['notifyid'],
                            $this->dbClass->notifyHandler->queriedEntity(),
                            $this->dbClass->notifyHandler->queriedPrimaryKeys(),
                            $result,
                            strpos(strtolower($currentDataSource['sync-control']), 'create-notify') !== false
                        );
                    } catch (Exception $ex) {
                        throw $ex;
                    }
                }
                // Messaging
                $msgEntry = isset($currentDataSource['send-mail']) ? $currentDataSource['send-mail'] :
                    (isset($currentDataSource['messaging']) ? $currentDataSource['messaging'] : null);
                if ($msgEntry) {
                    $msgArray = isset($msgEntry['new']) ? $msgEntry['new'] :
                        (isset($msgEntry['create']) ? $msgEntry['create'] : null);
                    if ($msgArray) {
                        $this->logger->setDebugMessage("Try to send a message.", 2);
                        $driver = isset($msgEntry['driver']) ? $msgEntry['driver'] : "mail";
                        $msgProxy = new MessagingProxy($driver);
                        $msgResult = $msgProxy->processing($this, $msgArray, $result);
                        if ($msgResult !== true) {
                            $this->logger->setErrorMessage("Mail sending error: $msgResult");
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[3] {$e->getMessage()}");
            return false;
        }
        return $resultOfCreate;

    }

    /**
     * @param $dataSourceName
     * @return mixed
     */
    function deleteFromDB()
    {
        try {
            $className = is_null($this->userExpanded) ? "" : get_class($this->userExpanded);
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeDeleteFromDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeDeleteFromDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeDeleteFromDB();
            }
            if ($this->dbClass) {
                $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                if (isset($tableInfo['soft-delete'])) {
                    $delFlagField = is_string($tableInfo['soft-delete']) ? $tableInfo['soft-delete'] : 'delete';
                    $this->logger->setDebugMessage(
                        "The soft-delete applies to this delete operation with '{$delFlagField}' field.", 2);
                    $this->dbSettings->addValueWithField($delFlagField, 1);
                    $result = $this->dbClass->updateDB(false);
                } else {
                    $result = $this->dbClass->deleteFromDB();
                }
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterDeleteFromDB")) {
                $this->logger->setDebugMessage("The method 'doAfterDeleteFromDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterDeleteFromDB($result);
            }
            $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
            if ($this->dbSettings->notifyServer
                && $this->clientSyncAvailable
                && isset($currentDataSource['sync-control'])
                && strpos(strtolower($currentDataSource['sync-control']), 'delete') !== false) {
                try {
                    $this->dbSettings->notifyServer->deleted(
                        $this->PostData['notifyid'],
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
     * @param $dataSourceName
     * @return mixed
     */
    function copyInDB()
    {
        try {
            $className = is_null($this->userExpanded) ? "" : get_class($this->userExpanded);
            if ($this->userExpanded && method_exists($this->userExpanded, "doBeforeCopyInDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeCopyInDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeCopyInDB();
            }
            if ($this->dbClass) {
                $result = $this->dbClass->copyInDB();
            }
            if ($this->userExpanded && method_exists($this->userExpanded, "doAfterCopyInDB")) {
                $this->logger->setDebugMessage("The method 'doAfterCopyInDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterCopyInDB($result);
            }
            if ($this->dbSettings->notifyServer
                && $this->clientSyncAvailable
                && isset($currentDataSource['sync-control'])
                && strpos(strtolower($currentDataSource['sync-control']), 'create') !== false) {
                try {
                    $this->dbSettings->notifyServer->created(
                        $this->PostData['notifyid'],
                        $this->dbClass->notifyHandler->queriedEntity(),
                        $this->dbClass->notifyHandler->queriedPrimaryKeys(),
                        $this->dbClass->updatedRecord(),
                        strpos(strtolower($currentDataSource['sync-control']), 'create-notify') !== false
                    );
                } catch (Exception $ex) {
                    throw $ex;
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception:[5] {$e->getMessage()}");
            return false;
        }
        return $result;
    }

    /**
     * @param $dataSourceName
     * @return mixed
     */
    function getFieldInfo($dataSourceName)
    {
        if ($this->dbClass) {
            $result = $this->dbClass->getFieldInfo($dataSourceName);
        }
        return $result;
    }

    public function requireUpdatedRecord($value)
    {
        if ($this->dbClass) {
            $this->dbClass->requireUpdatedRecord($value);
        }
    }

    public function updatedRecord()
    {
        if ($this->dbClass) {
            return $this->dbClass->updatedRecord();
        }
        return null;
    }

    public function ignoringPost()
    {
        $this->ignorePost = true;
    }

    public function ignorePost()
    {
        $this->ignorePost = true;
    }

    /**
     * @param $datasource
     * @param $options
     * @param $dbspec
     * @param $debug
     * @param null $target
     * @return bool
     */
    function initialize($datasource, $options, $dbspec, $debug, $target = null)
    {
        $this->PostData = $this->ignorePost ? array() : $_POST;
        $this->setUpSharedObjects();
        $this->logger->setDebugMessage("Start to initialize the DB\Proxy class instance.", 2);

        $params = IMUtil::getFromParamsPHPFile(array(
            "dbClass", "dbServer", "dbPort", "dbUser", "dbPassword", "dbDataType", "dbDatabase", "dbProtocol",
            "dbOption", "dbDSN", "prohibitDebugMode", "issuedHashDSN", "sendMailSMTP",
            "activateClientService", "accessLogLevel", "certVerifying", "passwordHash", "alwaysGenSHA2",
            "isSAML", "samlAuthSource", "migrateSHA1to2",
        ), true);
        $this->accessLogLevel = intval($params['accessLogLevel']);
        $this->clientSyncAvailable = (isset($params["activateClientService"]) && $params["activateClientService"]);
        $this->passwordHash = isset($params['passwordHash']) ? $params['passwordHash'] : "1";
        $this->alwaysGenSHA2 = isset($params['alwaysGenSHA2']) ? boolval($params['alwaysGenSHA2']) : false;
        $this->migrateSHA1to2 = isset($params['migrateSHA1to2']) ? boolval($params['migrateSHA1to2']) : false;

        $this->dbSettings->setDataSource($datasource);
        $this->dbSettings->setOptions($options);
        IMLocale::$options = $options;
        $this->dbSettings->setDbSpec($dbspec);

        $this->dbSettings->setSeparator(isset($options['separator']) ? $options['separator'] : '@');
        $this->formatter->setFormatter(isset($options['formatter']) ? $options['formatter'] : null);
        $this->dbSettings->setDataSourceName(!is_null($target) ? $target : (isset($this->PostData['name']) ? $this->PostData['name'] : "_im_auth"));
        $context = $this->dbSettings->getDataSourceTargetArray();
        if (count($_FILES) > 0) {
            $this->dbSettings->setAttachedFiles($context['name'], $_FILES);
        }

        $dbClassName = '\\INTERMediator\\DB\\' .
            (isset($context['db-class']) ? $context['db-class'] :
                (isset($dbspec['db-class']) ? $dbspec['db-class'] :
                    (isset ($params['dbClass']) ? $params['$dbClass'] : '')));
        $this->dbSettings->setDbSpecServer(
            isset($context['server']) ? $context['server'] :
                (isset($dbspec['server']) ? $dbspec['server'] :
                    (isset ($params['dbServer']) ? $params['dbServer'] : '')));
        $this->dbSettings->setDbSpecPort(
            isset($context['port']) ? $context['port'] :
                (isset($dbspec['port']) ? $dbspec['port'] :
                    (isset ($params['dbPort']) ? $params['dbPort'] : '')));
        $this->dbSettings->setDbSpecUser(
            isset($context['user']) ? $context['user'] :
                (isset($dbspec['user']) ? $dbspec['user'] :
                    (isset ($params['dbUser']) ? $params['dbUser'] : '')));
        $this->dbSettings->setDbSpecPassword(
            isset($context['password']) ? $context['password'] :
                (isset($dbspec['password']) ? $dbspec['password'] :
                    (isset ($params['dbPassword']) ? $params['dbPassword'] : '')));
        $this->dbSettings->setDbSpecDataType(
            isset($context['datatype']) ? $context['datatype'] :
                (isset($dbspec['datatype']) ? $dbspec['datatype'] :
                    (isset ($params['dbDataType']) ? $params['dbDataType'] : '')));
        $this->dbSettings->setDbSpecDatabase(
            isset($context['database']) ? $context['database'] :
                (isset($dbspec['database']) ? $dbspec['database'] :
                    (isset ($params['dbDatabase']) ? $params['dbDatabase'] : '')));
        $this->dbSettings->setDbSpecProtocol(
            isset($context['protocol']) ? $context['protocol'] :
                (isset($dbspec['protocol']) ? $dbspec['protocol'] :
                    (isset ($params['dbProtocol']) ? $params['dbProtocol'] : '')));
        $this->dbSettings->setDbSpecOption(
            isset($context['option']) ? $context['option'] :
                (isset($dbspec['option']) ? $dbspec['option'] :
                    (isset ($params['dbOption']) ? $params['dbOption'] : '')));
        $this->dbSettings->setCertVerifying(
            isset($context['cert-verifying']) ? $context['cert-verifying'] :
                (isset($dbspec['cert-verifying']) ? $dbspec['cert-verifying'] :
                    (isset ($params['certVerifying']) ? $params['certVerifying'] : true)));
        if (isset($options['authentication']) && isset($options['authentication']['issuedhash-dsn'])) {
            $this->dbSettings->setDbSpecDSN($options['authentication']['issuedhash-dsn']);
        } else {
            $this->dbSettings->setDbSpecDSN(
                isset($context['dsn']) ? $context['dsn'] :
                    (isset($dbspec['dsn']) ? $dbspec['dsn'] :
                        (isset ($params['dbDSN']) ? $params['dbDSN'] : '')));
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
        if ((!isset($params['prohibitDebugMode']) || !$params['prohibitDebugMode']) && $debug) {
            $this->logger->setDebugMode($debug);
        }
        $this->dbSettings->setAggregationSelect(
            isset($context['aggregation-select']) ? $context['aggregation-select'] : null);
        $this->dbSettings->setAggregationFrom(
            isset($context['aggregation-from']) ? $context['aggregation-from'] : null);
        $this->dbSettings->setAggregationGroupBy(
            isset($context['aggregation-group-by']) ? $context['aggregation-group-by'] : null);

        /* Authentication and Authorization Judgement */
        $challengeDSN = null;
        if (isset($options['authentication']) && isset($options['authentication']['issuedhash-dsn'])) {
            $challengeDSN = $options['authentication']['issuedhash-dsn'];
        } else if (isset($params['issuedHashDSN'])) {
            $challengeDSN = $params['issuedHashDSN'];
        }
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
            if (isset($this->PostData['notifyid'])
                && $this->dbSettings->notifyServer->initialize($this->authDbClass, $this->dbSettings, $this->PostData['notifyid'])
            ) {
                $this->logger->setDebugMessage("The NotifyServer was instanciated.", 2);
            }
        }

        $this->dbSettings->setCurrentDataAccess($this->dbClass);

        if (isset($context['extending-class'])) {
            $className = $context['extending-class'];
            $this->userExpanded = new $className();
            if ($this->userExpanded === null) {
                $this->logger->setErrorMessage("The class '{$className}' wasn't instanciated.");
            } else {
                $this->logger->setDebugMessage("The class '{$className}' was instanciated.", 2);
            }
            if (is_subclass_of($this->userExpanded, '\INTERMediator\DB\UseSharedObjects')) {
                $this->userExpanded->setUpSharedObjects($this);
            }
        }
        $this->dbSettings->setPrimaryKeyOnly(isset($this->PostData['pkeyonly']));

        $this->dbSettings->setCurrentUser(isset($this->PostData['authuser']) ? $this->PostData['authuser'] : null);
        $this->dbSettings->setAuthentication(isset($options['authentication']) ? $options['authentication'] : null);

        $this->dbSettings->setStart(isset($this->PostData['start']) ? $this->PostData['start'] : 0);
        $this->dbSettings->setRecordCount(isset($this->PostData['records']) ? $this->PostData['records'] : 10000000);

        for ($count = 0; $count < 10000; $count++) {
            if (isset($this->PostData["condition{$count}field"])) {
                $this->dbSettings->addExtraCriteria(
                    $this->PostData["condition{$count}field"],
                    isset($this->PostData["condition{$count}operator"]) ? $this->PostData["condition{$count}operator"] : '=',
                    isset($this->PostData["condition{$count}value"]) ? $this->PostData["condition{$count}value"] : null);
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
        if (isset($options['authentication']) && isset($options['authentication']['email-as-username'])) {
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
        } else if (isset($params['sendMailSMTP'])) {
            $this->dbSettings->setSmtpConfiguration($params['sendMailSMTP']);
        }

        $this->paramAuthUser = isset($this->PostData['authuser']) ? $this->PostData['authuser'] : "";
        $this->paramResponse = isset($this->PostData['response']) ? $this->PostData['response'] : "";
        $this->paramResponse2m = isset($this->PostData['response2m']) ? $this->PostData['response2m'] : "";
        $this->paramResponse2 = isset($this->PostData['response2']) ? $this->PostData['response2'] : "";
        $this->paramCryptResponse = isset($this->PostData['cresponse']) ? $this->PostData['cresponse'] : "";
        $this->credential = isset($_COOKIE['_im_credential_token']) ? $_COOKIE['_im_credential_token'] : "";
        $this->clientId = isset($this->PostData['clientid']) ? $this->PostData['clientid'] :
            (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "Non-browser-client");

        $this->dbSettings->setMediaRoot(isset($options['media-root-dir']) ? $options['media-root-dir'] : null);

        $this->logger->setDebugMessage("Server side locale: " . setlocale(LC_ALL, "0"), 2);

        if (isset($params['isSAML'])) {
            $this->dbSettings->setIsSAML($params['isSAML']);
        }
        if (isset($params['samlAuthSource'])) {
            $this->dbSettings->setSAMLAuthSource($params['samlAuthSource']);
        }
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
     * @param $options
     * @param null $access
     * @param bool $bypassAuth
     */
    function processingRequest($access = null, $bypassAuth = false, $ignoreFiles = false)
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
//            $authStoring = $this->dbSettings->getAuthenticationItem('storing');
//            if ($authStoring != 'credential'||$access != 'challenge') {
                $this->dbSettings->setRequireAuthorization(true);
//            }
            if (isset($authOptions['user'])
                && $authOptions['user'][0] == 'database_native'
            ) {
                $this->dbSettings->setDBNative(true);
            }
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
                if ($this->dbSettings->isDBNative()) {
                    list($password, $challenge) = $this->decrypting($this->paramCryptResponse);
                    if ($password !== false) {
                        if (!$this->checkChallenge($challenge, $this->clientId)) {
                            $access = "do nothing";
                            $this->dbSettings->setRequireAuthentication(true);
                        } else {
                            $this->dbSettings->setUserAndPasswordForAccess($this->paramAuthUser, $password);
                            $this->logger->setDebugMessage("[checkChallenge] returns true.", 2);
                        }
                    } else {
                        $this->logger->setDebugMessage("Can't decrypt.");
                        $access = "do nothing";
                        $this->dbSettings->setRequireAuthentication(true);
                    }
                } else { // Other than native authentication
                    $noAuthorization = true;
                    $authorizedGroups = $this->dbClass->authHandler->getAuthorizedGroups($access);
                    $authorizedUsers = $this->dbClass->authHandler->getAuthorizedUsers($access);

                    $this->logger->setDebugMessage(str_replace("\n", "",
                            "contextName={$this->dbSettings->getDataSourceName()}/access={$access}/"
                            . "authorizedUsers=" . var_export($authorizedUsers, true)
                            . "/authorizedGroups=" . var_export($authorizedGroups, true))
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

                    $ldap = new LDAPAuth();
                    $ldap->setLogger($this->logger);
                    if ($ldap->isActive) { // LDAP auth
                        if ($this->checkAuthorization($signedUser, true)) {
                            $this->logger->setDebugMessage("IM-built-in Authentication for LDAP user succeed.");
                            $this->authSucceed = true;
                        } else { // Timeout with LDAP
                            list($password, $challenge) = $this->decrypting($this->paramCryptResponse);
                            if ($ldap->bindCheck($signedUser, $password)) {
                                $this->logger->setDebugMessage("LDAP Authentication succeed.");
                                $this->authSucceed = true;
                                [$addResult, $hashedpw] = $this->addUser($signedUser, $password, true);
                                if ($addResult) {
                                    $this->dbSettings->setRequireAuthentication(false);
                                    $this->dbSettings->setCurrentUser($signedUser);
                                    $access = $this->originalAccess;
                                }
                                // The following re-auth doesn't work. The salt of hashed password is
                                // different from the request. Here is after bind checking, so authentication
                                // is passed anyway.
//                                    if ($this->checkAuthorization($signedUser, true)) {
//                                        $this->logger->setDebugMessage("IM-built-in Authentication succeed.");
//                                        $this->authSucceed = true;
//                                    }
                            }
                        }
                    } else if ($this->dbSettings->getIsSAML()) { // Set up as SAML
                        if ($this->checkAuthorization($signedUser, true)) {
                            $this->logger->setDebugMessage("IM-built-in Authentication for SAML user succeed.");
                            $this->authSucceed = true;
                        } else { // Timeout with SAML
                            $SAMLAuth = new SAMLAuth($this->dbSettings->getSAMLAuthSource());
                            $signedUser = $SAMLAuth->samlLoginCheck();
                            $this->outputOfProcessing['samlloginurl'] = $SAMLAuth->samlLoginURL($_SERVER['HTTP_REFERER']);
                            $this->outputOfProcessing['samllogouturl'] = $SAMLAuth->samlLogoutURL($_SERVER['HTTP_REFERER']);
                            $this->paramAuthUser = $signedUser;
                            if ($signedUser) {
                                $this->logger->setDebugMessage("SAML Authentication succeed.");
                                $this->authSucceed = true;
                                $password = IMUtil::generateRandomPW();
                                [$addResult, $hashedpw] = $this->addUser($signedUser, $password, true);
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
                    for ($index = 0;
                         $index < $recordCount;
                         $index++) {
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
                    $this->outputOfProcessing['dbresult'] = $this->dbClass->updatedRecord();
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
                    $this->logger->setDebugMessage("File importing operation gets stated.", 2);
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
                        $result = $this->createInDB($access == 'replace');
                        $this->outputOfProcessing['newRecordKeyValue'] = $result;
                        $this->outputOfProcessing['dbresult'] = $this->dbClass->updatedRecord();
                        if (!$ignoreFiles && $result !== false) {
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
                                        $tableInfo['name'], $tableInfo['key'], $result,
                                        $this->dbSettings->getAttachedFields(), $uploadFiles, true
                                    );
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
                    $this->outputOfProcessing['dbresult'] = $this->dbClass->updatedRecord();
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
                    $this->outputOfProcessing['changePasswordResult'] = ($changeResult ? true : false);
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
    function finishCommunication($notFinish = false)
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
                        $this->generateCredential($generatedChallenge, $generatedUID),
                        time() + $this->dbSettings->getAuthenticationItem('authexpired'),
                        '/', $_SERVER['SERVER_NAME'], false, true);
                } else {
                    setcookie("_im_credential_token", "", time() - 3600);
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
            if (isset($tableInfo['authentication']) &&
                isset($tableInfo['authentication']['media-handling']) &&
                $tableInfo['authentication']['media-handling'] === true &&
                !$this->suppressMediaToken
            ) {
                $generatedChallenge = IMUtil::generateChallenge();
                $this->saveChallenge($this->paramAuthUser, $generatedChallenge, "_im_media");
                //$this->outputOfProcessing['mediatoken'] = $generatedChallenge;
                setcookie('_im_mediatoken', $generatedChallenge,
                    time() + $this->dbSettings->getAuthenticationItem('authexpired'),
                    '/', $_SERVER['SERVER_NAME'], false, true);
                setcookie('_im_username', $this->paramAuthUser,
                    time() + $this->dbSettings->getAuthenticationItem('authexpired'),
                    '/', $_SERVER['SERVER_NAME'], false, false);
                $this->logger->setDebugMessage("mediatoken stored", 2);
            }
        }
        $this->addOutputData('errorMessages', $this->logger->getErrorMessages());
        $this->addOutputData('debugMessages', $this->logger->getDebugMessages());
        //$this->outputOfProcessing['errorMessages'] = $this->logger->getErrorMessages();
        //$this->outputOfProcessing['debugMessages'] = $this->logger->getDebugMessages();
    }

    private function generateCredential($generatedChallenge, $generatedUID)
    {
        return hash("sha256", $generatedChallenge . $generatedUID);
    }

    public
    function getDatabaseResult()
    {
        if (isset($this->outputOfProcessing['dbresult'])) {
            return $this->outputOfProcessing['dbresult'];
        }
        return null;
    }

    public
    function getDatabaseResultCount()
    {
        if (isset($this->outputOfProcessing['resultCount'])) {
            return $this->outputOfProcessing['resultCount'];
        }
        return null;
    }

    public
    function getDatabaseTotalCount()
    {
        if (isset($this->outputOfProcessing['totalCount'])) {
            return $this->outputOfProcessing['totalCount'];
        }
        return null;
    }

    public
    function getDatabaseNewRecordKey()
    {
        if (isset($this->outputOfProcessing['newRecordKeyValue'])) {
            return $this->outputOfProcessing['newRecordKeyValue'];
        }
        return null;
    }

    /* Authentication support */
    function decrypting($paramCryptResponse)
    {
        $password = FALSE;
        $challenge = FALSE;

        $generatedPrivateKey = '';
        $passPhrase = '';

        $imRootDir = IMUtil::pathToINTERMediator() . DIRECTORY_SEPARATOR;
        $currentDirParam = $imRootDir . 'params.php';
        $parentDirParam = dirname($imRootDir) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }

        /* cf.) encrypted in generate_authParams() of Adapter_DBServer.js */
        $rsa = new RSA();
        $rsa->setPassword($passPhrase);
        $rsa->loadKey($generatedPrivateKey);
        $rsa->setEncryptionMode(RSA::ENCRYPTION_PKCS1);
        $token = isset($_SESSION['FM-Data-token']) ? $_SESSION['FM-Data-token'] : '';
        $array = explode("\n", $paramCryptResponse);
        if (strlen($array[0]) > 0 && isset($array[1]) && strlen($array[1]) > 0) {
            $encryptedArray = explode("\n", $rsa->decrypt(base64_decode($array[0])));
            if (isset($encryptedArray[1])) {
                $challenge = $encryptedArray[1];
            }
            $encryptedPassword = $encryptedArray[0] . $array[1];
            if (strlen($encryptedPassword) > 0) {
                if (strlen($token) > 0 && get_class($this->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') {
                    $password = '';
                } else {
                    $password = $rsa->decrypt(base64_decode($encryptedPassword));
                }
            } else {
                return array(FALSE, FALSE);
            }
        }

        return array($password, $challenge);
    }

    /**
     * @param $username
     * @return string
     */
    function authSupportGetSalt($username)
    {
        $hashedpw = $this->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
        return substr($hashedpw, -8);
    }

    /* returns user's hash salt.

    */
    /**
     * @param $username
     * @param $challenge
     * @param $clientId
     * @return string
     */
    function saveChallenge($username, $challenge, $clientId, $isLDAP = false)
    {
        $this->logger->setDebugMessage(
            "[saveChallenge]user=${username}, challenge={$challenge}, clientid={$clientId}, isLDAP={$isLDAP}", 2);
        $username = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($username);
        $uid = $this->dbClass->authHandler->authSupportGetUserIdFromUsername($username);
        $this->authDbClass->authHandler->authSupportStoreChallenge($uid, $challenge, $clientId);
        return $username === 0 ? "" : $this->authSupportGetSalt($username);
    }

    /**
     * @param $username
     * @param $isLDAP
     * @return bool
     */
    function checkAuthorization($username, $isLDAP = false): bool
    {
        $falseHash = hash("sha256", uniqid("", true)); // for failing auth.
        $hashedvalue = $this->paramResponse ? $this->paramResponse : $falseHash;
        $hashedvalue2m = $this->paramResponse2m ? $this->paramResponse2m : $falseHash;
        $hashedvalue2 = $this->paramResponse2 ? $this->paramResponse2 : $falseHash;
        $this->logger->setDebugMessage("[checkAuthorization]user=${username}, paramResponse={$hashedvalue}, "
            . "paramResponse2m={$hashedvalue2m}, paramResponse2={$hashedvalue2}, clientid={$this->clientId}", 2);

        $returnValue = false;
        $this->authDbClass->authHandler->authSupportRemoveOutdatedChallenges();
        $signedUser = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($username);
        $uid = $this->dbClass->authHandler->authSupportGetUserIdFromUsername($signedUser);
        $this->logger->setDebugMessage("[checkAuthorization]uid={$uid}", 2);
        if ($uid <= 0) {
            return $returnValue;
        }
        if ($isLDAP && !$this->dbClass->authHandler->authSupportIsWithinLDAPLimit($uid)) {
            return $returnValue;
        }
        $storedChallenge = $this->authDbClass->authHandler->authSupportRetrieveChallenge($uid, $this->clientId);
        $this->logger->setDebugMessage("[checkAuthorization]storedChallenge={$storedChallenge}/{$this->credential}", 2);
        if (strlen($storedChallenge) == 24) { // ex.fc0d54312ce33c2fac19d758
            if ($this->credential == $this->generateCredential($storedChallenge, $this->clientId)) { // Credential Auth passed
                $this->logger->setDebugMessage("[checkAuthorization]Credential auth passed.", 2);
                $returnValue = true;
            } else { // Hash Auth checking
                $hashedPassword = $this->dbClass->authHandler->authSupportRetrieveHashedPassword($username);
                $hmacValue = hash_hmac('sha256', $hashedPassword, $storedChallenge);
                $hmacValue2m = hash_hmac('sha256', $hashedPassword, $storedChallenge);
                $this->logger->setDebugMessage(
                    "[checkAuthorization]hashedPassword={$hashedPassword}/hmac_value={$hmacValue}", 2);
                if (strlen($hashedPassword) > 0) {
                    if ($hashedvalue == $hmacValue) {
                        $this->logger->setDebugMessage("[checkAuthorization]sha1 hash used.", 2);
                        $returnValue = true;
                        if ($this->migrateSHA1to2) {
                            $salt = hex2bin(substr($hashedPassword, -8));
                            $hashedPw = IMUtil::convertHashedPassword($hashedPassword, $this->passwordHash, true, $salt);
                            $result = $this->dbClass->authHandler->authSupportChangePassword($signedUser, $hashedPw);
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
     * @param $challenge
     * @param $clientId
     * @return bool
     */
    function checkChallenge($challenge, $clientId)
    {
        $returnValue = false;
        $this->authDbClass->authHandler->authSupportRemoveOutdatedChallenges();
        // Database user mode is user_id=0
        $storedChallenge = $this->authDbClass->authHandler->authSupportRetrieveChallenge(0, $clientId);
        if (strlen($storedChallenge) == 24 && $storedChallenge == $challenge) { // ex.fc0d54312ce33c2fac19d758
            $returnValue = true;
        }
        return $returnValue;
    }

    /**
     * @param $user
     * @param $token
     * @return bool
     */
    function checkMediaToken($user, $token)
    {
        $this->logger->setDebugMessage("[checkMediaToken] user={$user}, token={$token}", 2);
        $returnValue = false;
        $this->authDbClass->authHandler->authSupportRemoveOutdatedChallenges();
        // Database user mode is user_id=0
        $user = $this->dbClass->authHandler->authSupportUnifyUsernameAndEmail($user);
        $uid = $this->dbClass->authHandler->authSupportGetUserIdFromUsername($user);
        $storedChallenge = $this->authDbClass->authHandler->authSupportCheckMediaToken($uid);
        if (strlen($storedChallenge) == 24 && $storedChallenge == $token) { // ex.fc0d54312ce33c2fac19d758
            $returnValue = true;
        }
        return $returnValue;
    }


    /**
     * @param $username
     * @param $password
     * @return mixed
     */
    function addUser($username, $password, $isLDAP = false)
    {
        $this->logger->setDebugMessage("[addUser] username={$username}, isLDAP={$isLDAP}", 2);
        $hashedPw = IMUtil::convertHashedPassword($password, $this->passwordHash, $this->alwaysGenSHA2);
        $returnValue = $this->dbClass->authHandler->authSupportCreateUser($username, $hashedPw, $isLDAP, $password);
        $this->logger->setDebugMessage("[addUser] authSupportCreateUser returns: {$returnValue}", 2);
        return [$returnValue, $hashedPw];
    }

    /**
     * @param $username
     * @param $newpassword
     * @return mixed
     */
    function changePassword($username, $newpassword)
    {
        $returnValue = $this->dbClass->authHandler->authSupportChangePassword($username, $newpassword);
        return $returnValue;
    }

    /**
     * @param $email
     * @return array|bool
     */
    function resetPasswordSequenceStart($email)
    {
        if ($email === false || $email == '') {
            return false;
        }
        $userid = $this->dbClass->authHandler->authSupportGetUserIdFromEmail($email);
        $username = $this->dbClass->authHandler->authSupportGetUsernameFromUserId($userid);
        if ($username === false || $username == '') {
            return false;
        }
        $clienthost = IMUtil::generateChallenge();
        $hash = sha1($clienthost . $email . $username);
        if ($this->authDbClass->authHandler->authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)) {
            return array('randdata' => $clienthost, 'username' => $username);
        }
        return false;
    }

    /**
     * @param $username
     * @param $email
     * @param $randdata
     * @param $newpassword
     * @return bool
     *
     * Using
     */
    function resetPasswordSequenceReturnBack($username, $email, $randdata, $newpassword)
    {
        if (is_null($username) && !is_null($email)) {
            $userid = $this->dbClass->authHandler->authSupportGetUserIdFromEmail($email);
            $username = $this->dbClass->authHandler->authSupportGetUsernameFromUserId($userid);
        }
        if ($email === false || $email == '' || $username === false || $username == '') {
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

    function userEnrollmentStart($userID)
    {
        $hash = IMUtil::generateChallenge();
        $this->authDbClass->authHandler->authSupportUserEnrollmentStart($userID, $hash);
        return $hash;
    }

    function userEnrollmentActivateUser($challenge, $password, $rawPWField = false)
    {
        $userInfo = null;
        $userID = $this->authDbClass->authHandler->authSupportUserEnrollmentEnrollingUser($challenge);
        if ($userID < 1) {
            return false;
        }
        $result = $this->dbClass->authHandler->authSupportUserEnrollmentActivateUser(
            $userID, IMUtil::convertHashedPassword($password, $this->passwordHash, $this->alwaysGenSHA2),
            $rawPWField, $password);
//        if ($userID !== false) {
//            $hashednewpassword = $this->convertHashedPassword($password);
//            $userInfo = authSupportUserEnrollmentCheckHash($userID, $hashednewpassword);
//        }
        return $result;
    }

    private
    function checkValidation()
    {
        $inValid = false;
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        if (isset($tableInfo['validation'])) {
            $reqestedFieldValue = [];
            $counter = 0;
            $fieldValues = $this->dbSettings->getValue();
            foreach ($this->dbSettings->getFieldsRequired() as $field) {
                $value = $fieldValues[$counter];
                $reqestedFieldValue[$field] = (is_array($value)) ? implode("\n", $value) : $value;
                $counter++;
            }

            $serviceServer = ServiceServerProxy::instance();
            $inValid = false;
            foreach ($tableInfo['validation'] as $entry) {
                if (array_key_exists($entry['field'], $reqestedFieldValue)) {
                    $this->logger->setDebugMessage("Validation: field={$entry['field']}, rule={$entry['rule']}:", 2);
                    if (!$serviceServer->validate($entry['rule'], ["value" => $reqestedFieldValue[$entry['field']]])) {
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


    public
    function setupConnection()
    {
        // TODO: Implement setupConnection() method.
    }

    public
    function setupHandlers($dsn = false)
    {
        // TODO: Implement setupHandlers() method.
    }

    public
    function normalizedCondition($condition)
    {
        // TODO: Implement normalizedCondition() method.
    }

    public
    function softDeleteActivate($field, $value)
    {
        // TODO: Implement softDeleteActivate() method.
    }

    public
    function setUpdatedRecord($field, $value, $index = 0)
    {
        // TODO: Implement setUpdatedRecord() method.
    }

    public
    function queryForTest($table, $conditions = null)
    {
        // TODO: Implement queryForTest() method.
    }

    public
    function deleteForTest($table, $conditions = null)
    {
        // TODO: Implement deleteForTest() method.
    }
}
