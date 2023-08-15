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
use INTERMediator\DB\FileMaker_FX;

/**
 *
 */
class DB_Notification_Handler_FileMaker_FX
    extends DB_Notification_Common
    implements DB_Interface_Registering
{
    /**
     * @var FileMaker_FX
     */
    protected FileMaker_FX $fmdb;

    /**
     * @param $parent
     */
    public function __construct($parent)
    {
        parent::__construct($parent);
        $this->fmdb = $parent;
    }

    /**
     * @return bool
     */
    public function isExistRequiredTable(): bool
    {
        $regTable = $this->dbSettings->registerTableName;
        if ($regTable == null) {
            $this->fmdb->errorMessageStore("The table doesn't specified.");
            return false;
        }

        $this->fmdb->setupFXforDB($regTable, 1);
        $fxResult = $this->fmdb->fx->DoFxAction('show_all', TRUE, TRUE, 'full');
        if ($fxResult['errorCode'] != 0 && $fxResult['errorCode'] != 401) {
            $this->fmdb->errorMessageStore("The table '{$regTable}' doesn't exist in the database.");
            return false;
        }
        return true;
    }

    /**
     * @param string|null $clientId
     * @param string $entity
     * @param string $condition
     * @param array $pkArray
     * @return string|null
     */
    public function register(?string $clientId, string $entity, string $condition, array $pkArray): ?string
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->fmdb->setupFXforDB($regTable, 1);
        $this->fmdb->fx->AddDBParam('clientid', $clientId);
        $this->fmdb->fx->AddDBParam('entity', $entity);
        $this->fmdb->fx->AddDBParam('conditions', $condition);
        $this->fmdb->fx->AddDBParam('registereddt', $currentDTFormat);
        $result = $this->fmdb->fx->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential("FX reports error at insert action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return null;
        }
        $newContextId = null;
        foreach ($result['data'] as $recordData) {
            foreach ($recordData as $field => $value) {
                if ($field == 'id') {
                    $newContextId = $value[0];
                }
            }
        }
        foreach ($pkArray as $pk) {
            $this->fmdb->setupFXforDB($pksTable, 1);
            $this->fmdb->fx->AddDBParam('context_id', $newContextId);
            $this->fmdb->fx->AddDBParam('pk', $pk);
            $result = $this->fmdb->fx->DoFxAction('new', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(
                    $this->fmdb->stringWithoutCredential("FX reports error at insert action: " .
                        "code={$result['errorCode']}, url={$result['URL']}"));
                $this->fmdb->errorMessageStore(
                    $this->fmdb->stringWithoutCredential("FX reports error at insert action: " .
                        "code={$result['errorCode']}, url={$result['URL']}"));
                return null;
            }
        }
        return $newContextId;
    }

    /**
     * @param string|null $clientId
     * @param array|null $tableKeys
     * @return bool
     */
    public function unregister(?string $clientId, ?array $tableKeys): bool
    {
        $regTable = $this->dbSettings->registerTableName;

        $this->fmdb->setupFXforDB($regTable, 'all');
        $this->fmdb->fx->AddDBParam('clientid', $clientId, 'eq');
        if ($tableKeys) {
            foreach ($tableKeys as $regId) {
                $this->fmdb->fx->AddDBParam('id', $regId, 'eq');
            }
        }
        $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');

        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        } else {
            if ($result['foundCount'] > 0) {
                $this->fmdb->setupFXforDB($regTable, '');
                foreach ($result['data'] as $key => $row) {
                    $recId = substr($key, 0, strpos($key, '.'));
                    $this->fmdb->fx->SetRecordID($recId);
                    $this->fmdb->fx->DoFxAction('delete', TRUE, TRUE, 'full');
                }
            }
        }
        return true;
    }

    /**
     * @param string|null $clientId
     * @param string $entity
     * @param array $pkArray
     * @return array|null
     */
    public function matchInRegistered(?string $clientId, string $entity, array $pkArray): ?array
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $originPK = $pkArray[0];
        $this->fmdb->setupFXforDB($regTable, 'all');
        $this->fmdb->fx->AddDBParam('clientid', $clientId, 'neq');
        $this->fmdb->fx->AddDBParam('entity', $entity, 'eq');
        $this->fmdb->fx->AddSortParam('clientid');
        $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $contextIds = array();
        $targetId = null;
        $targetClient = null;
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
        } else {
            if ($result['foundCount'] > 0) {
                foreach ($result['data'] as $recordData) {
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

        foreach ($contextIds as $context) {
            $this->fmdb->setupFXforDB($pksTable, '1');
            $this->fmdb->fx->AddDBParam('context_id', $context[0], 'eq');
            $this->fmdb->fx->AddDBParam('pk', $originPK, 'eq');
            $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
                $this->fmdb->errorMessageStore(
                    $this->fmdb->stringWithoutCredential("FX reports error at find action: " .
                        "code={$result['errorCode']}, url={$result['URL']}"));
            } else {
                if ($result['foundCount'] > 0) {
                    $targetClients[] = $context[1];
                }
            }
        }

        return array_unique($targetClients);
    }

    /**
     * @param string|null $clientId
     * @param string $entity
     * @param string $pkField
     * @param array $pkArray
     * @return array|null
     */
    public function appendIntoRegistered(?string $clientId, string $entity, string $pkField, array $pkArray): ?array
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;

        $this->fmdb->setupFXforDB($regTable, 'all');
        $this->fmdb->fx->AddDBParam('entity', $entity, 'eq');
        $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $targetClients = array();
        $targetId = null; // For PHPStan level 1
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return null;
        } else {
            if ($result['foundCount'] > 0) {
                foreach ($result['data'] as $recordData) {
                    foreach ($recordData as $field => $value) {
                        if ($field == 'id') {
                            $targetId = $value[0];
                        }
                        if ($field == 'clientid') {
                            $targetClients[] = $value[0];
                        }
                    }
                    $this->fmdb->setupFXforDB($pksTable, 1);
                    $this->fmdb->fx->AddDBParam('context_id', $targetId);
                    $this->fmdb->fx->AddDBParam('pk', $pkArray[0]);
                    $result = $this->fmdb->fx->DoFxAction('new', TRUE, TRUE, 'full');
                    if (!is_array($result)) {
                        $this->fmdb->errorMessageStore(
                            $this->fmdb->stringWithoutCredential("FX reports error at insert action: " .
                                "code={$result['errorCode']}, url={$result['URL']}"));
                        return null;
                    }
                    $this->logger->setDebugMessage("Inserted count: " . $result['foundCount'], 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    /**
     * @param string|null $clientId
     * @param string $entity
     * @param array $pkArray
     * @return array|null
     */
    public function removeFromRegistered(?string $clientId, string $entity, array $pkArray): ?array
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $this->fmdb->setupFXforDB($regTable, 'all');
        $this->fmdb->fx->AddDBParam('entity', $entity, 'eq');
        $result = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $this->logger->setDebugMessage(var_export($result, true));
        $targetClients = array();
        $targetId = null; // For PHPStan level 1
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->fmdb->errorMessageStore(
                $this->fmdb->stringWithoutCredential("FX reports error at find action: " .
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return null;
        } else {
            if ($result['foundCount'] > 0) {
                foreach ($result['data'] as $recordData) {
                    foreach ($recordData as $field => $value) {
                        if ($field == 'id') {
                            $targetId = $value[0];
                        }
                        if ($field == 'clientid') {
                            $targetClients[] = $value[0];
                        }
                    }
                    $this->fmdb->setupFXforDB($pksTable, 'all');
                    $this->fmdb->fx->AddDBParam('context_id', $targetId, 'eq');
                    $this->fmdb->fx->AddDBParam('pk', $pkArray[0], 'eq');
                    $resultForRemove = $this->fmdb->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
                    if ($resultForRemove['foundCount'] > 0) {
                        $this->fmdb->setupFXforDB($pksTable, '');
                        foreach ($resultForRemove['data'] as $key => $row) {
                            $recId = substr($key, 0, strpos($key, '.'));
                            $this->fmdb->fx->SetRecordID($recId);
                            $this->fmdb->fx->DoFxAction('delete', TRUE, TRUE, 'full');
                        }
                    }
                    $this->logger->setDebugMessage("Deleted count: " . $resultForRemove['foundCount'], 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }
}