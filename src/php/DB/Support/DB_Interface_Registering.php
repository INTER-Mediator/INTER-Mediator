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

/**
 * Interface for registering and tracking queried entities and their primary keys in the database.
 * Provides methods for managing registration, matching, and removal of registered records for clients.
 */
interface DB_Interface_Registering
{
    /**
     * Checks if the required table for registration exists.
     *
     * @return bool True if the required table exists, false otherwise.
     */
    public function isExistRequiredTable(): bool;

    /**
     * Gets the name of the last queried entity.
     *
     * @return string|null Name of the queried entity or null if not set.
     */
    public function queriedEntity(): ?string;

    /**
     * Sets the name of the last queried entity.
     *
     * @param string|null $name Name of the queried entity.
     * @return void
     */
    public function setQueriedEntity(?string $name): void;

    /**
     * Gets the last queried condition string.
     *
     * @return string|null The queried condition or null if not set.
     */
    public function queriedCondition(): ?string;

    /**
     * Sets the last queried condition string.
     *
     * @param string $name The queried condition.
     * @return void
     */
    public function setQueriedCondition(string $name): void;

    /**
     * Gets the primary keys from the last query.
     *
     * @return array|null Array of primary keys or null if not set.
     */
    public function queriedPrimaryKeys(): ?array;

    /**
     * Sets the primary keys from the last query.
     *
     * @param array|null $name Array of primary keys.
     * @return void
     */
    public function setQueriedPrimaryKeys(?array $name): void;

    /**
     * Adds a primary key to the last queried primary keys.
     *
     * @param string $name Primary key to add.
     * @return void
     */
    public function addQueriedPrimaryKeys(string $name): void;

    /**
     * Registers a new record for a client.
     *
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param string $condition Query condition string.
     * @param array $pkArray Array of primary keys.
     * @return string|null Registration identifier or null on failure.
     */
    public function register(?string $clientId, string $entity, string $condition, array $pkArray):?string;

    /**
     * Unregisters a record for a client.
     *
     * @param string|null $clientId Client identifier.
     * @param array|null $tableKeys Array of table keys to unregister.
     * @return bool True if successful, false otherwise.
     */
    public function unregister(?string $clientId, ?array $tableKeys):bool;

    /**
     * Checks if a record is registered for a client.
     *
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param array $pkArray Array of primary keys.
     * @return array|null Matching registration details or null if not found.
     */
    public function matchInRegistered(?string $clientId, string $entity, array $pkArray): ?array;

    /**
     * Appends a primary key into the registered records for a client.
     *
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param string $pkField Primary key field name.
     * @param array $pkArray Array of primary keys to append.
     * @return array|null Updated registration details or null on failure.
     */
    public function appendIntoRegistered(?string $clientId, string $entity, string $pkField, array $pkArray):?array;

    /**
     * Removes a primary key from the registered records for a client.
     *
     * @param string|null $clientId Client identifier.
     * @param string $entity Entity name.
     * @param array $pkArray Array of primary keys to remove.
     * @return array|null Updated registration details or null on failure.
     */
    public function removeFromRegistered(?string $clientId, string $entity, array $pkArray):?array;
}
