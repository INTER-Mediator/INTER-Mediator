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

class DB_PDO_MySQL_Handler extends DB_PDO_Handler
{
    protected $tableInfo = array();
    protected $fieldNameForField = 'Field';
    protected $fieldNameForType = 'Type';
    protected $fieldNameForNullable = 'Null';
    protected $numericFieldTypes = ['int', 'integer', 'numeric', 'smallint', 'tinyint', 'mediumint',
        'bigint', 'decimal', 'float', 'double', 'bit', 'dec', 'fixed', 'double percision', 'year',];
    protected $timeFieldTypes = ['datetime', 'time', 'timestamp'];
    protected $booleanFieldTypes = ['boolean', 'bool'];

    public function sqlSELECTCommand()
    {
        return "SELECT ";
    }

    public function sqlLimitCommand($param)
    {
        return "LIMIT {$param}";
    }

    public function sqlOffsetCommand($param)
    {
        return "OFFSET {$param}";
    }

    public function sqlDELETECommand()
    {
        return "DELETE FROM ";
    }

    public function sqlUPDATECommand()
    {
        return "UPDATE IGNORE ";
    }

    public function sqlINSERTCommand($tableRef, $setClause)
    {
        return "INSERT IGNORE INTO {$tableRef} {$setClause}";
    }

    public function sqlREPLACECommand($tableRef, $setClause)
    {
        return "REPLACE INTO {$tableRef} {$setClause}";
    }

    public function sqlSETClause($tableName, $setColumnNames, $keyField, $setValues)
    {
        [$setNames, $setValuesConv] = $this->sqlSETClauseData($tableName, $setColumnNames, $keyField, $setValues);
        return (count($setColumnNames) == 0) ? "SET {$keyField}=DEFAULT" :
            '(' . implode(',', $setNames) . ') VALUES(' . implode(',', $setValuesConv) . ')';
    }

    public function getNullableFields($tableName)
    {
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $fieldArray = [];
        foreach ($result as $row) {
            if ($row[$this->fieldNameForNullable] == "YES") {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    protected function getTalbeInfoSQL($tableName)
    {
        return "SHOW COLUMNS FROM " . $this->quotedEntityName($tableName);
    }

    /*
      * mysql> show columns from func;
+-------+------------------------------+------+-----+---------+-------+
| Field | Type                         | Null | Key | Default | Extra |
+-------+------------------------------+------+-----+---------+-------+
| name  | char(64)                     | NO   | PRI |         |       |
| ret   | tinyint(1)                   | NO   |     | 0       |       |
| dl    | char(128)                    | NO   |     |         |       |
| type  | enum('function','aggregate') | NO   |     | NULL    |       |
+-------+------------------------------+------+-----+---------+-------+
4 rows in set (0.00 sec)

    In case of calculation field of a vew, the type column is going to be ''.
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
            if ($keyField === $row['Field'] || !is_null($row['Default'])) {
                // skip key field to assign value.
            } else if ($assocField === $row['Field']) {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else if (isset($defaultValues[$row['Field']])) {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->dbClassObj->link->quote($defaultValues[$row['Field']]);
            } else {
                $fieldArray[] = $this->quotedEntityName($row['Field']);
                $listArray[] = $this->quotedEntityName($row['Field']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }


    public function quotedEntityName($entityName)
    {
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

    public function optionalOperationInSetup()
    {
        $this->dbClassObj->link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    public function authSupportCanMigrateSHA256Hash($userTable, $hashTable)  // authuser, issuedhash
    {
        $checkFieldDefinition = function ($type, $min) {
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
}
