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

/**
 *
 */
abstract class DB_Notification_Common
{
    /**
     * @var Settings|null
     */
    protected ?Settings $dbSettings = null;
    /**
     * @var DBClass|null
     */
    protected ?DBClass $dbClass = null;
    /**
     * @var Logger|null
     */
    protected ?Logger $logger = null;

    /**
     * @param DBClass $parent
     */
    public function __construct(DBClass $parent)
    {
        $this->dbClass = $parent;
        $this->dbSettings = $parent->dbSettings;
        $this->logger = $parent->logger;
    }

    /**
     * @var string|null
     */
    private ?string $queriedEntity = null;
    /**
     * @var string|null
     */
    private ?string $queriedCondition = null;
    /**
     * @var array|null
     */
    private ?array $queriedPrimaryKeys = null;

    /**
     * @return string|null
     */
    public function queriedEntity(): ?string
    {
        return $this->queriedEntity;
    }

    /**
     * @return string|null
     */
    public function queriedCondition(): ?string
    {
        return $this->queriedCondition;
    }

    /**
     * @return array|null
     */
    public function queriedPrimaryKeys(): ?array
    {
        return $this->queriedPrimaryKeys;
    }

    /**
     * @param string|null $name
     * @return void
     */
    public function setQueriedEntity(?string $name): void
    {
        $this->queriedEntity = $name;
    }

    /**
     * @param string|null $name
     * @return void
     */
    public function setQueriedCondition(?string $name): void
    {
        $this->queriedCondition = $name;
    }

    /**
     * @param array|null $name
     * @return void
     */
    public function setQueriedPrimaryKeys(?array $name): void
    {
        $this->queriedPrimaryKeys = $name;
    }

    /**
     * @param string|null $name
     * @return void
     */
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