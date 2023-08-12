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

use INTERMediator\DB\DBClass;
use INTERMediator\DB\Logger;
use INTERMediator\DB\Settings;

abstract class DB_Notification_Common
{
    protected ?Settings $dbSettings = null;
    protected ?DBClass $dbClass = null;
    protected ?Logger $logger = null;

    public function __construct(DBClass $parent)
    {
        if ($parent) {
            $this->dbClass = $parent;
            $this->dbSettings = $parent->dbSettings;
            $this->logger = $parent->logger;
        } else {
            trigger_error("Misuse of constructor.", E_USER_ERROR);
        }
    }

    private ?string $queriedEntity = null;
    private ?string $queriedCondition = null;
    private ?array $queriedPrimaryKeys = null;

    public function queriedEntity(): ?string
    {
        return $this->queriedEntity;
    }

    public function queriedCondition(): ?string
    {
        return $this->queriedCondition;
    }

    public function queriedPrimaryKeys(): ?array
    {
        return $this->queriedPrimaryKeys;
    }

    public function setQueriedEntity(?string $name): void
    {
        $this->queriedEntity = $name;
    }

    public function setQueriedCondition(?string $name): void
    {
        $this->queriedCondition = $name;
    }

    public function setQueriedPrimaryKeys(?array $name): void
    {
        $this->queriedPrimaryKeys = $name;
    }

    public function addQueriedPrimaryKeys(?string $name): void
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