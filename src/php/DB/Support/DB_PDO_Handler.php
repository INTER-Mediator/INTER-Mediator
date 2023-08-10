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
use INTERMediator\DB\DBClass;

abstract class DB_PDO_Handler
{
    protected ?DBClass $dbClassObj = null;

    public static function generateHandler(DBClass $dbObj, string $dsn): ?DB_PDO_Handler
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

    public abstract function sqlSELECTCommand(): string;

    public abstract function sqlLimitCommand(string $param): string;

    public abstract function sqlOffsetCommand(string $param): string;

    public function sqlOrderByCommand(string $sortClause, string $limit, string $offset): string
    {
        return
            (strlen($sortClause) > 0 ? "ORDER BY {$sortClause} " : "") .
            (strlen($limit) > 0 ? "LIMIT {$limit} " : "") .
            (strlen($offset) > 0 ? "OFFSET {$offset} " : "");
    }

    public abstract function sqlDELETECommand(): string;

    public abstract function sqlUPDATECommand(): string;

    public abstract function sqlINSERTCommand(string $tableRef, string $setClause): string;

    public function sqlREPLACECommand(array $tableRef, string $setClause): string
    {
        return $this->sqlINSERTCommand($tableRef, $setClause);
    }

    public abstract function sqlSETClause(
        string $tableName, array $setColumnNames, string $keyField, array $setValues): string;

    protected function sqlSETClauseData(string $tableName, array $setColumnNames, array $setValues): array
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

    public function copyRecords(array  $tableInfo, string $queryClause, string $assocField,
                                string $assocValue, array $defaultValues): ?string
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
            $returnValue = $this->lastInsertIdAlt($seqObject, $tableName);
            //$returnValue = $this->dbClassObj->link->lastInsertId($seqObject);
        } catch (Exception $ex) {
            $this->dbClassObj->errorMessageStore($ex->getMessage());
            return null;
        }
        return $returnValue;
    }

    public function getNumericFields(string $tableName): array
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = [];
        $matches = [];
        foreach ($result as $row) {
            if (!is_null($row[$this->fieldNameForType])) {
                preg_match("/[a-z ]+/", strtolower($row[$this->fieldNameForType]), $matches);
                if (count($matches) > 0 && in_array($matches[0], $this->numericFieldTypes)) {
                    $fieldArray[] = $row[$this->fieldNameForField];
                }
            }
        }
        return $fieldArray;
    }

    public function getNullableFields(string $tableName): array
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = [];
        foreach ($result as $row) {
            if ($this->checkNullableField($row[$this->fieldNameForNullable])) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    public function getNullableNumericFields(string $tableName): array
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

    public function getTimeFields(string $tableName): array
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

    public function getDateFields(string $tableName): array
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = [];
        foreach ($result as $row) {
            if (in_array(strtolower($row[$this->fieldNameForType]), $this->dateFieldTypes)) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    public function getBooleanFields(string $tableName): array
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            return [];
        }
        $fieldArray = [];
        $matches = [];
        foreach ($result as $row) {
            if (!is_null($row[$this->fieldNameForType])) {
                preg_match("/[a-z ]+/", strtolower($row[$this->fieldNameForType]), $matches);
                if (in_array($matches[0], $this->booleanFieldTypes)) {
                    $fieldArray[] = $row[$this->fieldNameForField];
                }
            }
        }
        return $fieldArray;
    }

    public function getTypedFields(string $tableName): array
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $numericFields = [];
        $nullableFields = [];
        $timeFields = [];
        $dateFields = [];
        $booleanFields = [];
        $matches = [];
        foreach ($result as $row) {
            if (!is_null($row[$this->fieldNameForType])) {
                preg_match("/[a-z ]+/", strtolower($row[$this->fieldNameForType]), $matches);
                if (count($matches) > 0) {
                    $fieldType = $matches[0];
                    if ($this->checkNullableField($row[$this->fieldNameForNullable])) {
                        $nullableFields[] = $row[$this->fieldNameForField];
                    }
                    if (in_array($fieldType, $this->numericFieldTypes)) {
                        $numericFields[] = $row[$this->fieldNameForField];
                    }
                    if (in_array($fieldType, $this->booleanFieldTypes)) {
                        $booleanFields[] = $row[$this->fieldNameForField];
                    }
                    if (in_array($fieldType, $this->timeFieldTypes)) {
                        $timeFields[] = $row[$this->fieldNameForField];
                    }
                    if (in_array($fieldType, $this->dateFieldTypes)) {
                        $dateFields[] = $row[$this->fieldNameForField];
                    }
                }
            }
        }
        return [$nullableFields, $numericFields, $booleanFields, $timeFields, $dateFields];
    }

    public abstract function quotedEntityName(string $entityName): ?string;

    public abstract function optionalOperationInSetup(): void;

    public abstract function dateResetForNotNull(): string;

    protected abstract function checkNullableField(string $info): bool;

    protected function getTableInfo(string $tableName): array
    {
        if (!isset($this->tableInfo[$tableName])) {
            $sql = $this->getTalbeInfoSQL($tableName); // Returns SQL as like 'SHOW COLUMNS FROM $tableName'.
            $result = null;
            $this->dbClassObj->logger->setDebugMessage($sql);
            try {
                $result = $this->dbClassObj->link->query($sql);
            } catch (Exception $ex) { // In case of aggregation-select and aggregation-from keyword appear in context definition.
                //return []; // do nothing
            }
            $infoResult = [];
            if ($result) {
                foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $infoResult[] = $row;
                }
            }
            $this->tableInfo[$tableName] = $infoResult;
        } else {
            $infoResult = $this->tableInfo[$tableName];
        }
        return $infoResult;
    }

    protected abstract function getAutoIncrementField(string $tableName): ?string;

    protected abstract function getTalbeInfoSQL(string $tableName): string;

    protected abstract function getFieldListsForCopy(
        string $tableName, string $keyField, string $assocField, string $assocValue, array $defaultValues):array;

    public abstract function authSupportCanMigrateSHA256Hash(string $userTable, string $hashTable):?array;

    private function isTrue(?string $d):bool
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

    /*
     * As far as MySQL goes, in case of rising up the warning of violating constraints of foreign keys.
     * it happens any kind of warning but errorCode returns 00000 which means no error. There is no other way
     * to call SHOW WARNINGS. Other db engines don't do anything here
     */
    public function specialErrorHandling(string $sql):void
    {

    }

    public function getLastInsertId(string $seqObject):?string
    {
        if (!$this->dbClassObj->link) {
            return null;
        }
        return $this->dbClassObj->link->lastInsertId($seqObject);
    }

    public function lastInsertIdAlt(string $seqObject, string $tableName):?string
    {
        $incrementField = $this->getAutoIncrementField($tableName);
        $contextDef = $this->dbClassObj->dbSettings->getDataSourceTargetArray();
        $keyField = $contextDef['key'] ?? null;
        if ($incrementField && ($incrementField == $keyField || $incrementField == '_CANCEL_THE_INCR_FIELD_DETECT_')) {
            // Exists AUTO_INCREMENT field
            return $this->getLastInsertId($seqObject);
        } else {  // Not exist AUTO_INCREMENT field
            if (isset($keyField)) {
                $settingValues = $this->dbClassObj->dbSettings->getValuesWithFields(); // $dbClassObj is PDO class.
                return $settingValues[$keyField] ?? null;
            }
        }
        return null;
    }
}
