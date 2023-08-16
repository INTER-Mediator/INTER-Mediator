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
use INTERMediator\DB\PDO;

/**
 *
 */
abstract class DB_PDO_Handler
{
    /**
     * @var PDO|null
     */
    protected ?PDO $dbClassObj = null;

    /**
     * @var array
     */
    protected array $tableInfo = array();
    /**
     * @var string
     */
    protected string $fieldNameForField = '';
    /**
     * @var string
     */
    protected string $fieldNameForType = '';
    /**
     * @var string
     */
    protected string $fieldNameForNullable = '';
    /**
     * @var array
     */
    protected array $numericFieldTypes = [];
    /**
     * @var array
     */
    protected array $timeFieldTypes = [];
    /**
     * @var array
     */
    protected array $dateFieldTypes = [];
    /**
     * @var array
     */
    protected array $booleanFieldTypes = [];


    /**
     * @param PDO|null $dbObj
     * @param string $dsn
     * @return DB_PDO_Handler|null
     */
    public static function generateHandler(?PDO $dbObj, string $dsn): ?DB_PDO_Handler
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

    /**
     * @return string
     */
    public abstract function sqlSELECTCommand(): string;

    /**
     * @param string $param
     * @return string
     */
    public abstract function sqlLimitCommand(string $param): string;

    /**
     * @param string $param
     * @return string
     */
    public abstract function sqlOffsetCommand(string $param): string;

    /**
     * @param string $sortClause
     * @param string $limit
     * @param string $offset
     * @return string
     */
    public function sqlOrderByCommand(string $sortClause, string $limit, string $offset): string
    {
        return
            (strlen($sortClause) > 0 ? "ORDER BY {$sortClause} " : "") .
            (strlen($limit) > 0 ? "LIMIT {$limit} " : "") .
            (strlen($offset) > 0 ? "OFFSET {$offset} " : "");
    }

    /**
     * @return string
     */
    public abstract function sqlDELETECommand(): string;

    /**
     * @return string
     */
    public abstract function sqlUPDATECommand(): string;

    /**
     * @param string $tableRef
     * @param string $setClause
     * @return string
     */
    public abstract function sqlINSERTCommand(string $tableRef, string $setClause): string;

    /**
     * @param string $tableRef
     * @param string $setClause
     * @return string
     */
    public function sqlREPLACECommand(string $tableRef, string $setClause): string
    {
        return $this->sqlINSERTCommand($tableRef, $setClause);
    }

    /**
     * @param string $tableName
     * @param array $setColumnNames
     * @param string $keyField
     * @param array $setValues
     * @return string
     */
    public abstract function sqlSETClause(
        string $tableName, array $setColumnNames, string $keyField, array $setValues): string;

    /**
     * @param string $tableName
     * @param array $setColumnNames
     * @param array $setValues
     * @return array[]
     * @throws Exception
     */
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

    /**
     * @param array|null $tableInfo
     * @param string|null $queryClause
     * @param string|null $assocField
     * @param string|null $assocValue
     * @param array|null $defaultValues
     * @return string|null
     */
    public function copyRecords(?array  $tableInfo, ?string $queryClause, ?string $assocField,
                                ?string $assocValue, ?array $defaultValues): ?string
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

    /**
     * @param string $tableName
     * @return array
     * @throws Exception
     */
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

    /**
     * @param string $tableName
     * @return array
     * @throws Exception
     */
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

    /**
     * @param string $tableName
     * @return array
     * @throws Exception
     */
    public function getNullableNumericFields(string $tableName): array
    {
        try {
            $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $nullableFields = $this->getNullableFields($tableName);
        $numericFields = $this->getNumericFields($tableName);
        return array_intersect($nullableFields, $numericFields);
    }

    /**
     * @param string $tableName
     * @return array
     * @throws Exception
     */
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

    /**
     * @param string $tableName
     * @return array
     * @throws Exception
     */
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

    /**
     * @param string $tableName
     * @return array
     */
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

    /**
     * @param string $tableName
     * @return array[]
     * @throws Exception
     */
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

    /**
     * @param string $entityName
     * @return string|null
     */
    public abstract function quotedEntityName(string $entityName): ?string;

    /**
     * @return void
     */
    public abstract function optionalOperationInSetup(): void;

    /**
     * @return string
     */
    public abstract function dateResetForNotNull(): string;

    /**
     * @param string $info
     * @return bool
     */
    protected abstract function checkNullableField(string $info): bool;

    /**
     * @param string $tableName
     * @return array
     */
    protected function getTableInfo(string $tableName): array
    {
        if (!isset($this->tableInfo[$tableName])) {
            $sql = $this->getTableInfoSQL($tableName); // Returns SQL as like 'SHOW COLUMNS FROM $tableName'.
            $result = null;
            $this->dbClassObj->logger->setDebugMessage($sql);
            try {
                $result = $this->dbClassObj->link->query($sql);
            } catch (Exception $ex) { // In case of aggregation-select and aggregation-from keyword appear in context definition.
                //return []; // do nothing
            }
            $infoResult = [];
            if ($result) {
                foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $infoResult[] = $row;
                }
            }
            $this->tableInfo[$tableName] = $infoResult;
        } else {
            $infoResult = $this->tableInfo[$tableName];
        }
        return $infoResult;
    }

    /**
     * @param string $tableName
     * @return string|null
     */
    protected abstract function getAutoIncrementField(string $tableName): ?string;

    /**
     * @param string $tableName
     * @return string
     */
    protected abstract function getTableInfoSQL(string $tableName): string;

    /**
     * @param string $tableName
     * @param string $keyField
     * @param string $assocField
     * @param string $assocValue
     * @param array $defaultValues
     * @return array
     */
    protected abstract function getFieldListsForCopy(
        string $tableName, string $keyField, string $assocField, string $assocValue, array $defaultValues): array;

    /**
     * @param string $userTable
     * @param string $hashTable
     * @return array|null
     */
    public abstract function authSupportCanMigrateSHA256Hash(string $userTable, string $hashTable): ?array;

    /**
     * @param string|null $d
     * @return bool
     */
    private function isTrue(?string $d): bool
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
    /**
     * @param string $sql
     * @return void
     */
    public function specialErrorHandling(string $sql): void
    {

    }

    /**
     * @param string $seqObject
     * @return string|null
     */
    public function getLastInsertId(string $seqObject): ?string
    {
        if (!$this->dbClassObj->link) {
            return null;
        }
        return $this->dbClassObj->link->lastInsertId($seqObject);
    }

    /**
     * @param string $seqObject
     * @param string $tableName
     * @return string|null
     */
    public function lastInsertIdAlt(string $seqObject, string $tableName): ?string
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
