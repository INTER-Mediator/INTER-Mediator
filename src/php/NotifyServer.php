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

use INTERMediator\DB\Logger;

class NotifyServer
{
    private $dbClass;
    private $dbSettings;
    private $clientId;

    /**
     * @param $dbClass
     * @param $dbSettings
     * @param $clientId
     * @return bool
     */
    public function initialize($dbClass, $dbSettings, $clientId)
    {
        $this->dbClass = $dbClass;
        $this->dbSettings = $dbSettings;
        $this->clientId = $clientId;
        if (is_null($dbClass) || is_null($dbSettings) || is_null($clientId)
            || !is_subclass_of($dbClass, 'DB_Interface_Registering')
            || !$dbClass->notifyHandler->isExistRequiredTable()
//            || is_null($dbSettings->pusherAppId) || strlen($dbSettings->pusherAppId) < 1
//            || is_null($dbSettings->pusherKey) || strlen($dbSettings->pusherKey) < 1
//            || is_null($dbSettings->pusherSecret) || strlen($dbSettings->pusherSecret) < 1
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param $channels array associated clinet ids as below:
     *   ['5099b6c0b4d47a3d312ee21458216170916d7c0f09adb374a07ea0d44c6da7b0',
     *    '7254b1a045fddc516c5df286df33a147364b64d65a7f18e9c3c7494cb3d7cc57',]
     * @param $operation string 'update' and so on.
     * @param $data array associated array describes modified data as like
     * ['entity' => '`person`',
     * 'pkvalue' => [0 => '1',]
     * 'field' => [0 => 'name',]
     * 'value' => [0 => 'Masayuki Nii',],]
     */
    private function trigger($channels, $operation, $data)
    {
        $ssInstance = ServiceServerProxy::instance();
        $ssInstance->clearMessages();
        $ssInstance->clearErrors();
        $ssInstance->sync($channels, $operation, $data);
        $logger = Logger::getInstance();
        $logger->setDebugMessages($ssInstance->getMessages());
        $logger->setErrorMessages($ssInstance->getErrors());
    }

    /**
     * @param $entity
     * @param $condition
     * @param $pkArray
     * @return mixed
     */
    public function register($entity, $condition, $pkArray)
    {
        return $this->dbClass->notifyHandler->register($this->clientId, $entity, $condition, $pkArray);
    }

    /**
     * @param $client
     * @param $tableKeys
     * @return mixed
     */
    public function unregister($client, $tableKeys)
    {
        return $this->dbClass->notifyHandler->unregister($client, $tableKeys);
    }

    /**
     * @param $clientId
     * @param $entity
     * @param $pkArray
     * @param $field
     * @param $value
     */
    public function updated($clientId, $entity, $pkArray, $field, $value, $isNotify)
    {
        $channels = $this->dbClass->notifyHandler->matchInRegistered($clientId, $entity, $pkArray);
        $this->trigger($channels, 'update',
            ['justnotify' => $isNotify, 'entity' => $entity, 'pkvalue' => $pkArray, 'field' => $field, 'value' => $value]);
    }

    /**
     * @param $clientId
     * @param $entity
     * @param $pkArray
     * @param $record
     */
    public function created($clientId, $entity, $pkArray, $record, $isNotify)
    {
        $channels = $this->dbClass->notifyHandler->appendIntoRegistered($clientId, $entity, $pkArray);
        $this->trigger($channels, 'create',
            ['justnotify' => $isNotify, 'entity' => $entity, 'pkvalue' => $pkArray, 'value' => array_values($record)]);
    }

    /**
     * @param $clientId
     * @param $entity
     * @param $pkArray
     */
    public function deleted($clientId, $entity, $pkArray)
    {
        $channels = $this->dbClass->notifyHandler->removeFromRegistered($clientId, $entity, $pkArray);

        $data = array('entity' => $entity, 'pkvalue' => $pkArray);
        $this->trigger($channels, 'delete', $data);
    }

    /**
     * @param $client
     * @param $entity
     * @param $keying
     */
    public function notify($client, $entity, $keying)
    {

    }
}
