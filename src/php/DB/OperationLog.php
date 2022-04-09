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

use INTERMediator\IMUtil;

class OperationLog
{
    private $accessLogLevel;
    private $isWithData;
    private $dbClassLog;
    private $dbUserLog;
    private $dbPasswordLog;
    private $dbDSNLog;
    private $recordingContexts;
    private $recordingOperations;
    private $contextOptions;
    private $dontRecordTheme;
    private $dontRecordChallenge;
    private $dontRecordDownload;
    private $dontRecordDownloadNoGet;
    private $accessLogExtensionClass;

    public function __construct($options)
    {
        $this->contextOptions = $options;
        // Read from params.php
//        $paramKeys = ["accessLogLevel", "dbClassLog", "dbUserLog", "dbPasswordLog", "dbDSNLog",
//            "recordingContexts", "dontRecordTheme", "dontRecordChallenge", "dontRecordDownload",
//            "dontRecordDownloadNoGet", "recordingOperations", "accessLogExtensionClass"];
//        $params = IMUtil::getFromParamsPHPFile($paramKeys, true);
//        $this->accessLogLevel = intval($params['accessLogLevel']);    // false: No logging, 1: without data, 2: with data
//        $this->isWithData = ($this->accessLogLevel === 2);
//        $this->dbClassLog = isset($params['dbClassLog']) ? $params['dbClassLog'] : '';
//        $this->dbUserLog = isset($params['dbUserLog']) ? $params['dbUserLog'] : '';
//        $this->dbPasswordLog = isset($params['dbPasswordLog']) ? $params['dbPasswordLog'] : '';
//        $this->dbDSNLog = isset($params['dbDSNLog']) ? $params['dbDSNLog'] : '';
//        $this->recordingContexts = isset($params['recordingContexts']) ? $params['recordingContexts'] : false;
//        $this->recordingOperations = isset($params['recordingOperations']) ? $params['recordingOperations'] : false;
//        $this->dontRecordTheme = isset($params['dontRecordTheme']) ? $params['dontRecordTheme'] : false;
//        $this->dontRecordChallenge = isset($params['dontRecordChallenge']) ? $params['dontRecordChallenge'] : false;
//        $this->dontRecordDownload = isset($params['dontRecordDownload']) ? $params['dontRecordDownload'] : false;
//        $this->dontRecordDownloadNoGet = isset($params['dontRecordDownloadNoGet']) ? $params['dontRecordDownloadNoGet'] : false;
//        $this->accessLogExtensionClass = isset($params['accessLogExtensionClass']) ? $params['accessLogExtensionClass'] : false;
        [$this->accessLogLevel, $this->dbClassLog, $this->dbUserLog, $this->dbPasswordLog, $this->dbDSNLog,
            $this->recordingContexts, $this->dontRecordTheme, $this->dontRecordChallenge, $this->dontRecordDownload,
            $this->dontRecordDownloadNoGet, $this->recordingOperations, $this->accessLogExtensionClass]
            = Params::getParameterValue(["accessLogLevel", "dbClassLog", "dbUserLog", "dbPasswordLog", "dbDSNLog",
            "recordingContexts", "dontRecordTheme", "dontRecordChallenge", "dontRecordDownload",
            "dontRecordDownloadNoGet", "recordingOperations", "accessLogExtensionClass"], false);
        $this->isWithData = ($this->accessLogLevel === 2);
    }

    public function setEntry($result)
    {
        $access = isset($_GET['access']) ? $_GET['access']
            : (isset($_POST['access']) ? $_POST['access']
                : (isset($_GET['theme']) ? 'theme' : 'download'));
        if (
            ($this->recordingOperations !== false && !in_array($access, $this->recordingOperations))
            || ($this->dontRecordTheme && $access == 'theme')
            || ($this->dontRecordChallenge && $access == 'challenge')
            || ($this->dontRecordDownload && $access == 'download')
            || ($this->dontRecordDownloadNoGet && $access == 'download' && (!is_array($_GET) || count($_GET) == 0))
        ) {
            return;
        }
        $targetContext = isset($_GET['name']) ? $_GET['name']
            : (isset($_POST['name']) ? $_POST['name']
                : (isset($_GET['theme']) ? (isset($_GET['css']) ? $_GET['css'] : '') : ''));
        if ($this->recordingContexts !== false && !in_array($targetContext, $this->recordingContexts)) {
            return;
        }

        $dbInstance = new Proxy(true);
        $dbInstance->ignoringPost();
        $contextName = 'operationlog';
        $dataSource = [[
            'name' => $contextName,
            'key' => 'id',
        ]];
        $options = [];
        $dbSpecification = [
            'db-class' => $this->dbClassLog,
            'dsn' => $this->dbDSNLog,
            'option' => [],
            'user' => $this->dbUserLog,
            'password' => $this->dbPasswordLog,
        ];
        $debug = 2;
        $isInitialized = $dbInstance->initialize($dataSource, $options, $dbSpecification, $debug, $contextName);
        if ($isInitialized) {
            $dbInstance->dbSettings->addValueWithField("context", $targetContext);
            $userValue = isset($_POST['authuser']) ? $_POST['authuser'] : '';
            if ($userValue === '') {
                $cookieNameUser = "_im_username";
                if (isset($this->contextOptions['authentication']['realm'])) {
                    $cookieNameUser .= ('_' . str_replace(" ", "_",
                            str_replace(".", "_", $this->contextOptions['authentication']['realm']) ?? ""));
                }
                $userValue = isset($_COOKIE[$cookieNameUser]) ? $_COOKIE[$cookieNameUser] : '';
            }
            $dbInstance->dbSettings->addValueWithField("user", $userValue);
            $dbInstance->dbSettings->addValueWithField("client_id_in", isset($_POST['clientid']) ? $_POST['clientid'] : '');
            $clientIdOut = '';
            foreach ($result as $key => $value) {
                if ($key == 'clientid') {
                    $clientIdOut = $value;
                }
            }
            $dbInstance->dbSettings->addValueWithField("client_id_out", $clientIdOut);
            $dbInstance->dbSettings->addValueWithField("client_ip", $_SERVER['REMOTE_ADDR']);
            $dbInstance->dbSettings->addValueWithField("path", $_SERVER['PHP_SELF']);
            $dbInstance->dbSettings->addValueWithField("access", $access);
            $requireAuth = false;
            if (isset($result['requireAuth'])
                && ($result['requireAuth'] === true || $result['requireAuth'] === 'true')) {
                $requireAuth = true;
            }
            $dbInstance->dbSettings->addValueWithField("require_auth", $requireAuth);
            $setAuth = false;
            if (isset($result['getRequireAuthorization'])
                && ($result['getRequireAuthorization'] === true || $result['getRequireAuthorization'] === 'true')) {
                $setAuth = true;
            }
            $dbInstance->dbSettings->addValueWithField("set_auth", $setAuth);
            $dbInstance->dbSettings->addValueWithField("get_data", $this->arrayToString($_GET));
            $dbInstance->dbSettings->addValueWithField("post_data", $this->arrayToString($_POST));
            $dbInstance->dbSettings->addValueWithField("result", $this->arrayToString($result));
            $dbInstance->dbSettings->addValueWithField("error",
                $this->arrayToString($dbInstance->logger->getErrorMessages()));

            if ($this->accessLogExtensionClass !== false && class_exists($this->accessLogExtensionClass)) {
                $extInstance = new ($this->accessLogExtensionClass)($dbInstance);
                $fields = $extInstance->extendingFields();
                foreach ($fields as $field) {
                    $dbInstance->dbSettings->addValueWithField($field, $extInstance->valueForField($field));
                }
            }
            $dbInstance->setStopNotifyAndMessaging();
            $dbInstance->processingRequest("create", true, true);
        }
    }

    private function arrayToString($ar)
    {
        if (is_null($ar) || count($ar) === 0) {
            return null;
        }
        $result = [];
        foreach ($ar as $k => $v) {
            if (is_array($k)) {
                $v = $this->arrayToString($v);
            }
            if ($this->accessLogLevel < 2 && preg_match("/(value_[0-9]+)/", $k, $matches)) {
                if (is_array($matches) && count($matches) > 1) {
                    $v = '***';
                }
            }
            $result[] = str_replace(["\n", "\r", "\t"], ['', '', ''], "{$k} => {$v}");
        }
        return '[' . implode(',', $result) . ']';
    }
}