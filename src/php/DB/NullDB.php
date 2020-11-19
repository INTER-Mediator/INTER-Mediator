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

class NullDB extends UseSharedObjects implements DBClass_Interface
{

    public function readFromDB()
    {
        return null;
    }

    public function countQueryResult()
    {
        return 0;
    }

    public function getTotalCount()
    {
        return 0;
    }

    public function updateDB($bypassAuth)
    {
        return null;
    }

    public function createInDB()
    {
        return null;
    }

    public function deleteFromDB()
    {
        return null;
    }

    public function getFieldInfo($dataSourceName)
    {
        return null;
    }

    public function setupConnection()
    {
        return true;
    }

    public function requireUpdatedRecord($value)
    {
        return null;
    }

    public function updatedRecord()
    {
        return null;
    }

    public function softDeleteActivate($field, $value)
    {
        return null;
    }

    public function copyInDB()
    {
        return false;
    }

    public function setupHandlers($dsn = false)
    {

    }

    public function normalizedCondition($condition)
    {

    }

    public function setUpdatedRecord($field, $value, $index = 0)
    {

    }

    public function queryForTest($table, $conditions = null)
    {

    }

    public function deleteForTest($table, $conditions = null)
    {

    }
}
