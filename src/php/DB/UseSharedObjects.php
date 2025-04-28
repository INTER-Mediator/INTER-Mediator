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

use INTERMediator\DB\Support\DB_Auth_Common;
use INTERMediator\DB\Support\DB_Notification_Common;
use INTERMediator\DB\Support\DB_PDO_Handler;
use INTERMediator\DB\Support\DB_Spec_Behavior;

/**
 * Abstract class UseSharedObjects for sharing core DB-related objects in INTER-Mediator.
 * Provides properties and methods to manage shared settings, logger, formatter, and handlers.
 */
abstract class UseSharedObjects
{
    /**
     * Settings object for DB configuration.
     * @var Settings|null
     */
    public ?Settings $dbSettings = null;
    /**
     * Logger instance.
     * @var Logger|null
     */
    public ?Logger $logger = null;
    /**
     * Formatter instance.
     * @var Formatters|null
     */
    public ?Formatters $formatter = null;
    /**
     * DBClass instance.
     * @var DBClass|null
     */
    public ?DBClass $dbClass = null;
    /**
     * Proxy object instance.
     * @var Proxy|null
     */
    public ?Proxy $proxyObject = null;
    /**
     * PDO handler instance.
     * @var DB_PDO_Handler|null
     */
    public ?DB_PDO_Handler $handler = null;    // Handle for each database engine. Uses just PDO.
    /**
     * DB authentication handler.
     * @var DB_Auth_Common|null
     */
    public ?DB_Auth_Common $authHandler = null;
    /**
     * DB notification handler.
     * @var DB_Notification_Common|null
     */
    public ?DB_Notification_Common $notifyHandler = null;
    /**
     * DB specification behavior handler.
     * @var DB_Spec_Behavior|null
     */
    public ?DB_Spec_Behavior $specHandler = null;

    /**
     * Set up shared objects for the current context.
     * @param Proxy|null $obj Proxy instance to share objects from, or null to create new ones.
     * @return void
     */
    public function setUpSharedObjects(?Proxy $obj = null)
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

    /**
     * Set the settings object.
     * @param Settings $dbSettings
     * @return void
     */
    private function setSettings($dbSettings)
    {
        $this->dbSettings = $dbSettings;
    }

    /**
     * Set the logger object.
     * @param Logger $logger
     * @return void
     */
    private function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set the formatter object.
     * @param Formatters $formatter
     * @return void
     */
    private function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }
}
