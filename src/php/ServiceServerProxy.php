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
    private $errors = [];
    private $messages = [];
    private $messageHead = "[ServiceServerProxy] ";
    private $nodePath;
    private $foreverPath;

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
        $params = IMUtil::getFromParamsPHPFile(array("serviceServerPort", "serviceServerHost"), true);
        $this->paramsHost = $params["serviceServerHost"] ? $params["serviceServerHost"] : "localhost";
        $this->paramsPort = $params["serviceServerPort"] ? intval($params["serviceServerPort"]) : 11478;
        $imPath = IMUtil::pathToINTERMediator();
        $this->foreverPath = "{$imPath}/node_modules/forever/bin/forever";
        if(IMUtil::isPHPExecutingWindows()){
            $this->foreverPath .=  "-win";
        }
        $this->nodePath = "{$imPath}/vendor/bin/node";
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
        $waitSec = 5;
        $startDT = new \DateTime();
        $counterInit = $counter = 10;
        while (!$this->isActive()) {
            $this->startServer();
            $counter -= 1;

            if ($counter < 1) {
                $this->errors[] = $this->messageHead . "Service Server couldn't boot in spite of {$counterInit} times trial.";
                return;
            }

            $intObj = (new \DateTime())->diff($startDT, true);
            $intSecs = ((((($intObj->y * 30) + $intObj->m) * 12 + $intObj->d) * 24 + $intObj->h) * 60 + $intObj->i) * 60 + $intObj->s;
            if ($intSecs > $waitSec) {
                $this->errors[] = $this->messageHead . 'Service Server could not be available for timeout.';
                return;
            }
            sleep(2.0);
        }
    }

    public function isActive()
    {
        $url = "http://{$this->paramsHost}:{$this->paramsPort}/info";
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 1,]);
        $result = curl_exec($ch);
        $this->messages[] = $this->messageHead . "URL:$url, Result:$result";
        $info = curl_getinfo($ch);
        if (curl_errno($ch) !== CURLE_OK || $info['http_code'] !== 200) {
            $this->errors[] = $this->messageHead . 'Absent Service Server or Communication Probrems.';
            $this->errors[] = $this->messageHead . curl_error($ch);
            return false;
        }

        if (strpos($result, 'Service Server is active.') === false) {
            $this->errors[] = $this->messageHead . 'Server respond an irregular message.';
            return false;
        }
        return true;
    }

    private function startServer()
    {
        openlog("INTER-Mediator_ServiceServer", LOG_PID | LOG_PERROR, LOG_USER);

        $imPath = IMUtil::pathToINTERMediator();

        $script = file_get_contents($this->foreverPath);
        $script = str_replace(" node", " " . $this->nodePath, $script);
        file_put_contents($this->foreverPath, $script);
        $logFile = tempnam(sys_get_temp_dir(), 'IMSS-') . ".log";
        $cmd = "{$this->foreverPath} start -w -a -l {$logFile} " .
            "{$imPath}/src/js/Service_Server.js {$this->paramsPort}";
        syslog(LOG_INFO, "Command:$cmd");
        $result = [];
        $returnValue = 0;
        exec($cmd, $result, $returnValue);

        syslog(LOG_INFO, "Returns:$returnValue, Output:" . implode("/", $result));
        closelog();
        return true;
    }

    public function stopServer()
    {
        $cmd = "{$this->foreverPath} stopall";
        exec(escapeshellcmd("$cmd"));
    }
}
