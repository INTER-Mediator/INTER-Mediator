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
 * Abstract base class for notification and registration handling.
 * Provides common logic to manage queried entities, conditions, and primary keys for notification systems.
 * Implements DB_Interface_Registering.
 */
abstract class DB_Notification_Common implements DB_Interface_Registering
{
    /** @var Settings|null Reference to the Settings object for DB configuration.
     */
    protected ?Settings $dbSettings = null;
    /** @var DBClass|null Reference to the parent DBClass instance.
     */
    protected ?DBClass $dbClass = null;
    /** @var Logger|null Logger instance for debug and error messages.
     */
    protected ?Logger $logger = null;

    /** Constructor.
     * @param DBClass $parent Parent DBClass instance for context.
     */
    public function __construct(DBClass $parent)
    {
        $this->dbClass = $parent;
        $this->dbSettings = $parent->dbSettings;
        $this->logger = $parent->logger;
    }

    /** @var string|null Name of the last queried entity.
     */
    private ?string $queriedEntity = null;
    /** @var string|null Last queried condition string.
     */
    private ?string $queriedCondition = null;
    /** @var array|null Primary keys from the last query.
     */
    private ?array $queriedPrimaryKeys = null;

    /** Gets the name of the last queried entity.
     * @return string|null Name of the queried entity or null if not set.
     */
    public function queriedEntity(): ?string
    {
        return $this->queriedEntity;
    }

    /** Gets the last queried condition string.
     * @return string|null The queried condition or null if not set.
     */
    public function queriedCondition(): ?string
    {
        return $this->queriedCondition;
    }

    /** Gets the primary keys from the last query.
     * @return array|null Array of primary keys or null if not set.
     */
    public function queriedPrimaryKeys(): ?array
    {
        return $this->queriedPrimaryKeys;
    }

    /** Sets the name of the last queried entity.
     * @param string|null $name Name of the queried entity.
     * @return void
     */
    public function setQueriedEntity(?string $name): void
    {
        $this->queriedEntity = $name;
    }

    /** Sets the last queried condition string.
     * @param string|null $name The queried condition.
     * @return void
     */
    public function setQueriedCondition(?string $name): void
    {
        $this->queriedCondition = $name;
    }

    /** Sets the primary keys from the last query.
     * @param array|null $name Array of primary keys.
     * @return void
     */
    public function setQueriedPrimaryKeys(?array $name): void
    {
        $this->queriedPrimaryKeys = $name;
    }

    /** Adds a primary key to the list of primary keys from the last query.
     * @param string|null $name Primary key to add.
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