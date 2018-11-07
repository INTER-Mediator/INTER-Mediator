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
        $this->messages[] = $this->messageHead . 'Instanciated of the ServiceServerProxy class';

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
        if (!$this->isActive()) {
            $this->startServer();
        }
    }

    public function isActive()
    {
        $url = "http://{$this->paramsHost}:{$this->paramsPort}/info";
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 1,]);
        $result = curl_exec($ch);
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
        $this->messages[] = $this->messageHead . "URL:$url, Result:$result";
        return true;
    }

    private function startServer()
    {
        $waitSec = 5;
        $startDT = new \DateTime();
        $imPath = IMUtil::pathToINTERMediator();
        $cmd = "{$this->foreverPath} start {$imPath}/src/js/Service_Server.js {$this->paramsPort}";
        $this->messages[] = $this->messageHead . "Command:$cmd";
        $result = [];
        $returnValue = 0;
        exec(escapeshellcmd("$cmd"), $result, $returnValue);
        $this->messages[] = $this->messageHead . "Returns:$returnValue, Output:" . implode("/", $result);
        while (!$this->isActive()) {
            $intObj = (new \DateTime())->diff($startDT, true);
            $intSecs = ((((($intObj->y * 30) + $intObj->m) * 12 + $intObj->d) * 24 + $intObj->h) * 60 + $intObj->i) * 60 + $intObj->s;
            if ($intSecs > $waitSec) {
                return false;
            }
            sleep(1.0);
        }
        return true;
    }

    public function stopServer()
    {
        $cmd = "{$this->foreverPath} stopall";
        exec(escapeshellcmd("$cmd"));
    }
}
