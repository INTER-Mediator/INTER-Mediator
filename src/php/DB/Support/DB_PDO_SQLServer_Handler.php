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
 * SQL Server-specific handler for PDO database operations.
 * Implements SQL command generation and schema inspection for SQL Server databases.
 * Extends DB_PDO_Handler with SQL Server-specific logic.
 */
class DB_PDO_SQLServer_Handler extends DB_PDO_Handler
{
    /**
     * @var array Table information for schema inspection.
     */
    protected array $tableInfo = array();
    /**
     * @var string Field name for column field.
     */
    public string $fieldNameForField = 'name';
    /**
     * @var string Field name for column type.
     */
    protected string $fieldNameForType = 'type';
    /**
     * @var string Field name for nullable property.
     */
    protected string $fieldNameForNullable = 'is_nullable';
    /**
     * @var array|string[] List of numeric field types.
     */
    protected array $numericFieldTypes = array('bigint', 'bit', 'decimal', 'float', 'hierarchyid',
        'int', 'money', 'numeric', 'real', 'smallint', 'smallmoney', 'tinyint',);
    /**
     * @var array|string[] List of time field types.
     */
    protected array $timeFieldTypes = ['datetime', 'datetime2', 'datetimeoffset', 'time', 'smalldatetime'];
    /**
     * @var array|string[] List of date field types.
     */
    protected array $dateFieldTypes = ['date', 'datetimeoffset', 'smalldatetime'];
    /**
     * @var array List of boolean field types.
     */
    protected array $booleanFieldTypes = [];

    /**
     * Returns the SQL SELECT command for SQL Server.
     *
     * @return string SQL SELECT command.
     */
    public function sqlSELECTCommand(): string
    {
        return "SELECT ";
    }

    /**
     * Returns the SQL LIMIT command for SQL Server.
     *
     * @param string $param Limit parameter.
     * @return string SQL LIMIT command.
     */
    public function sqlLimitCommand(string $param): string
    {
        return "LIMIT {$param}";
    }

    /**
     * Returns the SQL OFFSET command for SQL Server.
     *
     * @param string $param Offset parameter.
     * @return string SQL OFFSET command.
     */
    public function sqlOffsetCommand(string $param): string
    {
        return "OFFSET {$param}";
    }

    /**
     * Returns the default date value for not-nullable date fields.
     *
     * @return string Default date value.
     */
    public function dateResetForNotNull(): string
    {
        return '1000-01-01';
    }

    /**
     * Returns the SQL ORDER BY command for SQL Server.
     *
     * @param string $sortClause Sort clause.
     * @param string $limit Limit parameter.
     * @param string $offset Offset parameter.
     * @return string SQL ORDER BY command.
     */
    public function sqlOrderByCommand(string $sortClause, string $limit, string $offset): string
    {
        if ($sortClause === '') {
            $tableInfo = $this->dbClassObj->dbSettings->getDataSourceTargetArray();
            if ($tableInfo["key"]) {
                $sortClause = $tableInfo["key"];
            } else if (count($this->dbClassObj->dbSettings->getFieldsRequired()) > 0) {
                $fields = $this->dbClassObj->dbSettings->getFieldsRequired();
                $sortClause = $fields[0];
            }
        }
        return "ORDER BY {$sortClause} "
            . (strlen($offset) > 0 ? "OFFSET {$offset} ROWS " : "OFFSET 0 ROWS ")
            . (strlen($limit) > 0 ? "FETCH NEXT {$limit} ROWS ONLY " : "");
    }

    /**
     * Returns the SQL DELETE command for SQL Server.
     *
     * @return string SQL DELETE command.
     */
    public function sqlDELETECommand(): string
    {
        return "DELETE ";
    }

    /**
     * Returns the SQL UPDATE command for SQL Server.
     *
     * @return string SQL UPDATE command.
     */
    public function sqlUPDATECommand(): string
    {
        return "UPDATE ";
    }

    /**
     * Returns the SQL INSERT command for SQL Server.
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
     * Returns the SQL SET clause for SQL Server.
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
     * Checks if a field is nullable.
     *
     * @param string $info Field information.
     * @return bool True if the field is nullable, false otherwise.
     */
    protected function checkNullableField(string $info): bool
    {
        return $info === "0";
    }

    /**
     * Returns the SQL query to retrieve table information for SQL Server.
     *
     * @param string $tableName Table name.
     * @return string SQL query.
     */
    public function getTableInfoSQL(string $tableName): string
    {
        $fields = "c.name, t.name type, c.max_length, c.precision, c.scale, c.is_nullable, " .
            "c.is_identity, c.default_object_id, c.is_computed, c.collation_name";
        return "SELECT {$fields} FROM sys.columns c INNER JOIN sys.types t ON c. system_type_id = t. system_type_id " .
            "WHERE object_id = object_id('{$this->quotedEntityName($tableName)}')";
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
            $quatedFieldName = $this->quotedEntityName($row['name']);
            if ($keyField === $row['name']) {
                // skip key field to asign value.
            } else if ($assocField === $row['name']) {
                if (!in_array($quatedFieldName, $fieldArray)) {
                    $fieldArray[] = $quatedFieldName;
                    $listArray[] = $this->dbClassObj->link->quote($assocValue);
                }
            } else if (isset($defaultValues[$row['name']])) {
                if (!in_array($quatedFieldName, $fieldArray)) {
                    $fieldArray[] = $quatedFieldName;
                    $listArray[] = $this->dbClassObj->link->quote($defaultValues[$row['name']]);
                }
            } else if ($row['is_identity'] === 1) {

            } else {
                if (!in_array($quatedFieldName, $fieldArray)) {
                    $fieldArray[] = $quatedFieldName;
                    $listArray[] = $this->quotedEntityName($row['name']);
                }
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    /**
     * Returns the quoted entity name.
     *
     * @param $entityName Entity name.
     * @return string|null Quoted entity name.
     */
    public function quotedEntityName($entityName): ?string
    {
        return "{$entityName}";
    }

    /**
     * Optional operation in setup.
     */
    public function optionalOperationInSetup(): void
    {
    }

    /**
     * Checks if the SHA256 hash can be migrated.
     *
     * @param string $userTable User table.
     * @param string $hashTable Hash table.
     * @return array|null Migration result.
     */
    public function authSupportCanMigrateSHA256Hash(string $userTable, string $hashTable): ?array
    {
        $checkFieldDefinition = function (string $type, int $len, int $min): bool {
            $fDef = strtolower($type);
            if ($fDef != 'text' && $fDef === 'varchar') {
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
                    && !$checkFieldDefinition($fieldInfo['type'], $fieldInfo['max_length'], 72)) {
                    $returnValue[] = "The hashedpassword field of the authuser table has to be longer than 72 characters.";
                }
            }
        }
        if ($infoIssuedHash) {
            foreach ($infoIssuedHash as $fieldInfo) {
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] === 'clienthost'
                    && !$checkFieldDefinition($fieldInfo['type'], $fieldInfo['max_length'], 64)) {
                    $returnValue[] = "The clienthost field of the issuedhash table has to be longer than 64 characters.";
                }
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] === 'hash'
                    && !$checkFieldDefinition($fieldInfo['type'], $fieldInfo['max_length'], 64)) {
                    $returnValue[] = "The hash field of the issuedhash table has to be longer than 64 characters.";
                }
            }
        }
        return $returnValue;
    }

    /**
     * Returns the auto-increment field.
     *
     * @param $tableName Table name.
     * @return string|null Auto-increment field.
     */
    protected function getAutoIncrementField($tableName): ?string
    {
        return "unknown";
    }

    /**
     * Returns the SQL LIST DATABASE command for SQL Server.
     *
     * @return string SQL LIST DATABASE command.
     */
    public function sqlLISTDATABASECommand(): string
    {
        // schema generation does not support.
        return '';
    }

    /**
     * Returns the SQL LIST DATABASE column for SQL Server.
     *
     * @return string SQL LIST DATABASE column.
     */
    public function sqlLISTDATABASEColumn(): string
    {
        // schema generation does not support.
        return '';
    }

    /**
     * Returns the SQL CREATE USER command for SQL Server.
     *
     * @param string $dbName Database name.
     * @param string $userEntity User entity.
     * @param string $password Password.
     * @return string SQL CREATE USER command.
     */
    public function sqlCREATEUSERCommand(string $dbName, string $userEntity, string $password): string
    {
        // schema generation does not support.
        return '';
    }
}
/*
SELECT c.name, t.name type, c.max_length, c.precision, c.scale, c.is_nullable, c.is_identity, c.default_object_id, c.is_computed, c.collation_name FROM sys.columns c INNER JOIN sys.types t ON c. system_type_id = t. system_type_id WHERE object_id = object_id('person')
GO
name       type     max_length precision scale is_nullable is_identity default_object_id is_computed collation_name
---------- -------- ---------- --------- ----- ----------- ----------- ----------------- ----------- -----------------------------
memo       text             16         0     0           1           0                 0           0 SQL_Latin1_General_CP1_CI_AS
id         int               4        10     0           0           1                 0           0 NULL
category   int               4        10     0           1           0                 0           0 NULL
checking   int               4        10     0           1           0                 0           0 NULL
location   int               4        10     0           1           0                 0           0 NULL
name       varchar          20         0     0           1           0                 0           0 SQL_Latin1_General_CP1_CI_AS
address    varchar          40         0     0           1           0                 0           0 SQL_Latin1_General_CP1_CI_AS
mail       varchar          40         0     0           1           0                 0           0 SQL_Latin1_General_CP1_CI_AS
(8 rows affected)
1> select name from sys.types;
2> GO
name
-----------------
bigint
binary
bit
char
date
datetime
datetime2
datetimeoffset
decimal
float
geography
geometry
hierarchyid
image
int
money
nchar
ntext
numeric
nvarchar
real
smalldatetime
smallint
smallmoney
sql_variant
sysname
text
time
timestamp
tinyint
uniqueidentifier
varbinary
varchar
xml
*/