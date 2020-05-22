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

class Logger
{
    /* Debug and Messages */
    private $debugLevel = false;
    private $errorMessage = array();
    private $debugMessage = array();

    private static $instance = null;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    public function clearLogs()
    {
        $this->errorMessage = array();
        $this->debugMessage = array();
    }

    public function setDebugMessage($str, $level = 1)
    {
        if ($this->debugLevel !== false && $this->debugLevel >= $level) {
            $this->debugMessage[] = $str;
        }
    }

    public function setDebugMessages($msgs, $level = 1)
    {
        if ($this->debugLevel !== false && $this->debugLevel >= $level && is_array($msgs)) {
            foreach($msgs as $msg) {
                $this->debugMessage[] = $msg;
            }
        }
    }

    public function setErrorMessage($str)
    {
        $this->errorMessage[] = $str;
    }

    public function setErrorMessages($msgs)
    {
        foreach($msgs as $msg) {
            $this->errorMessage[] = $msg;
        }
    }

    function getMessagesForJS()
    {
        $q = '"';
        $returnData = array();
        foreach ($this->errorMessage as $oneError) {
            $returnData[] = "INTERMediatorLog.setErrorMessage({$q}"
                . str_replace("\n", " ", addslashes($oneError)) . "{$q});";
        }
        foreach ($this->debugMessage as $oneError) {
            $returnData[] = "INTERMediatorLog.setDebugMessage({$q}"
                . str_replace("\n", " ", addslashes($oneError)) . "{$q});";
        }
        return $returnData;
    }

    public function getErrorMessages()
    {
        return $this->errorMessage;
    }

    public function getDebugMessages()
    {
        return $this->debugMessage;
    }

    function getAllErrorMessages()
    {
        $returnData = "";
        foreach ($this->errorMessage as $oneError) {
            $returnData .= "{$oneError}\n";
        }
        return $returnData;
    }

    public function setDebugMode($val)
    {
        if ($val === true) {
            $this->debugLevel = 1;
        } else {
            $this->debugLevel = $val;
        }
    }

    public function getDebugMessage()
    {
        return $this->debugMessage;
    }

    public function getDebugLevel()
    {
        return $this->debugLevel;
    }
}
