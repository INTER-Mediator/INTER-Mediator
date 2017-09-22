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

require_once("DB_PDO_MySQL_Handler.php");
require_once("DB_PDO_PostgreSQL_Handler.php");
require_once("DB_PDO_SQLite_Handler.php");

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
        }
        return null;
    }

    public abstract function sqlSELECTCommand();

    public abstract function sqlDELETECommand();

    public abstract function sqlUPDATECommand();

    public abstract function sqlINSERTCommand();

    public abstract function sqlSETClause($setColumnNames, $keyField, $setValues);

    public function copyRecords($tableInfo, $queryClause, $assocField, $assocValue)
    {
        $tableName = isset($tableInfo["table"]) ? $tableInfo["table"] : $tableInfo["name"];
        try {
            list($fieldList, $listList) = $this->getFieldLists(
                $tableName, $tableInfo['key'], $assocField, $assocValue);
            $sql = "{$this->sqlINSERTCommand()}{$tableName} ({$fieldList}) " .
                "SELECT {$listList} FROM {$tableName} WHERE {$queryClause}";
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

    public abstract function quotedEntityName($entityName);

    public abstract function optionalOperationInSetup();

    protected abstract function getTableInfo($tableName);

    protected abstract function getFieldLists($tableName, $keyField, $assocField, $assocValue);
}