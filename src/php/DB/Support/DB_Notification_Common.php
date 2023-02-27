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

abstract class DB_Notification_Common
{
    protected $dbSettings = null;
    protected $dbClass = null;
    protected $logger = null;

    public function __construct($parent)
    {
        if ($parent) {
            $this->dbClass = $parent;
            $this->dbSettings = $parent->dbSettings;
            $this->logger = $parent->logger;
        } else {
            trigger_error("Misuse of constructor.", E_USER_ERROR);
        }
    }

    private $queriedEntity = null;
    private $queriedCondition = null;
    private $queriedPrimaryKeys = null;

    public function queriedEntity()
    {
        return $this->queriedEntity;
    }

    public function queriedCondition()
    {
        return $this->queriedCondition;
    }

    public function queriedPrimaryKeys()
    {
        return $this->queriedPrimaryKeys;
    }

    public function setQueriedEntity($name)
    {
        $this->queriedEntity = $name;
    }

    public function setQueriedCondition($name)
    {
        $this->queriedCondition = $name;
    }

    public function setQueriedPrimaryKeys($name)
    {
        $this->queriedPrimaryKeys = $name;
    }

    public function addQueriedPrimaryKeys($name)
    {
        if (!$name) {
            return;
        }
        if (is_null($this->queriedPrimaryKeys)) {
            $this->queriedPrimaryKeys = array($name);
        } else {
            $this->queriedPrimaryKeys[] = $name;
        }
    }

}