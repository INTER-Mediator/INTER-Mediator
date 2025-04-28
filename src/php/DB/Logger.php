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
 * Logger class for managing error, warning, and debug messages in INTER-Mediator.
 * Provides static instance management, log storage, and log output utilities.
 */
class Logger
{
    /**
     * Debug level (int or bool).
     * @var int|bool
     */
    private int|bool $debugLevel = false;
    /**
     * Array of error messages.
     * @var array
     */
    private array $errorMessage = array();
    /**
     * Array of warning messages.
     * @var array
     */
    private array $warningMessage = array();
    /**
     * Array of debug messages.
     * @var array
     */
    private array $debugMessage = array();
    /**
     * Whether error message logging is enabled.
     * @var bool
     */
    private bool $errorMessageLogging;
    /**
     * Whether warning message logging is enabled.
     * @var bool
     */
    private bool $warningMessageLogging;
    /**
     * Whether debug message logging is enabled.
     * @var bool
     */
    private bool $debugMessageLogging;
    /**
     * Singleton instance of Logger.
     * @var Logger|null
     */
    private static ?Logger $instance = null;

    /**
     * Get the singleton Logger instance.
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
     * Logger constructor (private for singleton pattern).
     */
    private function __construct()
    {
        [$this->errorMessageLogging, $this->warningMessageLogging, $this->debugMessageLogging]
            = Params::getParameterValue(["errorMessageLogging", "warningMessageLogging", "debugMessageLogging",], false);
    }

    /**
     * Clear all logs (error, warning, debug).
     * @return void
     */
    public function clearLogs(): void
    {
        $this->errorMessage = array();
        $this->warningMessage = array();
        $this->debugMessage = array();
    }

    /**
     * Clear error logs only.
     * @return void
     */
    public function clearErrorLog(): void
    {
        $this->errorMessage = array();
    }

    /**
     * Get the namespace of the caller if the setting is true or matches the namespace.
     * @param bool $setting
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
                $returnValue = str_starts_with($ref->getNamespaceName(), $setting);
            }
        } catch (ReflectionException $e) {
        }
        return $returnValue;
    }

    /**
     * Set a debug message with a specified level.
     * @param string $str
     * @param int $level
     * @return void
     */
    public function setDebugMessage(string $str, int $level = 1): void
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
     * Set multiple debug messages with a specified level.
     * @param array $msgs
     * @param int $level
     * @return void
     */
    public function setDebugMessages(array $msgs, int $level = 1): void
    {
        if ($this->debugLevel !== false && $this->debugLevel >= $level) {
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
     * Set a warning message.
     * @param string $str
     * @return void
     */
    public function setWarningMessage(string $str): void
    {
        $this->warningMessage[] = $str;
        if ($this->warningMessageLogging) {
            $dt = (new DateTime())->format("y:m:d h:i:s.v");
            error_log("[INTER-Mediator WARNING] {$dt} {$str}");
        }
    }

    /**
     * Set multiple warning messages.
     * @param array $msgs
     * @return void
     */
    public function setWarningMessages(array $msgs): void
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
     * Set an error message.
     * @param string $str
     * @return void
     */
    public function setErrorMessage(string $str): void
    {
        $this->errorMessage[] = $str;
        if ($this->errorMessageLogging) {
            $dt = (new DateTime())->format("y:m:d h:i:s.v");
            error_log("[INTER-Mediator ERROR] {$dt} {$str}");
        }
    }

    /**
     * Set multiple error messages.
     * @param array $msgs
     * @return void
     */
    public function setErrorMessages(array $msgs): void
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
     * Get messages for JavaScript output.
     * @return array
     */
    public function getMessagesForJS(): array
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
     * Get error messages.
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessage;
    }

    /**
     * Get warning messages.
     * @return array
     */
    public function getWarningMessages(): array
    {
        return $this->warningMessage;
    }

    /**
     * Get debug messages.
     * @return array
     */
    public function getDebugMessages(): array
    {
        return $this->debugMessage;
    }

    /**
     * Get all error messages as a string.
     * @return string
     */
    public function getAllErrorMessages(): string
    {
        $returnData = "";
        foreach ($this->errorMessage as $oneError) {
            $returnData .= "{$oneError}\n";
        }
        return $returnData;
    }

    /**
     * Set debug mode.
     * @param $val
     * @return void
     */
    public function setDebugMode($val): void
    {
        if ($val === true) {
            $this->debugLevel = 1;
        } else {
            $this->debugLevel = $val;
        }
    }

    /**
     * Get debug messages.
     * @return array
     */
    public function getDebugMessage(): array
    {
        return $this->debugMessage;
    }

    /**
     * Get debug level.
     * @return int|bool
     */
    public function getDebugLevel(): int|bool
    {
        return $this->debugLevel;
    }
}
