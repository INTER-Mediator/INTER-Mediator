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

/**
 * Handles notification and registration for PDO-based databases.
 * Implements registration, matching, and removal of registered records using PDO as backend.
 * Extends DB_Notification_Common and provides PDO-specific logic.
 */
class DB_Notification_Handler_PDO extends DB_Notification_Common
{
    /** @var \INTERMediator\DB\PDO PDO database handler instance.
     */
    protected \INTERMediator\DB\PDO $pdoDB;

    /** Constructor.
     * @param \INTERMediator\DB\PDO $parent Parent PDO instance.
     */
    public function __construct(\INTERMediator\DB\PDO $parent)
    {
        parent::__construct($parent);
        $this->pdoDB = $parent;
    }

    /** Checks if the required table for registration exists.
     * @return bool True if the required table exists, false otherwise.
     */
    public function isExistRequiredTable(): bool
    {
        $regTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerTableName);
        if (is_null($regTable)) {
            $this->pdoDB->errorMessageStore("[DB_Notification_Handler_PDO] The table doesn't specified.");
            return false;
        }
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            $this->pdoDB->errorMessageStore("[DB_Notification_Handler_PDO] Can't open db connection.");
            return false;
        }
        $sql = $this->pdoDB->handler->sqlSELECTCommand() . "id FROM {$regTable} " .
            $this->pdoDB->handler->sqlOrderByCommand("id", 1, 0);
        //$sql = "SELECT id FROM {$regTable} LIMIT 1";
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] Checking the table exists: {$sql}");
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore("[DB_Notification_Handler_PDO] The table '{$regTable}' doesn't exist in the database.");
            return false;
        }
        return true;

    }

    /** Registers a new record for a client.
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param string $condition Query condition string.
     * @param array $pkArray Array of primary keys.
     * @return string|null Registration identifier or null on failure.
     */
    public function register(?string $clientId, string $entity, string $condition, array $pkArray): ?string
    {
        $regTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }
        $currentDTFormat = IMUtil::currentDTString();

        // Delete outdated records from registereddt
        $limitDT = new DateTime();
        $backSeconds = Params::getParameterValue("backSeconds", 3600 * 24 * 2);
        $limitDT->sub(new DateInterval("PT{$backSeconds}S"));
        $limitDT = $this->pdoDB->link->quote($limitDT->format('Y-m-d H:i:s'));
        $sql = "{$this->pdoDB->handler->sqlDELETECommand()}{$regTable} "
            . "WHERE {$this->pdoDB->handler->quotedEntityName('registereddt')} < {$limitDT}";
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->pdoDB->link->exec($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore("[DB_Notification_Handler_PDO][ERROR] Delete:{$sql}");
            return null;
        }

        // Register displaying records to registereddt
        $tableRef = "{$regTable} (clientid,entity,conditions,registereddt)";
        $setArray = implode(',', array_map(function ($e) {
            return $this->pdoDB->link->quote($e);
        }, [$clientId, $entity, $condition, $currentDTFormat]));
        $sql = $this->pdoDB->handler->sqlINSERTCommand($tableRef, "VALUES ({$setArray})");
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->pdoDB->link->exec($sql);
        if ($result !== 1) {
            $this->pdoDB->errorMessageStore("[DB_Notification_Handler_PDO][ERROR] Insert: {$sql}");
            return null;
        }
        if (strpos($this->dbSettings->getDbSpecDSN(), 'pgsql:') === 0) {
            $newContextId = $this->pdoDB->link->lastInsertId("registeredcontext_id_seq");
        } else {
            $newContextId = $this->pdoDB->link->lastInsertId();
        }
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0) {
            // SQLite supports multiple records inserting, but it reported error.
            // PDO driver doesn't recognize it, does it ?
            foreach ($pkArray as $pk) {
                $tableRef = "{$pksTable} (context_id,pk)";
                $setClause = "VALUES({$newContextId},{$this->pdoDB->link->quote($pk)})";
                $sql = $this->pdoDB->handler->sqlINSERTCommand($tableRef, $setClause);
                $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
                $result = $this->pdoDB->link->exec($sql);
                if ($result < 1) {
                    $this->logger->setDebugMessage("[DB_Notification_Handler_PDO][ERROR] "
                        . var_export($this->pdoDB->link->errorInfo(), true));
                    $this->pdoDB->errorMessageStore("[DB_Notification_Handler_PDO][ERROR] Insert: {$sql}");
                    return null;
                }
            }
        } else {
            $sql = $this->pdoDB->handler->sqlINSERTCommand("{$pksTable} (context_id,pk)", "VALUES ");
            $isFirstRow = true;
            foreach ($pkArray as $pk) {
                if (!$isFirstRow) {
                    $sql .= ",";
                }
                $sql .= "({$newContextId},{$this->pdoDB->link->quote($pk)})";
                $isFirstRow = false;
            }
            if (!$isFirstRow) {
                $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
                $result = $this->pdoDB->link->exec($sql);
                if ($result < 1) {
                    $this->logger->setDebugMessage("[DB_Notification_Handler_PDO][ERROR] "
                        . var_export($this->pdoDB->link->errorInfo(), true));
                    $this->pdoDB->errorMessageStore("[DB_Notification_Handler_PDO][ERROR] Insert: {$sql}");
                    return null;
                }
            }
        }
        return $newContextId;
    }

    /** Unregisters a client from the database.
     * @param string|null $clientId Client identifier.
     * @param array|null $tableKeys Array of table keys.
     * @return bool True on success, false on failure.
     */
    public function unregister(?string $clientId, ?array $tableKeys): bool
    {
        $regTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerTableName);
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return false;
        }

        $criteria = ["clientid = " . $this->pdoDB->link->quote($clientId ?? '')];
        if ($tableKeys) {
            $subCriteria = [];
            foreach ($tableKeys as $regId) {
                if ($regId) {
                    $subCriteria[] = "id = " . $this->pdoDB->link->quote($regId);
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
            $result = $this->pdoDB->link->query($sql);
            if ($result === false) {
                $this->pdoDB->errorMessageStore("[DB_Notification_Handler_PDO] Pragma:{$sql}");
                return false;
            }
        }
        $sql = "{$this->pdoDB->handler->sqlDELETECommand()}{$regTable} WHERE {$criteriaString}";
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->pdoDB->link->exec($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore("Delete:{$sql}");
            return false;
        }
        return true;
    }

    /** Finds matching registered records for a client.
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param array $pkArray Array of primary keys.
     * @return array|null Array of matching client identifiers or null on failure.
     */
    public function matchInRegistered(?string $clientId, string $entity, array $pkArray): ?array
    {
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] matchInRegistered / clientId={$clientId}, entity={$entity}, pkArray=" . var_export($pkArray, true));

        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }
        if (!isset($pkArray[0])) {
            return [];
        }
        $originPK = $pkArray[0];
        $regTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        $extraCond = " AND clientid <> {$this->pdoDB->link->quote($clientId ?? '')}";
        $entityValue = $this->pdoDB->link->quote($entity);
        $pkValue = $this->pdoDB->link->quote($originPK);
        $sql = "SELECT DISTINCT clientid FROM {$pksTable},{$regTable} WHERE context_id = id {$extraCond}"
            . " AND entity = {$entityValue} AND pk = {$pkValue} ORDER BY clientid";
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore("ERROR in SELECT: {$sql}");
            return null;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
        }
        return array_unique($targetClients);
    }

    /** Appends a new record to the registered records for a client.
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param string $pkField Primary key field name.
     * @param array $pkArray Array of primary keys.
     * @return array|null Array of matching client identifiers or null on failure.
     */
    public function appendIntoRegistered(?string $clientId, string $entity, string $pkField, array $pkArray): ?array
    {
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] appendIntoRegistered / clientId={$clientId}, entity={$entity}, pkField={$pkField}, pkArray=" . var_export($pkArray, true));
        //$this->logger->setDebugMessage("[DB_Notification_Handler_PDO] contextDef=" . var_export($this->dbSettings->getDataSourceTargetArray(), true));

        $regTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerPKTableName);
//        $contextDef = $this->dbSettings->getDataSourceTargetArray();
        if (!$pkField) {
            $this->pdoDB->errorMessageStore("The entity {$entity} doesn't have the 'key'.");
            return null;
        }
//        $keyField = $contextDef['key'];
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }
        if (!$pkArray || !isset($pkArray[0])) {
            return null;
        }
        $sql = "SELECT id,clientid,conditions FROM {$regTable} WHERE entity = " . $this->pdoDB->link->quote($entity);
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore("ERROR in SELECT:{$sql}");
            return null;
        }
        $targetClients = [];
        $conditionToContent = [];
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {

//            $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] row=" . var_export($row, true));

            if (!isset($conditionToContent[$row['conditions']])) {
                $sql = "SELECT {$pkField} FROM {$row['conditions']}";
                $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
                $resultContent = $this->pdoDB->link->query($sql);
                if ($resultContent === false) {
                    $this->pdoDB->errorMessageStore("ERROR in SELECT:{$sql}");
                    return null;
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
                $setClause = "VALUES({$this->pdoDB->link->quote($row['id'])},{$this->pdoDB->link->quote($pkArray[0])})";
                $sql = $this->pdoDB->handler->sqlINSERTCommand($tableRef, $setClause);
                $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
                $result = $this->pdoDB->link->query($sql);
                if ($result === false) {
                    $this->pdoDB->errorMessageStore("[DB_Notification_Handler_PDO] Insert: {$sql}");
                    return null;
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    /** Removes a record from the registered records for a client.
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param array $pkArray Array of primary keys.
     * @return array|null Array of matching client identifiers or null on failure.
     */
    public function removeFromRegistered(?string $clientId, string $entity, array $pkArray): ?array
    {
        $regTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->pdoDB->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->pdoDB->setupConnection()) { //Establish the connection
            return null;
        }
        $sql = "SELECT id,clientid FROM {$regTable} WHERE entity = " . $this->pdoDB->link->quote($entity);
        $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
        $result = $this->pdoDB->link->query($sql);
        if ($result === false) {
            $this->pdoDB->errorMessageStore("ERROR in SELECT:{$sql}");
            return null;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
            $sql = "{$this->pdoDB->handler->sqlDELETECommand()}{$pksTable} WHERE context_id = "
                . $this->pdoDB->link->quote($row['id']);
            if ($pkArray && isset($pkArray[0])) {
                $sql .= " and pk = " . $this->pdoDB->link->quote($pkArray[0]);
            }
            $this->logger->setDebugMessage("[DB_Notification_Handler_PDO] {$sql}");
            $resultDelete = $this->pdoDB->link->query($sql);
            if ($resultDelete === false) {
                $this->pdoDB->errorMessageStore("Delete:{$sql}");
                return null;
            }
            $this->logger->setDebugMessage("Deleted count: {$resultDelete->rowCount()}", 2);
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }
}
