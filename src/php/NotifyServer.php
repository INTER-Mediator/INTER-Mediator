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

namespace INTERMediator;

use INTERMediator\DB\DBClass;
use INTERMediator\DB\Logger;
use INTERMediator\DB\Support\DB_Interface_Registering;

/**
 * NotifyServer handles client registration and notification triggers for data changes.
 * It interacts with the database notification handler and the service server proxy to
 * propagate, create, update, and delete events to registered clients.
 */
class NotifyServer
{
    /** The notification handler of the database class. It is null until the initialization succeeds.
     * @var DB_Interface_Registering|null
     */
    private ?DB_Interface_Registering $handler = null;
    /** The client ID associated with the notification session.
     * @var string|null
     */
    private ?string $clientId = null;

    /** Initializes the NotifyServer with a DBClass and client ID.
     * Checks if the notification handler and required table exist.
     * @param DBClass $dbClass Database class instance.
     * @param string|null $clientId Client ID for this session.
     * @return bool True if initialization successful, false otherwise.
     */
    public function initialize(DBClass $dbClass, ?string $clientId): bool
    {
        $this->handler = null;
        $this->clientId = $clientId;
        $handler = $dbClass->notifyHandler;
        if (!($handler instanceof DB_Interface_Registering) || !$handler->isExistRequiredTable()) {
            return false;
        }
        $this->handler = $handler;
        return true;
    }

    /**
     * Triggers a notification event to the service server for the specified channels and data.
     * @param array<array-key, mixed>|null $channels Array of client IDs to notify. Null means the failure of matching.
     * @param string $operation Operation type (e.g., 'update', 'create', 'delete').
     * @param array<array-key, mixed> $data Data describing the change.
     * @return void
     */
    private function trigger(?array $channels, string $operation, array $data): void
    {
        $logger = Logger::getInstance();
        if (empty($channels)) {
            $logger->setDebugMessage("[NotifyServer] trigger / No channels to notify, operation={$operation}", 2);
            return;
        }
        $logger->setDebugMessage(str_replace("\n", "", "[NotifyServer] trigger / channels="
            . var_export($channels, true) . ", operation={$operation}, data=" . var_export($data, true)), 2);

        $ssInstance = ServiceServerProxy::instance();
        $ssInstance->clearMessages();
        $ssInstance->clearErrors();
        $ssInstance->sync($channels, $operation, $data);
        $logger->setDebugMessages($ssInstance->getMessages());
        $logger->setErrorMessages($ssInstance->getErrors());
    }

    /**
     * Registers a client for notifications on a given entity and condition.
     * @param string $entity Entity name to register for.
     * @param string $condition Condition for registration.
     * @param array<array-key, mixed> $pkArray Primary key values for the entity.
     * @return string|null Registration result or null on failure.
     */
    public function register(string $entity, string $condition, array $pkArray): ?string
    {
        Logger::getInstance()->setDebugMessage("[NotifyServer] register", 2);
        return $this->handler?->register($this->clientId, $entity, $condition, $pkArray);
    }

    /**
     * Unregisters a client from notifications for specific table keys.
     * @param string|null $client Client ID to unregister.
     * @param array<array-key, mixed>|null $tableKeys Table keys to unregister.
     * @return bool True if successfully unregistered, false otherwise.
     */
    public function unregister(?string $client, ?array $tableKeys): bool
    {
        Logger::getInstance()->setDebugMessage("[NotifyServer] unregister", 2);
        if (!is_null($tableKeys)) {
            $tableKeys = array_values(array_filter($tableKeys, fn($regId) => !empty($regId)));
            if (count($tableKeys) === 0) {
                return !is_null($this->handler);
            }
        }
        return $this->handler?->unregister($client, $tableKeys) ?? false;
    }

    /**
     * Handles update notifications and triggers the appropriate event.
     * @param string|null $clientId Client ID that performed the update.
     * @param string $entity Entity name.
     * @param array<array-key, mixed> $pkArray Primary key values.
     * @param array<array-key, mixed> $field Updated fields.
     * @param array<array-key, mixed> $value Updated values.
     * @param bool $isNotify Whether to just notify or not.
     * @return void
     */
    public function updated(?string $clientId, string $entity, array $pkArray, array $field, array $value, bool $isNotify): void
    {
        Logger::getInstance()->setDebugMessage("[NotifyServer] updated", 2);
        if ($this->handler) {
            $channels = $this->handler->matchInRegistered($clientId, $entity, $pkArray);
            $this->trigger($channels, 'update',
                ['justnotify' => $isNotify, 'entity' => $entity, 'pkvalue' => $pkArray, 'field' => $field, 'value' => $value]);
        }
    }

    /**
     * Handles create notifications and trigger the appropriate event.
     * @param string|null $clientId Client ID that performed the creation.
     * @param string $entity Entity name.
     * @param array<array-key, mixed> $pkArray Primary key values.
     * @param string $pkField Primary key field name.
     * @param array<array-key, mixed> $record Created record data.
     * @param bool $isNotify Whether to just notify or not.
     * @return void
     */
    public function created(?string $clientId, string $entity, array $pkArray, string $pkField, array $record, bool $isNotify): void
    {
        Logger::getInstance()->setDebugMessage("[NotifyServer] created", 2);
        if ($this->handler) {
            $channels = $this->handler->appendIntoRegistered($clientId, $entity, $pkField, $pkArray);
            $this->trigger($channels, 'create',
                ['justnotify' => $isNotify, 'entity' => $entity, 'pkvalue' => $pkArray, 'value' => array_values($record)]);
        }
    }

    /**
     * Handles delete notifications and trigger the appropriate event.
     * @param string|null $clientId Client ID that performed the deletion.
     * @param string $entity Entity name.
     * @param array<array-key, mixed> $pkArray Primary key values.
     * @return void
     */
    public function deleted(?string $clientId, string $entity, array $pkArray): void
    {
        Logger::getInstance()->setDebugMessage("[NotifyServer] deleted", 2);
        if ($this->handler) {
            $channels = $this->handler->removeFromRegistered($clientId, $entity, $pkArray);

            $data = array('entity' => $entity, 'pkvalue' => $pkArray);
            $this->trigger($channels, 'delete', $data);
        }
    }
}
