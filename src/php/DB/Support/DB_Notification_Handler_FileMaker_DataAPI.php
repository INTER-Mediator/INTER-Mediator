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

use DateTime;
use Exception;

class DB_Notification_Handler_FileMaker_DataAPI
    extends DB_Notification_Common
    implements DB_Interface_Registering
{
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
        $this->dbClass->setupFMDataAPIforDB($regTable, 1);
        $recordId = $this->dbClass->fmData->{$regTable}->create(array(
            'clientid' => $clientId,
            'entity' => $entity,
            'conditions' => $condition,
            'registereddt' => $currentDTFormat,
        ));
        if (!is_numeric($recordId)) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential(
                    "FMDataAPI reports error at insert action: " .
                    "code={$this->dbClass->fmData->errorCode()}, message={$this->dbClass->fmData->errorMessage()}"
                )
            );
            return false;
        }

        $newContextId = null;
        try {
            $result = $this->dbClass->fmData->{$regTable}->getRecord($recordId);
            foreach ($result as $record) {
                $newContextId = $record->id;
            }
        } catch (Exception $e) {
            return $newContextId;
        }
        if (is_array($pkArray)) {
            foreach ($pkArray as $pk) {
                $this->dbClass->setupFMDataAPIforDB($pksTable, 1);
                $recordId = $this->dbClass->fmData->{$pksTable}->create(array(
                    'context_id' => $newContextId,
                    'pk' => $pk,
                ));
                if (!is_numeric($recordId)) {
                    $this->logger->setDebugMessage(
                        $this->dbClass->stringWithoutCredential(
                            "FMDataAPI reports error at insert action: " .
                            "code={$this->dbClass->fmData->errorCode()}, message={$this->dbClass->fmData->errorMessage()}"
                        )
                    );
                    $this->dbClass->errorMessageStore(
                        $this->dbClass->stringWithoutCredential(
                            "FMDataAPI reports error at insert action: " .
                            "code={$this->dbClass->fmData->errorCode()}, message={$this->dbClass->fmData->errorMessage()}"
                        )
                    );
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

        $this->dbClass->setupFMDataAPIforDB($regTable, 'all');
        $conditions = array('clientid' => $clientId);
        if ($tableKeys) {
            $subCriteria = array();
            foreach ($tableKeys as $regId) {
                $conditions += array('id' => $regId);
            }
        }
        $conditions = array($conditions);
        try {
            $result = $this->dbClass->fmData->{$regTable}->query($conditions);
            if (!is_null($result) && $result->count() > 0) {
                $this->dbClass->setupFMDataAPIforDB($regTable, '');
                foreach ($result as $record) {
                    $recId = $record->getRecordId();
                    try {
                        $result = $this->dbClass->fmData->{$regTable}->delete($recId);
                    } catch (Exception $e) {
                    }
                }
            }
        } catch (Exception $e) {
        }

        if ($this->dbClass->fmData->errorCode() != 0 &&
            $this->dbClass->fmData->errorCode() != 401) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential(
                    "FMDataAPI reports error at find action: " .
                    "code={$this->dbClass->fmData->errorCode()}, message={$this->dbClass->fmData->errorMessage()}"
                )
            );
            return false;
        }

        return true;
    }

    public function matchInRegistered($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $originPK = $pkArray[0];
        $this->dbClass->setupFMDataAPIforDB($regTable, 'all');
        $conditions = array(array('entity' => $entity), array('clientid' => $clientId, "omit" => "true"));
        $sort = array(array('clientid', 'ascend'));
        try {
            $result = $this->dbClass->fmData->{$regTable}->query($conditions, $sort);
        } catch (Exception $e) {
        }
        $contextIds = array();
        $targetClients = array();
        if ($this->dbClass->fmData->errorCode() != 0 &&
            $this->dbClass->fmData->errorCode() != 401) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential(
                    "FMDataAPI reports error at find action: " .
                    "code={$this->dbClass->fmData->errorCode()}, message={$this->dbClass->fmData->errorMessage()}"
                )
            );
        } else {
            if ($this->dbClass->fmData->getFoundCount() > 0) {
                foreach ($result as $record) {
                    $targetId = $record->id;
                    $targetClient = $record->clientid;
                    $contextIds[] = array($targetId, $targetClient);
                }
            }
        }

        foreach ($contextIds as $key => $context) {
            $this->dbClass->setupFMDataAPIforDB($pksTable, '1');
            $conditions = array(array('context_id' => $context[0], 'pk' => $originPK));
            try {
                $result = $this->dbClass->fmData->{$pksTable}->query($conditions, NULL, 1, 1);
                if (!is_null($result) && $result->count() > 0) {
                    $targetClients[] = $context[1];
                }
            } catch (Exception $e) {
            }
            if ($this->dbClass->fmData->errorCode() != 0 &&
                $this->dbClass->fmData->errorCode() != 401) {
                $this->dbClass->errorMessageStore(
                    $this->dbClass->stringWithoutCredential(
                        "FMDataAPI reports error at find action: " .
                        "code={$this->dbClass->fmData->errorCode()}, message={$this->dbClass->fmData->errorMessage()}"
                    )
                );
            }
        }

        return array_unique($targetClients);
    }

    public function appendIntoRegistered($clientId, $entity, $pkField, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;

        $this->dbClass->setupFMDataAPIforDB($regTable, 'all');
        $conditions = array(array('entity' => $entity));
        try {
            $result = $this->dbClass->fmData->{$regTable}->query($conditions);
        } catch (Exception $e) {
        }
        $targetClients = array();
        if ($this->dbClass->fmData->errorCode() != 0 &&
            $this->dbClass->fmData->errorCode() != 401) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential(
                    "FMDataAPI reports error at find action: " .
                    "code={$this->dbClass->fmData->errorCode()}, message={$this->dbClass->fmData->errorMessage()}"
                )
            );
            return false;
        } else {
            if ($this->dbClass->fmData->getFoundCount() > 0) {
                foreach ($result as $record) {
                    $targetId = $record->id;
                    $targetClients[] = $record->clientid;
                    $this->dbClass->setupFMDataAPIforDB($pksTable, 1);
                    $recordId = $this->dbClass->fmData->{$pksTable}->create(array(
                        'context_id' => $targetId,
                        'pk' => $pkArray[0],
                    ));

                    if (!is_numeric($recordId)) {
                        $this->dbClass->errorMessageStore(
                            $this->dbClass->stringWithoutCredential(
                                "FMDataAPI reports error at insert action: " .
                                "code={$this->dbClass->fmData->errorCode()}, message={$this->dbClass->fmData->errorMessage()}"
                            )
                        );
                        return false;
                    }
                    $this->logger->setDebugMessage("Inserted count: " . $result->count(), 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    public function removeFromRegistered($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $this->dbClass->setupFMDataAPIforDB($regTable, 'all');
        $conditions = array(array('entity' => $entity));
        try {
            $result = $this->dbClass->fmData->{$regTable}->query($conditions);
            $this->logger->setDebugMessage(var_export($result, true));
        } catch (Exception $e) {
        }
        $targetClients = array();
        if ($this->dbClass->fmData->errorCode() != 0 &&
            $this->dbClass->fmData->errorCode() != 401) {
            $this->dbClass->errorMessageStore(
                $this->dbClass->stringWithoutCredential(
                    "FMDataAPI reports error at find action: " .
                    "code={$this->dbClass->fmData->errorCode()}, message={$this->dbClass->fmData->errorMessage()}"
                )
            );
            return false;
        } else {
            if ($this->dbClass->fmData->getFoundCount() > 0) {
                foreach ($result as $record) {
                    $targetId = $record->id;
                    $targetClients[] = $record->clientid;
                    $this->dbClass->setupFMDataAPIforDB($pksTable, 'all');
                    $conditions = array(array('context_id' => $targetId, 'pk' => $pkArray[0]));
                    try {
                        $resultForRemove = $this->dbClass->fmData->{$pksTable}->query($conditions);
                        if ($resultForRemove->count() > 0) {
                            $this->dbClass->setupFMDataAPIforDB($pksTable, '');
                            foreach ($resultForRemove as $recordForRemove) {
                                $recordId = $recordForRemove->getRecordId();
                                try {
                                    $this->dbClass->fmData->{$pksTable}->delete($recordId);
                                } catch (Exception $e) {
                                }
                            }
                        }
                        $this->logger->setDebugMessage("Deleted count: " . $resultForRemove->count(), 2);
                    } catch (Exception $e) {
                    }
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }
}