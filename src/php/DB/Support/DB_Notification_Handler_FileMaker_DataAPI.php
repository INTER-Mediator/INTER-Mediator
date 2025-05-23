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
use INTERMediator\DB\FileMaker_DataAPI;

/**
 * Handles notification and registration for FileMaker Data API backend.
 * Implements registration, matching, and removal of registered records using FileMaker as backend.
 * Extends DB_Notification_Common and provides FileMaker-specific logic.
 */
class DB_Notification_Handler_FileMaker_DataAPI extends DB_Notification_Common
{
    /**
     * @var FileMaker_DataAPI FileMaker Data API handler instance.
     */
    protected FileMaker_DataAPI $fmdb;

    /**
     * Constructor.
     *
     * @param FileMaker_DataAPI $parent Parent FileMaker_DataAPI instance.
     */
    public function __construct(FileMaker_DataAPI $parent)
    {
        parent::__construct($parent);
        $this->fmdb = $parent;
    }

    /**
     * FileMaker Data API doesn't have any function to inspect entities in database.
     * So we can't implement the isExistRequiredTable method.
     * This method is used just from NotifyServer class.
     *
     * @return bool Always returns true.
     */
    public function isExistRequiredTable(): bool
    {
        return true;
    }

    /**
     * Registers a new record for a client.
     *
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param string $condition Query condition string.
     * @param array $pkArray Array of primary keys.
     * @return string|null Registration identifier or null on failure.
     * @throws Exception
     */
    public function register(?string $clientId, string $entity, string $condition, array $pkArray): ?string
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->fmdb->setupFMDataAPIforDB($regTable, 1);
        $recordId = $this->fmdb->fmData->{$regTable}->create(array(
            'clientid' => $clientId,
            'entity' => $entity,
            'conditions' => $condition,
            'registereddt' => $currentDTFormat,
        ));
        if (!is_numeric($recordId)) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential(
                    "FMDataAPI reports error at insert action: " .
                    "code={$this->fmdb->fmData->errorCode()}, message={$this->fmdb->fmData->errorMessage()}"
                )
            );
            return null;
        }

        $newContextId = null;
        try {
            $result = $this->fmdb->fmData->{$regTable}->getRecord($recordId);
            foreach ($result as $record) {
                $newContextId = $record->id;
            }
        } catch (Exception $e) {
            return $newContextId;
        }
        foreach ($pkArray as $pk) {
            $this->fmdb->setupFMDataAPIforDB($pksTable, 1);
            $recordId = $this->fmdb->fmData->{$pksTable}->create(array(
                'context_id' => $newContextId,
                'pk' => $pk,
            ));
            if (!is_numeric($recordId)) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential(
                        "FMDataAPI reports error at insert action: " .
                        "code={$this->fmdb->fmData->errorCode()}, message={$this->fmdb->fmData->errorMessage()}"
                    )
                );
                $this->fmdb->errorMessageStore(
                    $this->fmdb->stringWithoutCredential(
                        "FMDataAPI reports error at insert action: " .
                        "code={$this->fmdb->fmData->errorCode()}, message={$this->fmdb->fmData->errorMessage()}"
                    )
                );
                return null;
            }
        }
        return $newContextId;
    }

    /**
     * Unregisters a client.
     *
     * @param string|null $clientId Client identifier.
     * @param array|null $tableKeys Array of table keys.
     * @return bool True on success, false on failure.
     */
    public function unregister(?string $clientId, ?array $tableKeys): bool
    {
        $regTable = $this->dbSettings->registerTableName;

        $this->fmdb->setupFMDataAPIforDB($regTable, 'all');
        $conditions = array('clientid' => $clientId);
        if ($tableKeys) {
            foreach ($tableKeys as $regId) {
                $conditions += array('id' => $regId);
            }
        }
        $conditions = array($conditions);
        try {
            $result = $this->fmdb->fmData->{$regTable}->query($conditions);
            if (!is_null($result) && $result->count() > 0) {
                $this->fmdb->setupFMDataAPIforDB($regTable, '');
                foreach ($result as $record) {
                    $recId = $record->getRecordId();
                    try {
                        $this->fmdb->fmData->{$regTable}->delete($recId);
                    } catch (Exception $e) {
                    }
                }
            }
        } catch (Exception $e) {
        }

        if ($this->fmdb->fmData->errorCode() != 0 &&
            $this->fmdb->fmData->errorCode() != 401) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential(
                    "FMDataAPI reports error at find action: " .
                    "code={$this->fmdb->fmData->errorCode()}, message={$this->fmdb->fmData->errorMessage()}"
                )
            );
            return false;
        }

        return true;
    }

    /**
     * Matches registered records for a client.
     *
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param array $pkArray Array of primary keys.
     * @return array|null Array of matching client identifiers or null on failure.
     */
    public function matchInRegistered(?string $clientId, string $entity, array $pkArray): ?array
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $originPK = $pkArray[0];
        $this->fmdb->setupFMDataAPIforDB($regTable, 'all');
        $conditions = array(array('entity' => $entity), array('clientid' => $clientId, "omit" => "true"));
        $sort = array(array('clientid', 'ascend'));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmData->{$regTable}->query($conditions, $sort);
        } catch (Exception $e) {
        }
        $contextIds = array();
        $targetClients = array();
        if ($this->fmdb->fmData->errorCode() != 0 &&
            $this->fmdb->fmData->errorCode() != 401) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential(
                    "FMDataAPI reports error at find action: " .
                    "code={$this->fmdb->fmData->errorCode()}, message={$this->fmdb->fmData->errorMessage()}"
                )
            );
        } else {
            if ($this->fmdb->fmData->getFoundCount() > 0) {
                foreach ($result as $record) {
                    $targetId = $record->id;
                    $targetClient = $record->clientid;
                    $contextIds[] = array($targetId, $targetClient);
                }
            }
        }

        foreach ($contextIds as $context) {
            $this->fmdb->setupFMDataAPIforDB($pksTable, '1');
            $conditions = array(array('context_id' => $context[0], 'pk' => $originPK));
            try {
                $result = $this->fmdb->fmData->{$pksTable}->query($conditions, NULL, 1, 1);
                if (!is_null($result) && $result->count() > 0) {
                    $targetClients[] = $context[1];
                }
            } catch (Exception $e) {
            }
            if ($this->fmdb->fmData->errorCode() != 0 &&
                $this->fmdb->fmData->errorCode() != 401) {
                $this->fmdb->errorMessageStore(
                    $this->fmdb->stringWithoutCredential(
                        "FMDataAPI reports error at find action: " .
                        "code={$this->fmdb->fmData->errorCode()}, message={$this->fmdb->fmData->errorMessage()}"
                    )
                );
            }
        }

        return array_unique($targetClients);
    }

    /**
     * Appends a new record to the registered records for a client.
     *
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param string $pkField Primary key field name.
     * @param array $pkArray Array of primary keys.
     * @return array|null Array of client identifiers or null on failure.
     * @throws Exception
     */
    public function appendIntoRegistered(?string $clientId, string $entity, string $pkField, array $pkArray): ?array
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;

        $this->fmdb->setupFMDataAPIforDB($regTable, 'all');
        $conditions = array(array('entity' => $entity));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmData->{$regTable}->query($conditions);
        } catch (Exception $e) {
        }
        $targetClients = array();
        if ($this->fmdb->fmData->errorCode() != 0 &&
            $this->fmdb->fmData->errorCode() != 401) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential(
                    "FMDataAPI reports error at find action: " .
                    "code={$this->fmdb->fmData->errorCode()}, message={$this->fmdb->fmData->errorMessage()}"
                )
            );
            return null;
        } else {
            if ($this->fmdb->fmData->getFoundCount() > 0) {
                foreach ($result as $record) {
                    $targetId = $record->id;
                    $targetClients[] = $record->clientid;
                    $this->fmdb->setupFMDataAPIforDB($pksTable, 1);
                    $recordId = $this->fmdb->fmData->{$pksTable}->create(array(
                        'context_id' => $targetId,
                        'pk' => $pkArray[0],
                    ));

                    if (!is_numeric($recordId)) {
                        $this->fmdb->errorMessageStore(
                            $this->fmdb->stringWithoutCredential(
                                "FMDataAPI reports error at insert action: " .
                                "code={$this->fmdb->fmData->errorCode()}, message={$this->fmdb->fmData->errorMessage()}"
                            )
                        );
                        return null;
                    }
                    $this->logger->setDebugMessage("Inserted count: " . $result->count(), 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    /**
     * Removes a record from the registered records for a client.
     *
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param array $pkArray Array of primary keys.
     * @return array|null Array of client identifiers or null on failure.
     */
    public function removeFromRegistered(?string $clientId, string $entity, array $pkArray): ?array
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $this->fmdb->setupFMDataAPIforDB($regTable, 'all');
        $conditions = array(array('entity' => $entity));
        $result = null; // For PHPStan level 1
        try {
            $result = $this->fmdb->fmData->{$regTable}->query($conditions);
            $this->logger->setDebugMessage(var_export($result, true));
        } catch (Exception $e) {
        }
        $targetClients = array();
        if ($this->fmdb->fmData->errorCode() != 0 &&
            $this->fmdb->fmData->errorCode() != 401) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential(
                    "FMDataAPI reports error at find action: " .
                    "code={$this->fmdb->fmData->errorCode()}, message={$this->fmdb->fmData->errorMessage()}"
                )
            );
            return null;
        } else {
            if ($this->fmdb->fmData->getFoundCount() > 0) {
                foreach ($result as $record) {
                    $targetId = $record->id;
                    $targetClients[] = $record->clientid;
                    $this->fmdb->setupFMDataAPIforDB($pksTable, 'all');
                    $conditions = array(array('context_id' => $targetId, 'pk' => $pkArray[0]));
                    try {
                        $resultForRemove = $this->fmdb->fmData->{$pksTable}->query($conditions);
                        if ($resultForRemove->count() > 0) {
                            $this->fmdb->setupFMDataAPIforDB($pksTable, '');
                            foreach ($resultForRemove as $recordForRemove) {
                                $recordId = $recordForRemove->getRecordId();
                                try {
                                    $this->fmdb->fmData->{$pksTable}->delete($recordId);
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