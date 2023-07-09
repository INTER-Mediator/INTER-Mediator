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

use INTERMediator\Params;
use DateTime;
use ReflectionClass;
use ReflectionException;

class Logger
{
    /* Debug and Messages */
    private $debugLevel = false;
    private $errorMessage = array();
    private $warningMessage = array();
    private $debugMessage = array();
    private $errorMessageLogging = false;
    private $warningMessageLogging = false;
    private $debugMessageLogging = false;

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
        [$this->errorMessageLogging, $this->warningMessageLogging, $this->debugMessageLogging]
            = Params::getParameterValue(["errorMessageLogging", "warningMessageLogging", "debugMessageLogging",], false);
    }

    public function clearLogs()
    {
        $this->errorMessage = array();
        $this->warningMessage = array();
        $this->debugMessage = array();
    }

    public function clearErrorLog()
    {
        $this->errorMessage = array();
    }

    private function getCallersNamespace($setting)
    {
        if ($setting === true || $setting === "*") {
            return true;
        }
        $returnValue = false;
        try {
            $bt = debug_backtrace();
            if (count($bt) >= 2 && isset($bt[2]['object'])) {
                $obj = $bt[2]['object'];
                if ($obj) {
                    $ref = new ReflectionClass($obj);
                    $returnValue = strpos($ref->getNamespaceName(), $setting) === 0;
                }
            }
        } catch (ReflectionException $e) {
        }
        return $returnValue;
    }

    public function setDebugMessage($str, $level = 1)
    {
        if ($this->debugLevel !== false && $this->debugLevel >= $level) {
            $this->debugMessage[] = $str;
            if ($this->debugMessageLogging && $this->getCallersNamespace($this->debugMessageLogging)) {
                $dt = (new DateTime())->format("y:m:d h:i:s.v");
                error_log("[INTER-Mediator DEBUG] {$dt} {$str}");
            }
        }
    }

    public function setDebugMessages($msgs, $level = 1)
    {
        if ($this->debugLevel !== false && $this->debugLevel >= $level && is_array($msgs)) {
            $dt = (new DateTime())->format("y:m:d h:i:s.v");
            foreach ($msgs as $msg) {
                $this->debugMessage[] = $msg;
                if ($this->debugMessageLogging && $this->getCallersNamespace($this->debugMessageLogging)) {
                    error_log("[INTER-Mediator DEBUG] {$dt} {$msg}");
                }
            }
        }
    }

    public function setWarningMessage($str)
    {
        $this->warningMessage[] = $str;
        if ($this->warningMessageLogging) {
            $dt = (new DateTime())->format("y:m:d h:i:s.v");
            error_log("[INTER-Mediator WARNING] {$dt} {$str}");
        }
    }

    public function setWarningMessages($msgs)
    {
        $dt = (new DateTime())->format("y:m:d h:i:s.v");
        foreach ($msgs as $msg) {
            $this->warningMessage[] = $msg;
            if ($this->warningMessageLogging) {
                error_log("[INTER-Mediator WARNING] {$dt} {$msg}");
            }
        }
    }

    public function setErrorMessage($str)
    {
        $this->errorMessage[] = $str;
        if ($this->errorMessageLogging) {
            $dt = (new DateTime())->format("y:m:d h:i:s.v");
            error_log("[INTER-Mediator ERROR] {$dt} {$str}");
        }
    }

    public function setErrorMessages($msgs)
    {
        $dt = (new DateTime())->format("y:m:d h:i:s.v");
        foreach ($msgs as $msg) {
            $this->errorMessage[] = $msg;
            if ($this->errorMessageLogging) {
                error_log("[INTER-Mediator ERROR] {$dt} {$msg}");
            }
        }
    }

    function getMessagesForJS()
    {
        $q = '"';
        $returnData = array();
        foreach ($this->errorMessage as $oneError) {
            $returnData[] = "INTERMediatorLog.setErrorMessage({$q}"
                . str_replace("\n", " ", addslashes($oneError) ?? "") . "{$q});";
        }
        foreach ($this->warningMessage as $oneError) {
            $returnData[] = "INTERMediatorLog.setWarningMessage({$q}"
                . str_replace("\n", " ", addslashes($oneError) ?? "") . "{$q});";
        }
        foreach ($this->debugMessage as $oneError) {
            $returnData[] = "INTERMediatorLog.setDebugMessage({$q}"
                . str_replace("\n", " ", addslashes($oneError) ?? "") . "{$q});";
        }
        return $returnData;
    }

    public function getErrorMessages()
    {
        return $this->errorMessage;
    }

    public function getWarningMessages()
    {
        return $this->warningMessage;
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
