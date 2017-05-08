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

require_once('DataConverter_NumberBase.php');

class DataConverter_Number extends DataConverter_NumberBase
{

    private $d = null;
    private $isZeroNoString = false;

    /**
     *
     * @param
     * @return
     */
    function __construct($digits = 0)
    {
        parent::__construct();
        if ($digits === true)    {
            $this->isZeroNoString = true;
            $this->d = 0;
        } else {
            $this->d = $digits;
        }
    }

    function converterFromDBtoUser($str)
    {
        if ($this->isZeroNoString && (double)$str == 0)  {
            return "";
        }
        return number_format((double)$str, (int)($this->d), $this->decimalMark, $this->thSepMark);
    }
}
