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

/**
 * MySQL-specific handler for PDO database operations.
 * Implements SQL command generation and schema inspection for MySQL databases.
 * Extends DB_PDO_Handler with MySQL-specific logic.
 */
class DB_PDO_MySQL_Handler extends DB_PDO_Handler
{
    /**
     * @var array Table information for schema inspection.
     */
    protected array $tableInfo = array();
    /**
     * @var string Field name for column field.
     */
    public string $fieldNameForField = 'Field';
    /**
     * @var string Field name for column type.
     */
    protected string $fieldNameForType = 'Type';
    /**
     * @var string Field name for nullable property.
     */
    protected string $fieldNameForNullable = 'Null';
    /**
     * @var array|string[] List of numeric field types.
     */
    protected array $numericFieldTypes = ['int', 'integer', 'numeric', 'smallint', 'tinyint', 'mediumint',
        'bigint', 'decimal', 'float', 'double', 'bit', 'dec', 'fixed', 'double percision', 'year',];
    /**
     * @var array|string[] List of time field types.
     */
    protected array $timeFieldTypes = ['datetime', 'time', 'timestamp'];
    /**
     * @var array|string[] List of date field types.
     */
    protected array $dateFieldTypes = ['datetime', 'date', 'timestamp'];
    /**
     * @var array|string[] List of boolean field types.
     */
    protected array $booleanFieldTypes = ['boolean', 'bool'];

    /**
     * Returns the SQL SELECT command for MySQL.
     *
     * @return string SQL SELECT command.
     */
    public function sqlSELECTCommand(): string
    {
        return "SELECT ";
    }

    /**
     * Returns the SQL LIMIT command for MySQL.
     *
     * @param string $param Limit parameter.
     * @return string SQL LIMIT command.
     */
    public function sqlLimitCommand(string $param): string
    {
        return "LIMIT {$param}";
    }

    /**
     * Returns the SQL OFFSET command for MySQL.
     *
     * @param string $param Offset parameter.
     * @return string SQL OFFSET command.
     */
    public function sqlOffsetCommand(string $param): string
    {
        return "OFFSET {$param}";
    }

    /**
     * Returns the SQL DELETE command for MySQL.
     *
     * @return string SQL DELETE command.
     */
    public function sqlDELETECommand(): string
    {
        return "DELETE FROM ";
    }

    /**
     * Returns the SQL UPDATE command for MySQL.
     *
     * @return string SQL UPDATE command.
     */
    public function sqlUPDATECommand(): string
    {
        return "UPDATE IGNORE ";
    }

    /**
     * Returns the SQL INSERT command for MySQL.
     *
     * @param string $tableRef Table reference.
     * @param string $setClause Set clause.
     * @return string SQL INSERT command.
     */
    public function sqlINSERTCommand(string $tableRef, string $setClause): string
    {
        return "INSERT IGNORE INTO {$tableRef} {$setClause}";
    }

    /**
     * Returns the SQL REPLACE command for MySQL.
     *
     * @param string $tableRef Table reference.
     * @param string $setClause Set clause.
     * @return string SQL REPLACE command.
     */
    public function sqlREPLACECommand(string $tableRef, string $setClause): string
    {
        return "REPLACE INTO {$tableRef} {$setClause}";
    }

    /**
     * Returns the SQL SET clause for MySQL.
     *
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
        return (count($setColumnNames) === 0) ? "SET {$keyField}=DEFAULT" :
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
     * @param mixed $info Field information.
     * @return bool True if the field is nullable, false otherwise.
     */
    protected function checkNullableField($info): bool
    {
        return $info == 'YES';
    }

    /**
     * Returns the auto-increment field for a table.
     *
     * @param string $tableName Table name.
     * @return string|null Auto-increment field, or null if not found.
     * @throws Exception If an error occurs.
     */
    protected function getAutoIncrementField(string $tableName): ?string
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        foreach ($result as $row) {
            if (strpos($row["Extra"], "auto_increment") !== false) {
                return $row["Field"];
            }
        }
        return null;
    }

    /**
     * Returns the SQL command to get table information.
     *
     * @param string $tableName Table name.
     * @return string SQL command.
     */
    public function getTableInfoSQL(string $tableName): string
    {
        return "SHOW COLUMNS FROM " . $this->quotedEntityName($tableName);
    }

    /**
     * Returns the field lists for copying data.
     *
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
            if ($keyField === $row['Field']) {
                // skip key field to assign value.
            } else if ($assocField === $row['Field']) {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else if (isset($defaultValues[$row['Field']])) {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->dbClassObj->link->quote($defaultValues[$row['Field']]);
            } else if (!is_null($row['Default'])){
                // skip if field has a default value
            } else {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->quotedEntityName($row['Field']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    /**
     * Returns the quoted entity name.
     *
     * @param string $entityName Entity name.
     * @return string|null Quoted entity name, or null if empty.
     */
    public function quotedEntityName(string $entityName): ?string
    {
        if (!$entityName) {
            return null;
        }
        if (strpos($entityName, ".") !== false) {
            $components = explode(".", $entityName);
            $quotedName = array();
            foreach ($components as $item) {
                $quotedName[] = "`{$item}`";
            }
            return implode(".", $quotedName);
        }
        return "`{$entityName}`";
    }

    /**
     * Performs optional operations in setup.
     */
    public function optionalOperationInSetup(): void
    {
        $this->dbClassObj->link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    /**
     * Checks if the SHA256 hash can be migrated.
     *
     * @param string $userTable User table.
     * @param string $hashTable Hash table.
     * @return array|null Migration result, or null if not applicable.
     */
    public function authSupportCanMigrateSHA256Hash(string $userTable, string $hashTable):?array // authuser, issuedhash
    {
        $checkFieldDefinition = function (string $type, int $min):bool {
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
                if (isset($fieldInfo['Field'])
                    && $fieldInfo['Field'] == 'hashedpasswd'
                    && !$checkFieldDefinition($fieldInfo['Type'], 72)) {
                    $returnValue[] = "The hashedpassword field of the authuser table has to be longer than 72 characters.";
                }
            }
        }
        if ($infoIssuedHash) {
            foreach ($infoIssuedHash as $fieldInfo) {
                if (isset($fieldInfo['Field'])
                    && $fieldInfo['Field'] == 'clienthost'
                    && !$checkFieldDefinition($fieldInfo['Type'], 64)) {
                    $returnValue[] = "The clienthost field of the issuedhash table has to be longer than 64 characters.";
                }
                if (isset($fieldInfo['Field'])
                    && $fieldInfo['Field'] == 'hash'
                    && !$checkFieldDefinition($fieldInfo['Type'], 64)) {
                    $returnValue[] = "The hash field of the issuedhash table has to be longer than 64 characters.";
                }
            }
        }
        return $returnValue;
    }

    /**
     * Handles special error handling for MySQL.
     *
     *  As far as MySQL goes, in case of raising up the warning of violating constraints of foreign keys.
     *  it happens any kind of warning, but errorCode returns 00000 which means no error. There is no other way
     *  to call SHOW WARNINGS. Other db engines don't do anything here.
     *  Sample of SHOW WARNINGS
     *  mysql> show warnings;
     * +---------+------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
     * | Level   | Code | Message                                                                                                                                                                                                           |
     * +---------+------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
     * | Warning | 1452 | Cannot add or update a child row: a foreign key constraint fails (`embryoscope`.`transferembryo`, CONSTRAINT `transferembryo_ibfk_2` FOREIGN KEY (`embryoID`) REFERENCES `embryo` (`embryoID`) ON DELETE CASCADE) |
     * +---------+------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
     * 1 row in set (0.00 sec)
     *
     * @param string $sql SQL command.
     */
    public function specialErrorHandling(string $sql): void
    {
        if ($this->dbClassObj->link) {
            $warnings = $this->dbClassObj->link->query('SHOW COUNT(*) WARNINGS');
            $warningsCount = 0;
            foreach ($warnings->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $warningsCount = intval($row['@@session.warning_count']);
            }
            if ($warningsCount > 0) {
                $warnings = $this->dbClassObj->link->query('SHOW WARNINGS');
                $debugMsg = "";
                foreach ($warnings->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $message = "[{$row['Level']}]({$row['Code']}){$row['Message']}";
                    $debugMsg .= "{$message}\n";
                    if ($row['Level'] == 'Warning') {
                        $this->dbClassObj->logger->setWarningMessage("{$message} by {$sql}");
                    }
                }
                if (strlen($debugMsg) > 0) {
                    $this->dbClassObj->logger->setDebugMessage($debugMsg);
                }
            }
        }
    }

    /**
     * Returns the last insert ID.
     *
     * @param string $seqObject Sequence object.
     * @return string|null Last insert ID, or null if not applicable.
     */
    public function getLastInsertId(string $seqObject): ?string
    {
        if ($this->dbClassObj->link) {
            $warnings = $this->dbClassObj->link->query('SELECT LAST_INSERT_ID() AS ID');
            foreach ($warnings->fetchAll(PDO::FETCH_ASSOC) as $row) {
                return (string )intval($row['ID']);
            }
        }
        return null;
    }

    /**
     * Returns the SQL command to create a user.
     *
     * @param string $dbName Database name.
     * @param string $userEntity User entity.
     * @param string $password Password.
     * @return string SQL command.
     */
    public function sqlCREATEUSERCommand(string $dbName, string $userEntity, string $password): string
    {
        $quotedDB = $this->quotedEntityName($dbName);
        $quotedUser = $this->quotedData($userEntity, "@");
        $quotedPassword = $this->dbClassObj->link->quote($password);
        return "CREATE USER IF NOT EXISTS {$quotedUser};\n"
            . "GRANT SELECT, INSERT, DELETE, UPDATE, SHOW VIEW ON TABLE {$quotedDB}.* TO {$quotedUser};\n"
            . "SET PASSWORD FOR {$quotedUser} = {$quotedPassword};\n";
    }

    /**
     * Returns the SQL command to list databases.
     *
     * @return string SQL command.
     */
    public function sqlLISTDATABASECommand(): string
    {
        return "SHOW DATABASES;";
    }

    /**
     * Returns the column name for database list.
     *
     * @return string Column name.
     */
    public function sqlLISTDATABASEColumn(): string
    {
        return "Database";
    }
}
/*
mysql> show columns from testtable;
+---------+--------------+------+-----+---------------------+----------------+
| Field   | Type         | Null | Key | Default             | Extra          |
+---------+--------------+------+-----+---------------------+----------------+
| id      | int          | NO   | PRI | NULL                | auto_increment |
| num1    | int          | NO   |     | 0                   |                |
| num2    | int          | YES  |     | NULL                |                |
| num3    | int          | YES  |     | NULL                |                |
| dt1     | datetime     | NO   |     | 2001-01-01 00:00:00 |                |
| dt2     | datetime     | YES  |     | NULL                |                |
| dt3     | datetime     | YES  |     | NULL                |                |
| date1   | date         | NO   |     | 2001-01-01          |                |
| date2   | date         | YES  |     | NULL                |                |
| time1   | time         | NO   |     | 00:00:00            |                |
| time2   | time         | YES  |     | NULL                |                |
| ts1     | timestamp    | NO   |     | 2001-01-01 00:00:00 |                |
| ts2     | timestamp    | YES  |     | 2001-01-01 00:00:00 |                |
| vc1     | varchar(100) | NO   |     |                     |                |
| vc2     | varchar(100) | YES  |     | NULL                |                |
| vc3     | varchar(100) | YES  |     | NULL                |                |
| text1   | text         | YES  |     | NULL                |                |
| text2   | text         | YES  |     | NULL                |                |
| float1  | float        | NO   |     | 0                   |                |
| float2  | float        | YES  |     | NULL                |                |
| double1 | double       | NO   |     | 0                   |                |
| double2 | double       | YES  |     | NULL                |                |
| bool1   | tinyint(1)   | NO   |     | 0                   |                |
| bool2   | tinyint(1)   | YES  |     | NULL                |                |
+---------+--------------+------+-----+---------------------+----------------+
mysql> show columns from item_display;
+-------------+-------------+------+-----+---------+-------+
| Field       | Type        | Null | Key | Default | Extra |
+-------------+-------------+------+-----+---------+-------+
| id          | int         | NO   |     | 0       |       |
| invoice_id  | int         | YES  |     | NULL    |       |
| product_id  | int         | YES  |     | NULL    |       |
| category_id | int         | YES  |     | NULL    |       |
| name        | varchar(20) | YES  |     | NULL    |       |
| qty         | int         | YES  |     | NULL    |       |
| unitprice   | float       | YES  |     | NULL    |       |
| amount      | double      | YES  |     | NULL    |       |
+-------------+-------------+------+-----+---------+-------+
8 rows in set (0.00 sec)
In case of calculation field of a view, the type column is going to be ''.
*/