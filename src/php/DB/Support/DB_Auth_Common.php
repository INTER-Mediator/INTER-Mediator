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
 * Abstract base class for database authentication support.
 * Implements common logic for authorization fields, targets, users, and challenge/response mechanisms.
 * Provides references to DBClass, Settings, and Logger for use in subclasses.
 * Implements Auth_Interface_CommonDB.
 */
abstract class DB_Auth_Common implements Auth_Interface_CommonDB
{
    /**
     * @var Settings|null Reference to the Settings object for DB configuration.
     */
    protected ?Settings $dbSettings = null;
    /**
     * @var DBClass|null Reference to the parent DBClass instance.
     */
    protected ?DBClass $dbClass = null;
    /**
     * @var Logger|null Logger instance for debug and error messages.
     */
    protected ?Logger $logger = null;

    /**
     * Constructor.
     *
     * @param DBClass|null $parent Parent DBClass instance for context.
     */
    public function __construct(?DBClass $parent)
    {
        if ($parent) {
            $this->dbClass = $parent;
            $this->dbSettings = $parent->dbSettings;
            $this->logger = $parent->logger;
        } else {
            trigger_error("Misuse of constructor.", E_USER_ERROR);
        }
    }

    /**
     * Returns an array of operation aliases for a given operation.
     *
     * @param string $operation The operation type.
     * @return array Operation aliases.
     */
    private function getOperationSeries(string $operation): array
    {
        $operations = array();
        if (($operation === 'select') || ($operation === 'load') || ($operation === 'read')) {
            $operations = array('read', 'select', 'load');
        } else if (($operation === 'update') || ($operation === 'edit')) {
            $operations = array('update', 'edit');
        } else if (($operation === 'create') || ($operation === 'new')) {
            $operations = array('create', 'new');
        } else if ($operation === 'delete') {
            $operations = array('delete');
        }
        return $operations;
    }

    /**
     * Returns the authorization field for a given operation.
     *
     * @param string $operation The operation type.
     * @return string|null The authorization field or null if not found.
     */
    public function getFieldForAuthorization(string $operation): ?string
    {
        $operations = $this->getOperationSeries($operation);
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $authInfoField = null;
        if (isset($tableInfo['authentication']['all']['field'])) {
            $authInfoField = $tableInfo['authentication']['all']['field'];
        }
        foreach ($operations as $op) {
            if (isset($tableInfo['authentication'][$op]['field'])) {
                $authInfoField = $tableInfo['authentication'][$op]['field'];
                break;
            }
        }
        return $authInfoField;
    }

    /**
     * Returns the authorization target for a given operation.
     *
     * @param string $operation The operation type.
     * @return string|null The authorization target or null if not found.
     */
    public function getTargetForAuthorization(string $operation): ?string
    {
        $operations = $this->getOperationSeries($operation);
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $authInfoTarget = null;
        if (isset($tableInfo['authentication']['all']['target'])) {
            $authInfoTarget = $tableInfo['authentication']['all']['target'];
        }
        foreach ($operations as $op) {
            if (isset($tableInfo['authentication'][$op]['target'])) {
                $authInfoTarget = $tableInfo['authentication'][$op]['target'];
                break;
            }
        }
        return $authInfoTarget;
    }

    /**
     * Returns the no-set value for authorization for a given operation.
     *
     * @param string $operation The operation type.
     * @return string|null The no-set value or null if not found.
     */
    public function getNoSetForAuthorization(string $operation): ?string
    {
        $operations = $this->getOperationSeries($operation);
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $authInfoNoSet = null;
        if (isset($tableInfo['authentication']['all']['noset'])) {
            $authInfoNoSet = $tableInfo['authentication']['all']['noset'];
        }
        foreach ($operations as $op) {
            if (isset($tableInfo['authentication'][$op]['noset'])) {
                $authInfoNoSet = $tableInfo['authentication'][$op]['noset'];
                break;
            }
        }
        return $authInfoNoSet;
    }

    /**
     * Returns an array of authorized users for a given operation.
     *
     * @param string|null $operation The operation type or null for all operations.
     * @return array Authorized users.
     */
    public function getAuthorizedUsers(?string $operation = null): array
    {
        $operations = $this->getOperationSeries($operation);
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $usersArray = array();
        if ($this->dbSettings->getAuthenticationItem('user')) {
            $usersArray = array_merge($usersArray, $this->dbSettings->getAuthenticationItem('user'));
        }
        if (isset($tableInfo['authentication']['all']['user'])) {
            $usersArray = array_merge($usersArray, $tableInfo['authentication']['all']['user']);
        }
        foreach ($operations as $op) {
            if (isset($tableInfo['authentication'][$op]['user'])) {
                $usersArray = array_merge($usersArray, $tableInfo['authentication'][$op]['user']);
                break;
            }
        }
        return array_values(array_unique($usersArray));
    }

    /**
     * Returns an array of authorized groups for a given operation.
     *
     * @param string|null $operation The operation type or null for all operations.
     * @return array Authorized groups.
     */
    public function getAuthorizedGroups(?string $operation = null): array
    {
        $operations = $this->getOperationSeries($operation);
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $groupsArray = array();
        if ($this->dbSettings->getAuthenticationItem('group')) {
            $groupsArray = array_merge($groupsArray, $this->dbSettings->getAuthenticationItem('group'));
        }
        if (isset($tableInfo['authentication']['all']['group'])) {
            $groupsArray = array_merge($groupsArray, $tableInfo['authentication']['all']['group']);
        }
        foreach ($operations as $op) {
            if (isset($tableInfo['authentication'][$op]['group'])) {
                $groupsArray = array_merge($groupsArray, $tableInfo['authentication'][$op]['group']);
                break;
            }
        }
        return array_values(array_unique($groupsArray));
    }
}