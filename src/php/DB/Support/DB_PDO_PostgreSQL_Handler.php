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

/**
 * PostgreSQL-specific handler for PDO database operations.
 * Implements SQL command generation and schema inspection for PostgreSQL databases.
 * Extends DB_PDO_Handler with PostgreSQL-specific logic.
 */
class DB_PDO_PostgreSQL_Handler extends DB_PDO_Handler
{
    /**
     * @var array Table information for schema inspection.
     */
    protected array $tableInfo = array();
    /**
     * @var string Field name for column field.
     */
    public string $fieldNameForField = 'column_name';
    /**
     * @var string Field name for column type.
     */
    protected string $fieldNameForType = 'data_type';
    /**
     * @var string Field name for nullable property.
     */
    protected string $fieldNameForNullable = 'is_nullable';
    /**
     * @var array|string[] List of numeric field types.
     */
    protected array $numericFieldTypes = ['smallint', 'integer', 'bigint', 'decimal', 'numeric',
        'real', 'double precision', 'smallserial', 'serial', 'bigserial', 'money',];
    /**
     * @var array|string[] List of time field types.
     */
    protected array $timeFieldTypes = ['datetime', 'datetime without time zone',
        'time', 'time without time zone', 'timestamp', 'timestamp without time zone'];
    /**
     * @var array|string[] List of date field types.
     */
    protected array $dateFieldTypes = ['datetime', 'datetime without time zone',
        'date', 'date without time zone', 'timestamp', 'timestamp without time zone',];
    /**
     * @var array|string[] List of boolean field types.
     */
    protected array $booleanFieldTypes = ['boolean'];

    /**
     * Returns the SQL SELECT command for PostgreSQL.
     *
     * @return string SQL SELECT command.
     */
    public function sqlSELECTCommand(): string
    {
        return "SELECT ";
    }

    /**
     * Returns the SQL LIMIT command for PostgreSQL.
     *
     * @param string $param Limit parameter.
     * @return string SQL LIMIT command.
     */
    public function sqlLimitCommand(string $param): string
    {
        return "LIMIT {$param}";
    }

    /**
     * Returns the SQL OFFSET command for PostgreSQL.
     *
     * @param string $param Offset parameter.
     * @return string SQL OFFSET command.
     */
    public function sqlOffsetCommand(string $param): string
    {
        return "OFFSET {$param}";
    }

    /**
     * Returns the SQL DELETE command for PostgreSQL.
     *
     * @return string SQL DELETE command.
     */
    public function sqlDELETECommand(): string
    {
        return "DELETE FROM ";
    }

    /**
     * Returns the SQL UPDATE command for PostgreSQL.
     *
     * @return string SQL UPDATE command.
     */
    public function sqlUPDATECommand(): string
    {
        return "UPDATE ";
    }

    /**
     * Returns the SQL INSERT command for PostgreSQL.
     *
     * @param string $tableRef Table reference.
     * @param string $setClause SET clause.
     * @return string SQL INSERT command.
     */
    public function sqlINSERTCommand(string $tableRef, string $setClause): string
    {
        return "INSERT INTO {$tableRef} {$setClause}";
    }

    /**
     * Returns the SQL SET clause for PostgreSQL.
     *
     * @param string $tableName Table name.
     * @param array $setColumnNames Column names.
     * @param string $keyField Key field.
     * @param array $setValues Values.
     * @return string SQL SET clause.
     * @throws Exception
     */
    public function sqlSETClause(string $tableName, array $setColumnNames, string $keyField, array $setValues): string
    {
        [$setNames, $setValuesConv] = $this->sqlSETClauseData($tableName, $setColumnNames, $setValues);
        return (count($setColumnNames) === 0) ? "DEFAULT VALUES" :
            '(' . implode(',', $setNames) . ') VALUES(' . implode(',', $setValuesConv) . ')';
    }

    /**
     * Returns the date reset value for not-null fields.
     *
     * @return string Date reset value.
     */
    public function dateResetForNotNull(): string
    {
        return '1000-01-01';
    }

    /**
     * Checks if a field is nullable.
     *
     * @param string $info Field information.
     * @return bool True if the field is nullable, false otherwise.
     */
    protected function checkNullableField(string $info): bool
    {
        return $info === 'YES';
    }

    /**
     * Returns the auto-increment field for a table.
     *
     * @param string $tableName Table name.
     * @return string|null Auto-increment field, or null if not found.
     * @throws Exception
     */
    protected function getAutoIncrementField(string $tableName): ?string
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        foreach ($result as $row) {
            if ($row["column_default"] && strpos($row["column_default"], "nextval(") !== false) {
                return $row["column_name"];
            }
        }
        return null;
    }

    /**
     * Returns the SQL to retrieve table information.
     *
     * @param string $tableName Table name.
     * @return string SQL to retrieve table information.
     */
    public function getTableInfoSQL(string $tableName): string
    {
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
        return $sql;
    }

    /**
     * Returns the field lists for a copy operation.
     *
     * @param string $tableName Table name.
     * @param string $keyField Key field.
     * @param string|null $assocField Associated field.
     * @param string|null $assocValue Associated value.
     * @param array|null $defaultValues Default values.
     * @return array Field lists.
     * @throws Exception
     */
    protected function getFieldListsForCopy(string $tableName, string $keyField, ?string $assocField, ?string $assocValue,
                                            ?array $defaultValues): array
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = array();
        $listArray = array();
        foreach ($result as $row) {
            if ($keyField === $row['column_name']) {

            } else if ($assocField === $row['column_name']) {
                $fieldArray[] = $this->quotedEntityName($row['column_name']);
                $listArray[] = $this->setValue($assocValue, $row);
            } else if (isset($defaultValues[$row['column_name']])) {
                $fieldArray[] = $this->quotedEntityName($row['column_name']);
                $listArray[] = $this->setValue($defaultValues[$row['column_name']], $row);
            } else if (!is_null($row['column_default'])){

            } else {
                $fieldArray[] = $this->quotedEntityName($row['column_name']);
                $listArray[] = $this->quotedEntityName($row['column_name']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    /**
     * Sets a value for a field.
     *
     * @param string $value Value.
     * @param array $row Field information.
     * @return string Set value.
     */
    protected function setValue(string $value, array $row): string
    {
        if ($row['is_nullable'] && $value === '') {
            return 'NULL';
        }
        return $this->dbClassObj->link->quote($value);
    }

    /**
     * Returns the quoted entity name.
     *
     * @param string $entityName Entity name.
     * @return string|null Quoted entity name, or null if not found.
     */
    public function quotedEntityName(string $entityName): ?string
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

    /**
     * Optional operation in setup.
     */
    public function optionalOperationInSetup(): void
    {
    }

    /**
     * Checks if the auth support can migrate SHA256 hash.
     *
     * @param string $userTable User table.
     * @param string $hashTable Hash table.
     * @return array|null Migration result, or null if not applicable.
     */
    public function authSupportCanMigrateSHA256Hash(string $userTable, string $hashTable): ?array
    {
        $checkFieldDefinition = function (string $type, int $len, int $min): bool {
            $fDef = strtolower($type);
            if ($fDef != 'text' && $fDef === 'character varying') {
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
                    && $fieldInfo['column_name'] === 'hashedpasswd'
                    && !$checkFieldDefinition($fieldInfo['data_type'], $fieldInfo['character_maximum_length'], 72)) {
                    $returnValue[] = "The hashedpassword field of the authuser table has to be longer than 72 characters.";
                }
            }
        }
        if ($infoIssuedHash) {
            foreach ($infoIssuedHash as $fieldInfo) {
                if (isset($fieldInfo['column_name'])
                    && $fieldInfo['column_name'] === 'clienthost'
                    && !$checkFieldDefinition($fieldInfo['data_type'], $fieldInfo['character_maximum_length'], 64)) {
                    $returnValue[] = "The clienthost field of the issuedhash table has to be longer than 64 characters.";
                }
                if (isset($fieldInfo['column_name'])
                    && $fieldInfo['column_name'] === 'hash'
                    && !$checkFieldDefinition($fieldInfo['data_type'], $fieldInfo['character_maximum_length'], 64)) {
                    $returnValue[] = "The hash field of the issuedhash table has to be longer than 64 characters.";
                }
            }
        }
        return $returnValue;
    }

    /**
     * Returns the SQL for numeric to like operation.
     *
     * @param string $field Field.
     * @param string $value Value.
     * @return string SQL for numeric to like operation.
     */
    public function getSQLNumericToLikeOpe(string $field, string $value): string
    {
        return "CAST({$field} AS TEXT) LIKE {$value}";
    }

    /**
     * Returns the SQL to create a user.
     *
     * @param string $dbName Database name.
     * @param string $userEntity User entity.
     * @param string $password Password.
     * @return string SQL to create a user.
     */
    public function sqlCREATEUSERCommand(string $dbName, string $userEntity, string $password): string
    {
        $quotedDB = $this->quotedEntityName($dbName);
        $justUsername = explode("@", $userEntity)[0];
        return
            "DROP SCHEMA IF EXISTS {$quotedDB} CASCADE;"
            . "CREATE SCHEMA {$quotedDB};"
            . "SET search_path TO {$quotedDB},public;"
            . "ALTER USER web SET search_path TO {$quotedDB},public;"
            . "GRANT ALL PRIVILEGES ON SCHEMA {$quotedDB} TO {$justUsername};";
    }

    /**
     * Returns the SQL to list databases.
     *
     * @return string SQL to list databases.
     */
    public function sqlLISTDATABASECommand(): string
    {
        return "SELECT datname, datdba, encoding, datcollate, datctype FROM pg_database;";
    }

    /**
     * Returns the column name for database list.
     *
     * @return string Column name for database list.
     */
    public function sqlLISTDATABASEColumn(): string
    {
        return "datname";
    }
}
/*
# SELECT column_name, column_default, is_nullable, data_type, character_maximum_length,numeric_precision, numeric_scale FROM information_schema.columns WHERE table_schema='im_sample' AND table_name='testtable';
column_name |                   column_default                   | is_nullable |          data_type          | character_maximum_length | numeric_precision | numeric_scale
-------------+----------------------------------------------------+-------------+-----------------------------+--------------------------+-------------------+---------------
id          | nextval('im_sample.testtable_id_seq'::regclass)    | NO          | integer                     |                          |                32 |             0
num1        | 0                                                  | NO          | integer                     |                          |                32 |             0
num2        |                                                    | YES         | integer                     |                          |                32 |             0
num3        |                                                    | YES         | integer                     |                          |                32 |             0
dt1         | CURRENT_TIMESTAMP                                  | NO          | timestamp without time zone |                          |                   |
dt2         |                                                    | YES         | timestamp without time zone |                          |                   |
dt3         |                                                    | YES         | timestamp without time zone |                          |                   |
date1       | CURRENT_TIMESTAMP                                  | NO          | date                        |                          |                   |
date2       |                                                    | YES         | date                        |                          |                   |
time1       | CURRENT_TIMESTAMP                                  | NO          | time without time zone      |                          |                   |
time2       |                                                    | YES         | time without time zone      |                          |                   |
ts1         | CURRENT_TIMESTAMP                                  | NO          | timestamp without time zone |                          |                   |
ts2         | '2001-01-01 00:00:00'::timestamp without time zone | YES         | timestamp without time zone |                          |                   |
vc1         | ''::character varying                              | NO          | character varying           |                      100 |                   |
vc2         |                                                    | YES         | character varying           |                      100 |                   |
vc3         |                                                    | YES         | character varying           |                      100 |                   |
text1       | ''::text                                           | NO          | text                        |                          |                   |
text2       |                                                    | YES         | text                        |                          |                   |
(18 rows)
*/