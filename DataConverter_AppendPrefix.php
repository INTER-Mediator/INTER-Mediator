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

class DataConverter_AppendPrefix
{

    private $appendStr;

    function __construct($str = '')
    {
        $this->appendStr = $str;
    }

    function converterFromDBtoUser($str)
    {
        return $this->appendStr . $str;
    }

    function converterFromUserToDB($str)
    {
        if (strpos($str, $this->appendStr) === 0) {
            return substr($str, strlen($this->appendStr));
        }
        return $str;
    }
}
