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

namespace INTERMediator\DB\Support;

use Exception;
use PDO;

class DB_PDO_PostgreSQL_Handler extends DB_PDO_Handler
{
    public function sqlSELECTCommand()
    {
        return "SELECT ";
    }

    public function sqlLimitCommand($param)
    {
        return "LIMIT {$param}";
    }

    public function sqlOffsetCommand($param)
    {
        return "OFFSET {$param}";
    }

    public function sqlDELETECommand()
    {
        return "DELETE FROM ";
    }

    public function sqlUPDATECommand()
    {
        return "UPDATE ";
    }

    public function sqlINSERTCommand($tableRef, $setClause)
    {
        return "INSERT INTO {$tableRef} {$setClause}";
    }

    public function sqlSETClause($setColumnNames, $keyField, $setValues)
    {
        $setNames = array_map(function ($value) {
            return $this->quotedEntityName($value);
        }, $setColumnNames);
        return (count($setColumnNames) == 0) ? "DEFAULT VALUES" :
            '(' . implode(',', $setNames) . ') VALUES(' . implode(',', $setValues) . ')';
    }

    public function getNullableNumericFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldNameForNullable = 'is_nullable';
        $fieldArray = array();
        $numericFieldTypes = array('smallint', 'integer', 'bigint', 'decimal', 'numeric',
            'real', 'double precision', 'smallserial', 'serial', 'bigserial', 'money',
            'timestamp', 'date', 'time', 'interval',);
        $matches = array();
        foreach ($result as $row) {
            preg_match("/[a-z ]+/", strtolower($row[$this->fieldNameForType]), $matches);
            if ($row[$fieldNameForNullable] && in_array($matches[0], $numericFieldTypes)) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    public function getTimeFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            return [];
        }
        $timeFieldTypes = ['datetime', 'time', 'timestamp'];
        $fieldArray = [];
        $matches = [];
        foreach ($result as $row) {
            preg_match("/[a-z]+/", strtolower($row[$this->fieldNameForType]), $matches);
            if (in_array($matches[0], $timeFieldTypes)) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    public function getBooleanFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            return [];
        }
        $timeFieldTypes = ['boolean'];
        $fieldArray = [];
        $matches = [];
        foreach ($result as $row) {
            preg_match("/[a-z]+/", strtolower($row[$this->fieldNameForType]), $matches);
            if (in_array($matches[0], $timeFieldTypes)) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    private $tableInfo = array();
    private $fieldNameForField = 'column_name';
    private $fieldNameForType = 'data_type';

    protected function getTableInfo($tableName)
    {
        if (!isset($this->tableInfo[$tableName])) {
            if (strpos($tableName, ".") !== false) {
                $tName = substr($tableName, strpos($tableName, ".") + 1);
                $schemaName = substr($tableName, 0, strpos($tableName, "."));
                $sql = "SELECT column_name, column_default, is_nullable, data_type, character_maximum_length, "
                    . "numeric_precision, numeric_scale FROM information_schema.columns "
                    . "WHERE table_schema=" . $this->dbClassObj->link->quote($schemaName)
                    . " AND table_name=" . $this->dbClassObj->link->quote($tName);
            } else {
                $sql = "SELECT column_name, column_default, is_nullable, data_type, character_maximum_length, "
                    . "numeric_precision, numeric_scale FROM information_schema.columns "
                    . "WHERE table_name=" . $this->dbClassObj->link->quote($tableName);
            }
            $this->dbClassObj->logger->setDebugMessage($sql);
            $result = $this->dbClassObj->link->query($sql);
            if (!$result) {
                throw new Exception('INSERT Error:' . $sql);
            }
            $infoResult = [];
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $infoResult[] = $row;
            }
            $this->tableInfo[$tableName] = $infoResult;
        } else {
            $infoResult = $this->tableInfo[$tableName];
        }
        return $infoResult;
    }

    /*
# select table_catalog,table_schema,table_name,column_name,column_default from information_schema.columns where table_name='person';
table_catalog | table_schema | table_name | column_name |                column_default
---------------+--------------+------------+-------------+----------------------------------------------
test_db       | im_sample    | person     | id          | nextval('im_sample.person_id_seq'::regclass)
test_db       | im_sample    | person     | name        |
test_db       | im_sample    | person     | address     |
test_db       | im_sample    | person     | mail        |
test_db       | im_sample    | person     | category    |
test_db       | im_sample    | person     | checking    |
test_db       | im_sample    | person     | location    |
test_db       | im_sample    | person     | memo        |
 */


    protected function getFieldListsForCopy($tableName, $keyField, $assocField, $assocValue, $defaultValues)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = array();
        $listArray = array();
        foreach ($result as $row) {
            if ($keyField === $row['column_name'] || !is_null($row['column_default'])) {

            } else if ($assocField === $row['column_name']) {
                $fieldArray[] = $this->quotedEntityName($row['column_name']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else if (isset($defaultValues[$row['column_name']])) {
                $fieldArray[] = $this->quotedEntityName($row['column_name']);
                $listArray[] = $this->dbClassObj->link->quote($defaultValues[$row['column_name']]);
            } else {
                $fieldArray[] = $this->quotedEntityName($row['column_name']);
                $listArray[] = $this->quotedEntityName($row['column_name']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    public function quotedEntityName($entityName)
    {
        $q = '"';
        if (strpos($entityName, ".") !== false) {
            $components = explode(".", $entityName);
            $quotedName = array();
            foreach ($components as $item) {
                $quotedName[] = $q . str_replace($q, $q . $q, $item) . $q;
            }
            return implode(".", $quotedName);
        }
        return $q . str_replace($q, $q . $q, $entityName) . $q;

    }

    public function optionalOperationInSetup()
    {
    }


    public function authSupportCanMigrateSHA256Hash($userTable, $hashTable)  // authuser, issuedhash
    {
        $checkFieldDefinition = function ($type, $len, $min) {
            $fDef = strtolower($type);
            if ($fDef != 'text' && $fDef == 'character varying') {
                if ($len < $min) {
                    return false;
                }
            }
            return true;
        };

        $infoAuthUser = $this->getTableInfo($userTable);
        $infoIssuedHash = $this->getTableInfo($hashTable);
        $returnValue = [];
        if ($infoAuthUser) {
            foreach ($infoAuthUser as $fieldInfo) {
                if (isset($fieldInfo['column_name'])
                    && $fieldInfo['column_name'] == 'hashedpasswd'
                    && !$checkFieldDefinition($fieldInfo['data_type'], $fieldInfo['character_maximum_length'], 72)) {
                    $returnValue[] = "The hashedpassword field of the authuser table has to be longer than 72 characters.";
                }
            }
        }
        if ($infoIssuedHash) {
            foreach ($infoIssuedHash as $fieldInfo) {
                if (isset($fieldInfo['column_name'])
                    && $fieldInfo['column_name'] == 'clienthost'
                    && !$checkFieldDefinition($fieldInfo['data_type'], $fieldInfo['character_maximum_length'], 64)) {
                    $returnValue[] = "The clienthost field of the issuedhash table has to be longer than 64 characters.";
                }
                if (isset($fieldInfo['column_name'])
                    && $fieldInfo['column_name'] == 'hash'
                    && !$checkFieldDefinition($fieldInfo['data_type'], $fieldInfo['character_maximum_length'], 64)) {
                    $returnValue[] = "The hash field of the issuedhash table has to be longer than 64 characters.";
                }
            }
        }
        return $returnValue;
    }
}
