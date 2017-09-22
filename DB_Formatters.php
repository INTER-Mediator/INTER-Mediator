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

class DB_Formatters
{
    private $formatter = null;
    /* Formatter processing */
    public function setFormatter($fmt)
    {
        if (is_array($fmt)) {
            $this->formatter = array();
            foreach ($fmt as $oneItem) {
                if (!isset($this->formatter[$oneItem['field']])) {
                    $cvClassName = "DataConverter_{$oneItem['converter-class']}";
                    //    require_once("{$cvClassName}.php");
                    $this->formatter[$oneItem['field']]
                        = new $cvClassName(isset($oneItem['parameter']) ? $oneItem['parameter'] : '');
                }
            }
        }
    }

    public function formatterFromDB($field, $data)
    {
        if (is_array($this->formatter)) {
            if (isset($this->formatter[$field])) {
                return $this->formatter[$field]->converterFromDBtoUser($data);
            }
        }
        return $data;
    }

    public function formatterToDB($field, $data)
    {
        if (is_array($this->formatter)) {
            if (isset($this->formatter[$field])) {
                return $this->formatter[$field]->converterFromUserToDB($data);
            }
        }
        return $data;
    }

}
