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

interface DBClass_Interface
{
    public function setupConnection(): bool;

    public function setupHandlers(?string $dsn = null): void;

    public function readFromDB(): ?array;

    public function countQueryResult(): int;

    public function getTotalCount(): int;

    public function updateDB(bool $bypassAuth): bool;

    public function createInDB(bool $isReplace = false): ?string;

    public function deleteFromDB(): bool;

    public function copyInDB(): ?string;

    //private function normalizedCondition(array $condition);

    public function softDeleteActivate(string $field, string $value): void;

    public function getFieldInfo(string $dataSourceName): ?array;

    public function requireUpdatedRecord(bool $value): void;

    public function getUpdatedRecord(): ?array;

    public function updatedRecord(); // Same as getUpdatedRecord for compatibiliy; Don't describe type.

    public function setUpdatedRecord(array $record, string $value = null, int $index = 0): void;

    public function setDataToUpdatedRecord(string $field, string $value, int $index = 0): void;

    public function getUseSetDataToUpdatedRecord(): bool;

    public function clearUseSetDataToUpdatedRecord(): void;

    public function queryForTest(string $table, ?array $conditions = null): ?array;

    public function deleteForTest(string $table, ?array $conditions = null): bool;

    public function hasTransaction(): bool;

    public function inTransaction(): bool;

    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;

    public function closeDBOperation(): void;

}
