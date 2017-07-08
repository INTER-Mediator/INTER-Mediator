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

class DB_FileMaker_DataAPI_Notification_Handler implements DB_Interface_Registering
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

    /*
     * FileMaker Data API doesn't have any function to inspect entities in database.
     * So we can't implement the isExistRequiredTable method.
     * This method is used just from NotifyServer class.
     * Masayuki Nii 2017-07-08
     */
    public function isExistRequiredTable()
    {
        return true;
    }

    public function register($clientId, $entity, $condition, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->setupFMDataAPIforDB($regTable, 1);
        $this->fmData->AddDBParam('clientid', $clientId);
        $this->fmData->AddDBParam('entity', $entity);
        $this->fmData->AddDBParam('conditions', $condition);
        $this->fmData->AddDBParam('registereddt', $currentDTFormat);
        $result = $this->fmData->DoFxAction('new', TRUE, TRUE, 'full');
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
                $this->setupFMDataAPIforDB($pksTable, 1);
                $this->fmData->AddDBParam('context_id', $newContextId);
                $this->fmData->AddDBParam('pk', $pk);
                $result = $this->fmData->DoFxAction('new', TRUE, TRUE, 'full');
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

        $this->setupFMDataAPIforDB($regTable, 'all');
        $this->fmData->AddDBParam('clientid', $clientId, 'eq');
        if ($tableKeys) {
            $subCriteria = array();
            foreach ($tableKeys as $regId) {
                $this->fmData->AddDBParam('id', $regId, 'eq');
            }
        }
        $result = $this->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');

        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        } else {
            if ($result['foundCount'] > 0) {
                $this->setupFMDataAPIforDB($regTable, '');
                foreach ($result['data'] as $key => $row) {
                    $recId = substr($key, 0, strpos($key, '.'));
                    $this->fmData->SetRecordID($recId);
                    $this->fmData->DoFxAction('delete', TRUE, TRUE, 'full');
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
        $this->setupFMDataAPIforDB($regTable, 'all');
        $this->fmData->AddDBParam('clientid', $clientId, 'neq');
        $this->fmData->AddDBParam('entity', $entity, 'eq');
        $this->fmData->AddSortParam('clientid');
        $result = $this->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
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
            $this->setupFMDataAPIforDB($pksTable, '1');
            $this->fmData->AddDBParam('context_id', $context[0], 'eq');
            $this->fmData->AddDBParam('pk', $originPK, 'eq');
            $result = $this->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
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

        $this->setupFMDataAPIforDB($regTable, 'all');
        $this->fmData->AddDBParam('entity', $entity, 'eq');
        $result = $this->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
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
                    $this->setupFMDataAPIforDB($pksTable, 1);
                    $this->fmData->AddDBParam('context_id', $targetId);
                    $this->fmData->AddDBParam('pk', $pkArray[0]);
                    $result = $this->fmData->DoFxAction('new', TRUE, TRUE, 'full');
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
        $this->setupFMDataAPIforDB($regTable, 'all');
        $this->fmData->AddDBParam('entity', $entity, 'eq');
        $result = $this->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
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
                    $this->setupFMDataAPIforDB($pksTable, 'all');
                    $this->fmData->AddDBParam('context_id', $targetId, 'eq');
                    $this->fmData->AddDBParam('pk', $pkArray[0], 'eq');
                    $resultForRemove = $this->fmData->DoFxAction('perform_find', TRUE, TRUE, 'full');
                    if ($resultForRemove['foundCount'] > 0) {
                        $this->setupFMDataAPIforDB($pksTable, '');
                        foreach ($resultForRemove['data'] as $key => $row) {
                            $recId = substr($key, 0, strpos($key, '.'));
                            $this->fmData->SetRecordID($recId);
                            $this->fmData->DoFxAction('delete', TRUE, TRUE, 'full');
                        }
                    }
                    $this->logger->setDebugMessage("Deleted count: " . $resultForRemove['foundCount'], 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }
}