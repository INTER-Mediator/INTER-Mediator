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

class DB_PDO_SQLServer_Handler extends DB_PDO_Handler
{
    public function sqlSELECTCommand()
    {
        return "SELECT ";
    }

    public function sqlOrderByCommand($sortClause, $limit, $offset)
    {
        if ($sortClause == '') {
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

    public function sqlDELETECommand()
    {
        return "DELETE ";
    }

    public function sqlUPDATECommand()
    {
        return "UPDATE ";
    }

    public function sqlINSERTCommand($tableRef, $setClause)
    {
        return "INSERT INTO {$tableRef} {$setClause}";
    }

    public function sqlSETClause($setColumnNames, $keyField, $setValues)
    {
        $setNames = array_map(function ($value) {
            return $this->quotedEntityName($value);
        }, $setColumnNames);
        return (count($setColumnNames) == 0) ? "DEFAULT VALUES" :
            '(' . implode(',', $setNames) . ') VALUES(' . implode(',', $setValues) . ')';
    }

    public function getNullableNumericFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldNameForNullable = 'is_nullable';
        $fieldArray = array();
        $numericFieldTypes = array('bigint', 'bit', 'date', 'datetime', 'datetime2', 'decimal',
            'float', 'hierarchyid', 'int', 'money', 'numeric', 'real', 'smalldatetime', 'smallint',
            'smallmoney', 'time', 'timestamp', 'tinyint',);
        $matches = array();
        foreach ($result as $row) {
            preg_match("/[a-z]+/", strtolower($row[$this->fieldNameForType]), $matches);
            if ($row[$fieldNameForNullable] && in_array($matches[0], $numericFieldTypes)) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    public function getTimeFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $timeFieldTypes = ['datetime', 'datetime2', 'datetimeoffset', 'time', 'smaldatetime'];
        $fieldArray = [];
        foreach ($result as $row) {
            if (in_array(strtolower($row[$this->fieldNameForType]), $timeFieldTypes)) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }


    private $tableInfo = array();
    private $fieldNameForField = 'name';
    private $fieldNameForType = 'type';

    protected function getTableInfo($tableName)
    {
        if (!isset($this->tableInfo[$tableName])) {
            $fields = "c.name, t.name type, c.max_length, c.precision, c.scale, c.is_nullable, " .
                "c.is_identity, c.default_object_id, c.is_computed, c.collation_name";
            $sql = "SELECT {$fields} FROM sys.columns c INNER JOIN sys.types t ON c. system_type_id = t. system_type_id " .
                "WHERE object_id = object_id('{$this->quotedEntityName($tableName)}')";
            $this->dbClassObj->logger->setDebugMessage($sql);
            $result = $this->dbClassObj->link->query($sql);
            $this->tableInfo[$tableName] = $result;
            if (!$result) {
                throw new Exception('INSERT Error:' . $sql);
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

    protected function getFieldListsForCopy($tableName, $keyField, $assocField, $assocValue, $defaultValues)
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
                if (array_search($quatedFieldName, $fieldArray) === FALSE) {
                    $fieldArray[] = $quatedFieldName;
                    $listArray[] = $this->dbClassObj->link->quote($assocValue);
                }
            } else if (isset($defaultValues[$row['name']])) {
                if (array_search($quatedFieldName, $fieldArray) === FALSE) {
                    $fieldArray[] = $quatedFieldName;
                    $listArray[] = $this->dbClassObj->link->quote($defaultValues[$row['name']]);
                }
            } else {
                if (array_search($quatedFieldName, $fieldArray) === FALSE) {
                    $fieldArray[] = $quatedFieldName;
                    $listArray[] = $this->quotedEntityName($row['name']);
                }
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    public function quotedEntityName($entityName)
    {
        return "{$entityName}";
    }

    public function optionalOperationInSetup()
    {
    }


    public function authSupportCanMigrateSHA256Hash($userTable, $hashTable)  // authuser, issuedhash
    {
        $checkFieldDefinition = function ($type, $len, $min) {
            $fDef = strtolower($type);
            if ($fDef != 'text' && $fDef == 'varchar') {
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
                    && $fieldInfo['name'] == 'hashedpasswd'
                    && !$checkFieldDefinition($fieldInfo['type'], $fieldInfo['max_length'], 72)) {
                    $returnValue[] = "The hashedpassword field of the authuser table has to be longer than 72 characters.";
                }
            }
        }
        if ($infoIssuedHash) {
            foreach ($infoIssuedHash as $fieldInfo) {
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] == 'clienthost'
                    && !$checkFieldDefinition($fieldInfo['type'], $fieldInfo['max_length'], 64)) {
                    $returnValue[] = "The clienthost field of the issuedhash table has to be longer than 64 characters.";
                }
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] == 'hash'
                    && !$checkFieldDefinition($fieldInfo['type'], $fieldInfo['max_length'], 64)) {
                    $returnValue[] = "The hash field of the issuedhash table has to be longer than 64 characters.";
                }
            }
        }
        return $returnValue;
    }
}
