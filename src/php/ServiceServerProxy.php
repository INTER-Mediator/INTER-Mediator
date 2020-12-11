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

    static public function instance()
    {
        global $gSSPInstance;
        if (is_null($gSSPInstance)) {
            $gSSPInstance = new ServiceServerProxy();
        }
        return $gSSPInstance;
    }

    private function __construct()
    {
        $params = IMUtil::getFromParamsPHPFile([
            "serviceServerPort", "serviceServerConnect", "stopSSEveryQuit",
            "bootWithInstalledNode", "preventSSAutoBoot", "notUseServiceServer", "foreverLog"], true);
        $this->paramsHost = $params["serviceServerConnect"] ? $params["serviceServerConnect"] : "localhost";
        $this->paramsPort = $params["serviceServerPort"] ? intval($params["serviceServerPort"]) : 11478;
        $this->paramsQuit = is_null($params["stopSSEveryQuit"]) ? false : boolval($params["stopSSEveryQuit"]);
        $this->paramsBoot = is_null($params["bootWithInstalledNode"]) ? false : boolval($params["bootWithInstalledNode"]);
        $this->dontAutoBoot = is_null($params["preventSSAutoBoot"]) ? false : boolval($params["preventSSAutoBoot"]);
        $this->dontUse = is_null($params["notUseServiceServer"]) ? false : boolval($params["notUseServiceServer"]);
        $this->foreverLog = is_null($params["foreverLog"]) ? "" : $params["foreverLog"];
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

    public function getMessages()
    {
        return $this->messages;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function checkServiceServer()
    {
        if ($this->dontAutoBoot || $this->dontUse) {
            $ssStatus = $this->isActive();
            if (!$ssStatus) {
                $this->messages[] = $this->messageHead . 'Service Server is NOT working so far.';
            }
            return $ssStatus;
        } else {
            if (!$this->isServerStartable()) {
                // https://stackoverflow.com/questions/7771586/how-to-check-what-user-php-is-running-as
                // get_current_user doen't work on the ubuntu 18 of EC2. It returns the user logs in with ssh.
                $uInfo = posix_getpwuid(posix_geteuid());
                $this->errors[] = $this->messageHead . "Service Server can't boot because the root directory " .
                    "({$uInfo["dir"]}) of the web server user ({$uInfo['name']})  isn't writable.";
                return false;
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

    public function isActive()
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
        $url = "http://{$this->paramsHost}:{$this->paramsPort}/{$path}";
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
    }

    private function executeCommand($command)
    {
        $imPath = IMUtil::pathToINTERMediator();
        if (IMUtil::isPHPExecutingWindows()) {
            $home = getenv("USERPROFILE");
            putenv('FOREVER_ROOT=' . $home);
        } else {
            $user = posix_getpwuid(posix_getuid());
            putenv('FOREVER_ROOT=' . $user['dir']);
        }
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
    }

    private function isServerStartable()
    {
        // https://stackoverflow.com/questions/7771586/how-to-check-what-user-php-is-running-as
        // get_current_user doen't work on the ubuntu 18 of EC2. It returns the user logs in with ssh.
        $homeDir = posix_getpwuid(posix_geteuid())["dir"];
        if (file_exists($homeDir) && is_dir($homeDir) && is_writable($homeDir)) {
            return true;
        }
        return false;
    }

    private function startServer()
    {
        $this->messages[] = $this->messageHead . "startServer() called";
        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
        $scriptPath = "src/js/Service_Server.js";
        if (IMUtil::isPHPExecutingWindows()) {
            $scriptPath = str_replace("/", DIRECTORY_SEPARATOR, $scriptPath);
        }

        $logFile = $this->foreverLog ? $this->foreverLog : tempnam(sys_get_temp_dir(), 'IMSS-') . ".log";
        $options = "-a -l {$logFile} --minUptime 5000 --spinSleepTime 5000";
        $cmd = "{$forever} start {$options} {$scriptPath} {$this->paramsPort}";
        $this->executeCommand($cmd);
    }

    private function restartServer()
    {
        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
        $scriptPath = "src/js/Service_Server.js";
        if (IMUtil::isPHPExecutingWindows()) {
            $scriptPath = str_replace("/", DIRECTORY_SEPARATOR, $scriptPath);
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

    public function validate($expression, $values)
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

    private function getSSVersionCode()
    {
        $composer = json_decode(file_get_contents(IMUtil::pathToINTERMediator() . "/composer.json"));
        return hash("sha256", $composer->time . $composer->version);
    }
}
