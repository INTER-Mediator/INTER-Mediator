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
class DB_Logger
{
    /* Debug and Messages */
    private $debugLevel = false;
    private $errorMessage = array();
    private $debugMessage = array();

    private static $instance = null;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new DB_Logger();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    public function setDebugMessage($str, $level = 1)
    {
        if ($this->debugLevel !== false && $this->debugLevel >= $level) {
            $this->debugMessage[] = $str;
        }
    }

    public function setErrorMessage($str)
    {
        $this->errorMessage[] = $str;
    }

    function getMessagesForJS()
    {
        $q = '"';
        $returnData = array();
        foreach ($this->errorMessage as $oneError) {
            $returnData[] = "INTERMediator.setErrorMessage({$q}"
                . str_replace("\n", " ", addslashes($oneError)) . "{$q});";
        }
        foreach ($this->debugMessage as $oneError) {
            $returnData[] = "INTERMediator.setDebugMessage({$q}"
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
