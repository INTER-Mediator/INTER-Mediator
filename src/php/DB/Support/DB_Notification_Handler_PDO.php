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

use INTERMediator\IMUtil;
use INTERMediator\Params;
use PDO;
use DateTime;
use DateInterval;

class DB_Notification_Handler_PDO extends DB_Notification_Common implements DB_Interface_Registering
{
    public function isExistRequiredTable(): bool
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        if ($regTable == null) {
            $this->dbClass->errorMessageStore("[DB_Notification_Handler_PDO] The table doesn't specified.");
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            $this->dbClass->errorMessageStore("[DB_Notification_Handler_PDO] Can't open db connection.");
            return false;
        }
        $sql = $this->dbClass->handler->sqlSELECTCommand() . "id FROM {$regTable} " .
            $this->dbClass->handler->sqlOrderByCommand("id", 1, 0);
        //$sql = "SELECT id FROM {$regTable} LIMIT 1";
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore("[DB_Notification_Handler_PDO] The table '{$regTable}' doesn't exist in the database.");
            return false;
        }
        return true;

    }

    public function register($clientId, $entity, $condition, $pkArray) /*: bool|int|string */
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();

        // Delete outdated records from registereddt
        $limitDT = new DateTime();
        $backSeconds = Params::getParameterValue("backSeconds", 3600 * 24 * 2);
        $limitDT->sub(new DateInterval("PT{$backSeconds}S"));
        $limitDT = $this->dbClass->link->quote($limitDT->format('Y-m-d H:i:s'));
        $sql = "{$this->dbClass->handler->sqlDELETECommand()}{$regTable} "
            . "WHERE {$this->dbClass->handler->quotedEntityName('registereddt')} < {$limitDT}";
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->dbClass->link->exec($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore("[DB_Notification_Handler_PDO][ERROR] Delete:{$sql}");
            return false;
        }

        // Register displaying records to registereddt
        $tableRef = "{$regTable} (clientid,entity,conditions,registereddt)";
        $setArray = implode(',', array_map(function ($e) {
            return $this->dbClass->link->quote($e);
        }, [$clientId, $entity, $condition, $currentDTFormat]));
        $sql = $this->dbClass->handler->sqlINSERTCommand($tableRef, "VALUES ({$setArray})");
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->dbClass->link->exec($sql);
        if ($result !== 1) {
            $this->dbClass->errorMessageStore("[DB_Notification_Handler_PDO][ERROR] Insert: {$sql}");
            return false;
        }
        if (strpos($this->dbSettings->getDbSpecDSN(), 'pgsql:') === 0) {
            $newContextId = $this->dbClass->link->lastInsertId("registeredcontext_id_seq");
        } else {
            $newContextId = $this->dbClass->link->lastInsertId();
        }
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0) {
            // SQLite supports multiple records inserting, but it reported error.
            // PDO driver doesn't recognize it, does it ?
            foreach ($pkArray as $pk) {
                $tableRef = "{$pksTable} (context_id,pk)";
                $setClause = "VALUES({$newContextId},{$this->dbClass->link->quote($pk)})";
                $sql = $this->dbClass->handler->sqlINSERTCommand($tableRef, $setClause);
                $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
                $result = $this->dbClass->link->exec($sql);
                if ($result < 1) {
                    $this->logger->setDebugMessage("[DB_Notification_Handler_PDO][ERROR] {$this->dbClass->link->errorInfo()}");
                    $this->dbClass->errorMessageStore("[DB_Notification_Handler_PDO][ERROR] Insert: {$sql}");
                    return false;
                }
            }
        } else {
            $sql = $this->dbClass->handler->sqlINSERTCommand("{$pksTable} (context_id,pk)", "VALUES ");
            $isFirstRow = true;
            foreach ($pkArray as $pk) {
                if (!$isFirstRow) {
                    $sql .= ",";
                }
                $sql .= "({$newContextId},{$this->dbClass->link->quote($pk)})";
                $isFirstRow = false;
            }
            if (!$isFirstRow) {
                $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
                $result = $this->dbClass->link->exec($sql);
                if ($result < 1) {
                    $this->logger->setDebugMessage("[DB_Notification_Handler_PDO][ERROR] {$this->dbClass->link->errorInfo()}");
                    $this->dbClass->errorMessageStore("[DB_Notification_Handler_PDO][ERROR] Insert: {$sql}");
                    return false;
                }
            }
        }
        return $newContextId;
    }

    public function unregister($clientId, $tableKeys)
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }

        $criteria = ["clientid = " . $this->dbClass->link->quote($clientId)];
        if ($tableKeys) {
            $subCriteria = [];
            foreach ($tableKeys as $regId) {
                if ($regId) {
                    $subCriteria[] = "id = " . $this->dbClass->link->quote($regId);
                }
            }
            if (count($subCriteria) > 0) {
                $criteria[] = "(" . implode(" or ", $subCriteria) . ")";
            }
        }
        $criteriaString = implode(" and ", $criteria);

        // SQLite initially doesn't support delete cascade. To support it,
        // the PRAGMA statement as below should be executed. But PHP 5.2 doens't
        // work, so it must delete
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0) {
            $sql = "PRAGMA foreign_keys = ON";
            $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
            $result = $this->dbClass->link->query($sql);
            if ($result === false) {
                $this->dbClass->errorMessageStore("[DB_Notification_Handler_PDO] Pragma:{$sql}");
                return false;
            }
        }
        $sql = "{$this->dbClass->handler->sqlDELETECommand()}{$regTable} WHERE {$criteriaString}";
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->dbClass->link->exec($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore("Delete:{$sql}");
            return false;
        }
        return true;
    }

    public function matchInRegistered($clientId, $entity, $pkArray)
    {
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] matchInRegistered / clientId={$clientId}, entity={$entity}, pkArray=" . var_export($pkArray, true));

        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        if (!isset($pkArray[0])) {
            return [];
        }
        $originPK = $pkArray[0];
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        $extraCond = (!is_null($clientId)) ? " AND clientid <> {$this->dbClass->link->quote($clientId)}" : "";
        $sql = "SELECT DISTINCT clientid FROM {$pksTable},{$regTable} WHERE context_id = id {$extraCond}"
            . " AND entity = {$this->dbClass->link->quote($entity)} AND pk = {$this->dbClass->link->quote($originPK)}"
            . " ORDER BY clientid";
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore("Select: {$sql}");
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
        }
        return array_unique($targetClients);
    }

    public function appendIntoRegistered($clientId, $entity, $pkField, $pkArray)
    {
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] appendIntoRegistered / clientId={$clientId}, entity={$entity}, pkField={$pkField}, pkArray=" . var_export($pkArray, true));
        //$this->logger->setDebugMessage("[DB_Notification_Handler_PDO] contextDef=" . var_export($this->dbSettings->getDataSourceTargetArray(), true));

        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerPKTableName);
//        $contextDef = $this->dbSettings->getDataSourceTargetArray();
        if (!$pkField) {
            $this->dbClass->errorMessageStore("The entity {$entity} doesn't have the 'key'.");
            return false;
        }
//        $keyField = $contextDef['key'];
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        if (!$pkArray || !isset($pkArray[0])) {
            return false;
        }
        $sql = "SELECT id,clientid,conditions FROM {$regTable} WHERE entity = " . $this->dbClass->link->quote($entity);
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore("Select:{$sql}");
            return false;
        }
        $targetClients = [];
        $conditionToContent = [];
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {

//            $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] row=" . var_export($row, true));

            if (!isset($conditionToContent[$row['conditions']])) {
                $sql = "SELECT {$pkField} FROM {$row['conditions']}";
                $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
                $resultContent = $this->dbClass->link->query($sql);
                if ($resultContent === false) {
                    $this->dbClass->errorMessageStore("Select:{$sql}");
                    return false;
                }
                $conditionToContent[$row['conditions']] = [];
                foreach ($resultContent->fetchAll(PDO::FETCH_ASSOC) as $rowContent) {
                    $conditionToContent[$row['conditions']][] = $rowContent[$pkField];
                }
            }

            // $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] content=" . var_export($conditionToContent[$row['conditions']], true));

            if (in_array($pkArray[0], $conditionToContent[$row['conditions']])) {
                $targetClients[] = $row['clientid'];
                $tableRef = "{$pksTable} (context_id,pk)";
                $setClause = "VALUES({$this->dbClass->link->quote($row['id'])},{$this->dbClass->link->quote($pkArray[0])})";
                $sql = $this->dbClass->handler->sqlINSERTCommand($tableRef, $setClause);
                $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
                $result = $this->dbClass->link->query($sql);
                if ($result === false) {
                    $this->dbClass->errorMessageStore("[DB_Notification_Handler_PDO] Insert: {$sql}");
                    return false;
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    public function removeFromRegistered($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT id,clientid FROM {$regTable} WHERE entity = " . $this->dbClass->link->quote($entity);
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore("Select:{$sql}");
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
            $sql = "{$this->dbClass->handler->sqlDELETECommand()}{$pksTable} WHERE context_id = "
                . $this->dbClass->link->quote($row['id']);
            if ($pkArray && isset($pkArray[0])) {
                $sql .= " and pk = " . $this->dbClass->link->quote($pkArray[0]);
            }
            $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
            $resultDelete = $this->dbClass->link->query($sql);
            if ($resultDelete === false) {
                $this->dbClass->errorMessageStore("Delete:{$sql}");
                return false;
            }
            $this->logger->setDebugMessage("Deleted count: {$resultDelete->rowCount()}", 2);
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }
}
