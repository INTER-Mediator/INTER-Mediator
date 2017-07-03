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

class NotifyServer
{

    private $dbClass;
    private $dbSettings;
    private $clientId;

    public function initialize($dbClass, $dbSettings, $clientId)
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

    public function register($entity, $condition, $pkArray)
    {
        return $this->dbClass->register($this->clientId, $entity, $condition, $pkArray);
    }

    public function unregister($client, $tableKeys)
    {
        return $this->dbClass->unregister($client, $tableKeys);
    }

    public function updated($clientId, $entity, $pkArray, $field, $value)
    {
        $channels = $this->dbClass->matchInRegisterd($clientId, $entity, $pkArray);

        $this->loadPusher();
        $pusher = new Pusher(
            $this->dbSettings->pusherKey,
            $this->dbSettings->pusherSecret,
            $this->dbSettings->pusherAppId
        );
        $data = array('entity'=>$entity, 'pkvalue'=>$pkArray, 'field'=>$field, 'value'=>$value);
        $response = $pusher->trigger($channels, 'update', $data);
    }

    public function created($clientId, $entity, $pkArray, $record)
    {
        $channels = $this->dbClass->appendIntoRegisterd($clientId, $entity, $pkArray);

        $this->loadPusher();
        $pusher = new Pusher(
            $this->dbSettings->pusherKey,
            $this->dbSettings->pusherSecret,
            $this->dbSettings->pusherAppId
        );
        $data = array(
            'entity'=>$entity,
            'pkvalue'=>$pkArray,
         //   'field'=>array_keys($record),
            'value'=>array_values($record)
        );
        $response = $pusher->trigger($channels, 'create', $data);
    }

    public function deleted($clientId, $entity, $pkArray)
    {
        $channels = $this->dbClass->removeFromRegisterd($clientId, $entity, $pkArray);

        $this->loadPusher();
        $pusher = new Pusher(
            $this->dbSettings->pusherKey,
            $this->dbSettings->pusherSecret,
            $this->dbSettings->pusherAppId
        );
        $data = array('entity'=>$entity, 'pkvalue'=>$pkArray);
        $response = $pusher->trigger($channels, 'delete', $data);
    }

    public function notify($client, $entity, $keying)
    {

    }

    protected function loadPusher() {
        $paths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $dirPath) {
            if ($dirPath === '.') {
                $dirPath = dirname(__FILE__);
            }
            if (file_exists($dirPath)) {
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
                foreach ($iterator as $element) {
                    $path = dirname($element) . DIRECTORY_SEPARATOR . 'Pusher.php';
                    if (is_file($path) && is_readable($path)) {
                        include_once($path);
                        return;
                    }
                }
            }
        }
        throw new Exception('_im_no_pusher_exception');
    }

}