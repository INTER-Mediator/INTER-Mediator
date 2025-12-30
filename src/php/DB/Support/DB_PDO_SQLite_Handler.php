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
 * SQLite-specific handler for PDO database operations.
 * Implements SQL command generation and schema inspection for SQLite databases.
 * Extends DB_PDO_Handler with SQLite-specific logic.
 */
class DB_PDO_SQLite_Handler extends DB_PDO_Handler
{
    /** @var array Table information for schema inspection.
     */
    protected array $tableInfo = array();
    /** @var string Field name for column field.
     */
    public string $fieldNameForField = 'name';
    /** @var string Field name for column type.
     */
    protected string $fieldNameForType = 'type';
    /** @var string Field name for nullable property.
     */
    protected string $fieldNameForNullable = 'notnull';
    /** @var array|string[] List of numeric field types.
     */
    protected array $numericFieldTypes = array('integer', 'int', 'real', 'numeric',
        'tinyint', 'smallint', 'mediumint', 'bigint', 'unsigned big int', 'int2', 'int8',
        'double', 'double precision', 'float', 'decimal', 'boolean');
    /** @var array|string[] List of time field types.
     */
    protected array $timeFieldTypes = ['datetime', 'time', 'timestamp'];
    /** @var array|string[] List of date field types.
     */
    protected array $dateFieldTypes = ['datetime', 'date', 'timestamp'];
    /** @var array List of boolean field types.
     */
    protected array $booleanFieldTypes = [];

    /** Returns the SQL SELECT command for SQLite.
     * @return string SQL SELECT command.
     */
    public function sqlSELECTCommand(): string
    {
        return "SELECT ";
    }

    /** Returns the SQL LIMIT command for SQLite.
     * @param string $param Limit parameter.
     * @return string SQL LIMIT command.
     */
    public function sqlLimitCommand(string $param): string
    {
        return "LIMIT {$param}";
    }

    /** Returns the SQL OFFSET command for SQLite.
     * @param string $param Offset parameter.
     * @return string SQL OFFSET command.
     */
    public function sqlOffsetCommand(string $param): string
    {
        return "OFFSET {$param}";
    }

    /** Returns the SQL DELETE command for SQLite.
     * @return string SQL DELETE command.
     */
    public function sqlDELETECommand(): string
    {
        return "DELETE FROM ";
    }

    /** Returns the SQL UPDATE command for SQLite.
     * @return string SQL UPDATE command.
     */
    public function sqlUPDATECommand(): string
    {
        return "UPDATE ";
    }

    /** Returns the SQL INSERT command for SQLite.
     * @param string $tableRef Table reference.
     * @param string $setClause Set clause.
     * @return string SQL INSERT command.
     */
    public function sqlINSERTCommand(string $tableRef, string $setClause): string
    {
        return "INSERT INTO {$tableRef} {$setClause}";
    }

    /** Returns the SQL REPLACE command for SQLite.
     * @param string $tableRef Table reference.
     * @param string $setClause Set clause.
     * @return string SQL REPLACE command.
     */
    public function sqlREPLACECommand(string $tableRef, string $setClause): string
    {
        return "REPLACE INTO {$tableRef} {$setClause}";
    }

    /** Returns the SQL SET clause for SQLite.
     * @param string $tableName Table name.
     * @param array $setColumnNames Set column names.
     * @param string $keyField Key field.
     * @param array $setValues Set values.
     * @return string SQL SET clause.
     * @throws Exception If an error occurs.
     */
    public function sqlSETClause(string $tableName, array $setColumnNames, string $keyField, array $setValues): string
    {
        [$setNames, $setValuesConv] = $this->sqlSETClauseData($tableName, $setColumnNames, $setValues);
        return (count($setColumnNames) === 0) ? "DEFAULT VALUES" :
            '(' . implode(',', $setNames) . ') VALUES(' . implode(',', $setValuesConv) . ')';
    }

    /** Returns the default date value for non-nullable date fields.
     * @return string Default date value.
     */
    public function dateResetForNotNull(): string
    {
        return '1970-01-01';
    }

    /** Checks if a field is nullable.
     * @param string $info Field information.
     * @return bool True if the field is nullable, false otherwise.
     */
    protected function checkNullableField(string $info): bool
    {
        return intval($info) === 0;
    }

    /** Returns the auto-increment field for a table.
     * @param string $tableName Table name.
     * @return string|null Auto-increment field or null if not found.
     */
    protected function getAutoIncrementField(string $tableName): ?string
    {
        // SQLite doesn't support to create a record with non AUTOINCREMENT field as the primary key.
        return '_CANCEL_THE_INCR_FIELD_DETECT_';
    }

    /** Returns the SQL command to retrieve table information.
     * @param string $tableName Table name.
     * @return string SQL command.
     */
    public function getTableInfoSQL(string $tableName): string
    {
        return "PRAGMA table_info({$tableName})";
    }

    /** Returns the field lists for copying data.
     * @param string $tableName Table name.
     * @param string $keyField Key field.
     * @param string|null $assocField Associated field.
     * @param string|null $assocValue Associated value.
     * @param array|null $defaultValues Default values.
     * @return array Field lists.
     * @throws Exception If an error occurs.
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
            if ($keyField === $row['name']) {

            } else if ($assocField === $row['name']) {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else if (isset($defaultValues[$row['name']])) {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->dbClassObj->link->quote($defaultValues[$row['name']]);
            } else if (!is_null($row['dflt_value'])) {

            } else {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->quotedEntityName($row['name']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    /** Returns the quoted entity name.
     * @param string $entityName Entity name.
     * @return string|null Quoted entity name or null if not found.
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

    /** Performs optional operations during setup.
     */
    public function optionalOperationInSetup(): void
    {
    }

    /** Checks if the authentication support can migrate SHA256 hash.
     * @param string $userTable User table.
     * @param string $hashTable Hash table.
     * @return array|null Migration result or null if not applicable.
     */
    public function authSupportCanMigrateSHA256Hash(string $userTable, string $hashTable): ?array
    {
        $checkFieldDefinition = function (string $type, int $min): bool {
            $fDef = strtolower($type);
            if ($fDef != 'text' && strpos($fDef, 'varchar') !== false) {
                $openParen = strpos($fDef, '(');
                $closeParen = strpos($fDef, ')');
                $len = intval(substr($fDef, $openParen + 1, $closeParen - $openParen - 1));
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
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] === 'hashedpasswd'
                    && !$checkFieldDefinition($fieldInfo['type'], 72)) {
                    $returnValue[] = "The hashedpassword field of the authuser table has to be longer than 72 characters.";
                }
            }
        }
        if ($infoIssuedHash) {
            foreach ($infoIssuedHash as $fieldInfo) {
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] === 'clienthost'
                    && !$checkFieldDefinition($fieldInfo['type'], 64)) {
                    $returnValue[] = "The clienthost field of the issuedhash table has to be longer than 64 characters.";
                }
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] === 'hash'
                    && !$checkFieldDefinition($fieldInfo['type'], 64)) {
                    $returnValue[] = "The hash field of the issuedhash table has to be longer than 64 characters.";
                }
            }
        }
        return $returnValue;
    }

    /** Returns the SQL command to list databases.
     * @return string SQL command.
     */
    public function sqlLISTDATABASECommand(): string
    {
        // schema generation does not support.
        return '';
    }

    /** Returns the SQL column to list databases.
     * @return string SQL column.
     */
    public function sqlLISTDATABASEColumn(): string
    {
        // schema generation does not support.
        return '';
    }

    /** Returns the SQL command to create a user.
     * @param string $dbName Database name.
     * @param string $userEntity User entity.
     * @param string $password Password.
     * @return string SQL command.
     */
    public function sqlCREATEUSERCommand(string $dbName, string $userEntity, string $password): string
    {
        // schema generation does not support.
        return '';
    }
}
/*
sqlite> .mode column
sqlite> PRAGMA table_info(testtable);
cid  name   type          notnull  dflt_value             pk
---  -----  ------------  -------  ---------------------  --
0    id     INTEGER       0                               1
1    num1   INT           1        0                      0
2    num2   INT           0                               0
3    num3   INT           0                               0
4    dt1    DateTime      1        CURRENT_TIMESTAMP      0
5    dt2    DateTime      0                               0
6    dt3    DateTime      0                               0
7    date1  Date          1        CURRENT_TIMESTAMP      0
8    date2  Date          0                               0
9    time1  Time          1        CURRENT_TIMESTAMP      0
10   time2  Time          0                               0
11   ts1    Timestamp     1        CURRENT_TIMESTAMP      0
12   ts2    Timestamp     0        '2001-01-01 00:00:00'  0
13   vc1    VARCHAR(100)  1        ''                     0
14   vc2    VARCHAR(100)  0                               0
15   vc3    VARCHAR(100)  0                               0
16   text1  TEXT          1        ''                     0
17   text2  TEXT          0                               0
    https://stackoverflow.com/questions/20979239/how-to-tell-if-a-sqlite-column-is-autoincrement
sqlite> SELECT COUNT(*) FROM sqlite_sequence WHERE name='testtable';
1
sqlite> SELECT * FROM sqlite_sequence WHERE name='testtable';
testtable|106
sqlite> SELECT COUNT(*) FROM sqlite_sequence WHERE name='person';
1
sqlite> SELECT * FROM sqlite_sequence;
person|90
contact|52
contact_way|6
*/