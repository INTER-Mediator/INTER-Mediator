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

class DB_PDO_Notification_Handler implements DB_Interface_Registering
{
    private $queriedEntity = null;
    private $queriedCondition = null;
    private $queriedPrimaryKeys = null;


    public function queriedEntity()
    {
        return $this->queriedEntity;
    }

    public function queriedCondition()
    {
        return $this->queriedCondition;
    }

    public function requireUpdatedRecord($value)
    {
        $this->isRequiredUpdated = $value;
    }

    public function queriedPrimaryKeys()
    {
        return $this->queriedPrimaryKeys;
    }

    public function isExistRequiredTable()
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        if ($regTable == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            $this->errorMessageStore("Can't open db connection.");
            return false;
        }
        $sql = "SELECT id FROM {$regTable} LIMIT 1";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore("The table '{$regTable}' doesn't exist in the database.");
            return false;
        }
        return true;

    }

    public function register($clientId, $entity, $condition, $pkArray)
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $sql = "{$this->handler->sqlINSERTCommand()}{$regTable} (clientid,entity,conditions,registereddt) VALUES("
            . implode(',', array(
                $this->link->quote($clientId),
                $this->link->quote($entity),
                $this->link->quote($condition),
                $this->link->quote($currentDTFormat),
            )) . ')';
        $this->logger->setDebugMessage($sql);
        $result = $this->link->exec($sql);
        if ($result !== 1) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $newContextId = $this->link->lastInsertId("registeredcontext_id_seq");
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0) {
            // SQLite supports multiple records inserting, but it reported error.
            // PDO driver doesn't recognize it, does it ?
            foreach ($pkArray as $pk) {
                $qPk = $this->link->quote($pk);
                $sql = "{$this->handler->sqlINSERTCommand()}{$pksTable} (context_id,pk) VALUES ({$newContextId},{$qPk})";
                $this->logger->setDebugMessage($sql);
                $result = $this->link->exec($sql);
                if ($result < 1) {
                    $this->logger->setDebugMessage($this->link->errorInfo());
                    $this->errorMessageStore('Insert:' . $sql);
                    return false;
                }
            }
        } else {
            $sql = "{$this->handler->sqlINSERTCommand()}{$pksTable} (context_id,pk) VALUES ";
            $isFirstRow = true;
            foreach ($pkArray as $pk) {
                $qPk = $this->link->quote($pk);
                if (!$isFirstRow) {
                    $sql .= ",";
                }
                $sql .= "({$newContextId},{$qPk})";
                $isFirstRow = false;
            }
            $this->logger->setDebugMessage($sql);
            $result = $this->link->exec($sql);
            if ($result < 1) {
                $this->logger->setDebugMessage($this->link->errorInfo());
                $this->errorMessageStore('Insert:' . $sql);
                return false;
            }
        }
        return $newContextId;
    }

    public function unregister($clientId, $tableKeys)
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }

        $criteria = array("clientid=" . $this->link->quote($clientId));
        if ($tableKeys) {
            $subCriteria = array();
            foreach ($tableKeys as $regId) {
                $subCriteria[] = "id=" . $this->link->quote($regId);
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
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Pragma:' . $sql);
                return false;
            }
            $versionSign = explode('.', phpversion());
            if ($versionSign[0] <= 5 && $versionSign[1] <= 2) {
                $sql = "SELECT id FROM {$regTable} WHERE {$criteriaString}";
                $this->logger->setDebugMessage($sql);
                $result = $this->link->query($sql);
                if ($result === false) {
                    $this->errorMessageStore('Select:' . $sql);
                    return false;
                }
                foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $contextIds[] = $row['id'];
                }
            }
        }
        $sql = "{$this->handler->sqlDELETECommand()}FROM {$regTable} WHERE {$criteriaString}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->exec($sql);
        if ($result === false) {
            $this->errorMessageStore('Delete:' . $sql);
            return false;
        }
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0 && count($contextIds) > 0) {
            foreach ($contextIds as $cId) {
                $sql = "{$this->handler->sqlDELETECommand()}FROM {$pksTable} WHERE context_id=" . $this->link->quote($cId);
                $this->logger->setDebugMessage($sql);
                $result = $this->link->exec($sql);
                if ($result === false) {
                    $this->errorMessageStore('Delete:' . $sql);
                    return false;
                }
            }
        }
        return true;
    }

    public function matchInRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $originPK = $pkArray[0];
        $sql = "SELECT DISTINCT clientid FROM " . $pksTable . "," . $regTable . " WHERE " .
            "context_id = id AND clientid <> " . $this->link->quote($clientId) .
            " AND entity = " . $this->link->quote($entity) .
            " AND pk = " . $this->link->quote($originPK) .
            " ORDER BY clientid";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
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
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT id,clientid FROM {$regTable} WHERE entity = " . $this->link->quote($entity);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
            $sql = "{$this->handler->sqlINSERTCommand()}{$pksTable} (context_id,pk) VALUES(" . $this->link->quote($row['id']) .
                "," . $this->link->quote($pkArray[0]) . ")";
            $this->logger->setDebugMessage($sql);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Insert:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("Inserted count: " . $result->rowCount(), 2);
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    public function removeFromRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT id,clientid FROM {$regTable} WHERE entity = " . $this->link->quote($entity);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        $this->logger->setDebugMessage(var_export($result, true));
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
            $sql = "{$this->handler->sqlDELETECommand()}FROM {$pksTable} WHERE context_id = " . $this->link->quote($row['id']) .
                " AND pk = " . $this->link->quote($pkArray[0]);
            $this->logger->setDebugMessage($sql);
            $resultDelete = $this->link->query($sql);
            if ($resultDelete === false) {
                $this->errorMessageStore('Delete:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("Deleted count: " . $resultDelete->rowCount(), 2);
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }
}