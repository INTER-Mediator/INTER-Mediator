<?php
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
    var $debugLevel = false;
    var $errorMessage = array();
    var $debugMessage = array();

    function setDebugMessage($str, $level = 1)
    {
        if ($this->debugLevel !== false && $this->debugLevel >= $level) {
            $this->debugMessage[] = $str;
        }
    }

    function setErrorMessage($str)
    {
        $this->errorMessage[] = $str;
    }

    function getMessagesForJS()
    {
        $q = '"';
        $returnData = array();
        foreach ($this->errorMessage as $oneError) {
            $returnData[] = "INTERMediator.errorMessages.push({$q}"
                . str_replace("\n", " ", addslashes($oneError)) . "{$q});";
        }
        foreach ($this->debugMessage as $oneError) {
            $returnData[] = "INTERMediator.debugMessages.push({$q}"
                . str_replace("\n", " ", addslashes($oneError)) . "{$q});";
        }
        return $returnData;
    }

    function setDebugMode($val)
    {
        if ($val === true) {
            $this->debugLevel = 1;
        } else {
            $this->debugLevel = $val;
        }
    }
}
