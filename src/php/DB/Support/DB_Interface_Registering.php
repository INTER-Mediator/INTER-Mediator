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

interface DB_Interface_Registering
{
    public function isExistRequiredTable(): bool;

    public function queriedEntity(): ?string;

    public function setQueriedEntity(?string $name): void;

    public function queriedCondition(): ?string;

    public function setQueriedCondition(string $name): void;

    public function queriedPrimaryKeys(): ?array;

    public function setQueriedPrimaryKeys(?array $name): void;

    public function addQueriedPrimaryKeys(string $name): void;

    public function register(string $clientId, string $entity, string $condition, array $pkArray):?string;

    public function unregister(string $clientId, ?array $tableKeys):bool;

    public function matchInRegistered(string $clientId, string $entity, array $pkArray): ?array;

    public function appendIntoRegistered(string $clientId, string $entity, string $pkField, array $pkArray):?array;

    public function removeFromRegistered(string $clientId, string $entity, array $pkArray):?array;
}
