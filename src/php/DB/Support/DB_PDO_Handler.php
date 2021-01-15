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

use \Exception;

abstract class DB_PDO_Handler
{
    protected $dbClassObj = null;

    public static function generateHandler($dbObj, $dsn)
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

    public abstract function sqlSELECTCommand();

    public function sqlOrderByCommand($sortClause, $limit, $offset)
    {
        return
            (strlen($sortClause) > 0 ? "ORDER BY {$sortClause} " : "") .
            (strlen($limit) > 0 ? "LIMIT {$limit} " : "") .
            (strlen($offset) > 0 ? "OFFSET {$offset} " : "");
    }

    public abstract function sqlDELETECommand();

    public abstract function sqlUPDATECommand();

    public abstract function sqlINSERTCommand($tableRef, $setClause);

    public function sqlREPLACECommand($tableRef, $setClause)
    {
        return $this->sqlINSERTCommand($tableRef, $setClause);
    }

    public abstract function sqlSETClause($setColumnNames, $keyField, $setValues);

    public function copyRecords($tableInfo, $queryClause, $assocField, $assocValue, $defaultValues)
    {
        $tableName = isset($tableInfo["table"]) ? $tableInfo["table"] : $tableInfo["name"];
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
        } catch (Exception $ex) {
            $this->dbClassObj->errorMessageStore($ex->getMessage());
            return false;
        }
        $seqObject = isset($tableInfo['sequence']) ? $tableInfo['sequence'] : $tableName;
        return $this->dbClassObj->link->lastInsertId($seqObject);
    }


    public abstract function getNullableNumericFields($tableName);

    public abstract function getTimeFields($tableName);

    public abstract function quotedEntityName($entityName);

    public abstract function optionalOperationInSetup();

    protected abstract function getTableInfo($tableName);

    protected abstract function getFieldListsForCopy(
        $tableName, $keyField, $assocField, $assocValue, $defaultValues);
}
