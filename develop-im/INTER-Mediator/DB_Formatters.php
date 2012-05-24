<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/23
 * Time: 17:57
 * To change this template use File | Settings | File Templates.
 */
class DB_Formatters
{
    var $formatter = null;
    /* Formatter processing */
    function setFormatter($fmt)
    {
        if (is_array($fmt)) {
            $this->formatter = array();
            foreach ($fmt as $oneItem) {
                if (!isset($this->formatter[$oneItem['field']])) {
                    $cvClassName = "DataConverter_{$oneItem['converter-class']}";
                    //    require_once("{$cvClassName}.php");
                    $parameter = isset($oneItem['parameter']) ? $oneItem['parameter'] : '';
                    $cvInstance = new $cvClassName($parameter);
                    $this->formatter[$oneItem['field']] = $cvInstance;
                }
            }
        }
    }

    function formatterFromDB($field, $data)
    {
        if (is_array($this->formatter)) {
            if (isset($this->formatter[$field])) {
                return $this->formatter[$field]->converterFromDBtoUser($data);
            }
        }
        return $data;
    }

    function formatterToDB($field, $data)
    {
        if (is_array($this->formatter)) {
            if (isset($this->formatter[$field])) {
                return $this->formatter[$field]->converterFromUserToDB($data);
            }
        }
        return $data;
    }

}
