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

class DB_FileMaker_FX_Notification_Handler implements DB_Interface_Registering
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

    public function queriedPrimaryKeys()
    {
        return $this->queriedPrimaryKeys;
    }

    public function isExistRequiredTable()
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        if ($regTable == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }

        $this->setupFXforDB($regTable, 1);
        $this->fxResult = $this->fx->DoFxAction('show_all', TRUE, TRUE, 'full');
        if ($this->fxResult['errorCode'] != 0 && $this->fxResult['errorCode'] != 401) {
            $this->errorMessageStore("The table '{$regTable}' doesn't exist in the database.");
            return false;
        }
        return true;
    }

    public function register($clientId, $entity, $condition, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->setupFXforDB($regTable, 1);
        $this->fx->AddDBParam('clientid', $clientId);
        $this->fx->AddDBParam('entity', $entity);
        $this->fx->AddDBParam('conditions', $condition);
        $this->fx->AddDBParam('registereddt', $currentDTFormat);
        $result = $this->fx->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at insert action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        }
        $newContextId = null;
        foreach ($result['data'] as $recmodid => $recordData) {
            foreach ($recordData as $field => $value) {
                if ($field == 'id') {
                    $newContextId = $value[0];
                }
            }
        }
        if (is_array($pkArray)) {
            foreach ($pkArray as $pk) {
                $this->setupFXforDB($pksTable, 1);
                $this->fx->AddDBParam('context_id', $newContextId);
                $this->fx->AddDBParam('pk', $pk);
                $result = $this->fx->DoFxAction('new', TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    $this->logger->setDebugMessage(
                        $this->stringWithoutCredential("FX reports error at insert action: " .
                            "code={$result['errorCode']}, url={$result['URL']}"));
                    $this->errorMessageStore(
                        $this->stringWithoutCredential("FX reports error at insert action: " .
                            "code={$result['errorCode']}, url={$result['URL']}"));
                    return false;
                }
            }
        }
        return $newContextId;
    }

    public function unregister($clientId, $tableKeys)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;

        $this->setupFXforDB($regTable, 'all');
        $this->fx->AddDBParam('clientid', $clientId, 'eq');
        if ($tableKeys) {
            $subCriteria = array();
            foreach ($tableKeys as $regId) {
                $this->fx->AddDBParam('id', $regId, 'eq');
            }
        }
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');

        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        } else {
            if ($result['foundCount'] > 0) {
                $this->setupFXforDB($regTable, '');
                foreach ($result['data'] as $key => $row) {
                    $recId = substr($key, 0, strpos($key, '.'));
                    $this->fx->SetRecordID($recId);
                    $this->fx->DoFxAction('delete', TRUE, TRUE, 'full');
                }
            }
        }

        return true;
    }

    public function matchInRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $originPK = $pkArray[0];
        $this->setupFXforDB($regTable, 'all');
        $this->fx->AddDBParam('clientid', $clientId, 'neq');
        $this->fx->AddDBParam('entity', $entity, 'eq');
        $this->fx->AddSortParam('clientid');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $contextIds = array();
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
        } else {
            if ($result['foundCount'] > 0) {
                foreach ($result['data'] as $recmodid => $recordData) {
                    foreach ($recordData as $field => $value) {
                        if ($field == 'id') {
                            $targetId = $value[0];
                        }
                        if ($field == 'clientid') {
                            $targetClient = $value[0];
                        }
                    }
                    $contextIds[] = array($targetId, $targetClient);
                }
            }
        }

        foreach ($contextIds as $key => $context) {
            $this->setupFXforDB($pksTable, '1');
            $this->fx->AddDBParam('context_id', $context[0], 'eq');
            $this->fx->AddDBParam('pk', $originPK, 'eq');
            $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
                $this->errorMessageStore(
                    $this->stringWithoutCredential("FX reports error at find action: " .
                        "code={$result['errorCode']}, url={$result['URL']}"));
            } else {
                if ($result['foundCount'] > 0) {
                    $targetClients[] = $context[1];
                }
            }
        }

        return array_unique($targetClients);
    }

    public function appendIntoRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;

        $this->setupFXforDB($regTable, 'all');
        $this->fx->AddDBParam('entity', $entity, 'eq');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        } else {
            if ($result['foundCount'] > 0) {
                foreach ($result['data'] as $recmodid => $recordData) {
                    foreach ($recordData as $field => $value) {
                        if ($field == 'id') {
                            $targetId = $value[0];
                        }
                        if ($field == 'clientid') {
                            $targetClients[] = $value[0];
                        }
                    }
                    $this->setupFXforDB($pksTable, 1);
                    $this->fx->AddDBParam('context_id', $targetId);
                    $this->fx->AddDBParam('pk', $pkArray[0]);
                    $result = $this->fx->DoFxAction('new', TRUE, TRUE, 'full');
                    if (!is_array($result)) {
                        $this->errorMessageStore(
                            $this->stringWithoutCredential("FX reports error at insert action: " .
                                "code={$result['errorCode']}, url={$result['URL']}"));
                        return false;
                    }
                    $this->logger->setDebugMessage("Inserted count: " . $result['foundCount'], 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    public function removeFromRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $this->setupFXforDB($regTable, 'all');
        $this->fx->AddDBParam('entity', $entity, 'eq');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $this->logger->setDebugMessage(var_export($result, true));
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        } else {
            if ($result['foundCount'] > 0) {
                foreach ($result['data'] as $recmodid => $recordData) {
                    foreach ($recordData as $field => $value) {
                        if ($field == 'id') {
                            $targetId = $value[0];
                        }
                        if ($field == 'clientid') {
                            $targetClients[] = $value[0];
                        }
                    }
                    $this->setupFXforDB($pksTable, 'all');
                    $this->fx->AddDBParam('context_id', $targetId, 'eq');
                    $this->fx->AddDBParam('pk', $pkArray[0], 'eq');
                    $resultForRemove = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
                    if ($resultForRemove['foundCount'] > 0) {
                        $this->setupFXforDB($pksTable, '');
                        foreach ($resultForRemove['data'] as $key => $row) {
                            $recId = substr($key, 0, strpos($key, '.'));
                            $this->fx->SetRecordID($recId);
                            $this->fx->DoFxAction('delete', TRUE, TRUE, 'full');
                        }
                    }
                    $this->logger->setDebugMessage("Deleted count: " . $resultForRemove['foundCount'], 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }
}