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
use INTERMediator\DB\Logger;
use INTERMediator\DB\PDO;

/**
 * Abstract base class for PDO database handlers.
 * Provides common logic and abstract methods for SQL command generation and schema inspection.
 * Subclasses implement database-specific logic for MySQL, PostgreSQL, SQLite, and SQL Server.
 */
abstract class DB_PDO_Handler
{
    /**
     * @var PDO|null Reference to the PDO database object.
     */
    protected ?PDO $dbClassObj = null;

    /**
     * @var array Table information for schema inspection.
     */
    protected array $tableInfo = array();
    /**
     * @var string Field name for column field.
     */
    public string $fieldNameForField = '';
    /**
     * @var string Field name for column type.
     */
    protected string $fieldNameForType = '';
    /**
     * @var string Field name for nullable property.
     */
    protected string $fieldNameForNullable = '';
    /**
     * @var array List of numeric field types.
     */
    protected array $numericFieldTypes = [];
    /**
     * @var array List of time field types.
     */
    protected array $timeFieldTypes = [];
    /**
     * @var array List of date field types.
     */
    protected array $dateFieldTypes = [];
    /**
     * @var array List of boolean field types.
     */
    protected array $booleanFieldTypes = [];

    /**
     * Generates a handler instance based on the DSN string.
     *
     * @param PDO|null $dbObj PDO database object.
     * @param string $dsn Data Source Name.
     * @return DB_PDO_Handler|null Handler instance or null if DSN is unsupported.
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
     * Returns the SQL SELECT command for the database.
     *
     * @return string SQL SELECT command.
     */
    public abstract function sqlSELECTCommand(): string;

    /**
     * Returns the SQL LIMIT command for the database.
     *
     * @param string $param Limit parameter.
     * @return string SQL LIMIT command.
     */
    public abstract function sqlLimitCommand(string $param): string;

    /**
     * Returns the SQL OFFSET command for the database.
     *
     * @param string $param Offset parameter.
     * @return string SQL OFFSET command.
     */
    public abstract function sqlOffsetCommand(string $param): string;

    /**
     * Returns the SQL ORDER BY command for the database.
     *
     * @param string $sortClause Sort clause.
     * @param string $limit Limit parameter.
     * @param string $offset Offset parameter.
     * @return string SQL ORDER BY command.
     */
    public function sqlOrderByCommand(string $sortClause, string $limit, string $offset): string
    {
        return
            (strlen($sortClause) > 0 ? "ORDER BY {$sortClause} " : "") .
            (strlen($limit) > 0 ? "LIMIT {$limit} " : "") .
            (strlen($offset) > 0 ? "OFFSET {$offset} " : "");
    }

    /**
     * Returns the SQL DELETE command for the database.
     *
     * @return string SQL DELETE command.
     */
    public abstract function sqlDELETECommand(): string;

    /**
     * Returns the SQL UPDATE command for the database.
     *
     * @return string SQL UPDATE command.
     */
    public abstract function sqlUPDATECommand(): string;

    /**
     * Returns the SQL INSERT command for the database.
     *
     * @param string $tableRef Table reference.
     * @param string $setClause Set clause.
     * @return string SQL INSERT command.
     */
    public abstract function sqlINSERTCommand(string $tableRef, string $setClause): string;

    /**
     * Returns the SQL LIST DATABASE command for the database.
     *
     * @return string SQL LIST DATABASE command.
     */
    public abstract function sqlLISTDATABASECommand(): string;

    /**
     * Returns the field name for database name in the result of database list.
     *
     * @return string Field name.
     */
    public abstract function sqlLISTDATABASEColumn(): string;

    /**
     * Returns the SQL REPLACE command for the database.
     *
     * @param string $tableRef Table reference.
     * @param string $setClause Set clause.
     * @return string SQL REPLACE command.
     */
    public function sqlREPLACECommand(string $tableRef, string $setClause): string
    {
        return $this->sqlINSERTCommand($tableRef, $setClause);
    }

    /**
     * @var bool Flag indicating whether it's the first column.
     */
    private bool $isFirstColumn;

    /**
     * Returns the SQL CREATE TABLE command start for the database.
     *
     * @param string $table Table name.
     * @return string SQL CREATE TABLE command start.
     */
    public function sqlCREATETABLECommandStart(string $table): string
    {
        $this->isFirstColumn = true;
        return "CREATE TABLE {$this->quotedEntityName($table)} (";
    }

    /**
     * Returns the SQL field definition command for the database.
     *
     * @param string $field Field name.
     * @param string $type Field type.
     * @return string SQL field definition command.
     */
    public function sqlFieldDefinitionCommand(string $field, string $type): string
    {
        $separator = $this->isFirstColumn ? '' : ',';
        $this->isFirstColumn = false;
        return "{$separator}\n{$this->quotedEntityName($field)} {$type}";
    }

    /**
     * Returns the SQL CREATE TABLE command end for the database.
     *
     * @return string SQL CREATE TABLE command end.
     */
    public function sqlCREATETABLECommandEnd(): string
    {
        return "\n) CHARACTER SET utf8mb4,\nCOLLATE utf8mb4_unicode_ci\nENGINE = InnoDB;\n";
    }

    /**
     * Returns the SQL ADD COLUMN command for the database.
     *
     * @param string $table Table name.
     * @param string $field Field name.
     * @param string $type Field type.
     * @return string SQL ADD COLUMN command.
     */
    public function sqlADDCOLUMNCommand(string $table, string $field, string $type): string
    {
        return "ALTER TABLE {$this->quotedEntityName($table)} ADD COLUMN({$this->quotedEntityName($field)} {$type});\n";
    }

    /**
     * Returns the SQL CREATE INDEX command for the database.
     *
     * @param string $table Table name.
     * @param string $field Field name.
     * @param int $length Index length.
     * @return string SQL CREATE INDEX command.
     */
    public function sqlCREATEINDEXCommand(string $table, string $field, int $length = 0): string
    {
        $indexName = $this->quotedEntityName("{$table}_{$field}");
        $lengthDesc = ($length > 0) ? "({$length})" : '';
        return "CREATE INDEX  {$indexName} ON {$this->quotedEntityName($table)} ({$this->quotedEntityName($field)}{$lengthDesc});\n";
    }

    /**
     * Returns the SQL CREATE DATABASE command for the database.
     *
     * @param string $dbName Database name.
     * @return string SQL CREATE DATABASE command.
     */
    public function sqlCREATEDATABASECommand(string $dbName): string
    {
        return "CREATE DATABASE {$this->quotedEntityName($dbName)};\n";
    }

    /**
     * Returns the SQL SELECT DATABASE command for the database.
     *
     * @param string $dbName Database name.
     * @return string SQL SELECT DATABASE command.
     */
    public function sqlSELECTDATABASECommand(string $dbName): string
    {
        return "USE {$this->quotedEntityName($dbName)};\n";
    }

    /**
     * Returns the SQL CREATE USER command for the database.
     *
     * @param string $dbName Database name.
     * @param string $userEntity User entity.
     * @param string $password Password.
     * @return string SQL CREATE USER command.
     */
    public abstract function sqlCREATEUSERCommand(string $dbName, string $userEntity, string $password): string;

    /**
     * Quotes data for SQL queries.
     *
     * @param string $data Data to quote.
     * @param string $separator Separator.
     * @return string|null Quoted data or null if data is null.
     */
    public function quotedData(string $data, string $separator = ''): ?string
    {
        $pos = strpos($data, $separator);
        if ($pos !== false) {
            return "{$this->dbClassObj->link->quote(substr($data, 0, $pos))}{$separator}{$this->dbClassObj->link->quote(substr($data, $pos + 1))}";
        }
        return $this->dbClassObj->link->quote($data);
    }

    /**
     * Returns the SQL SET clause for the database.
     *
     * @param string $tableName Table name.
     * @param array $setColumnNames Set column names.
     * @param string $keyField Key field.
     * @param array $setValues Set values.
     * @return string SQL SET clause.
     */
    public abstract function sqlSETClause(
        string $tableName, array $setColumnNames, string $keyField, array $setValues): string;

    /**
     * Returns the SQL SET clause data for the database.
     *
     * @param string $tableName Table name.
     * @param array $setColumnNames Set column names.
     * @param array $setValues Set values.
     * @return array[] SQL SET clause data.
     * @throws Exception If an error occurs.
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
     * Copies records from one table to another.
     *
     * @param array|null $tableInfo Table information.
     * @param string|null $queryClause Query clause.
     * @param string|null $assocField Association field.
     * @param string|null $assocValue Association value.
     * @param array|null $defaultValues Default values.
     * @return string|null Last insert ID or null if an error occurs.
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
     * Returns the numeric fields for a table.
     *
     * @param string|null $tableName Table name.
     * @return array Numeric fields.
     * @throws Exception If an error occurs.
     */
    public function getNumericFields(?string $tableName): array
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
     * Returns the nullable fields for a table.
     *
     * @param string $tableName Table name.
     * @return array Nullable fields.
     * @throws Exception If an error occurs.
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
     * Returns the nullable numeric fields for a table.
     *
     * @param string $tableName Table name.
     * @return array Nullable numeric fields.
     * @throws Exception If an error occurs.
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
     * Returns the time fields for a table.
     *
     * @param string $tableName Table name.
     * @return array Time fields.
     * @throws Exception If an error occurs.
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
     * Returns the date fields for a table.
     *
     * @param string $tableName Table name.
     * @return array Date fields.
     * @throws Exception If an error occurs.
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
     * Returns the boolean fields for a table.
     *
     * @param string|null $tableName Table name.
     * @return array Boolean fields.
     */
    public function getBooleanFields(?string $tableName): array
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
     * Returns the typed fields for a table.
     *
     * @param string $tableName Table name.
     * @return array[] Typed fields.
     * @throws Exception If an error occurs.
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
     * Quotes an entity name for SQL queries.
     *
     * @param string $entityName Entity name.
     * @return string|null Quoted entity name or null if entity name is null.
     */
    public abstract function quotedEntityName(string $entityName): ?string;

    /**
     * Performs optional operations in setup.
     *
     * @return void
     */
    public abstract function optionalOperationInSetup(): void;

    /**
     * Returns the date reset for not null.
     *
     * @return string Date reset.
     */
    public abstract function dateResetForNotNull(): string;

    /**
     * Checks if a field is nullable.
     *
     * @param string $info Field information.
     * @return bool Whether the field is nullable.
     */
    protected abstract function checkNullableField(string $info): bool;

    /**
     * Returns the table information for a table.
     *
     * @param string|null $tableName Table name.
     * @return array Table information.
     */
    public function getTableInfo(?string $tableName): array
    {
        if (is_null($tableName)){
            return [];
        }
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
     * Returns the field list for a table.
     *
     * @param string $tableName Table name.
     * @return array Field list.
     */
    public function getFieldList(string $tableName): array
    {
        $result = [];
        $tableInfo = $this->getTableInfo($tableName);
        foreach ($tableInfo as $fieldInfo) {
            $result[] = $fieldInfo[$this->fieldNameForField];
        }
        return $result;
    }

    /**
     * Returns the auto increment field for a table.
     *
     * @param string $tableName Table name.
     * @return string|null Auto increment field or null if not found.
     */
    protected abstract function getAutoIncrementField(string $tableName): ?string;

    /**
     * Returns the SQL to get table information for a table.
     *
     * @param string $tableName Table name.
     * @return string SQL to get table information.
     */
    public abstract function getTableInfoSQL(string $tableName): string;

    /**
     * Returns the field lists for copying records.
     *
     * @param string $tableName Table name.
     * @param string $keyField Key field.
     * @param string $assocField Association field.
     * @param string $assocValue Association value.
     * @param array $defaultValues Default values.
     * @return array Field lists.
     */
    protected abstract function getFieldListsForCopy(
        string $tableName, string $keyField, string $assocField, string $assocValue, array $defaultValues): array;

    /**
     * Returns whether the authentication support can migrate SHA256 hash.
     *
     * @param string $userTable User table.
     * @param string $hashTable Hash table.
     * @return array|null Whether the authentication support can migrate SHA256 hash or null if not supported.
     */
    public abstract function authSupportCanMigrateSHA256Hash(string $userTable, string $hashTable): ?array;

    /**
     * Checks if a value is true.
     *
     * @param string|null $d Value to check.
     * @return bool Whether the value is true.
     */
    private function isTrue(?string $d): bool
    {
        if (is_null($d)) {
            return false;
        }
        if (strtolower($d) === 'true' || strtolower($d) === 't') {
            return true;
        } else if (intval($d) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Performs special error handling for SQL queries.
     *
     * @param string $sql SQL query.
     * @return void
     */
    public function specialErrorHandling(string $sql): void
    {

    }

    /**
     * Returns the last insert ID for a sequence object.
     *
     * @param string $seqObject Sequence object.
     * @return string|null Last insert ID or null if not found.
     */
    public function getLastInsertId(string $seqObject): ?string
    {
        if (!$this->dbClassObj->link) {
            return null;
        }
        return $this->dbClassObj->link->lastInsertId($seqObject);
    }

    /**
     * Returns the last insert ID for a sequence object and table name.
     *
     * @param string $seqObject Sequence object.
     * @param string $tableName Table name.
     * @return string|null Last insert ID or null if not found.
     */
    public function lastInsertIdAlt(string $seqObject, string $tableName): ?string
    {
        $incrementField = $this->getAutoIncrementField($tableName);
        $contextDef = $this->dbClassObj->dbSettings->getDataSourceTargetArray();
        $keyField = $contextDef['key'] ?? null;
        if ($incrementField && ($incrementField === $keyField || $incrementField === '_CANCEL_THE_INCR_FIELD_DETECT_')) {
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

    /**
     * Returns the SQL numeric to like operator.
     *
     * @param string $field Field name.
     * @param string $value Value.
     * @return string SQL numeric to like operator.
     */
    public function getSQLNumericToLikeOpe(string $field, string $value): string
    {
        return "{$field} LIKE {$value}";
    }
}
