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
    public function isExistRequiredTable();
    public function queriedEntity();
    public function setQueriedEntity($name);
    public function queriedCondition();
    public function setQueriedCondition($name);
    public function queriedPrimaryKeys();
    public function setQueriedPrimaryKeys($name);
    public function addQueriedPrimaryKeys($name);
    public function register($clientId, $entity, $condition, $pkArray);
    public function unregister($clientId, $tableKeys);
    public function matchInRegistered($clientId, $entity, $pkArray);
    public function appendIntoRegistered($clientId, $entity, $pkArray);
    public function removeFromRegistered($clientId, $entity, $pkArray);
}
