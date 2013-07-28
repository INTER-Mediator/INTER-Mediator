<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2013 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

function IM_Dummy_Entry($datasource, $options, $dbspecification, $debug = false)
{
    global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;
    $globalDataSource = $datasource;
    $globalOptions = $options;
    $globalDBSpecs = $dbspecification;
    $globalDebug = $debug;
}

function getValueFromArray($ar, $index1, $index2 = null, $index3 = null)
{
    $value = null;
    if ($index1 !== null && $index2 !== null && $index3 !== null) {
        if (isset($ar[$index1]) && isset($ar[$index1][$index2]) && isset($ar[$index1][$index2][$index3])) {
            $value = $ar[$index1][$index2][$index3];
        }
    } else if ($index1 !== null && $index2 !== null && $index3 === null) {
        if (isset($ar[$index1]) && isset($ar[$index1][$index2])) {
            $value = $ar[$index1][$index2];
        }
    } else if ($index1 !== null && $index2 === null && $index3 === null) {
        if (isset($ar[$index1])) {
            $value = $ar[$index1];
        }
    }
    if ($value === true) {
        $value = "true";
    }
    if ($value === false) {
        $value = "false";
    }
    return $value;
}

class DB_DefEditor extends DB_AuthCommon implements DB_Access_Interface
{
    var $recordCount;

    function getFromDB($dataSourceName)
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;

        $filePath = $this->dbSettings->getCriteriaValue('target');
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage("The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            return null;
        }
        eval(str_replace("<?php", "", str_replace("?>", "", str_replace("IM_Entry", "IM_Dummy_Entry", $fileContent))));
        $result = array();
        $seq = 0;
        switch ($dataSourceName) {
            case 'contexts':
                foreach ($globalDataSource as $context) {
                    $result[] = array(
                        'id' => $seq,
                        'name' => getValueFromArray($context, 'name'),
                        'table' => getValueFromArray($context, 'table'),
                        'view' => getValueFromArray($context, 'view'),
                        'records' => getValueFromArray($context, 'records'),
                        'paging' => getValueFromArray($context, 'paging'),
                        'key' => getValueFromArray($context, 'key'),
                        'sequence' => getValueFromArray($context, 'sequence'),
                        'extending-class' => getValueFromArray($context, 'extending-class'),
                        'protect-writing' => getValueFromArray($context, 'protect-writing'),
                        'protect-reading' => getValueFromArray($context, 'protect-reading'),
                        'db-class' => getValueFromArray($context, 'db-class'),
                        'dsn' => getValueFromArray($context, 'dsn'),
                        'option' => getValueFromArray($context, 'option'),
                        'database' => getValueFromArray($context, 'database'),
                        'user' => getValueFromArray($context, 'user'),
                        'password' => getValueFromArray($context, 'password'),
                        'server' => getValueFromArray($context, 'server'),
                        'port' => getValueFromArray($context, 'port'),
                        'protocol' => getValueFromArray($context, 'protocol'),
                        'datatype' => getValueFromArray($context, 'datatype'),
                        'cache' => getValueFromArray($context, 'cache'),
                        'post-reconstruct' => getValueFromArray($context, 'post-reconstruct'),
                        'post-dismiss-message' => getValueFromArray($context, 'post-dismiss-message'),
                        'post-move-url' => getValueFromArray($context, 'post-move-url'),
                        'repeat-control' => getValueFromArray($context, 'repeat-control'),
                        'post-repeater' => getValueFromArray($context, 'post-repeater'),
                        'post-enclosure' => getValueFromArray($context, 'post-enclosure'),
                        'authentication-media-handling' => getValueFromArray($context, 'authentication', 'media-handling'),
                        'authentication-all-user' => getValueFromArray($context, 'authentication', 'all', 'user'),
                        'authentication-all-group' => getValueFromArray($context, 'authentication', 'all', 'group'),
                        'authentication-all-target' => getValueFromArray($context, 'authentication', 'all', 'target'),
                        'authentication-all-field' => getValueFromArray($context, 'authentication', 'all', 'field'),
                        'authentication-load-user' => getValueFromArray($context, 'authentication', 'load', 'user'),
                        'authentication-load-group' => getValueFromArray($context, 'authentication', 'load', 'group'),
                        'authentication-load-target' => getValueFromArray($context, 'authentication', 'load', 'target'),
                        'authentication-load-field' => getValueFromArray($context, 'authentication', 'load', 'field'),
                        'authentication-update-user' => getValueFromArray($context, 'authentication', 'update', 'user'),
                        'authentication-update-group' => getValueFromArray($context, 'authentication', 'update', 'group'),
                        'authentication-update-target' => getValueFromArray($context, 'authentication', 'update', 'target'),
                        'authentication-update-field' => getValueFromArray($context, 'authentication', 'update', 'field'),
                        'authentication-new-user' => getValueFromArray($context, 'authentication', 'new', 'user'),
                        'authentication-new-group' => getValueFromArray($context, 'authentication', 'new', 'group'),
                        'authentication-new-target' => getValueFromArray($context, 'authentication', 'new', 'target'),
                        'authentication-new-field' => getValueFromArray($context, 'authentication', 'new', 'field'),
                        'authentication-delete-user' => getValueFromArray($context, 'authentication', 'delete', 'user'),
                        'authentication-delete-group' => getValueFromArray($context, 'authentication', 'delete', 'group'),
                        'authentication-delete-target' => getValueFromArray($context, 'authentication', 'delete', 'target'),
                        'authentication-delete-field' => getValueFromArray($context, 'authentication', 'delete', 'field'),
                    );
                    $seq++;
                }
                break;
            case 'relation':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['relation'])) {
                    foreach ($globalDataSource[$contextID]['relation'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'foreign-key' => getValueFromArray($rel, 'foreign-key'),
                            'join-field' => getValueFromArray($rel, 'join-field'),
                            'operator' => getValueFromArray($rel, 'operator'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'query':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['query'])) {
                    foreach ($globalDataSource[$contextID]['query'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'field' => getValueFromArray($rel, 'field'),
                            'value' => getValueFromArray($rel, 'value'),
                            'operator' => getValueFromArray($rel, 'operator'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'sort':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['sort'])) {
                    foreach ($globalDataSource[$contextID]['sort'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'field' => getValueFromArray($rel, 'field'),
                            'direction' => getValueFromArray($rel, 'direction'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'default-values':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['default-values'])) {
                    foreach ($globalDataSource[$contextID]['default-values'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'field' => getValueFromArray($rel, 'field'),
                            'value' => getValueFromArray($rel, 'value'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'validation':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['validation'])) {
                    foreach ($globalDataSource[$contextID]['validation'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'field' => getValueFromArray($rel, 'field'),
                            'rule' => getValueFromArray($rel, 'rule'),
                            'message' => getValueFromArray($rel, 'message'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'script':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['script'])) {
                    foreach ($globalDataSource[$contextID]['script'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'db-operation' => getValueFromArray($rel, 'db-operation'),
                            'situation' => getValueFromArray($rel, 'situation'),
                            'definition' => getValueFromArray($rel, 'definition'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'global':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['global'])) {
                    foreach ($globalDataSource[$contextID]['global'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'db-operation' => getValueFromArray($rel, 'db-operation'),
                            'field' => getValueFromArray($rel, 'field'),
                            'value' => getValueFromArray($rel, 'value'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'file-upload':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['file-upload'])) {
                    foreach ($globalDataSource[$contextID]['file-upload'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'field' => getValueFromArray($rel, 'field'),
                            'context' => getValueFromArray($rel, 'context'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'options':
                $result[] = array(
                    'id' => $seq,
                    'separator' => getValueFromArray($globalOptions, 'separator'),
                    'transaction' => getValueFromArray($globalOptions, 'transaction'),
                    'media-root-dir' => getValueFromArray($globalOptions, 'media-root-dir'),
                    'media-context' => getValueFromArray($globalOptions, 'media-context'),
                    'authentication-user-table' => getValueFromArray($globalOptions, 'authentication', 'user-table'),
                    'authentication-group-table' => getValueFromArray($globalOptions, 'authentication', 'group-table'),
                    'authentication-corresponding-table' => getValueFromArray($globalOptions, 'authentication', 'corresponding-table'),
                    'authentication-challenge-table' => getValueFromArray($globalOptions, 'authentication', 'challenge-table'),
                    'authentication-authexpired' => getValueFromArray($globalOptions, 'authentication', 'authexpired'),
                    'authentication-realm' => getValueFromArray($globalOptions, 'authentication', 'realm'),
                    'authentication-email-as-username' => getValueFromArray($globalOptions, 'authentication', 'email-as-username'),
                );
                $seq++;
                break;
            case 'aliases':
                if (isset($globalOptions['aliases'])) {
                    foreach ($globalOptions['aliases'] as $rel => $org) {
                        $result[] = array(
                            'id' => $seq,
                            'alias' => $rel,
                            'original' => $org,
                        );
                        $seq++;
                    }
                }
                break;
            case 'browser-compatibility':
                if (isset($globalOptions['browser-compatibility'])) {
                    foreach ($globalOptions['browser-compatibility'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'browserdef' => $rel,
                        );
                        $seq++;
                    }
                }
                break;
            case 'formatter':
                if (isset($globalOptions['formatter'])) {
                    foreach ($globalOptions['formatter'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'field' => getValueFromArray($rel, 'field'),
                            'converter-class' => getValueFromArray($rel, 'converter-class'),
                            'parameter' => getValueFromArray($rel, 'parameter'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'dbsettings':
                $result[] = array(
                    'id' => $seq,
                    'db-class' => getValueFromArray($globalDBSpecs, 'db-class'),
                    'dsn' => getValueFromArray($globalDBSpecs, 'dsn'),
                    'option' => getValueFromArray($globalDBSpecs, 'option'),
                    'database' => getValueFromArray($globalDBSpecs, 'database'),
                    'user' => getValueFromArray($globalDBSpecs, 'user'),
                    'password' => getValueFromArray($globalDBSpecs, 'password'),
                    'server' => getValueFromArray($globalDBSpecs, 'server'),
                    'port' => getValueFromArray($globalDBSpecs, 'port'),
                    'protocol' => getValueFromArray($globalDBSpecs, 'protocol'),
                    'datatype' => getValueFromArray($globalDBSpecs, 'datatype'),
                );
                $seq++;
                break;
            case 'external-db':
                if (isset($globalDBSpecs['external-db'])) {
                    foreach ($globalDBSpecs['external-db'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'db' => $rel,
                        );
                    }
                }
                break;
            case 'debug':
                $result[] = array(
                    'id' => 0,
                    'debug' => $globalDebug
                );
                $seq++;
                break;
        }
        $this->recordCount = $seq;
        return $result;
    }

    function countQueryResult($dataSourceName)
    {
        return $this->recordCount;
    }

    function setToDB($dataSourceName)
    {
        // TODO: Implement setToDB() method.
    }

    function newToDB($dataSourceName, $bypassAuth)
    {
        // TODO: Implement newToDB() method.
    }

    function deleteFromDB($dataSourceName)
    {
        // TODO: Implement deleteFromDB() method.
    }

    function getFieldInfo($dataSourceName)
    {
        // TODO: Implement getFieldInfo() method.
    }

    function authSupportStoreChallenge($username, $challenge, $clientId)
    {
        // TODO: Implement authSupportStoreChallenge() method.
    }

    function authSupportRemoveOutdatedChallenges()
    {
        // TODO: Implement authSupportRemoveOutdatedChallenges() method.
    }

    function authSupportRetrieveChallenge($username, $clientId, $isDelete = true)
    {
        // TODO: Implement authSupportRetrieveChallenge() method.
    }

    function authSupportRetrieveHashedPassword($username)
    {
        // TODO: Implement authSupportRetrieveHashedPassword() method.
    }

    function authSupportCreateUser($username, $hashedpassword)
    {
        // TODO: Implement authSupportCreateUser() method.
    }

    function authSupportChangePassword($username, $hashednewpassword)
    {
        // TODO: Implement authSupportChangePassword() method.
    }

    function authSupportCheckMediaToken($user)
    {
        // TODO: Implement authSupportCheckMediaToken() method.
    }

    function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        // TODO: Implement authSupportCheckMediaPrivilege() method.
    }

    function authSupportGetUserIdFromEmail($email)
    {
        // TODO: Implement authSupportGetUserIdFromEmail() method.
    }

    function authSupportGetUserIdFromUsername($username)
    {
        // TODO: Implement authSupportGetUserIdFromUsername() method.
    }

    function authSupportGetUsernameFromUserId($userid)
    {
        // TODO: Implement authSupportGetUsernameFromUserId() method.
    }

    function authSupportGetGroupNameFromGroupId($groupid)
    {
        // TODO: Implement authSupportGetGroupNameFromGroupId() method.
    }

    function authSupportGetGroupsOfUser($user)
    {
        // TODO: Implement authSupportGetGroupsOfUser() method.
    }

    function authSupportUnifyUsernameAndEmail($username)
    {
        // TODO: Implement authSupportUnifyUsernameAndEmail() method.
    }

    function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        // TODO: Implement authSupportStoreIssuedHashForResetPassword() method.
    }

    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        // TODO: Implement authSupportCheckIssuedHashForResetPassword() method.
    }
}