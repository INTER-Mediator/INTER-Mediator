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
            $this->errors[] = curl_error($ch);
            return false;
        }

        if (strpos($result, 'Service Server is active.') === false) {
            $this->errors[] = 'Server respond an irregular message.';
            return false;
        }
        return true;
    }

    private function startServer()
    {
        $imPath = IMUtil::pathToINTERMediator();
        $cmd = "{$this->foreverPath} start {$imPath}/src/js/Service_Server.js {$this->paramsPort}";
        exec(escapeshellcmd("$cmd"));
        while(!$this->isActive()){
            sleep(0.1);
        }

    }

    public function stopServer()
    {
        $cmd = "{$this->foreverPath} stopall";
        exec(escapeshellcmd("$cmd"));
    }
}