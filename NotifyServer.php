<?php

/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2014 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

class NotifyServer
{

    private $dbClass;
    private $dbSettings;
    private $clientId;

    function initialize($dbClass, $dbSettings, $clientId)
    {
        $this->dbClass = $dbClass;
        $this->dbSettings = $dbSettings;
        $this->clientId = $clientId;
        if (is_null($dbClass) || is_null($dbSettings) || is_null($clientId)
            || !is_subclass_of($dbClass, 'DB_Interface_Registering')
            || !$dbClass->isExistRequiredTable()
            || is_null($dbSettings->pusherAppId) || strlen($dbSettings->pusherAppId) < 1
            || is_null($dbSettings->pusherKey) || strlen($dbSettings->pusherKey) < 1
            || is_null($dbSettings->pusherSecret) || strlen($dbSettings->pusherSecret) < 1
        ) {
            return false;
        }
        return true;
    }

    function register($entity, $condition, $pkArray)
    {
        $this->dbClass->register($this->clientId, $entity, $condition, $pkArray);
    }

    function unregister($client)
    {
        $this->dbClass->unregister($client);
    }

    function updated($clientId, $entity, $pkArray, $field, $value)
    {
        $channels = $this->dbClass->matchInRegisterd($clientId, $entity, $pkArray);

        if (!@include_once('Pusher.php')) {
            throw new Exception('_im_no_pusher_exception');
        }
        $pusher = new Pusher(
            $this->dbSettings->pusherKey,
            $this->dbSettings->pusherSecret,
            $this->dbSettings->pusherAppId
        );
        $data = array('entity'=>$entity, 'pkvalue'=>$pkArray, 'field'=>$field, 'value'=>$value);
        $response = $pusher->trigger($channels, 'update', $data);
    }

    function created($entity, $keying)
    {

    }

    function deleted($entity, $keying)
    {

    }

    function notify($client, $entity, $keying)
    {

    }

} 