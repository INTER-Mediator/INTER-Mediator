<?php

/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2018/06/17
 * Time: 16:51
 */

namespace INTERMediator;

$gSSPInstance = null;

class ServiceServerProxy
{
    private $paramsHost;
    private $paramsPort;
    private $paramsQuit;
    private $paramsBoot;
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
            "serviceServerPort", "serviceServerHost", "stopSSEveryQuit",
            "bootWithInstalledNode","preventSSAutoBoot"], true);
        $this->paramsHost = $params["serviceServerHost"] ? $params["serviceServerHost"] : "localhost";
        $this->paramsPort = $params["serviceServerPort"] ? intval($params["serviceServerPort"]) : 11478;
        $this->paramsQuit = $params["stopSSEveryQuit"] == NULL ? false : boolval($params["stopSSEveryQuit"]);
        $this->paramsBoot = $params["bootWithInstalledNode"] == NULL ? false : boolval($params["bootWithInstalledNode"]);
        $this->dontAutoBoot = $params["preventSSAutoBoot"] == NULL ? false : boolval($params["preventSSAutoBoot"]);
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
        if ($this->dontAutoBoot)   {
            $ssStatus= $this->isActive();
            if(!$ssStatus){
                $this->messages[] = $this->messageHead . 'Service Server is NOT working so far.';
            }
            return $ssStatus;
        } else {
            $waitSec = 5;
            $startDT = new \DateTime();
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

                $intObj = (new \DateTime())->diff($startDT, true);
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
        $this->messages[] = $this->messageHead . 'Check server working:';

        $result = $this->callServer("info", false);
        $this->messages[] = $this->messageHead . 'Server returns:' . $result;

        if (!$result) {
            return false;
        }
        if (strpos($result, 'Service Server is active.') === false) {
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
        if ($postData) {
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
        if(IMUtil::isPHPExecutingWindows()){
            $home = getenv("USERPROFILE");
            putenv('FOREVER_ROOT=' . $home);
        }else {
            $user = posix_getpwuid(posix_getuid());
            putenv('FOREVER_ROOT=' . $user['dir']);
        }
        if ( $this->paramsBoot) {
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

    private function startServer()
    {
        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
        $scriptPath = "src/js/Service_Server.js";
        if (IMUtil::isPHPExecutingWindows()) {
            $scriptPath = str_replace("/", DIRECTORY_SEPARATOR, $scriptPath);
        }
        $logFile = tempnam(sys_get_temp_dir(), 'IMSS-') . ".log";
        $options = "-a -l {$logFile} --minUptime 5000 --spinSleepTime 5000";
        $cmd = "{$forever} start {$options} {$scriptPath} {$this->paramsPort}";
        $this->executeCommand($cmd);
    }

    public function stopServer()
    {
        if (!$this->paramsQuit) {
            return;
        }
        $forever = IMUtil::isPHPExecutingWindows() ? "forever.cmd" : "forever";
        $cmd = "{$forever} stopall";
        $this->executeCommand($cmd);
    }

    public
    function validate($expression, $values)
    {
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
}
