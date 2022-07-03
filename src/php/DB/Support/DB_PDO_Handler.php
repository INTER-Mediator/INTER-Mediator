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

abstract class DB_PDO_Handler
{
    protected $dbClassObj = null;

    public static function generateHandler($dbObj, $dsn)
    {
        if (is_null($dbObj)) {
            return null;
        }
        if (strpos($dsn, 'mysql:') === 0) {
            $instance = new DB_PDO_MySQL_Handler();
            $instance->dbClassObj = $dbObj;
            return $instance;
        } else if (strpos($dsn, 'pgsql:') === 0) {
            $instance = new DB_PDO_PostgreSQL_Handler();
            $instance->dbClassObj = $dbObj;
            return $instance;
        } else if (strpos($dsn, 'sqlite:') === 0) {
            $instance = new DB_PDO_SQLite_Handler();
            $instance->dbClassObj = $dbObj;
            return $instance;
        } else if (strpos($dsn, 'sqlsrv:') === 0) {
            $instance = new DB_PDO_SQLServer_Handler();
            $instance->dbClassObj = $dbObj;
            return $instance;
        }
        return null;
    }

    public abstract function sqlSELECTCommand();

    public abstract function sqlLimitCommand($param);

    public abstract function sqlOffsetCommand($param);

    public function sqlOrderByCommand($sortClause, $limit, $offset)
    {
        return
            (strlen($sortClause) > 0 ? "ORDER BY {$sortClause} " : "") .
            (strlen($limit) > 0 ? "LIMIT {$limit} " : "") .
            (strlen($offset) > 0 ? "OFFSET {$offset} " : "");
    }

    public abstract function sqlDELETECommand();

    public abstract function sqlUPDATECommand();

    public abstract function sqlINSERTCommand($tableRef, $setClause);

    public function sqlREPLACECommand($tableRef, $setClause)
    {
        return $this->sqlINSERTCommand($tableRef, $setClause);
    }

    public abstract function sqlSETClause($tableName, $setColumnNames, $keyField, $setValues);

    protected function sqlSETClauseData($tableName, $setColumnNames, $setValues)
    {
        $nullableFields = $this->getNullableFields($tableName);
        $numericFields = $this->getNumericFields($tableName);
        $boolFields = $this->getBooleanFields($tableName);
        $setNames = [];
        $setValuesConv = [];
        $count = 0;
        foreach ($setColumnNames as $fName) {
            $setNames[] = $this->quotedEntityName($fName);
            $value = $setValues[$count];
            if (is_null($value)) {
                $setValuesConv[] = in_array($fName, $nullableFields)
                    ? "NULL" : (in_array($fName, $numericFields) ? "0" : $this->dbClassObj->link->quote(''));
            } else if (in_array($fName, $boolFields)) {
                $setValuesConv[] = $this->isTrue($value) ? "TRUE" : "FALSE";
            } else {
                $setValuesConv[] = (in_array($fName, $numericFields)
                    ? floatval($value) : $this->dbClassObj->link->quote($value));
            }
            $count += 1;
        }
        return [$setNames, $setValuesConv];
    }

    public function copyRecords($tableInfo, $queryClause, $assocField, $assocValue, $defaultValues)
    {
        $returnValue = null;
        $tableName = $tableInfo["table"] ?? $tableInfo["name"];
        try {
            list($fieldList, $listList) = $this->getFieldListsForCopy(
                $tableName, $tableInfo['key'], $assocField, $assocValue, $defaultValues);
            $tableRef = "{$tableName} ({$fieldList})";
            $setClause = "{$this->sqlSELECTCommand()}{$listList} FROM {$tableName} WHERE {$queryClause}";
            $sql = $this->sqlINSERTCommand($tableRef, $setClause);
            $this->dbClassObj->logger->setDebugMessage($sql);
            $result = $this->dbClassObj->link->query($sql);
            if (!$result) {
                throw new Exception('INSERT Error:' . $sql);
            }
            $keyField = $tableInfo['key'] ?? 'id';
            $seqObject = $tableInfo['sequence'] ?? "{$tableName}_{$keyField}_seq";
            $returnValue = $this->dbClassObj->link->lastInsertId($seqObject);
        } catch (Exception $ex) {
            $this->dbClassObj->errorMessageStore($ex->getMessage());
            return false;
        }
        return $returnValue;
    }

    public function getNumericFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = [];
        $matches = [];
        foreach ($result as $row) {
            if(!is_null($row[$this->fieldNameForType])) {
                preg_match("/[a-z ]+/", strtolower($row[$this->fieldNameForType]), $matches);
                if (count($matches) > 0 && in_array($matches[0], $this->numericFieldTypes)) {
                    $fieldArray[] = $row[$this->fieldNameForField];
                }
            }
        }
        return $fieldArray;
    }

    public function getNullableFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = [];
        foreach ($result as $row) {
            if (!$row[$this->fieldNameForNullable]) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    public function getNullableNumericFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $nullableFields = $this->getNullableFields($tableName);
        $numericFields = $this->getNumericFields($tableName);
        return array_intersect($nullableFields, $numericFields);
    }

    public function getTimeFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = [];
        foreach ($result as $row) {
            if (in_array(strtolower($row[$this->fieldNameForType]), $this->timeFieldTypes)) {
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
        $fieldArray = [];
        $matches = [];
        foreach ($result as $row) {
            if(!is_null($row[$this->fieldNameForType])) {
                preg_match("/[a-z ]+/", strtolower($row[$this->fieldNameForType]), $matches);
                if (in_array($matches[0], $this->booleanFieldTypes)) {
                    $fieldArray[] = $row[$this->fieldNameForField];
                }
            }
        }
        return $fieldArray;
    }

    public abstract function quotedEntityName($entityName);

    public abstract function optionalOperationInSetup();

    protected function getTableInfo($tableName)
    {
        if (!isset($this->tableInfo[$tableName])) {
            $sql = $this->getTalbeInfoSQL($tableName);
            $this->dbClassObj->logger->setDebugMessage($sql);
            $result = $this->dbClassObj->link->query($sql);
            if (!$result) {
                throw new Exception('Inspection SQL Error:' . $sql);
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

    protected abstract function getTalbeInfoSQL($tableName);

    protected abstract function getFieldListsForCopy(
        $tableName, $keyField, $assocField, $assocValue, $defaultValues);

    public abstract function authSupportCanMigrateSHA256Hash($userTable, $hashTable);

    private function isTrue($d)
    {
        if (is_null($d)) {
            return false;
        }
        if (strtolower($d) == 'true' || strtolower($d) == 't') {
            return true;
        } else if (intval($d) > 0) {
            return true;
        }
        return false;
    }

}
