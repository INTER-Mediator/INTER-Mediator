<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2018/06/17
 * Time: 16:51
 */

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
    //private bool $paramsQuit;
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
//        $this->paramsQuit = Params::getParameterValue("stopSSEveryQuit", false);
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
//            $ssStatus = $this->isActive();
//            if (!$ssStatus) {
//                $message = $this->messageHead . 'Service Server is NOT working so far.';
//                $this->messages[] = $message;
//                $logger = Logger::getInstance();
//                $logger->setDebugMessage("[ServiceServerProxy] {$message}");
//            }
//            return $ssStatus;
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
        //$this->messages[] = $this->messageHead . 'Server returns:' . $result;
        /*
         * Request Version:f413bb8852485e3dccdf04d76a95b1afb6b6cf601fdd26e33f87ce6b75460780
         * Server Version:f413bb8852485e3dccdf04d76a95b1afb6b6cf601fdd26e33f87ce6b75460780
         */
        // Checking both version of the executing and the code
        if (!$result) { // Apparently Service Server doesn't boot.
            //$this->startServer();
            $this->serverInfoCached = false;
        } else { // Service Server is booted.
            $this->serverInfoCached = true;
            /* nodemon is going to take care of booted daemon is alive or not.
              So cheking of Service Server version  doesn't need. */
//            $keyword = 'Request Version:';
//            $rPos = strpos($result, $keyword);
//            $reqVerStr = $rPos !== false ? substr($result, $rPos + strlen($keyword), 64) : "aa";
//            $keyword = 'Server Version:';
//            $sPos = strpos($result, 'Server Version:');
//            $svrVerStr = $sPos !== false ? substr($result, $sPos + strlen($keyword), 64) : "bb";
//            if ($reqVerStr != $svrVerStr) { // If they are different version. Server code might be old one.
//                $this->messages[] = $this->messageHead . "Restart Service Server: reqVerStr={$reqVerStr}, svrVerStr={$svrVerStr}";
//                //$this->stopServerCommand();
//                //$this->restartServer(); // Restart is going to fail. Why??
//                //throw new \Exception('Different version server is executing.');
//                //$this->startServer();
//                return false;
//            }
//            if (!$result) {
//                return false;
//            }
            if (strpos($result, "Service Server is active.") === false) {
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
            return false;
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
//        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
        $nodemon = /* IMUtil::pathToINTERMediator() .  "/node_modules/.bin/"
            . */(IMUtil::isPHPExecutingWindows() ? "nodemon.cmd" : "nodemon");
        $scriptPath = /* IMUtil::pathToINTERMediator() . */  "src/js/Service_Server.js";
        if (IMUtil::isPHPExecutingWindows()) {
            $nodemon = str_replace("/", DIRECTORY_SEPARATOR, $nodemon);
            $scriptPath = str_replace("/", DIRECTORY_SEPARATOR, $scriptPath);
        }
        $scriptDir = dirname($scriptPath);

        $logFile = $this->foreverLog ?? (tempnam(sys_get_temp_dir(), 'IMSS-') . ".log");
//        $options = "-a -l {$logFile} --minUptime 5000 --spinSleepTime 5000";
        $options = "--watch {$scriptDir}";
        $hostName = $_SERVER['HTTP_HOST'] ?? '*';
        $originURL = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $hostName;
//        $cmd = "{$forever} start {$options} {$scriptPath} {$this->paramsPort} {$dq}{$originURL}{$dq}";
        $cmd = "{$nodemon} {$options} {$scriptPath} {$this->paramsPort} {$dq}{$originURL}{$dq}";
        if ($this->serviceServerKey && $this->serviceServerCert) {
            $cmd .= " {$dq}{$this->serviceServerKey}{$dq} ";
            $cmd .= " {$dq}{$this->serviceServerCert}{$dq}";
            $cmd .= "  {$dq}{$this->serviceServerCA}{$dq}";
        }
        $this->executeCommand("$cmd >> {$logFile} &");
    }
    /*
     * About forever on Apr 14, 2019 by Masayuki Nii
     * The forever-win adds two short-cut links in node_module/.bin, but the forever doesn't.
     * So we don't execute command in the node_module/.bin directory.
     */

    /**
     * nodemon is going to restart with modifying ServiceServer.js, so this method won't be called.
     * @return void
     */
//    private function restartServer(): void
//    {
////        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
//        $nodemon = IMUtil::isPHPExecutingWindows() ? "nodemon.cmd" : "nodemon";
//        $scriptPath = "src/js/Service_Server.js";
//        if (IMUtil::isPHPExecutingWindows()) {
//            $scriptPath = str_replace("/", DIRECTORY_SEPARATOR, $scriptPath);
//        }
//        $cmd = "{$nodemon} --signal SIGHUP {$scriptPath}";
//        $this->executeCommand($cmd);
//    }

    /**
     * @return void
     */
//    public function stopServer(): void
//    {
//        $this->messages[] = $this->messageHead . "stopServer() called";
//        if (!$this->paramsQuit) {
//            return;
//        }
//        $this->stopServerCommand();
//    }

    /**
     * @return void
     */
//    private function stopServerCommand(): void
//    {
//        $this->messages[] = $this->messageHead . "stopServerCommand() called";
////        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
//        $nodemon = IMUtil::isPHPExecutingWindows() ? "nodemon.cmd" : "nodemon";
//        $scriptPath = "src/js/Service_Server.js";
//        $cmd = "{$nodemon} --signal SIGUSR2 {$scriptPath} &";
//        $this->executeCommand($cmd);
//        //sleep(1);
//    }

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
