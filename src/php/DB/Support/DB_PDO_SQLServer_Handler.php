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
 *
 */
class DB_PDO_SQLServer_Handler extends DB_PDO_Handler
{
    /**
     * @var array
     */
    protected array $tableInfo = array();
    /**
     * @var string
     */
    public string $fieldNameForField = 'name';
    /**
     * @var string
     */
    protected string $fieldNameForType = 'type';
    /**
     * @var string
     */
    protected string $fieldNameForNullable = 'is_nullable';
    /**
     * @var array|string[]
     */
    protected array $numericFieldTypes = array('bigint', 'bit', 'decimal', 'float', 'hierarchyid',
        'int', 'money', 'numeric', 'real', 'smallint', 'smallmoney', 'tinyint',);
    /**
     * @var array|string[]
     */
    protected array $timeFieldTypes = ['datetime', 'datetime2', 'datetimeoffset', 'time', 'smalldatetime'];
    /**
     * @var array|string[]
     */
    protected array $dateFieldTypes = ['date', 'datetimeoffset', 'smalldatetime'];
    /**
     * @var array
     */
    protected array $booleanFieldTypes = [];

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
    public function dateResetForNotNull(): string
    {
        return '1000-01-01';
    }

    /**
     * @param string $sortClause
     * @param string $limit
     * @param string $offset
     * @return string
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
     * @return string
     */
    public function sqlDELETECommand(): string
    {
        return "DELETE ";
    }

    /**
     * @return string
     */
    public function sqlUPDATECommand(): string
    {
        return "UPDATE ";
    }

    /**
     * @param string $tableRef
     * @param string $setClause
     * @return string
     */
    public function sqlINSERTCommand(string $tableRef, string $setClause): string
    {
        return "INSERT INTO {$tableRef} {$setClause}";
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
        return (count($setColumnNames) === 0) ? "DEFAULT VALUES" :
            '(' . implode(',', $setNames) . ') VALUES(' . implode(',', $setValuesConv) . ')';
    }

    /**
     * @param string $info
     * @return bool
     */
    protected function checkNullableField(string $info): bool
    {
        return $info === 0;
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getTableInfoSQL(string $tableName): string
    {
        $fields = "c.name, t.name type, c.max_length, c.precision, c.scale, c.is_nullable, " .
            "c.is_identity, c.default_object_id, c.is_computed, c.collation_name";
        return "SELECT {$fields} FROM sys.columns c INNER JOIN sys.types t ON c. system_type_id = t. system_type_id " .
            "WHERE object_id = object_id('{$this->quotedEntityName($tableName)}')";
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
            $quatedFieldName = $this->quotedEntityName($row['name']);
            if ($keyField === $row['name'] || $row['is_identity'] === 1) {
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
     * @param $entityName
     * @return string|null
     */
    public function quotedEntityName($entityName): ?string
    {
        return "{$entityName}";
    }

    /**
     * @return void
     */
    public function optionalOperationInSetup(): void
    {
    }


    /**
     * @param string $userTable
     * @param string $hashTable
     * @return array|null
     */
    public function authSupportCanMigrateSHA256Hash(string $userTable, string $hashTable): ?array  // authuser, issuedhash
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
     * @param $tableName
     * @return string|null
     */
    protected function getAutoIncrementField($tableName): ?string
    {
        return "unknown";
    }


    public function sqlLISTDATABASECommand(): string
    {
        // schema generation does not support.
        return '';
    }

    public function sqlLISTDATABASEColumn(): string
    {
        // schema generation does not support.
        return '';
    }

    public function sqlCREATEUSERCommand(string $dbName, string $userEntity, string $password): string
    {
        // schema generation does not support.
        return '';
    }
}
