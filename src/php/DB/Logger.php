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

/**
 *
 */
class Logger
{
    /* Debug and Messages */
    /**
     * @var bool
     */
    private bool $debugLevel = false;
    /**
     * @var array
     */
    private array $errorMessage = array();
    /**
     * @var array
     */
    private array $warningMessage = array();
    /**
     * @var array
     */
    private array $debugMessage = array();
    /**
     * @var mixed
     */
    private $errorMessageLogging;
    /**
     * @var mixed
     */
    private $warningMessageLogging;
    /**
     * @var mixed
     */
    private $debugMessageLogging;

    /**
     * @var Logger|null
     */
    private static ?Logger $instance = null;

    /**
     * @return Logger
     */
    public static function getInstance(): Logger
    {
        if (!self::$instance) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    /**
     *
     */
    private function __construct()
    {
        [$this->errorMessageLogging, $this->warningMessageLogging, $this->debugMessageLogging]
            = Params::getParameterValue(["errorMessageLogging", "warningMessageLogging", "debugMessageLogging",], false);
    }

    /**
     * @return void
     */
    public function clearLogs()
    {
        $this->errorMessage = array();
        $this->warningMessage = array();
        $this->debugMessage = array();
    }

    /**
     * @return void
     */
    public function clearErrorLog()
    {
        $this->errorMessage = array();
    }

    /**
     * @param $setting
     * @return bool
     */
    private function getCallersNamespace(bool $setting): bool
    {
        if ($setting === true) { // $setting === "*"
            return true;
        }
        $returnValue = false;
        try {
            $bt = debug_backtrace();
            if (count($bt) >= 2 && isset($bt[2]['object'])) {
                $obj = $bt[2]['object'];
                $ref = new ReflectionClass($obj);
                $returnValue = strpos($ref->getNamespaceName(), $setting) === 0;
            }
        } catch (ReflectionException $e) {
        }
        return $returnValue;
    }

    /**
     * @param $str
     * @param $level
     * @return void
     */
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

    /**
     * @param $msgs
     * @param $level
     * @return void
     */
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

    /**
     * @param $str
     * @return void
     */
    public function setWarningMessage($str)
    {
        $this->warningMessage[] = $str;
        if ($this->warningMessageLogging) {
            $dt = (new DateTime())->format("y:m:d h:i:s.v");
            error_log("[INTER-Mediator WARNING] {$dt} {$str}");
        }
    }

    /**
     * @param $msgs
     * @return void
     */
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

    /**
     * @param $str
     * @return void
     */
    public function setErrorMessage($str)
    {
        $this->errorMessage[] = $str;
        if ($this->errorMessageLogging) {
            $dt = (new DateTime())->format("y:m:d h:i:s.v");
            error_log("[INTER-Mediator ERROR] {$dt} {$str}");
        }
    }

    /**
     * @param $msgs
     * @return void
     */
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

    /**
     * @return array
     */
    public function getMessagesForJS()
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

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessage;
    }

    /**
     * @return array
     */
    public function getWarningMessages()
    {
        return $this->warningMessage;
    }

    /**
     * @return array
     */
    public function getDebugMessages()
    {
        return $this->debugMessage;
    }

    /**
     * @return string
     */
    public function getAllErrorMessages()
    {
        $returnData = "";
        foreach ($this->errorMessage as $oneError) {
            $returnData .= "{$oneError}\n";
        }
        return $returnData;
    }

    /**
     * @param $val
     * @return void
     */
    public function setDebugMode($val)
    {
        if ($val === true) {
            $this->debugLevel = 1;
        } else {
            $this->debugLevel = $val;
        }
    }

    /**
     * @return array
     */
    public function getDebugMessage()
    {
        return $this->debugMessage;
    }

    /**
     * @return bool
     */
    public function getDebugLevel()
    {
        return $this->debugLevel;
    }
}
