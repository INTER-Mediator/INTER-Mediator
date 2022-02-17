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

class DB_PDO_SQLite_Handler extends DB_PDO_Handler
{
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
        return "UPDATE ";
    }

    public function sqlINSERTCommand($tableRef, $setClause)
    {
        return "INSERT INTO {$tableRef} {$setClause}";
    }

    public function sqlREPLACECommand($tableRef, $setClause)
    {
        return "REPLACE INTO {$tableRef} {$setClause}";
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
        $fieldNameForNullable = 'notnull';
        $fieldArray = array();
        $numericFieldTypes = array('integer', 'real', 'numeric',
            'tinyint', 'smallint', 'mediumint', 'bigint', 'unsigned big int', 'int2', 'int8',
            'double', 'double precision', 'float', 'decimal', 'boolean', 'date', 'datetime',);
        $matches = array();
        foreach ($result as $row) {
            preg_match("/[a-z ]+/", strtolower($row[$this->fieldNameForType]), $matches);
            if (!$row[$fieldNameForNullable] && in_array($matches[0], $numericFieldTypes)) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    public function getTimeFields($tableName)
    {
        /* This isn't work because SQLite doesn't have any Date/Time type. It uses the text or numeric field. */
        try {
            $result = $this->getTableInfo($tableName);
        } catch (Exception $ex) {
            throw $ex;
        }
        $timeFieldTypes = ['datetime', 'time', 'timestamp'];
        $fieldArray = [];
        $matches = [];
        foreach ($result as $row) {
            preg_match("/[a-z]+/", strtolower($row[$this->fieldNameForType]), $matches);
            if (in_array($matches[0], $timeFieldTypes)) {
                $fieldArray[] = $row[$this->fieldNameForField];
            }
        }
        return $fieldArray;
    }

    public function getBooleanFields($tableName)
    {
        return [];
    }

    private $tableInfo = array();
    private $fieldNameForField = 'name';
    private $fieldNameForType = 'type';

    protected function getTableInfo($tableName)
    {
        if (!isset($this->tableInfo[$tableName])) {
            $sql = "PRAGMA table_info({$tableName})";
            $this->dbClassObj->logger->setDebugMessage($sql);
            $result = $this->dbClassObj->link->query($sql);
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
      sqlite> PRAGMA table_info(person);
      cid         name        type        notnull     dflt_value  pk
      ----------  ----------  ----------  ----------  ----------  ----------
      0           id          INTEGER     0                       1
      1           name        TEXT        0                       0
      2           address     TEXT        0                       0
      3           mail        TEXT        0                       0
      4           category    INTEGER     0                       0
      5           checking    INTEGER     0                       0
      6           location    INTEGER     0                       0
      7           memo        TEXT        0                       0
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
            if ($keyField === $row['name'] || !is_null($row['dflt_value'])) {

            } else if ($assocField === $row['name']) {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->dbClassObj->link->quote($assocValue);
            } else if (isset($defaultValues[$row['name']])) {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->dbClassObj->link->quote($defaultValues[$row['name']]);
            } else {
                $fieldArray[] = $this->quotedEntityName($row['name']);
                $listArray[] = $this->quotedEntityName($row['name']);
            }
        }
        return array(implode(',', $fieldArray), implode(',', $listArray));
    }

    public function quotedEntityName($entityName)
    {
        $q = '"';
        if (strpos($entityName, ".") !== false) {
            $components = explode(".", $entityName);
            $quotedName = array();
            foreach ($components as $item) {
                $quotedName[] = $q . str_replace($q, $q . $q, $item ?? "") . $q;
            }
            return implode(".", $quotedName);
        }
        return $q . str_replace($q, $q . $q, $entityName ?? "") . $q;

    }

    public function optionalOperationInSetup()
    {
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
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] == 'hashedpasswd'
                    && !$checkFieldDefinition($fieldInfo['type'], 72)) {
                    $returnValue[] = "The hashedpassword field of the authuser table has to be longer than 72 characters.";
                }
            }
        }
        if ($infoIssuedHash) {
            foreach ($infoIssuedHash as $fieldInfo) {
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] == 'clienthost'
                    && !$checkFieldDefinition($fieldInfo['type'], 64)) {
                    $returnValue[] = "The clienthost field of the issuedhash table has to be longer than 64 characters.";
                }
                if (isset($fieldInfo['name'])
                    && $fieldInfo['name'] == 'hash'
                    && !$checkFieldDefinition($fieldInfo['type'], 64)) {
                    $returnValue[] = "The hash field of the issuedhash table has to be longer than 64 characters.";
                }
            }
        }
        return $returnValue;
    }

}
