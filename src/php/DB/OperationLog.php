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
use INTERMediator\IMUtil;
use INTERMediator\Params;

/**
 * OperationLog class for logging and managing database operation logs in INTER-Mediator.
 * Handles log levels, recording options, and access log extension.
 */
class OperationLog
{
    /**
     * Access log level.
     * @var int
     */
    private int $accessLogLevel;
    /**
     * Database class log name.
     * @var string|null
     */
    private ?string $dbClassLog;
    /**
     * Database user log name.
     * @var string|null
     */
    private ?string $dbUserLog;
    /**
     * Database password log.
     * @var string|null
     */
    private ?string $dbPasswordLog;
    /**
     * Database DSN log.
     * @var string|null
     */
    private ?string $dbDSNLog;
    /**
     * Contexts to record.
     * @var array|null
     */
    private ?array $recordingContexts;
    /**
     * Operations to record.
     * @var array|null
     */
    private ?array $recordingOperations;
    /**
     * Context options.
     * @var array|null
     */
    private ?array $contextOptions;
    /**
     * Whether to not record theme.
     * @var bool
     */
    private bool $dontRecordTheme;
    /**
     * Whether to not record challenge.
     * @var bool
     */
    private bool $dontRecordChallenge;
    /**
     * Whether to not record download.
     * @var bool
     */
    private bool $dontRecordDownload;
    /**
     * Whether to not record download without GET.
     * @var bool
     */
    private bool $dontRecordDownloadNoGet;
    /**
     * Access log extension class name.
     * @var string|null
     */
    private ?string $accessLogExtensionClass;

    /**
     * Constructor for OperationLog.
     * @param array|null $options Context options for logging.
     */
    public function __construct(?array $options)
    {
        $this->contextOptions = $options;
        $this->accessLogLevel = Params::getParameterValue("accessLogLevel", false);
        $this->dbClassLog = Params::getParameterValue("dbClassLog", null);
        $this->dbUserLog = IMUtil::getFromProfileIfAvailable(
            Params::getParameterValue("dbUserLog", null));
        $this->dbPasswordLog = IMUtil::getFromProfileIfAvailable(
            Params::getParameterValue("dbPasswordLog", null));
        $this->dbDSNLog = Params::getParameterValue("dbDSNLog", null);
        $this->recordingContexts = Params::getParameterValue("recordingContexts", null);
        $this->dontRecordTheme = Params::getParameterValue("dontRecordTheme", false);
        $this->dontRecordChallenge = Params::getParameterValue("dontRecordChallenge", false);
        $this->dontRecordDownload = Params::getParameterValue("dontRecordDownload", false);
        $this->dontRecordDownloadNoGet = Params::getParameterValue("dontRecordDownloadNoGet", false);
        $this->recordingOperations = Params::getParameterValue("recordingOperations", null);
        $this->accessLogExtensionClass = Params::getParameterValue("accessLogExtensionClass", null);
    }

    /**
     * Sets an entry in the operation log.
     * @param array|null $result Result data for logging.
     * @return void
     * @throws Exception
     */
    public function setEntry(?array $result): void
    {
        $access = $_GET['access'] ?? ($_POST['access'] ?? (isset($_GET['theme']) ? 'theme' : 'download'));
        if (
            (!is_null($this->recordingOperations) && !in_array($access, $this->recordingOperations))
            || ($this->dontRecordTheme && $access == 'theme')
            || ($this->dontRecordChallenge && $access == 'challenge')
            || ($this->dontRecordDownload && $access == 'download')
            || ($this->dontRecordDownloadNoGet && $access == 'download' && (count($_GET) == 0))
        ) {
            return;
        }
        $targetContext = $_GET['name'] ?? $_POST['name'] ?? $result['name'] ?? (isset($_GET['theme']) ? ($_GET['css'] ?? '') : '');
        if (!is_null($this->recordingContexts) && !in_array($targetContext, $this->recordingContexts)) {
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
            $userValue = $_POST['authuser'] ?? $result['authuser'] ?? '';
            if ($userValue === '') {
                $cookieNameUser = "_im_username";
                if (isset($this->contextOptions['authentication']['realm'])) {
                    $cookieNameUser .= ('_' . str_replace(" ", "_",
                            str_replace(".", "_", $this->contextOptions['authentication']['realm'])));
                }
                $userValue = $_COOKIE[$cookieNameUser] ?? '';
            }
            $dbInstance->dbSettings->addValueWithField("user", $userValue);
            $dbInstance->dbSettings->addValueWithField("client_id_in", $_POST['clientid'] ?? '');
            $dbInstance->dbSettings->addValueWithField("client_id_out", $result['clientid'] ?? '');
            $dbInstance->dbSettings->addValueWithField("client_ip", $_SERVER['REMOTE_ADDR']);
            $dbInstance->dbSettings->addValueWithField("path", $_SERVER['PHP_SELF']);
            $dbInstance->dbSettings->addValueWithField("access", $access);
            $requireAuth = isset($result['requireAuth']) && ($result['requireAuth'] === true || $result['requireAuth'] === 'true');
            $dbInstance->dbSettings->addValueWithField("require_auth", $requireAuth);
            $setAuth = isset($result['getRequireAuthorization'])
                && ($result['getRequireAuthorization'] === true || $result['getRequireAuthorization'] === 'true');
            $dbInstance->dbSettings->addValueWithField("set_auth", $setAuth);
            $dbInstance->dbSettings->addValueWithField("get_data", $this->arrayToString($_GET));
            $dbInstance->dbSettings->addValueWithField("post_data", $this->arrayToString($_POST));
            $dbInstance->dbSettings->addValueWithField("result", $this->arrayToString($result));
            $dbInstance->dbSettings->addValueWithField("error",
                $this->arrayToString($dbInstance->logger->getErrorMessages()));

            if ($this->accessLogExtensionClass && class_exists($this->accessLogExtensionClass)) {
                $extInstance = new $this->accessLogExtensionClass($dbInstance, $result);
                $fields = $extInstance->extendingFields();
                foreach ($fields as $field) {
                    $dbInstance->dbSettings->addValueWithField($field, $extInstance->valueForField($field));
                }
            }
            $dbInstance->setStopNotifyAndMessaging();
            $dbInstance->processingRequest("create", true, true);
        }
    }

    /**
     * Converts an array to a string representation.
     * @param array|null $ar Array to convert.
     * @return string|null String representation of the array.
     */
    private function arrayToString(?array $ar): ?string
    {
        if (is_null($ar) || count($ar) === 0) {
            return null;
        }
        $result = [];
        foreach ($ar as $k => $v) {
            if (is_array($v)) {
                $v = $this->arrayToString($v);
            }
            if ($this->accessLogLevel < 2 && preg_match("/(value_[0-9]+)/", $k, $matches)) {
                $v = "***";
            }
            $result[] = str_replace(["\n", "\r", "\t"], ['', '', ''], "{$k} => {$v}");
        }
        return '[' . implode(',', $result) . ']';
    }
}