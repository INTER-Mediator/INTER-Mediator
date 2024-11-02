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
use INTERMediator\Params;

/**
 *
 */
class OperationLog
{
    /**
     * @var int
     */
    private int $accessLogLevel;
    /**
     * @var string|null
     */
    private ?string $dbClassLog;
    /**
     * @var string|null
     */
    private ?string $dbUserLog;
    /**
     * @var string|null
     */
    private ?string $dbPasswordLog;
    /**
     * @var string|null
     */
    private ?string $dbDSNLog;
    /**
     * @var array|null
     */
    private ?array $recordingContexts;
    /**
     * @var array|null
     */
    private ?array $recordingOperations;
    /**
     * @var array|null
     */
    private ?array $contextOptions;
    /**
     * @var bool
     */
    private bool $dontRecordTheme;
    /**
     * @var bool
     */
    private bool $dontRecordChallenge;
    /**
     * @var bool
     */
    private bool $dontRecordDownload;
    /**
     * @var bool
     */
    private bool $dontRecordDownloadNoGet;
    /**
     * @var ?string
     */
    private ?string $accessLogExtensionClass;

    /**
     * @param array|null $options
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
     * @param array|null $result
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
            || ($this->dontRecordDownloadNoGet && $access == 'download' && (!is_array($_GET) || count($_GET) == 0))
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
     * @param array|null $ar
     * @return string|null
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
                if (is_array($matches) && count($matches) > 1) {
                    $v = '***';
                }
            }
            $result[] = str_replace(["\n", "\r", "\t"], ['', '', ''], "{$k} => {$v}");
        }
        return '[' . implode(',', $result) . ']';
    }
}