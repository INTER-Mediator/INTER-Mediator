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
use \DateTime;

class DB_Notification_Handler_FileMaker_FX
    extends DB_Notification_Common
    implements DB_Interface_Registering
{
    public function isExistRequiredTable()
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        if ($regTable == null) {
            $this->dbClass->errorMessageStore("The table doesn't specified.");
            return false;
        }

        $this->dbClass->setupFXforDB($regTable, 1);
        $this->dbClass->fxResult = $this->dbClass->fx->DoFxAction('show_all', TRUE, TRUE, 'full');
        if ($this->dbClass->fxResult['errorCode'] != 0 && $this->dbClass->fxResult['errorCode'] != 401) {
            $this->dbClass->errorMessageStore("The table '{$regTable}' doesn't exist in the database.");
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
        $this->dbClass->setupFXforDB($regTable, 1);
        $this->dbClass->fx->AddDBParam('clientid', $clientId);
        $this->dbClass->fx->AddDBParam('entity', $entity);
        $this->dbClass->fx->AddDBParam('conditions', $condition);
        $this->dbClass->fx->AddDBParam('registereddt', $currentDTFormat);
        $result = $this->dbClass->fx->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential("FX reports error at insert action: " .
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
                $this->dbClass->setupFXforDB($pksTable, 1);
                $this->dbClass->fx->AddDBParam('context_id', $newContextId);
                $this->dbClass->fx->AddDBParam('pk', $pk);
                $result = $this->dbClass->fx->DoFxAction('new', TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    $this->logger->setDebugMessage(
                        $this->dbClass->stringWithoutCredential("FX reports error at insert action: " .
                            "code={$result['errorCode']}, url={$result['URL']}"));
                    $this->dbClass->errorMessageStore(
                        $this->dbClass->stringWithoutCredential("FX reports error at insert action: " .
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

        $this->dbClass->setupFXforDB($regTable, 'all');
        $this->dbClass->fx->AddDBParam('clientid', $clientId, 'eq');
        if ($tableKeys) {
            foreach ($tableKeys as $regId) {
                $this->dbClass->fx->AddDBParam('id', $regId, 'eq');
            }
        }
        $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');

        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        } else {
            if ($result['foundCount'] > 0) {
                $this->dbClass->setupFXforDB($regTable, '');
                foreach ($result['data'] as $key => $row) {
                    $recId = substr($key, 0, strpos($key, '.'));
                    $this->dbClass->fx->SetRecordID($recId);
                    $this->dbClass->fx->DoFxAction('delete', TRUE, TRUE, 'full');
                }
            }
        }
        return true;
    }

    public function matchInRegistered($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $originPK = $pkArray[0];
        $this->dbClass->setupFXforDB($regTable, 'all');
        $this->dbClass->fx->AddDBParam('clientid', $clientId, 'neq');
        $this->dbClass->fx->AddDBParam('entity', $entity, 'eq');
        $this->dbClass->fx->AddSortParam('clientid');
        $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $contextIds = array();
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential("FX reports error at find action: " .
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
            $this->dbClass->setupFXforDB($pksTable, '1');
            $this->dbClass->fx->AddDBParam('context_id', $context[0], 'eq');
            $this->dbClass->fx->AddDBParam('pk', $originPK, 'eq');
            $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
                $this->dbClass->errorMessageStore(
                    $this->dbClass->stringWithoutCredential("FX reports error at find action: " .
                        "code={$result['errorCode']}, url={$result['URL']}"));
            } else {
                if ($result['foundCount'] > 0) {
                    $targetClients[] = $context[1];
                }
            }
        }

        return array_unique($targetClients);
    }

    public function appendIntoRegistered($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;

        $this->dbClass->setupFXforDB($regTable, 'all');
        $this->dbClass->fx->AddDBParam('entity', $entity, 'eq');
        $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential("FX reports error at find action: " .
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
                    $this->dbClass->setupFXforDB($pksTable, 1);
                    $this->dbClass->fx->AddDBParam('context_id', $targetId);
                    $this->dbClass->fx->AddDBParam('pk', $pkArray[0]);
                    $result = $this->dbClass->fx->DoFxAction('new', TRUE, TRUE, 'full');
                    if (!is_array($result)) {
                        $this->dbClass->errorMessageStore(
                            $this->dbClass->stringWithoutCredential("FX reports error at insert action: " .
                                "code={$result['errorCode']}, url={$result['URL']}"));
                        return false;
                    }
                    $this->logger->setDebugMessage("Inserted count: " . $result['foundCount'], 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    public function removeFromRegistered($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $this->dbClass->setupFXforDB($regTable, 'all');
        $this->dbClass->fx->AddDBParam('entity', $entity, 'eq');
        $result = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $this->logger->setDebugMessage(var_export($result, true));
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential("FX reports error at find action: " .
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
                    $this->dbClass->setupFXforDB($pksTable, 'all');
                    $this->dbClass->fx->AddDBParam('context_id', $targetId, 'eq');
                    $this->dbClass->fx->AddDBParam('pk', $pkArray[0], 'eq');
                    $resultForRemove = $this->dbClass->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
                    if ($resultForRemove['foundCount'] > 0) {
                        $this->dbClass->setupFXforDB($pksTable, '');
                        foreach ($resultForRemove['data'] as $key => $row) {
                            $recId = substr($key, 0, strpos($key, '.'));
                            $this->dbClass->fx->SetRecordID($recId);
                            $this->dbClass->fx->DoFxAction('delete', TRUE, TRUE, 'full');
                        }
                    }
                    $this->logger->setDebugMessage("Deleted count: " . $resultForRemove['foundCount'], 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }
}