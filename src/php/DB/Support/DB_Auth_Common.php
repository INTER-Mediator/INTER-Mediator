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

abstract class DB_Auth_Common implements Auth_Interface_CommonDB
{
    protected ?Settings $dbSettings = null;
    protected ?DBClass $dbClass = null;
    protected ?Logger $logger = null;

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

    function getFieldForAuthorization(string $operation): string
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

    function getTargetForAuthorization(string $operation):string
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

    function getNoSetForAuthorization(string $operation):?string
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

    function getAuthorizedUsers(?string $operation = null): array
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

    function getAuthorizedGroups(?string $operation = null): array
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