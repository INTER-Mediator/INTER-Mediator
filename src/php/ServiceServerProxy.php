<?php

namespace INTERMediator;

use DateTime;
use INTERMediator\DB\Logger;

/**
 *
 */
class ServiceServerProxy
{
    /**
     * @var string
     */
    private string $paramsHost;
    /**
     * @var int
     */
    private int $paramsPort;
    /**
     * @var bool
     */
    private bool $paramsBoot;
    /**
     * @var bool
     */
    private bool $dontUse;
    /**
     * @var array
     */
    private array $errors = [];
    /**
     * @var array
     */
    private array $messages = [];
    /**
     * @var string
     */
    private string $messageHead = "[ServiceServerProxy] ";
    /**
     * @var bool
     */
    private bool $dontAutoBoot;
    /**
     * @var ?string
     */
    private ?string $foreverLog;
    /**
     * @var string
     */
    private string $serviceServerKey;  // Path of Key file for wss protocol
    /**
     * @var string
     */
    private string $serviceServerCert; // Path of Cert file for wss protocol
    /**
     * @var string
     */
    private string $serviceServerCA; // Path of CA file for wss protocol
    /**
     * @var DateTime|null
     */
    private ?DateTime $serverInfoCachedDT = null;
    /**
     * @var bool
     */
    private bool $serverInfoCached = false;
    /**
     * @var ServiceServerProxy|null
     */
    static private ?ServiceServerProxy $gSSPInstance = null;

    /**
     * Returns the singleton instance.
     * @return ServiceServerProxy|null
     */
    static public function instance(): ?ServiceServerProxy
    {
        if (is_null(ServiceServerProxy::$gSSPInstance)) {
            ServiceServerProxy::$gSSPInstance = new ServiceServerProxy();
        }
        return ServiceServerProxy::$gSSPInstance;
    }

    /**
     * Constructor of this class.
     */
    private function __construct()
    {
        $this->paramsHost = Params::getParameterValue("serviceServerConnect", "http://localhost");
        $this->paramsPort = Params::getParameterValue("serviceServerPort", 11478);
        $this->paramsBoot = Params::getParameterValue("bootWithInstalledNode", false);
        $this->dontAutoBoot = Params::getParameterValue("preventSSAutoBoot", false);
        $this->dontUse = Params::getParameterValue("notUseServiceServer", true);
        $this->foreverLog = Params::getParameterValue("foreverLog", null);
        $this->serviceServerKey = Params::getParameterValue("serviceServerKey", '');
        $this->serviceServerCert = Params::getParameterValue("serviceServerCert", '');
        $this->serviceServerCA = Params::getParameterValue("serviceServerCA", '');
        $this->messages[] = $this->messageHead . 'instantiated the ServiceServerProxy class';
    }

    /**
     * @return void
     */
    public function clearMessages(): void
    {
        $this->messages = [];
    }

    /**
     * @return void
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function checkServiceServer(): bool
    {
        if ($this->dontAutoBoot || $this->dontUse) {
            return false;
        } else {
            if (!$this->isServerStartable()) { // Check the home directory can be writable.
                $userName = IMUtil::getServerUserName();
                $userHome = IMUtil::getServerUserHome();
                $message = $this->messageHead . "Service Server can't boot because the root directory " .
                    "({$userHome}) of the web server user ({$userName})  isn't writable.";
                $this->messages[] = $message;
                return false;
            }
            $isStartCLI = false;
            if (php_sapi_name() == 'cli') { // It's executing with command line interface.
                $message = $this->messageHead . "[ServiceServerProxy] php_sapi_name() returns=" . php_sapi_name();
                $this->messages[] = $message;
                $isStartCLI = true; // Do nothing; that is no try to boot the service server.
            }

            $waitSec = 3;
            $startDT = new DateTime();
            $counterInit = $counter = $isStartCLI ? 1 : 5;
            $isStartServer = false;
            while (!$this->isActive()) {
                if (!$isStartServer && !$isStartCLI) {
                    $this->startServer();
                    $isStartServer = true;
                }
                $counter -= 1;

                if ($counter < 1) {
                    $message = $this->messageHead . "Service Server couldn't boot in spite of {$counterInit} times trial.";
                    $this->messages[] = $message;
                    return false;
                }

                $intObj = (new DateTime())->diff($startDT, true);
                $intSecs = ((((($intObj->y * 30) + $intObj->m) * 12 + $intObj->d) * 24 + $intObj->h) * 60 + $intObj->i) * 60 + $intObj->s;
                if ($intSecs > $waitSec) {
                    $message = $this->messageHead . 'Service Server could not be available for timeout.';
                    $this->messages[] = $message;
                    return false;
                }
                sleep(1.0);
            }
            return true;
        }
    }

    /**
     * This method just checks whether the Service Server respond.
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->dontUse) {
            $this->messages[] = $this->messageHead . 'Service Server is NOT used.';
            return false;
        }

        if (!is_null($this->serverInfoCachedDT)) {
            return $this->serverInfoCached;
        }
        $this->serverInfoCachedDT = new DateTime();

        $this->messages[] = $this->messageHead . 'Check server working:';

        $result = $this->callServer("info", []);
        /* Example of returned string:
         * Request Version:f413bb8852485e3dccdf04d76a95b1afb6b6cf601fdd26e33f87ce6b75460780
         * Server Version:f413bb8852485e3dccdf04d76a95b1afb6b6cf601fdd26e33f87ce6b75460780
         */
        if (!$result) { // Apparently Service Server doesn't boot.
            //$this->startServer();
            $this->serverInfoCached = false;
        } else { // Service Server is booted.
            $this->serverInfoCached = true;
            if (!str_contains($result, "Service Server is active.")) {
                $this->errors[] = $this->messageHead . 'Server respond an irregular message.';
                $this->serverInfoCached = false;
            }
        }
        return $this->serverInfoCached;
    }

    /**
     * @param string $path
     * @param array|null $postData
     * @return string|null
     */
    private function callServer(string $path, ?array $postData = null): ?string
    {
        $url = "{$this->paramsHost}:{$this->paramsPort}/{$path}";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);
        if (is_array($postData)) {
            $postData['vcode'] = $this->getSSVersionCode();
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        }
        $result = curl_exec($ch);
        $this->messages[] = $this->messageHead . "URL:$url, Result:$result";
        $info = curl_getinfo($ch);
        if (curl_errno($ch) !== CURLE_OK || $info['http_code'] !== 200) {
            $this->messages[] = $this->messageHead . 'Absent Service Server or Communication Probrems.';
            $this->messages[] = $this->messageHead . curl_error($ch);
            return null;
        }
        return $result;
    }

    /**
     * @param string $command
     * @return void
     */
    private function executeCommand(string $command): void
    {
        $imPath = IMUtil::pathToINTERMediator();
        //putenv('FOREVER_ROOT=' . IMUtil::getServerUserHome());
        if ($this->paramsBoot) {
            putenv('PATH=' . realpath($imPath . "/node_modules/.bin") .
                (IMUtil::isPHPExecutingWindows() ? ';' : ':') . getenv('PATH'));
        } else {
            putenv('PATH=' . realpath($imPath . "/vendor/bin") .
                (IMUtil::isPHPExecutingWindows() ? ';' : ':') . realpath($imPath . "/node_modules/.bin") .
                (IMUtil::isPHPExecutingWindows() ? ';' : ':') . getenv('PATH'));
        }
        $this->messages[] = $this->messageHead . "PATH = " . getenv('PATH');
        $this->messages[] = $this->messageHead . "Command: {$command}";
        $result = [];
        $returnValue = 0;
        chdir($imPath);
        $this->messages[] = $this->messageHead . "PWD = " . getcwd();
        exec($command, $result, $returnValue);
        $this->messages[] = $this->messageHead . "Returns: {$returnValue}, Output:" . implode("/", $result);
        error_log("Service Server tried to boot: {$command}");
    }

    /**
     * @return bool
     */
    private function isServerStartable(): bool
    {
        $homeDir = IMUtil::getServerUserHome();
        if (file_exists($homeDir) && is_dir($homeDir) && is_writable($homeDir)) {
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    private function startServer(): void
    {
        $dq = '"';
        $this->messages[] = $this->messageHead . "startServer() called";
        $nodemon = IMUtil::isPHPExecutingWindows() ? "nodemon.cmd" : "nodemon";
        $scriptPath = "src/js/Service_Server.js";
        if (IMUtil::isPHPExecutingWindows()) {
            $nodemon = str_replace("/", DIRECTORY_SEPARATOR, $nodemon);
            $scriptPath = str_replace("/", DIRECTORY_SEPARATOR, $scriptPath);
        }
        $scriptDir = dirname($scriptPath);

        $logFile = $this->foreverLog ?? (tempnam(sys_get_temp_dir(), 'IMSS-') . ".log");
        $options = "--watch {$scriptDir}";
        $hostName = $_SERVER['HTTP_HOST'] ?? '*';
        $originURL = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $hostName;
        $cmd = "{$nodemon} {$options} {$scriptPath} {$this->paramsPort} {$dq}{$originURL}{$dq}";
        if ($this->serviceServerKey && $this->serviceServerCert) {
            $cmd .= " {$dq}{$this->serviceServerKey}{$dq} ";
            $cmd .= " {$dq}{$this->serviceServerCert}{$dq}";
            $cmd .= "  {$dq}{$this->serviceServerCA}{$dq}";
        }
        $this->executeCommand("$cmd >> {$logFile} &");
    }

     /**
     * @param string $expression
     * @param array $values
     * @return bool
     */
    public function validate(string $expression, array $values): bool
    {
        if ($this->dontUse) {
            $this->messages[] = $this->messageHead . 'Service Server is NOT used.';
            return true;
        }

        $this->messages[] = $this->messageHead . 'Validation start:' . $expression . ' with ' . var_export($values, true);

        $result = $this->callServer("eval", ["expression" => $expression, "values" => $values]);
        if (!$result) {
            return false;
        }
        if (strpos($result, 'true') === false && strpos($result, 'false') === false) {
            $this->errors[] = $this->messageHead . 'Server respond an irregular message.';
            return false;
        }
        return true;
    }

    /**
     * @param array $channels
     * @param string $operation
     * @param array $data
     * @return bool
     */
    public function sync(array $channels, string $operation, array $data): bool
    {
        $logger = Logger::getInstance();
        $logger->setDebugMessage("[ServiceServerProxy] sync");
        if (!$this->checkServiceServer()) {
            $logger->setDebugMessage("[ServiceServerProxy] return false");
            return false;
        }
        $result = $this->callServer("trigger", ['clients' => $channels, 'operation' => $operation, 'data' => $data]);
        $logger->setDebugMessage("[ServiceServerProxy] callServer result={$result}");
        return true;
    }

    /**
     * @return string
     */
    private function getSSVersionCode(): string
    {
        $composer = json_decode(file_get_contents(IMUtil::pathToINTERMediator() . "/composer.json"));
        return hash("sha256", $composer->time . $composer->version);
    }
}
