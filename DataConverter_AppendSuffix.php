<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
*
*   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
*   INTER-Mediator is supplied under MIT License.
*/

class DataConverter_AppendSuffix
{

    private $appendStr;

    function __construct($str = '')
    {
        $this->appendStr = $str;
    }

    function converterFromDBtoUser($str)
    {
        return $str . $this->appendStr;
    }

    function converterFromUserToDB($str)
    {
        if (strrpos($str, $this->appendStr) === (strlen($str) - strlen($this->appendStr))) {
            return substr($str, 0, strlen($str) - strlen($this->appendStr));
        }
        return $str;
    }
}

?>
