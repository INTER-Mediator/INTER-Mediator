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

abstract class UseSharedObjects
{
    public $dbSettings = null;
    public $logger = null;
    public $formatter = null;
    public $dbClass = null;
    public $proxyObject = null;
    public $handler = null;    // Handle for each database engine. Uses just PDO.
    public $authHandler = null;
    public $notifyHandler = null;
    public $specHandler = null;

    public function setUpSharedObjects($obj = null)
    {
        if (is_null($obj)) {
            $this->setSettings(new Settings());
            $this->setLogger(Logger::getInstance());
            $this->setFormatter(new Formatters());
        } else {
            $this->setSettings($obj->dbSettings);
            $this->setLogger($obj->logger);
            $this->setFormatter($obj->formatter);
            $this->dbClass = $obj->dbClass;
            $this->proxyObject = $obj;
            //$this->dbClass->setupHandlers();
        }
    }

    private function setSettings($dbSettings)
    {
        $this->dbSettings = $dbSettings;
    }

    private function setLogger($logger)
    {
        $this->logger = $logger;
    }

    private function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }
}

