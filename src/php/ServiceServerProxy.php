<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2018/06/17
 * Time: 16:51
 */

namespace INTERMediator;

use DateTime;

$gSSPInstance = null;

class ServiceServerProxy
{
    private $paramsHost;
    private $paramsPort;
    private $paramsQuit;
    private $paramsBoot;
    private $dontUse;
    private $errors = [];
    private $messages = [];
    private $messageHead = "[ServiceServerProxy] ";
    private $dontAutoBoot;
    private $foreverLog;
    private $serviceServerKey = "";  // Path of Key file for wss protocol
    private $serviceServerCert = ""; // Path of Cert file for wss protocol
    private $serviceServerCA = ""; // Path of CA file for wss protocol

    static public function instance(): ?ServiceServerProxy
    {
        global $gSSPInstance;
        if (is_null($gSSPInstance)) {
            $gSSPInstance = new ServiceServerProxy();
        }
        return $gSSPInstance;
    }

    private function __construct()
    {
        [$this->paramsHost, $this->paramsPort, $this->paramsQuit, $this->paramsBoot,
            $this->dontAutoBoot, $this->dontUse, $this->foreverLog, $this->serviceServerKey,
            $this->serviceServerCert, $this->serviceServerCA]
            = Params::getParameterValue([
            "serviceServerConnect", "serviceServerPort", "stopSSEveryQuit", "bootWithInstalledNode",
            "preventSSAutoBoot", "notUseServiceServer", "foreverLog", "serviceServerKey",
            "serviceServerCert", "serviceServerCA",],
            ["http://localhost", 11478, false, false, false, true, null, '', '', '']);
        $this->messages[] = $this->messageHead . 'Instanciated the ServiceServerProxy class';
    }

    public function clearMessages()
    {
        $this->messages = [];
    }

    public function clearErrors()
    {
        $this->errors = [];
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function checkServiceServer(): bool
    {
        if ($this->dontAutoBoot || $this->dontUse) {
            $ssStatus = $this->isActive();
            if (!$ssStatus) {
                $this->messages[] = $this->messageHead . 'Service Server is NOT working so far.';
            }
            return $ssStatus;
        } else {
            if (!$this->isServerStartable()) { // Check the home directory can be writable.
                $userName = IMUtil::getServerUserName();
                $userHome = IMUtil::getServerUserHome();
                $this->errors[] = $this->messageHead . "Service Server can't boot because the root directory " .
                    "({$userHome}) of the web server user ({$userName})  isn't writable.";
                return false;
            }
            if (strpos(php_sapi_name(), 'cli') !== false) { // It's executing with command line interface.
                return false; // Do nothing; that is no try to boot the service server.
            }

            $waitSec = 3;
            $startDT = new DateTime();
            $counterInit = $counter = 5;
            $isStartServer = false;
            while (!$this->isActive()) {
                if (!$isStartServer) {
                    $this->startServer();
                    $isStartServer = true;
                }
                $counter -= 1;

                if ($counter < 1) {
                    $this->errors[] = $this->messageHead . "Service Server couldn't boot in spite of {$counterInit} times trial.";
                    return false;
                }

                $intObj = (new DateTime())->diff($startDT, true);
                $intSecs = ((((($intObj->y * 30) + $intObj->m) * 12 + $intObj->d) * 24 + $intObj->h) * 60 + $intObj->i) * 60 + $intObj->s;
                if ($intSecs > $waitSec) {
                    $this->errors[] = $this->messageHead . 'Service Server could not be available for timeout.';
                    return false;
                }
                sleep(1.0);
            }
            return true;
        }
    }

    public function isActive(): bool
    {
        if ($this->dontUse) {
            $this->messages[] = $this->messageHead . 'Service Server is NOT used.';
            return false;
        }

        $this->messages[] = $this->messageHead . 'Check server working:';

        $result = $this->callServer("info", []);
        $this->messages[] = $this->messageHead . 'Server returns:' . $result;
        /*
         * Request Version:f413bb8852485e3dccdf04d76a95b1afb6b6cf601fdd26e33f87ce6b75460780
         * Server Version:f413bb8852485e3dccdf04d76a95b1afb6b6cf601fdd26e33f87ce6b75460780
         */
        // Checking both version of the executing and the code
        $keyword = 'Request Version:';
        $rPos = strpos($result, $keyword);
        $reqVerStr = $rPos >= 0 ? substr($result, $rPos + strlen($keyword), 64) : "aa";
        $keyword = 'Server Version:';
        $sPos = strpos($result, 'Server Version:');
        $svrVerStr = $sPos >= 0 ? substr($result, $sPos + strlen($keyword), 64) : "bb";
        if ($reqVerStr != $svrVerStr) { // If they are different version.
            $this->messages[] = $this->messageHead . "Restart Service Server: reqVerStr={$reqVerStr}, svrVerStr={$svrVerStr}";
            $this->stopServerCommand();
            //$this->restartServer(); // Restart is going to fail. Why??
            //throw new \Exception('Different version server is executing.');
            //$this->startServer();
            return false;
        }
        if (!$result) {
            return false;
        }
        if (strpos($result, "Service Server is active.") === false) {
            $this->errors[] = $this->messageHead . 'Server respond an irregular message.';
            return false;
        }
        return true;
    }

    private function callServer($path, $postData = false, $ignoreError = false)
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
        if (!$ignoreError && (curl_errno($ch) !== CURLE_OK || $info['http_code'] !== 200)) {
            $this->messages[] = $this->messageHead . 'Absent Service Server or Communication Probrems.';
            $this->messages[] = $this->messageHead . curl_error($ch);
            return false;
        }
        return $result;

        //
    }

    private function executeCommand($command)
    {
        $imPath = IMUtil::pathToINTERMediator();
        putenv('FOREVER_ROOT=' . IMUtil::getServerUserHome());
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

    private function isServerStartable(): bool
    {
        $homeDir = IMUtil::getServerUserHome();
        if (file_exists($homeDir) && is_dir($homeDir) && is_writable($homeDir)) {
            return true;
        }
        return false;
    }

    private function startServer()
    {
        $dq = '"';
        $this->messages[] = $this->messageHead . "startServer() called";
        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
        $scriptPath = "src/js/Service_Server.js";
        if (IMUtil::isPHPExecutingWindows()) {
            $scriptPath = str_replace("/", DIRECTORY_SEPARATOR, $scriptPath ?? "");
        }

        $logFile = $this->foreverLog ?? (tempnam(sys_get_temp_dir(), 'IMSS-') . ".log");
        $options = "-a -l {$logFile} --minUptime 5000 --spinSleepTime 5000";
        $hostName = $_SERVER['HTTP_HOST'] ?? '*';
        $originURL = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $hostName;
        $cmd = "{$forever} start {$options} {$scriptPath} {$this->paramsPort} {$dq}{$originURL}{$dq}";
        if ($this->serviceServerKey && $this->serviceServerCert) {
            $cmd .= " {$dq}{$this->serviceServerKey}{$dq} ";
            $cmd .= " {$dq}{$this->serviceServerCert}{$dq}";
            $cmd .= "  {$dq}{$this->serviceServerCA}{$dq}";
        }
        $this->executeCommand($cmd);
    }

    private function restartServer()
    {
        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
        $scriptPath = "src/js/Service_Server.js";
        if (IMUtil::isPHPExecutingWindows()) {
            $scriptPath = str_replace("/", DIRECTORY_SEPARATOR, $scriptPath ?? "");
        }
        $cmd = "{$forever} restart {$scriptPath}";
        $this->executeCommand($cmd);
    }

    public function stopServer()
    {
        $this->messages[] = $this->messageHead . "stopServer() called";
        if (!$this->paramsQuit) {
            return;
        }
        $this->stopServerCommand();
    }

    private function stopServerCommand()
    {
        $this->messages[] = $this->messageHead . "stopServerCommand() called";
        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
        $cmd = "{$forever} stopall";
        $this->executeCommand($cmd);
        //sleep(1);
    }

    public function validate($expression, $values): bool
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

    public function sync($channels, $operation, $data): bool
    {
        if (!$this->checkServiceServer()) {
            return false;
        }
        $result = $this->callServer("trigger", ['clients' => $channels, 'operation' => $operation, 'data' => $data]);
        return true;
    }

    private function getSSVersionCode(): string
    {
        $composer = json_decode(file_get_contents(IMUtil::pathToINTERMediator() . "/composer.json"));
        return hash("sha256", $composer->time . $composer->version);
    }
}
