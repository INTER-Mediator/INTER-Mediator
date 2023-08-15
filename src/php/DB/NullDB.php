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
 *
 */
class NullDB extends DBClass
{

    /**
     * @return array|null
     */
    public function readFromDB(): ?array
    {
        return null;
    }

    /**
     * @return int
     */
    public function countQueryResult(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return 0;
    }

    /**
     * @param bool $bypassAuth
     * @return bool
     */
    public function updateDB(bool $bypassAuth): bool
    {
        return false;
    }

    /**
     * @param bool $isReplace
     * @return string|null
     */
    public function createInDB(bool $isReplace = false): ?string
    {
        return null;
    }

    /**
     * @return bool
     */
    public function deleteFromDB(): bool
    {
        return false;
    }

    /**
     * @param string $dataSourceName
     * @return array|null
     */
    public function getFieldInfo(string $dataSourceName): ?array
    {
        return null;
    }

    /**
     * @return bool
     */
    public function setupConnection(): bool
    {
        return true;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function requireUpdatedRecord(bool $value): void
    {
    }

    /**
     * @return array|null
     */
    public function getUpdatedRecord(): ?array
    {
        return null;
    }

    /**
     * @return array|null
     */
    public function updatedRecord(): ?array
    {
        return null;
    }

    /**
     * @param array $record
     * @return void
     */
    public function setUpdatedRecord(array $record): void
    {
    }

    /**
     * @param string $field
     * @param string $value
     * @return void
     */
    public function softDeleteActivate(string $field, string $value): void
    {

    }

    /**
     * @return string|null
     */
    public function copyInDB(): ?string
    {
        return null;
    }

    /**
     * @param string|null $dsn
     * @return void
     */
    public function setupHandlers(?string $dsn = null): void
    {
    }

    /**
     * @param string $field
     * @param string $value
     * @param int $index
     * @return void
     */
    public function setDataToUpdatedRecord(string $field, string $value, int $index = 0): void
    {
    }

    /**
     * @return bool
     */
    public function getUseSetDataToUpdatedRecord(): bool
    {
        return [];
    }

    /**
     * @return void
     */
    public function clearUseSetDataToUpdatedRecord(): void
    {
    }

    /**
     * @param string $table
     * @param array|null $conditions
     * @return array|null
     */
    public function queryForTest(string $table, ?array $conditions = null):?array
    {
        return null;
    }

    /**
     * @param string $table
     * @param array|null $conditions
     * @return bool
     */
    public function deleteForTest(string $table, ?array $conditions = null): bool
    {
        return false;
    }

    /*
* Transaction
*/
    /**
     * @return bool
     */
    public function hasTransaction():bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function inTransaction():bool
    {
        return false;
    }

    /**
     * @return void
     */
    public function beginTransaction():void
    {
    }

    /**
     * @return void
     */
    public function commitTransaction():void
    {
    }

    /**
     * @return void
     */
    public function rollbackTransaction():void
    {
    }

    /**
     * @return void
     */
    public function closeDBOperation():void
    {
    }
}
