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
class DB_Notification_Handler_PDO
    extends DB_Notification_Common
    implements DB_Interface_Registering
{
    public function isExistRequiredTable()
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        if ($regTable == null) {
            $this->dbClass->errorMessageStore("The table doesn't specified.");
            return false;
        }
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            $this->dbClass->errorMessageStore("Can't open db connection.");
            return false;
        }
        $sql = "SELECT id FROM {$regTable} LIMIT 1";
        $this->logger->setDebugMessage($sql);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore("The table '{$regTable}' doesn't exist in the database.");
            return false;
        }
        return true;

    }

    public function register($clientId, $entity, $condition, $pkArray)
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$regTable} (clientid,entity,conditions,registereddt) VALUES("
            . implode(',', array(
                $this->dbClass->link->quote($clientId),
                $this->dbClass->link->quote($entity),
                $this->dbClass->link->quote($condition),
                $this->dbClass->link->quote($currentDTFormat),
            )) . ')';
        $this->logger->setDebugMessage($sql);
        $result = $this->dbClass->link->exec($sql);
        if ($result !== 1) {
            $this->dbClass->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $newContextId = $this->dbClass->link->lastInsertId("registeredcontext_id_seq");
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0) {
            // SQLite supports multiple records inserting, but it reported error.
            // PDO driver doesn't recognize it, does it ?
            foreach ($pkArray as $pk) {
                $qPk = $this->dbClass->link->quote($pk);
                $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$pksTable} (context_id,pk) VALUES ({$newContextId},{$qPk})";
                $this->logger->setDebugMessage($sql);
                $result = $this->dbClass->link->exec($sql);
                if ($result < 1) {
                    $this->logger->setDebugMessage($this->dbClass->link->errorInfo());
                    $this->dbClass->errorMessageStore('Insert:' . $sql);
                    return false;
                }
            }
        } else {
            $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$pksTable} (context_id,pk) VALUES ";
            $isFirstRow = true;
            foreach ($pkArray as $pk) {
                $qPk = $this->dbClass->link->quote($pk);
                if (!$isFirstRow) {
                    $sql .= ",";
                }
                $sql .= "({$newContextId},{$qPk})";
                $isFirstRow = false;
            }
            $this->logger->setDebugMessage($sql);
            $result = $this->dbClass->link->exec($sql);
            if ($result < 1) {
                $this->logger->setDebugMessage($this->dbClass->link->errorInfo());
                $this->dbClass->errorMessageStore('Insert:' . $sql);
                return false;
            }
        }
        return $newContextId;
    }

    public function unregister($clientId, $tableKeys)
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }

        $criteria = array("clientid=" . $this->dbClass->link->quote($clientId));
        if ($tableKeys) {
            $subCriteria = array();
            foreach ($tableKeys as $regId) {
                $subCriteria[] = "id=" . $this->dbClass->link->quote($regId);
            }
            $criteria[] = "(" . implode(" OR ", $subCriteria) . ")";
        }
        $criteriaString = implode(" AND ", $criteria);

        $contextIds = array();
        // SQLite initially doesn't support delete cascade. To support it,
        // the PRAGMA statement as below should be executed. But PHP 5.2 doens't
        // work, so it must delete
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0) {
            $sql = "PRAGMA foreign_keys = ON";
            $this->logger->setDebugMessage($sql);
            $result = $this->dbClass->link->query($sql);
            if ($result === false) {
                $this->dbClass->errorMessageStore('Pragma:' . $sql);
                return false;
            }
            $versionSign = explode('.', phpversion());
            if ($versionSign[0] <= 5 && $versionSign[1] <= 2) {
                $sql = "SELECT id FROM {$regTable} WHERE {$criteriaString}";
                $this->logger->setDebugMessage($sql);
                $result = $this->dbClass->link->query($sql);
                if ($result === false) {
                    $this->dbClass->errorMessageStore('Select:' . $sql);
                    return false;
                }
                foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $contextIds[] = $row['id'];
                }
            }
        }
        $sql = "{$this->dbClass->handler->sqlDELETECommand()}FROM {$regTable} WHERE {$criteriaString}";
        $this->logger->setDebugMessage($sql);
        $result = $this->dbClass->link->exec($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Delete:' . $sql);
            return false;
        }
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0 && count($contextIds) > 0) {
            foreach ($contextIds as $cId) {
                $sql = "{$this->dbClass->handler->sqlDELETECommand()}FROM {$pksTable} WHERE context_id=" . $this->dbClass->link->quote($cId);
                $this->logger->setDebugMessage($sql);
                $result = $this->dbClass->link->exec($sql);
                if ($result === false) {
                    $this->dbClass->errorMessageStore('Delete:' . $sql);
                    return false;
                }
            }
        }
        return true;
    }

    public function matchInRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $originPK = $pkArray[0];
        $sql = "SELECT DISTINCT clientid FROM " . $pksTable . "," . $regTable . " WHERE " .
            "context_id = id AND clientid <> " . $this->dbClass->link->quote($clientId) .
            " AND entity = " . $this->dbClass->link->quote($entity) .
            " AND pk = " . $this->dbClass->link->quote($originPK) .
            " ORDER BY clientid";
        $this->logger->setDebugMessage($sql);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
        }
        return array_unique($targetClients);
    }

    public function appendIntoRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT id,clientid FROM {$regTable} WHERE entity = " . $this->dbClass->link->quote($entity);
        $this->logger->setDebugMessage($sql);
        $result = $this->dbClass->link->query($sql);
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
            $sql = "{$this->dbClass->handler->sqlINSERTCommand()}{$pksTable} (context_id,pk) VALUES(" . $this->dbClass->link->quote($row['id']) .
                "," . $this->dbClass->link->quote($pkArray[0]) . ")";
            $this->logger->setDebugMessage($sql);
            $result = $this->dbClass->link->query($sql);
            if ($result === false) {
                $this->dbClass->errorMessageStore('Insert:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("Inserted count: " . $result->rowCount(), 2);
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    public function removeFromRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->dbClass->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->dbClass->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT id,clientid FROM {$regTable} WHERE entity = " . $this->dbClass->link->quote($entity);
        $this->logger->setDebugMessage($sql);
        $result = $this->dbClass->link->query($sql);
        $this->logger->setDebugMessage(var_export($result, true));
        if ($result === false) {
            $this->dbClass->errorMessageStore('Select:' . $sql);
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
            $sql = "{$this->dbClass->handler->sqlDELETECommand()}FROM {$pksTable} WHERE context_id = " . $this->dbClass->link->quote($row['id']) .
                " AND pk = " . $this->dbClass->link->quote($pkArray[0]);
            $this->logger->setDebugMessage($sql);
            $resultDelete = $this->dbClass->link->query($sql);
            if ($resultDelete === false) {
                $this->dbClass->errorMessageStore('Delete:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("Deleted count: " . $resultDelete->rowCount(), 2);
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }
}