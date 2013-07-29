<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2012 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/23
 * Time: 17:57
 * To change this template use File | Settings | File Templates.
 */
abstract class DB_AuthCommon extends DB_UseSharedObjects implements Auth_Interface_CommonDB
{

    function getFieldForAuthorization($operation)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $authInfoField = null;
        if (isset($tableInfo['authentication']['all']['field'])) {
            $authInfoField = $tableInfo['authentication']['all']['field'];
        }
        if (isset($tableInfo['authentication'][$operation]['field'])) {
            $authInfoField = $tableInfo['authentication'][$operation]['field'];
        }
        return $authInfoField;
    }

    function getTargetForAuthorization($operation)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $authInfoTarget = null;
        if (isset($tableInfo['authentication']['all']['target'])) {
            $authInfoTarget = $tableInfo['authentication']['all']['target'];
        }
        if (isset($tableInfo['authentication'][$operation]['target'])) {
            $authInfoTarget = $tableInfo['authentication'][$operation]['target'];
        }
        return $authInfoTarget;
    }

    function getAuthorizedUsers($operation = null)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $usersArray = array();
        if ($this->dbSettings->getAuthenticationItem('user')) {
            $usersArray = array_merge($usersArray, $this->dbSettings->getAuthenticationItem('user'));
        }
        if (isset($tableInfo['authentication']['all']['user'])) {
            $usersArray = array_merge($usersArray, $tableInfo['authentication']['all']['user']);
        }
        if (isset($tableInfo['authentication'][$operation]['user'])) {
            $usersArray = array_merge($usersArray, $tableInfo['authentication'][$operation]['user']);
        }
        return $usersArray;
    }

    function getAuthorizedGroups($operation = null)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $groupsArray = array();
        if ($this->dbSettings->getAuthenticationItem('group')) {
            $groupsArray = array_merge($groupsArray, $this->dbSettings->getAuthenticationItem('group'));
        }
        if (isset($tableInfo['authentication']['all']['group'])) {
            $groupsArray = array_merge($groupsArray, $tableInfo['authentication']['all']['group']);
        }
        if (isset($tableInfo['authentication'][$operation]['group'])) {
            $groupsArray = array_merge($groupsArray, $tableInfo['authentication'][$operation]['group']);
        }
        return $groupsArray;
    }

}
