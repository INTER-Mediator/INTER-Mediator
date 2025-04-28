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

namespace INTERMediator\DB;

/**
 * Interface for database class operations in INTER-Mediator, defining required methods for DB access, transactions, and record management.
 */
interface DBClass_Interface
{
    /**
     * Setup the database connection.
     * @return bool True on success, false otherwise.
     */
    public function setupConnection(): bool;

    /**
     * Setup handlers, optionally with a DSN.
     * @param string|null $dsn Optional DSN string.
     * @return void
     */
    public function setupHandlers(?string $dsn = null): void;

    /**
     * Read records from the database.
     * @return array|null The result set as an array, or null if none.
     */
    public function readFromDB(): ?array;

    /**
     * Count the number of query results.
     * @return int The count of results.
     */
    public function countQueryResult(): int;

    /**
     * Get the total count of records.
     * @return int The total count.
     */
    public function getTotalCount(): int;

    /**
     * Update records in the database.
     * @param bool $bypassAuth Whether to bypass authentication.
     * @return bool True on success, false otherwise.
     */
    public function updateDB(bool $bypassAuth): bool;

    /**
     * Create a new record in the database.
     * @param bool $isReplace Whether to replace an existing record.
     * @return string|null The new record's ID, or null on failure.
     */
    public function createInDB(bool $isReplace = false): ?string;

    /**
     * Delete a record from the database.
     * @return bool True on success, false otherwise.
     */
    public function deleteFromDB(): bool;

    /**
     * Copy a record in the database.
     * @return string|null The new record's ID, or null on failure.
     */
    public function copyInDB(): ?string;

    /**
     * Normalize a condition array (FileMaker only).
     * @param array $condition The condition array.
     * @return mixed
     */
    public function normalizedCondition(array $condition);

    /**
     * Activate soft delete on a field with a value.
     * @param string $field The field name.
     * @param string $value The value to set.
     * @return void
     */
    public function softDeleteActivate(string $field, string $value): void;

    /**
     * Get field information for a data source.
     * @param string $dataSourceName The data source name.
     * @return array|null Field info array or null.
     */
    public function getFieldInfo(string $dataSourceName): ?array;

    /**
     * Set whether updated records are required.
     * @param bool $value True if required.
     * @return void
     */
    public function requireUpdatedRecord(bool $value): void;

    /**
     * Get the updated record.
     * @return array|null The updated record or null.
     */
    public function getUpdatedRecord(): ?array;

    /**
     * Get the updated record (compatibility method).
     * @return mixed
     */
    public function updatedRecord();

    /**
     * Set the updated record data.
     * @param array $record The record data.
     * @return void
     */
    public function setUpdatedRecord(array $record): void;

    /**
     * Set a field value in the updated record.
     * @param string $field The field name.
     * @param string $value The value to set.
     * @param int $index The record index (default 0).
     * @return void
     */
    public function setDataToUpdatedRecord(string $field, string $value, int $index = 0): void;

    /**
     * Check if setDataToUpdatedRecord is used.
     * @return bool True if used.
     */
    public function getUseSetDataToUpdatedRecord(): bool;

    /**
     * Clear the use of setDataToUpdatedRecord.
     * @return void
     */
    public function clearUseSetDataToUpdatedRecord(): void;

    /**
     * Query the database for testing.
     * @param string $table The table name.
     * @param array|null $conditions Optional conditions.
     * @return array|null Result set or null.
     */
    public function queryForTest(string $table, ?array $conditions = null): ?array;

    /**
     * Delete records for testing.
     * @param string $table The table name.
     * @param array|null $conditions Optional conditions.
     * @return bool True on success, false otherwise.
     */
    public function deleteForTest(string $table, ?array $conditions = null): bool;

    /**
     * Check if the database supports transactions.
     * @return bool True if supported.
     */
    public function hasTransaction(): bool;

    /**
     * Check if a transaction is in progress.
     * @return bool True if in progress.
     */
    public function inTransaction(): bool;

    /**
     * Begin a transaction.
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commit the current transaction.
     * @return void
     */
    public function commitTransaction(): void;

    /**
     * Rollback the current transaction.
     * @return void
     */
    public function rollbackTransaction(): void;

    /**
     * Close the database operation and connection.
     * @return void
     */
    public function closeDBOperation(): void;
}
