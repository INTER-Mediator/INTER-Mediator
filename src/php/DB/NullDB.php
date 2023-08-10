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

class NullDB extends DBClass
{

    public function readFromDB(): ?array
    {
        return null;
    }

    public function countQueryResult(): int
    {
        return 0;
    }

    public function getTotalCount(): int
    {
        return 0;
    }

    public function updateDB(bool $bypassAuth): bool
    {
        return false;
    }

    public function createInDB(bool $isReplace = false): ?string
    {
        return null;
    }

    public function deleteFromDB(): bool
    {
        return false;
    }

    public function getFieldInfo(string $dataSourceName): ?array
    {
        return null;
    }

    public function setupConnection(): bool
    {
        return true;
    }

    public function requireUpdatedRecord(bool $value): void
    {
    }

    public function getUpdatedRecord(): ?array
    {
        return null;
    }

    public function updatedRecord(): ?array
    {
        return null;
    }

    public function setUpdatedRecord(array $record, string $value = null, int $index = 0): void
    {
    }

    public function softDeleteActivate(string $field, string $value): void
    {

    }

    public function copyInDB(): ?string
    {
        return null;
    }

    public function setupHandlers(?string $dsn = null): void
    {
    }

    public function setDataToUpdatedRecord(string $field, string $value, int $index = 0): void
    {
    }

    public function getUseSetDataToUpdatedRecord(): bool
    {
    }

    public function clearUseSetDataToUpdatedRecord(): void
    {
    }

    public function queryForTest(string $table, ?array $conditions = null):?array
    {
        return null;
    }

    public function deleteForTest(string $table, ?array $conditions = null): bool
    {
        return false;
    }

    /*
* Transaction
*/
    public function hasTransaction():bool
    {
        return false;
    }

    public function inTransaction():bool
    {
        return false;
    }

    public function beginTransaction():void
    {
    }

    public function commitTransaction():void
    {
    }

    public function rollbackTransaction():void
    {
    }

    public function closeDBOperation():void
    {
    }
}
