<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2012 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/20
 * Time: 14:23
 * To change this template use File | Settings | File Templates.
 */
class DB_Logger
{
    /* Debug and Messages */
    private $debugLevel = false;
    private $errorMessage = array();
    private $debugMessage = array();

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
