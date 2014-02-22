<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/23
 * Time: 17:57
 * To change this template use File | Settings | File Templates.
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
                    if (isset($oneItem['parameter']) && is_array($oneItem['parameter'])) {
                        $this->formatter[$oneItem['field']]
                            = new $cvClassName(isset($oneItem['parameter'][0]) ? $oneItem['parameter'][0] : '',
                                isset($oneItem['parameter'][1]) ? $oneItem['parameter'][1] : '');
                    } else {
                        $this->formatter[$oneItem['field']]
                            = new $cvClassName(isset($oneItem['parameter']) ? $oneItem['parameter'] : '');
                    }
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
