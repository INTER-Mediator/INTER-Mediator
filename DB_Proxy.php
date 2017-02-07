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
class DB_Proxy extends DB_UseSharedObjects implements DB_Proxy_Interface
{
    /**
     * @var null
     */
    public $dbClass = null; // for Default context
    /**
     * @var null
     */
    public $authDbClass = null; // for issuedhash context
    /**
     * @var array
     */
    public $dbClassForContext = array();
    /**
     * @var null
     */
    private $userExpanded = null;
    /**
     * @var string
     */
    private $outputOfProcessing = null;
    /**
     * @var
     */
    public $paramAuthUser = null;

    public $paramResponse = null;
    public $paramCryptResponse = null;
    public $clientId;
    /**
     * @var
     */
    private $previousChallenge;
    /**
     * @var
     */
    private $previousClientid;

    private $clientPusherAvailable;

    private $ignorePost = false;
    private $PostData = null;

    public static function defaultKey()
    {
        trigger_error("Don't call the static method defaultKey of DB_Proxy class.");
        return null;
    }

    public function getDefaultKey()
    {
        return $this->dbClass->getDefaultKey();
    }

    public function addOutputData($key, $value)
    {
        return $this->outputOfProcessing[$key] = $value;
    }

    public function exportOutputDataAsJSON()
    {
        if (((float)phpversion()) >= 5.3) {
            echo json_encode($this->outputOfProcessing, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        } else {
            $this->outputOfProcessing = str_replace(
                array('\"', '&', '\'', '<', '>'),
                array('\u0022', '\u0026', '\u0027', '\u003C', '\u003E'),
                json_encode($this->outputOfProcessing)
            );
            echo $this->outputOfProcessing;
        }
    }

    public function exportOutputDataAsJason()
    {
        $this->exportOutputDataAsJSON();
    }

    /**
     * @param bool $testmode
     */
    function __construct($testmode = false)
    {
        $this->PostData = $_POST;
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
            $className = get_class($this->userExpanded);
//            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeGetFromDB")) {
//                $this->logger->setDebugMessage("The method 'doBeforeGetFromDB' of the class '{$className}' is calling.", 2);
//                $this->userExpanded->doBeforeGetFromDB();
//            }
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeReadFromDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeReadFromDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeReadFromDB();
            }

            if ($this->dbClass !== null) {
                //$this->logger->setDebugMessage("The method 'getFromDB' of the class '{$className}' is calling.", 2);
                $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                if (isset($tableInfo['soft-delete'])) {
                    $delFlagField = 'delete';
                    if ($tableInfo['soft-delete'] !== true) {
                        $delFlagField = $tableInfo['soft-delete'];
                    }
                    $this->dbClass->softDeleteActivate($delFlagField, 1);
                    $this->logger->setDebugMessage(
                        "The soft-delete applies to this query with '{$delFlagField}' field.", 2);
                }
                $result = $this->dbClass->readFromDB();
            }

//            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterGetFromDB")) {
//                $this->logger->setDebugMessage("The method 'doAfterGetFromDB' of the class '{$className}' is calling.", 2);
//                $result = $this->userExpanded->doAfterGetFromDB($dataSourceName, $result);
//            }
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterReadFromDB")) {
                $this->logger->setDebugMessage("The method 'doAfterReadFromDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterReadFromDB($result);
            }

            if ($this->dbSettings->notifyServer && $this->clientPusherAvailable) {
                $this->outputOfProcessing['registeredid'] = $this->dbSettings->notifyServer->register(
                    $this->dbClass->queriedEntity(),
                    $this->dbClass->queriedCondition(),
                    $this->dbClass->queriedPrimaryKeys()
                );
            }
            if (isset($currentDataSource['send-mail']['load'])
                || isset($currentDataSource['send-mail']['read'])
            ) {
                $this->logger->setDebugMessage("Try to send an email.", 2);
                $mailSender = new SendMail();
                if (isset($currentDataSource['send-mail']['load'])) {
                    $dataSource = $currentDataSource['send-mail']['load'];
                } else if (isset($currentDataSource['send-mail']['read'])) {
                    $dataSource = $currentDataSource['send-mail']['read'];
                }
                $mailResult = $mailSender->processing(
                    $dataSource,
                    $result,
                    $this->dbSettings->getSmtpConfiguration());
                if ($mailResult !== true) {
                    $this->logger->setErrorMessage("Mail sending error: $mailResult");
                }
            }

        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception: {$e->getMessage()}");
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
        $className = is_null($this->userExpanded) ? null : get_class($this->userExpanded);
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "countQueryResult")) {
            $this->logger->setDebugMessage("The method 'countQueryResult' of the class '{$className}' is calling.", 2);
            return $result = $this->userExpanded->countQueryResult();
        }
        if ($this->dbClass !== null) {
            return $result = $this->dbClass->countQueryResult();
        }
    }

    /**
     * @param $dataSourceName
     * @return mixed
     */
    function getTotalCount()
    {
        $className = is_null($this->userExpanded) ? null : get_class($this->userExpanded);
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "getTotalCount")) {
            $this->logger->setDebugMessage("The method 'getTotalCount' of the class '{$className}' is calling.", 2);
            return $result = $this->userExpanded->getTotalCount();
        }
        if ($this->dbClass !== null) {
            return $result = $this->dbClass->getTotalCount();
        }
    }

    /**
     * @param $dataSourceName
     * @return mixed
     */
    function updateDB()
    {
        $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
        try {
            $className = get_class($this->userExpanded);
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeUpdateDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeUpdateDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeUpdateDB();
            }
            if ($this->dbClass !== null) {
                $this->dbClass->requireUpdatedRecord(true); // Always Get Updated Record
                $result = $this->dbClass->updateDB();
            }
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterUpdateToDB")) {
                $this->logger->setDebugMessage("The method 'doAfterUpdateToDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterUpdateToDB($result);
            }
            if ($this->dbSettings->notifyServer && $this->clientPusherAvailable) {
                try {
                    $this->dbSettings->notifyServer->updated(
                        $this->PostData['notifyid'],
                        $this->dbClass->queriedEntity(),
                        $this->dbClass->queriedPrimaryKeys(),
                        $this->dbSettings->getFieldsRequired(),
                        $this->dbSettings->getValue()
                    );
                } catch (Exception $ex) {
                    if ($ex->getMessage() == '_im_no_pusher_exception') {
                        $this->logger->setErrorMessage("The 'Pusher.php' isn't installed on any valid directory.");
                    } else {
                        throw $ex;
                    }
                }
            }
            if (isset($currentDataSource['send-mail']['edit'])
                || isset($currentDataSource['send-mail']['update'])
            ) {
                $this->logger->setDebugMessage("Try to send an email.", 2);
                $mailSender = new SendMail();
                if (isset($currentDataSource['send-mail']['edit'])) {
                    $dataSource = $currentDataSource['send-mail']['edit'];
                } else if (isset($currentDataSource['send-mail']['update'])) {
                    $dataSource = $currentDataSource['send-mail']['update'];
                }
                $mailResult = $mailSender->processing(
                    $dataSource,
                    $this->dbClass->updatedRecord(),
                    $this->dbSettings->getSmtpConfiguration());
                if ($mailResult !== true) {
                    $this->logger->setErrorMessage("Mail sending error: $mailResult");
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception: {$e->getMessage()}");
            return false;
        }
        return $result;
    }

    /**
     * @param $dataSourceName
     * @param $bypassAuth
     * @return mixed
     */
    public function createInDB($bypassAuth)
    {
        $currentDataSource = $this->dbSettings->getDataSourceTargetArray();
        try {
            $className = get_class($this->userExpanded);
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeCreateToDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeCreateToDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeCreateToDB();
            }
            if ($this->dbClass !== null) {
                $this->dbClass->requireUpdatedRecord(true); // Always Requred Created Record
                $resultOfCreate = $this->dbClass->createInDB($bypassAuth);
                $result = $this->dbClass->updatedRecord();
            }
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterCreateToDB")) {
                $this->logger->setDebugMessage("The method 'doAfterCreateToDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterCreateToDB($result);
            }
            if ($this->dbSettings->notifyServer && $this->clientPusherAvailable) {
                try {
                    $this->dbSettings->notifyServer->created(
                        $this->PostData['notifyid'],
                        $this->dbClass->queriedEntity(),
                        $this->dbClass->queriedPrimaryKeys(),
                        $result
                    );
                } catch (Exception $ex) {
                    if ($ex->getMessage() == '_im_no_pusher_exception') {
                        $this->logger->setErrorMessage("The 'Pusher.php' isn't installed on any valid directory.");
                    } else {
                        throw $ex;
                    }
                }
            }
            if (isset($currentDataSource['send-mail']['new']) ||
                isset($currentDataSource['send-mail']['create'])
            ) {
                $this->logger->setDebugMessage("Try to send an email.");
                $mailSender = new SendMail();
                if (isset($currentDataSource['send-mail']['new'])) {
                    $dataSource = $currentDataSource['send-mail']['new'];
                } else if (isset($currentDataSource['send-mail']['create'])) {
                    $dataSource = $currentDataSource['send-mail']['create'];
                }
                $mailResult = $mailSender->processing(
                    $dataSource,
                    $this->dbClass->updatedRecord(),
                    $this->dbSettings->getSmtpConfiguration());
                if ($mailResult !== true) {
                    $this->logger->setErrorMessage("Mail sending error: $mailResult");
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception: {$e->getMessage()}");
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
            $className = get_class($this->userExpanded);
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeDeleteFromDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeDeleteFromDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeDeleteFromDB();
            }
            if ($this->dbClass !== null) {
                $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                if (isset($tableInfo['soft-delete'])) {
                    $delFlagField = 'delete';
                    if ($tableInfo['soft-delete'] !== true) {
                        $delFlagField = $tableInfo['soft-delete'];
                    }
                    $this->logger->setDebugMessage(
                        "The soft-delete applies to this delete operation with '{$delFlagField}' field.", 2);
                    $this->dbSettings->addValueWithField($delFlagField, 1);
                    $result = $this->dbClass->updateDB();
                } else {
                    $result = $this->dbClass->deleteFromDB();
                }
            }
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterDeleteFromDB")) {
                $this->logger->setDebugMessage("The method 'doAfterDeleteFromDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterDeleteFromDB($result);
            }
            if ($this->dbSettings->notifyServer && $this->clientPusherAvailable) {
                try {
                    $this->dbSettings->notifyServer->deleted(
                        $this->PostData['notifyid'],
                        $this->dbClass->queriedEntity(),
                        $this->dbClass->queriedPrimaryKeys()
                    );
                } catch (Exception $ex) {
                    if ($ex->getMessage() == '_im_no_pusher_exception') {
                        $this->logger->setErrorMessage("The 'Pusher.php' isn't installed on any valid directory.");
                    } else {
                        throw $ex;
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception: {$e->getMessage()}");
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
            $className = get_class($this->userExpanded);
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeCopyInDB")) {
                $this->logger->setDebugMessage("The method 'doBeforeCopyInDB' of the class '{$className}' is calling.", 2);
                $this->userExpanded->doBeforeCopyInDB();
            }
            if ($this->dbClass !== null) {
                $result = $this->dbClass->copyInDB();
            }
            if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterCopyInDB")) {
                $this->logger->setDebugMessage("The method 'doAfterCopyInDB' of the class '{$className}' is calling.", 2);
                $result = $this->userExpanded->doAfterCopyInDB($result);
            }
            if ($this->dbSettings->notifyServer && $this->clientPusherAvailable) {
                try {
                    $this->dbSettings->notifyServer->created(
                        $this->PostData['notifyid'],
                        $this->dbClass->queriedEntity(),
                        $this->dbClass->queriedPrimaryKeys(),
                        $this->dbClass->updatedRecord()
                    );
                } catch (Exception $ex) {
                    if ($ex->getMessage() == '_im_no_pusher_exception') {
                        $this->logger->setErrorMessage("The 'Pusher.php' isn't installed on any valid directory.");
                    } else {
                        throw $ex;
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage("Exception: {$e->getMessage()}");
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
        if ($this->dbClass !== null) {
            $result = $this->dbClass->getFieldInfo($dataSourceName);
        }
        return $result;
    }

    public function requireUpdatedRecord($value)
    {
        if ($this->dbClass !== null) {
            $this->dbClass->requireUpdatedRecord($value);
        }
    }

    public function updatedRecord()
    {
        if ($this->dbClass !== null) {
            return $this->dbClass->updatedRecord();
        }
        return null;
    }

    public function ignoringPost()
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

        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';

        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }

        $this->clientPusherAvailable = (isset($this->PostData["pusher"]) && $this->PostData["pusher"] == "yes");
        $this->dbSettings->setDataSource($datasource);
        $this->dbSettings->setOptions($options);
        $this->dbSettings->setDbSpec($dbspec);

        $this->dbSettings->setSeparator(isset($options['separator']) ? $options['separator'] : '@');
        $this->formatter->setFormatter(isset($options['formatter']) ? $options['formatter'] : null);
        $this->dbSettings->setDataSourceName(!is_null($target) ? $target : (isset($this->PostData['name']) ? $this->PostData['name'] : "_im_auth"));
        $context = $this->dbSettings->getDataSourceTargetArray();

        $dbClassName = 'DB_' .
            (isset($context['db-class']) ? $context['db-class'] :
                (isset($dbspec['db-class']) ? $dbspec['db-class'] :
                    (isset ($dbClass) ? $dbClass : '')));
        $this->dbSettings->setDbSpecServer(
            isset($context['server']) ? $context['server'] :
                (isset($dbspec['server']) ? $dbspec['server'] :
                    (isset ($dbServer) ? $dbServer : '')));
        $this->dbSettings->setDbSpecPort(
            isset($context['port']) ? $context['port'] :
                (isset($dbspec['port']) ? $dbspec['port'] :
                    (isset ($dbPort) ? $dbPort : '')));
        $this->dbSettings->setDbSpecUser(
            isset($context['user']) ? $context['user'] :
                (isset($dbspec['user']) ? $dbspec['user'] :
                    (isset ($dbUser) ? $dbUser : '')));
        $this->dbSettings->setDbSpecPassword(
            isset($context['password']) ? $context['password'] :
                (isset($dbspec['password']) ? $dbspec['password'] :
                    (isset ($dbPassword) ? $dbPassword : '')));
        $this->dbSettings->setDbSpecDataType(
            isset($context['datatype']) ? $context['datatype'] :
                (isset($dbspec['datatype']) ? $dbspec['datatype'] :
                    (isset ($dbDataType) ? $dbDataType : '')));
        $this->dbSettings->setDbSpecDatabase(
            isset($context['database']) ? $context['database'] :
                (isset($dbspec['database']) ? $dbspec['database'] :
                    (isset ($dbDatabase) ? $dbDatabase : '')));
        $this->dbSettings->setDbSpecProtocol(
            isset($context['protocol']) ? $context['protocol'] :
                (isset($dbspec['protocol']) ? $dbspec['protocol'] :
                    (isset ($dbProtocol) ? $dbProtocol : '')));
        $this->dbSettings->setDbSpecOption(
            isset($context['option']) ? $context['option'] :
                (isset($dbspec['option']) ? $dbspec['option'] :
                    (isset ($dbOption) ? $dbOption : '')));
        if (isset($options['authentication']) && isset($options['authentication']['issuedhash-dsn'])) {
            $this->dbSettings->setDbSpecDSN($options['authentication']['issuedhash-dsn']);
        } else {
            $this->dbSettings->setDbSpecDSN(
                isset($context['dsn']) ? $context['dsn'] :
                    (isset($dbspec['dsn']) ? $dbspec['dsn'] :
                        (isset ($dbDSN) ? $dbDSN : '')));
        }

        $pusherParams = null;
        if (isset($pusherParameters)) {
            $pusherParams = $pusherParameters;
        } else if (isset($options['pusher'])) {
            $pusherParams = $options['pusher'];
        }
        if (!is_null($pusherParams)) {
            $this->dbSettings->pusherAppId = $pusherParams['app_id'];
            $this->dbSettings->pusherKey = $pusherParams['key'];
            $this->dbSettings->pusherSecret = $pusherParams['secret'];
            if (isset($pusherParams['channel'])) {
                $this->dbSettings->pusherChannel = $pusherParams['channel'];
            }
        }

        /* Setup Database Class's Object */
        require_once("{$dbClassName}.php");
        $this->dbClass = new $dbClassName();
        if ($this->dbClass == null) {
            $this->logger->setErrorMessage("The database class [{$dbClassName}] that you specify is not valid.");
            echo implode('', $this->logger->getMessagesForJS());
            return false;
        }
        $this->dbClass->setUpSharedObjects($this);
        if (!$this->dbClass->setupConnection()) {
            return false;
        }
        if ((!isset($prohibitDebugMode) || !$prohibitDebugMode) && $debug) {
            $this->logger->setDebugMode($debug);
        }
        $this->logger->setDebugMessage("The class '{$dbClassName}' was instanciated.", 2);

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
        } else if (isset($issuedHashDSN)) {
            $challengeDSN = $issuedHashDSN;
        }
        if (!is_null($challengeDSN)) {
            require_once("DB_PDO.php");
            $this->authDbClass = new DB_PDO();
            $this->authDbClass->setUpSharedObjects($this);
            $this->authDbClass->setupWithDSN($challengeDSN);
            $this->logger->setDebugMessage(
                "The class 'DB_PDO' was instanciated for issuedhash with {$challengeDSN}.", 2);
        } else {
            $this->authDbClass = $this->dbClass;
        }

        $this->dbSettings->notifyServer = null;
        if ($this->clientPusherAvailable) {
            require_once("NotifyServer.php");
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
            if (is_subclass_of($this->userExpanded, 'DB_UseSharedObjects')) {
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
            $this->dbSettings->addValue(get_magic_quotes_gpc() ? stripslashes($value) : $value);
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
        }

        $this->paramAuthUser = isset($this->PostData['authuser']) ? $this->PostData['authuser'] : "";
        $this->paramResponse = isset($this->PostData['response']) ? $this->PostData['response'] : "";
        $this->paramCryptResponse = isset($this->PostData['cresponse']) ? $this->PostData['cresponse'] : "";
        $this->clientId = isset($this->PostData['clientid']) ? $this->PostData['clientid'] :
            (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "Non-browser-client");
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
    function processingRequest($access = null, $bypassAuth = false)
    {
        $this->logger->setDebugMessage("[processingRequest]", 2);
        $options = $this->dbSettings->getAuthentication();

        $this->outputOfProcessing = array();
        $messageClass = IMUtil::getMessageClassInstance();

        /* Aggregation Judgement */
        $isSelect = $this->dbSettings->getAggregationSelect();
        $isFrom = $this->dbSettings->getAggregationFrom();
        $isGroupBy = $this->dbSettings->getAggregationGroupBy();
        $isDBSupport = $this->dbClass->isSupportAggregation();
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
        if (!is_null($options)
            || $access == 'challenge' || $access == 'changepassword'
            || (isset($tableInfo['authentication'])
                && (isset($tableInfo['authentication']['all']) || isset($tableInfo['authentication'][$access])))
        ) {
            $this->dbSettings->setRequireAuthorization(true);
            $this->dbSettings->setDBNative(false);
            if (isset($options['user'])
                && $options['user'][0] == 'database_native'
            ) {
                $this->dbSettings->setDBNative(true);
            }
        }

        if (!$bypassAuth && $this->dbSettings->getRequireAuthorization()) { // Authentication required
            if (strlen($this->paramAuthUser) == 0 || strlen($this->paramResponse) == 0) {
                // No username or password
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
                } else {
                    $noAuthorization = true;
                    $authorizedGroups = $this->dbClass->getAuthorizedGroups($access);
                    $authorizedUsers = $this->dbClass->getAuthorizedUsers($access);

                    $this->logger->setDebugMessage(str_replace("\n", "",
                            "contextName={$this->dbSettings->getDataSourceName()}/access={$access}/"
                            . "authorizedUsers=" . var_export($authorizedUsers, true)
                            . "/authorizedGroups=" . var_export($authorizedGroups, true))
                        , 2);
                    if ((count($authorizedUsers) == 0 && count($authorizedGroups) == 0)) {
                        $noAuthorization = false;
                    } else {
                        $signedUser = $this->dbClass->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                        if (in_array($signedUser, $authorizedUsers)) {
                            $noAuthorization = false;
                        } else {
                            if (count($authorizedGroups) > 0) {
                                $belongGroups = $this->dbClass->authSupportGetGroupsOfUser($signedUser);
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
                    $signedUser = $this->dbClass->authSupportUnifyUsernameAndEmail($this->paramAuthUser);

                    $authSucceed = false;
                    if ($this->checkAuthorization($signedUser, $this->paramResponse, $this->clientId)) {
                        $this->logger->setDebugMessage("IM-built-in Authentication succeed.");
                        $authSucceed = true;
                    } else {
                        $ldap = new LDAPAuth();
                        $ldap->setLogger($this->logger);
                        if ($ldap->isActive) {
                            list($password, $challenge) = $this->decrypting($this->paramCryptResponse);
                            if ($ldap->bindCheck($signedUser, $password)) {
                                $this->logger->setDebugMessage("LDAP Authentication succeed.");
                                $authSucceed = true;
                                $this->addUser($signedUser, $password, true);
                            }
                        }
                    }

                    if (!$authSucceed) {
                        $this->logger->setDebugMessage(
                            "Authentication doesn't meet valid.{$signedUser}/{$this->paramResponse}/{$this->clientId}");
                        // Not Authenticated!
                        $access = "do nothing";
                        $this->dbSettings->setRequireAuthentication(true);
                    }
                }
            }
        }
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
                break;
            case 'update':
                $this->logger->setDebugMessage("[processingRequest] start update processing", 2);
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
                $this->updateDB();
                $this->outputOfProcessing['dbresult'] = $this->dbClass->updatedRecord();
                break;
            case 'new':
            case 'create':
            $this->logger->setDebugMessage("[processingRequest] start create processing", 2);
            $result = $this->createInDB($this->dbSettings->getDataSourceName(), $bypassAuth);
                $this->outputOfProcessing['newRecordKeyValue'] = $result;
                $this->outputOfProcessing['dbresult'] = $this->dbClass->updatedRecord();
                break;
            case 'delete':
                $this->logger->setDebugMessage("[processingRequest] start delete processing", 2);
                $this->deleteFromDB($this->dbSettings->getDataSourceName());
                break;
            case 'copy':
                $this->logger->setDebugMessage("[processingRequest] start copy processing", 2);
                $result = $this->copyInDB($this->dbSettings->getDataSourceName());
                $this->outputOfProcessing['newRecordKeyValue'] = $result;
                $this->outputOfProcessing['dbresult'] = $this->dbClass->updatedRecord();
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
                if (!is_null($this->dbSettings->notifyServer) && $this->clientPusherAvailable) {
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
                foreach ($this->dbSettings->getFieldsRequired() as $fieldName) {
                    if (!$this->dbClass->isContainingFieldName($fieldName, $fInfo)) {
                        $this->logger->setErrorMessage($messageClass->getMessageAs(1033, array($fieldName)));
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

        $this->outputOfProcessing['errorMessages'] = $this->logger->getErrorMessages();
        $this->outputOfProcessing['debugMessages'] = $this->logger->getDebugMessages();
        $this->outputOfProcessing['usenull'] = $this->dbClass->isNullAcceptable();
        $this->outputOfProcessing['notifySupport']
            = is_null($this->dbSettings->notifyServer) ? false : $this->dbSettings->pusherKey;
        if ($notFinish || !$this->dbSettings->getRequireAuthorization()) {
            return;
        }
        $generatedChallenge = $this->generateChallenge();
        $generatedUID = $this->generateClientId('');
        $this->logger->setDebugMessage("generatedChallenge = $generatedChallenge", 2);
        $userSalt = $this->saveChallenge(
            $this->dbSettings->isDBNative() ? 0 : $this->paramAuthUser, $generatedChallenge, $generatedUID);

        $this->previousChallenge = "{$generatedChallenge}{$userSalt}";
        $this->previousClientid = "{$generatedUID}";
        $this->outputOfProcessing['challenge'] = "{$generatedChallenge}{$userSalt}";
        $this->outputOfProcessing['clientid'] = $generatedUID;
        if ($this->dbSettings->getRequireAuthentication()) {
            $this->outputOfProcessing['requireAuth'] = true;
        }
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        if (isset($tableInfo['authentication'])
            && isset($tableInfo['authentication']['media-handling'])
            && $tableInfo['authentication']['media-handling'] === true
        ) {
            $generatedChallenge = $this->generateChallenge();
            $this->saveChallenge($this->paramAuthUser, $generatedChallenge, "_im_media");
            $this->outputOfProcessing['mediatoken'] = $generatedChallenge;
        }
    }

    public function getDatabaseResult()
    {
        if (isset($this->outputOfProcessing['dbresult'])) {
            return $this->outputOfProcessing['dbresult'];
        }
        return null;
    }

    public function getDatabaseResultCount()
    {
        if (isset($this->outputOfProcessing['resultCount'])) {
            return $this->outputOfProcessing['resultCount'];
        }
        return null;
    }

    public function getDatabaseTotalCount()
    {
        if (isset($this->outputOfProcessing['totalCount'])) {
            return $this->outputOfProcessing['totalCount'];
        }
        return null;
    }

    public function getDatabaseNewRecordKey()
    {
        if (isset($this->outputOfProcessing['newRecordKeyValue'])) {
            return $this->outputOfProcessing['newRecordKeyValue'];
        }
        return null;
    }

    /* Authentication support */
    function decrypting($paramCryptResponse)
    {
        $generatedPrivateKey = '';
        $passPhrase = '';

        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }

        $rsaClass = IMUtil::phpSecLibClass('phpseclib\Crypt\RSA');
        $rsa = new $rsaClass;
        $rsa->setPassword($passPhrase);
        $rsa->loadKey($generatedPrivateKey);
        $rsa->setPassword();
        $privatekey = $rsa->getPrivateKey();
        if (IMUtil::phpVersion() < 6) {
            $priv = $rsa->_parseKey($privatekey, CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
        } else {
            $priv = $rsa->_parseKey($privatekey, constant('phpseclib\Crypt\RSA::PRIVATE_FORMAT_PKCS1'));
        }
        require_once('lib/bi2php/biRSA.php');
        $keyDecrypt = new biRSAKeyPair('0', $priv['privateExponent']->toHex(), $priv['modulus']->toHex());
        $decrypted = $keyDecrypt->biDecryptedString($paramCryptResponse);
        if ($decrypted === false) {
            return array(false, false);
        }

        $nlPos = strpos($decrypted, "\n");
        $nlPos = ($nlPos === false) ? strlen($decrypted) : $nlPos;
        $password = $keyDecrypt->biDecryptedString(substr($decrypted, 0, $nlPos));
        $password = (strlen($password) == 0) ? "f32b309d4759446fc81de858322ed391a0c167a0" : $password;
        $challenge = substr($decrypted, $nlPos + 1);
        return array($password, $challenge);
    }

    /**
     * @param $prefix
     * @return string
     */
    function generateClientId($prefix)
    {
        return sha1(uniqid($prefix, true));
    }

    /**
     * @return string
     */
    function generateChallenge()
    {
        $str = '';
        for ($i = 0; $i < 12; $i++) {
            $n = rand(1, 255);
            $str .= ($n < 16 ? '0' : '') . dechex($n);
        }
        return $str;
    }

    /**
     * @return string
     */
    function generateSalt()
    {
        $str = '';
        for ($i = 0; $i < 4; $i++) {
            $n = rand(33, 126); // They should be an ASCII character for JS SHA1 lib.
            $str .= chr($n);
        }
        return $str;
    }

    function convertHashedPassword($pw)
    {
        $salt = $this->generateSalt();
        return sha1($pw . $salt) . bin2hex($salt);
    }

    function generateCredential($digit)
    {
        $password = '';
        for ($i = 0; $i < $digit; $i++) {
            $password .= chr(rand(32, 127));
        }
        return $this->convertHashedPassword($password);
    }

    /**
     * @param $username
     * @return string
     */
    function authSupportGetSalt($username)
    {
        $hashedpw = $this->dbClass->authSupportRetrieveHashedPassword($username);
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
    function saveChallenge($username, $challenge, $clientId)
    {
        $this->logger->setDebugMessage("[saveChallenge]user=${username}, challenge={$challenge}, clientid={$clientId}", 2);
        $username = $this->dbClass->authSupportUnifyUsernameAndEmail($username);
        $uid = $this->dbClass->authSupportGetUserIdFromUsername($username);
        $this->authDbClass->authSupportStoreChallenge($uid, $challenge, $clientId);
        return $username === 0 ? "" : $this->authSupportGetSalt($username);
    }

    /**
     * @param $username
     * @param $hashedvalue
     * @param $clientId
     * @return bool
     */
    function checkAuthorization($username, $hashedvalue, $clientId)
    {
        $this->logger->setDebugMessage(
            "[checkAuthorization]user=${username}, paramResponse={$hashedvalue}, clientid={$clientId}", 2);
        $returnValue = false;

        $this->authDbClass->authSupportRemoveOutdatedChallenges();

        $signedUser = $this->dbClass->authSupportUnifyUsernameAndEmail($username);
        $uid = $this->dbClass->authSupportGetUserIdFromUsername($signedUser);
        $this->logger->setDebugMessage("[checkAuthorization]uid={$uid}", 2);
        if ($uid < 0) {
            return $returnValue;
        }
        $storedChallenge = $this->authDbClass->authSupportRetrieveChallenge($uid, $clientId);
        $this->logger->setDebugMessage("[checkAuthorization]storedChallenge={$storedChallenge}", 2);

        if (strlen($storedChallenge) == 24) { // ex.fc0d54312ce33c2fac19d758
            $hashedPassword = $this->dbClass->authSupportRetrieveHashedPassword($username);
            $hmacValue = hash_hmac('sha256', $hashedPassword, $storedChallenge);
            $this->logger->setDebugMessage("[checkAuthorization]hashedPassword={$hashedPassword}", 2);
            $this->logger->setDebugMessage("[checkAuthorization]hmac_value={$hmacValue}", 2);
            if (strlen($hashedPassword) > 0) {
                if ($hashedvalue == $hmacValue) {
                    $returnValue = true;
                } else {
                    $this->logger->setDebugMessage("[checkAuthorization]Built-in authorization fail.", 2);
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
        $this->authDbClass->authSupportRemoveOutdatedChallenges();
        // Database user mode is user_id=0
        $storedChallenge = $this->authDbClass->authSupportRetrieveChallenge(0, $clientId);
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
        $returnValue = false;
        $this->authDbClass->authSupportRemoveOutdatedChallenges();
        // Database user mode is user_id=0
        $user = $this->dbClass->authSupportUnifyUsernameAndEmail($user);
        $uid = $this->dbClass->authSupportGetUserIdFromUsername($user);
        $storedChallenge = $this->authDbClass->authSupportCheckMediaToken($uid);
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
        $salt = $this->generateSalt();
        $hexSalt = bin2hex($salt);
        $returnValue = $this->dbClass->authSupportCreateUser(
            $username, sha1($password . $salt) . $hexSalt, $isLDAP, $password);
        return $returnValue;
    }

    /**
     * @param $username
     * @param $newpassword
     * @return mixed
     */
    function changePassword($username, $newpassword)
    {
        $returnValue = $this->dbClass->authSupportChangePassword($username, $newpassword);
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
        $userid = $this->dbClass->authSupportGetUserIdFromEmail($email);
        $username = $this->dbClass->authSupportGetUsernameFromUserId($userid);
        if ($username === false || $username == '') {
            return false;
        }
        $clienthost = $this->generateChallenge();
        $hash = sha1($clienthost . $email . $username);
        if ($this->authDbClass->authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)) {
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
            $userid = $this->dbClass->authSupportGetUserIdFromEmail($email);
            $username = $this->dbClass->authSupportGetUsernameFromUserId($userid);
        }
        if ($email === false || $email == '' || $username === false || $username == '') {
            return false;
        }
        $userid = $this->dbClass->authSupportGetUserIdFromUsername($username);
        $hash = sha1($randdata . $email . $username);
        if ($this->authDbClass->authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)) {
            if ($this->changePassword($username, $newpassword)) {
                return true;
            }
        }
        return false;
    }

    function userEnrollmentStart($userID)
    {
        $hash = $this->generateChallenge();
        $this->authDbClass->authSupportUserEnrollmentStart($userID, $hash);
        return $hash;
    }

    function userEnrollmentActivateUser($challenge, $password, $rawPWField = false)
    {
        $userInfo = null;
        $userID = $this->authDbClass->authSupportUserEnrollmentEnrollingUser($challenge);
        if ($userID < 1) {
            return false;
        }
        $result = $this->dbClass->authSupportUserEnrollmentActivateUser(
            $userID, $this->convertHashedPassword($password), $rawPWField, $password);
//        if ($userID !== false) {
//            $hashednewpassword = $this->convertHashedPassword($password);
//            $userInfo = authSupportUserEnrollmentCheckHash($userID, $hashednewpassword);
//        }
        return $result;
    }

    public function setupConnection()
    {
        // TODO: Implement setupConnection() method.
    }

    public function isPossibleOperator($operator)
    {
        // TODO: Implement isPossibleOperator() method.
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        // TODO: Implement isPossibleOrderSpecifier() method.
    }

    public function isContainingFieldName($fname, $fieldnames)
    {
        return null;
    }

    public function isNullAcceptable()
    {
        return true;
    }

    public function softDeleteActivate($field, $value)
    {

    }

    public function isSupportAggregation()
    {
        // TODO: Implement isSupportAggregation() method.
    }
}
