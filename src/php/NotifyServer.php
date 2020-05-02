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

class NotifyServer
{
    private $dbClass;
    private $dbSettings;
    private $clientId;
    private $syncServerKey;

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
            || is_null($dbSettings->pusherAppId) || strlen($dbSettings->pusherAppId) < 1
            || is_null($dbSettings->pusherKey) || strlen($dbSettings->pusherKey) < 1
            || is_null($dbSettings->pusherSecret) || strlen($dbSettings->pusherSecret) < 1
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param $channels
     * @param $operation
     * @param $data
     */
    private function trigger($channels, $operation, $data)
    {

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
    public function updated($clientId, $entity, $pkArray, $field, $value)
    {
        $channels = $this->dbClass->notifyHandler->matchInRegisterd($clientId, $entity, $pkArray);
        $data = array('entity' => $entity, 'pkvalue' => $pkArray, 'field' => $field, 'value' => $value);
        $this->trigger($channels, 'update', $data);
    }

    /**
     * @param $clientId
     * @param $entity
     * @param $pkArray
     * @param $record
     */
    public function created($clientId, $entity, $pkArray, $record)
    {
        $channels = $this->dbClass->notifyHandler->appendIntoRegisterd($clientId, $entity, $pkArray);

        $data = array(
            'entity' => $entity,
            'pkvalue' => $pkArray,
            //   'field'=>array_keys($record),
            'value' => array_values($record)
        );
        $this->trigger($channels, 'create', $data);
    }

    /**
     * @param $clientId
     * @param $entity
     * @param $pkArray
     */
    public function deleted($clientId, $entity, $pkArray)
    {
        $channels = $this->dbClass->notifyHandler->removeFromRegisterd($clientId, $entity, $pkArray);

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
