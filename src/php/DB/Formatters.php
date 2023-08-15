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

/**
 *
 */
class Formatters
{
    /**
     * @var array
     */
    private array $formatter = [];
    /* Formatter processing */
    /**
     * @param $fmt
     * @return void
     */
    public function setFormatter($fmt)
    {
        if (is_array($fmt)) {
            $this->formatter = array();
            foreach ($fmt as $oneItem) {
                if (!isset($this->formatter[$oneItem['field']])) {
                    $cvClassName = "INTERMediator\\Data_Converter\\" . $oneItem['converter-class'];
                    $this->formatter[$oneItem['field']]
                        = new $cvClassName($oneItem['parameter'] ?? '');
                }
            }
        }
    }

    /**
     * @param $field
     * @param $data
     * @return mixed
     */
    public function formatterFromDB($field, $data)
    {
        if (isset($this->formatter[$field])) {
            return $this->formatter[$field]->converterFromDBtoUser($data);
        }
        return $data;
    }

    /**
     * @param $field
     * @param $data
     * @return mixed
     */
    public function formatterToDB($field, $data)
    {
        if (isset($this->formatter[$field])) {
            return $this->formatter[$field]->converterFromUserToDB($data);
        }
        return $data;
    }

}
