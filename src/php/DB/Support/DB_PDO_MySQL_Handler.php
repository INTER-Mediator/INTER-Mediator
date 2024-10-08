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
 *
 */
class DB_PDO_MySQL_Handler extends DB_PDO_Handler
{
    /**
     * @var array
     */
    protected array $tableInfo = array();
    /**
     * @var string
     */
    public string $fieldNameForField = 'Field';
    /**
     * @var string
     */
    protected string $fieldNameForType = 'Type';
    /**
     * @var string
     */
    protected string $fieldNameForNullable = 'Null';
    /**
     * @var array|string[]
     */
    protected array $numericFieldTypes = ['int', 'integer', 'numeric', 'smallint', 'tinyint', 'mediumint',
        'bigint', 'decimal', 'float', 'double', 'bit', 'dec', 'fixed', 'double percision', 'year',];
    /**
     * @var array|string[]
     */
    protected array $timeFieldTypes = ['datetime', 'time', 'timestamp'];
    /**
     * @var array|string[]
     */
    protected array $dateFieldTypes = ['datetime', 'date', 'timestamp'];
    /**
     * @var array|string[]
     */
    protected array $booleanFieldTypes = ['boolean', 'bool'];

    /**
     * @return string
     */
    public function sqlSELECTCommand(): string
    {
        return "SELECT ";
    }

    /**
     * @param string $param
     * @return string
     */
    public function sqlLimitCommand(string $param): string
    {
        return "LIMIT {$param}";
    }

    /**
     * @param string $param
     * @return string
     */
    public function sqlOffsetCommand(string $param): string
    {
        return "OFFSET {$param}";
    }

    /**
     * @return string
     */
    public function sqlDELETECommand(): string
    {
        return "DELETE FROM ";
    }

    /**
     * @return string
     */
    public function sqlUPDATECommand(): string
    {
        return "UPDATE IGNORE ";
    }

    /**
     * @param string $tableRef
     * @param string $setClause
     * @return string
     */
    public function sqlINSERTCommand(string $tableRef, string $setClause): string
    {
        return "INSERT IGNORE INTO {$tableRef} {$setClause}";
    }

    /**
     * @param string $tableRef
     * @param string $setClause
     * @return string
     */
    public function sqlREPLACECommand(string $tableRef, string $setClause): string
    {
        return "REPLACE INTO {$tableRef} {$setClause}";
    }

    /**
     * @param string $tableName
     * @param array $setColumnNames
     * @param string $keyField
     * @param array $setValues
     * @return string
     * @throws Exception
     */
    public function sqlSETClause(string $tableName, array $setColumnNames, string $keyField, array $setValues): string
    {
        [$setNames, $setValuesConv] = $this->sqlSETClauseData($tableName, $setColumnNames, $setValues);
        return (count($setColumnNames) === 0) ? "SET {$keyField}=DEFAULT" :
            '(' . implode(',', $setNames) . ') VALUES(' . implode(',', $setValuesConv) . ')';
    }

//    public function getNullableFields($tableName)
//    {
//        try {
//            $result = $this->getTableInfo($tableName);
//        } catch (Exception $ex) {
//            throw $ex;
//        }
//        $fieldArray = [];
//        foreach ($result as $row) {
//            if ($row[$this->fieldNameForNullable] == "YES") {
//                $fieldArray[] = $row[$this->fieldNameForField];
//            }
//        }
//        return $fieldArray;
//    }

    /**
     * @return string
     */
    public function dateResetForNotNull(): string
    {
        return '1000-01-01';
    }

    /**
     * @param $info
     * @return bool
     */
    protected function checkNullableField($info): bool
    {
        return $info == 'YES';
    }

    /**
     * @param string $tableName
     * @return string|null
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
            if (strpos($row["Extra"], "auto_increment") !== false) {
                return $row["Field"];
            }
        }
        return null;
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getTableInfoSQL(string $tableName): string
    {
        return "SHOW COLUMNS FROM " . $this->quotedEntityName($tableName);
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

    /**
     * @param string $tableName
     * @param string $keyField
     * @param string|null $assocField
     * @param string|null $assocValue
     * @param array|null $defaultValues
     * @return array
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
     * @param string $entityName
     * @return string|null
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
     * @return void
     */
    public function optionalOperationInSetup(): void
    {
        $this->dbClassObj->link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    /**
     * @param string $userTable
     * @param string $hashTable
     * @return array|null
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

    /*
  * As far as MySQL goes, in case of rising up the warning of violating constraints of foreign keys.
  * it happens any kind of warning but errorCode returns 00000 which means no error. There is no other way
  * to call SHOW WARNINGS. Other db engines don't do anything here.
     * Sample of SHOW WARNINGS
     * mysql> show warnings;
+---------+------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Level   | Code | Message                                                                                                                                                                                                           |
+---------+------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
| Warning | 1452 | Cannot add or update a child row: a foreign key constraint fails (`embryoscope`.`transferembryo`, CONSTRAINT `transferembryo_ibfk_2` FOREIGN KEY (`embryoID`) REFERENCES `embryo` (`embryoID`) ON DELETE CASCADE) |
+---------+------+-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------+
1 row in set (0.00 sec)

  */
    /**
     * @param string $sql
     * @return void
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
     * @param string $seqObject
     * @return string|null
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
     * @return string SQL command returns database list
     */
    public function sqlLISTDATABASECommand(): string{
        return "SHOW DATABASES;";
    }

    /**
     * @return string The field name for database name in the result of database list
     */
    public function sqlLISTDATABASEColumn(): string{
        return "Database";
    }
}
