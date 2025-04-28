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
 * NullDB class acts as a no-operation database driver for INTER-Mediator.
 * All methods return default or null values, enabling testing or fallback without a real database.
 */
class NullDB extends DBClass
{

    /**
     * Read records from the database (no-op).
     * @return array|null Always returns null.
     */
    public function readFromDB(): ?array
    {
        return null;
    }

    /**
     * Get the count of query results (no-op).
     * @return int Always returns 0.
     */
    public function countQueryResult(): int
    {
        return 0;
    }

    /**
     * Get the total count of records (no-op).
     * @return int Always returns 0.
     */
    public function getTotalCount(): int
    {
        return 0;
    }

    /**
     * Update records in the database (no-op).
     * @param bool $bypassAuth Whether to bypass authentication.
     * @return bool Always returns false.
     */
    public function updateDB(bool $bypassAuth): bool
    {
        return false;
    }

    /**
     * Create a new record in the database (no-op).
     * @param bool $isReplace Whether to replace existing data.
     * @return string|null Always returns null.
     */
    public function createInDB(bool $isReplace = false): ?string
    {
        return null;
    }

    /**
     * Delete a record from the database (no-op).
     * @return bool Always returns false.
     */
    public function deleteFromDB(): bool
    {
        return false;
    }

    /**
     * Get field information (no-op).
     * @param string $dataSourceName The data source name.
     * @return array|null Always returns null.
     */
    public function getFieldInfo(string $dataSourceName): ?array
    {
        return null;
    }

    /**
     * Setup the database connection (no-op).
     * @return bool Always returns true.
     */
    public function setupConnection(): bool
    {
        return true;
    }

    /**
     * Require updated record (no-op).
     * @param bool $value Whether to require updated record.
     * @return void
     */
    public function requireUpdatedRecord(bool $value): void
    {
    }

    /**
     * Get the updated record (no-op).
     * @return array|null Always returns null.
     */
    public function getUpdatedRecord(): ?array
    {
        return null;
    }

    /**
     * Get the updated record (no-op).
     * @return array|null Always returns null.
     */
    public function updatedRecord(): ?array
    {
        return null;
    }

    /**
     * Set the updated record (no-op).
     * @param array $record The record to set.
     * @return void
     */
    public function setUpdatedRecord(array $record): void
    {
    }

    /**
     * Soft delete or activate a record (no-op).
     * @param string $field The field to update.
     * @param string $value The value to set.
     * @return void
     */
    public function softDeleteActivate(string $field, string $value): void
    {

    }

    /**
     * Copy a record in the database (no-op).
     * @return string|null Always returns null.
     */
    public function copyInDB(): ?string
    {
        return null;
    }

    /**
     * Setup database handlers (no-op).
     * @param string|null $dsn The data source name.
     * @return void
     */
    public function setupHandlers(?string $dsn = null): void
    {
    }

    /**
     * Set data to the updated record (no-op).
     * @param string $field The field to update.
     * @param string|null $value The value to set.
     * @param int $index The index of the record.
     * @return void
     */
    public function setDataToUpdatedRecord(string $field, ?string $value, int $index = 0): void
    {
    }

    /**
     * Get whether to use set data to updated record (no-op).
     * @return bool Always returns false.
     */
    public function getUseSetDataToUpdatedRecord(): bool
    {
        return false;
    }

    /**
     * Clear the use set data to updated record flag (no-op).
     * @return void
     */
    public function clearUseSetDataToUpdatedRecord(): void
    {
    }

    /**
     * Query records for testing (no-op).
     * @param string $table The table to query.
     * @param array|null $conditions The query conditions.
     * @return array|null Always returns null.
     */
    public function queryForTest(string $table, ?array $conditions = null):?array
    {
        return null;
    }

    /**
     * Delete records for testing (no-op).
     * @param string $table The table to delete from.
     * @param array|null $conditions The delete conditions.
     * @return bool Always returns false.
     */
    public function deleteForTest(string $table, ?array $conditions = null): bool
    {
        return false;
    }

    /**
     * Check if a transaction is available (no-op).
     * @return bool Always returns false.
     */
    public function hasTransaction():bool
    {
        return false;
    }

    /**
     * Check if a transaction is in progress (no-op).
     * @return bool Always returns false.
     */
    public function inTransaction():bool
    {
        return false;
    }

    /**
     * Begin a transaction (no-op).
     * @return void
     */
    public function beginTransaction():void
    {
    }

    /**
     * Commit a transaction (no-op).
     * @return void
     */
    public function commitTransaction():void
    {
    }

    /**
     * Rollback a transaction (no-op).
     * @return void
     */
    public function rollbackTransaction():void
    {
    }

    /**
     * Close the database operation (no-op).
     * @return void
     */
    public function closeDBOperation():void
    {
    }
}
