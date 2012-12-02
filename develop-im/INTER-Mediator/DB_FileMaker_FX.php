<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

$currentEr = error_reporting();
error_reporting(0);
include_once('FX/FX.php');
if (error_get_last() !== null) {
// If FX.php isn't installed in valid directories, it shows error message and finishes.
    echo 'INTER-Mediator Error: Data Access Class "FileMaker_FX" requires FX.php on any right directory.';
//    var_dump(error_get_last());
    return;
}
error_reporting($currentEr);

class DB_FileMaker_FX extends DB_AuthCommon implements DB_Access_Interface
{
    var $fx = null;

    function setupFXforAuth($layoutName, $recordCount)
    {
        $this->setupFX_Impl($layoutName, $recordCount,
            $this->dbSettings->getDbSpecUser(), $this->dbSettings->getDbSpecPassword());
    }

    function setupFXforDB($layoutName, $recordCount)
    {
        $this->setupFX_Impl($layoutName, $recordCount,
            $this->dbSettings->getAccessUser(), $this->dbSettings->getAccessPassword());
    }

    function setupFX_Impl($layoutName, $recordCount, $user, $password)
    {
        $this->fx = new FX(
            $this->dbSettings->getDbSpecServer(),
            $this->dbSettings->getDbSpecPort(),
            $this->dbSettings->getDbSpecDataType(),
            $this->dbSettings->getDbSpecProtocol()
        );
        $this->fx->setCharacterEncoding('UTF-8');
        $this->fx->setDBUserPass($user, $password);
        $this->fx->setDBData($this->dbSettings->getDbSpecDatabase(), $layoutName, $recordCount);
    }

    function errorMessageStore($str)
    {
        //$errorInfo = var_export($this->link->errorInfo(), true);
        $this->logger->setErrorMessage("Query Error: [{$str}] Code= Info =");
    }

    function stringReturnOnly($str)
    {
        return str_replace("\n\r", "\r", str_replace("\n", "\r", $str));
    }

    function unifyCRLF($str)
    {
        return str_replace("\n", "\r", str_replace("\r\n", "\r", $str));
    }

    function getFromDB($dataSourceName)
    {
        $context = $this->dbSettings->getDataSourceTargetArray();
        $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(),
            isset($context['records']) ? $context['records'] : 100000000);
        $this->fx->FMSkipRecords(
            (isset($context['paging']) and $context['paging'] === true) ? $this->dbSettings->start : 0);

        $hasFindParams = false;
        if (isset($context['query'])) {
            foreach ($context['query'] as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $this->fx->SetLogicalOR();
                } else {
                    if (isset($condition['operator'])) {
                        $this->fx->AddDBParam($condition['field'], $condition['value'], $condition['operator']);
                    } else {
                        $this->fx->AddDBParam($condition['field'], $condition['value']);
                    }
                    $hasFindParams = true;
                }
            }
        }

        if (isset($this->dbSettings->extraCriteria)) {
            foreach ($this->dbSettings->extraCriteria as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $this->fx->SetLogicalOR();
                } else {
                    $op = $condition['operator'] == '=' ? 'eq' : $condition['operator'];
                    $this->fx->AddDBParam($condition['field'], $condition['value'], $op);
                    $hasFindParams = true;
                }
            }
        }
        if (count($this->dbSettings->foreignFieldAndValue) > 0) {
            foreach ($context['relation'] as $relDef) {
                foreach ($this->dbSettings->foreignFieldAndValue as $foreignDef) {
                    if ($relDef['join-field'] == $foreignDef['field']) {
                        $foreignField = $relDef['foreign-key'];
                        $foreignValue = $foreignDef['value'];
                        $foreignOperator = isset( $relDef['operator'] ) ? $relDef['operator'] : 'eq';
                        $formattedValue = $this->formatter->formatterToDB(
                            "{$dataSourceName}{$this->dbSettings->separator}{$foreignField}", $foreignValue);
                        $this->fx->AddDBParam($foreignField, $formattedValue, $foreignOperator);
                        $hasFindParams = true;
                    }
                }
            }
        }

        if (isset($context['authentication'])) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization("load");
            $authInfoTarget = $this->getTargetForAuthorization("load");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->currentUser) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, $this->dbSettings->currentUser, "eq");
                    $hasFindParams = true;
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->getGroupsOfUser($this->dbSettings->currentUser);
                $groupCriteria = array();
                if (strlen($this->dbSettings->currentUser) == 0 || count($groupCriteria) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                    $hasFindParams = true;
                }
            } else {
                $authorizedUsers = $this->getAuthorizedUsers("load");
                $authorizedGroups = $this->getAuthorizedGroups("load");
                $belongGroups = $this->getGroupsOfUser($this->dbSettings->currentUser);
                if (!in_array($this->dbSettings->currentUser, $authorizedUsers)
                    && array_intersect($belongGroups, $authorizedGroups)
                ) {
                    $authFailure = true;
                }
            }
            if ($authFailure) {
                return null;
            }
        }

        if (isset($context['sort'])) {
            foreach ($context['sort'] as $condition) {
                if (isset($condition['direction'])) {
                    $this->fx->AddSortParam($condition['field'], $condition['direction']);
                } else {
                    $this->fx->AddSortParam($condition['field']);
                }
            }
        }
        if (count($this->dbSettings->extraSortKey) > 0) {
            foreach ($this->dbSettings->extraSortKey as $condition) {
                $this->fx->AddSortParam($condition['field'], $condition['direction']);
            }
        }
        if (isset($context['global'])) {
            foreach ($context['global'] as $condition) {
                if ($condition['db-operation'] == 'load') {
                    $this->fx->SetFMGlobal($condition['field'], $condition['value']);
                }
            }
        }
        if (isset($context['script'])) {
            foreach ($context['script'] as $condition) {
                if ($condition['db-operation'] == 'load') {
                    if ($condition['situation'] == 'pre') {
                        $this->fx->PerformFMScriptPrefind($condition['definition']);
                    } else if ($condition['situation'] == 'presort') {
                        $this->fx->PerformFMScriptPresort($condition['definition']);
                    } else if ($condition['situation'] == 'post') {
                        $this->fx->PerformFMScript($condition['definition']);
                    }
                }
            }
        }
        if ($hasFindParams) {
            $this->fxResult = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        } else {
            $this->fxResult = $this->fx->DoFxAction("show_all", TRUE, TRUE, 'full');
        }

        //var_dump($this->fxResult);
        if (!is_array($this->fxResult)) {
            $this->logger->setErrorMessage(
                get_class($this->fxResult) . ': ' . $this->fxResult->getDebugInfo() . var_export($this->fx, true));
            return null;
        }
        if ($this->fxResult['errorCode'] != 0 && $this->fxResult['errorCode'] != 401) {
            $this->logger->setErrorMessage("FX reports error at find action: "
                . "code={$this->fxResult['errorCode']}, url={$this->fxResult['URL']}");
            return null;
        }
        $this->logger->setDebugMessage($this->fxResult['URL']);
        //$this->logger->setDebugMessage( arrayToJS( $this->fxResult['data'], '' ));
        $this->mainTableCount = $this->fxResult['foundCount'];

        $returnArray = array();
        if (isset($this->fxResult['data'])) {
            foreach ($this->fxResult['data'] as $oneRecord) {
                $oneRecordArray = array();
                foreach ($oneRecord as $field => $dataArray) {
                    if (count($dataArray) == 1) {
                        $oneRecordArray[$field] = $this->formatter->formatterFromDB(
                            "{$dataSourceName}{$this->dbSettings->separator}$field", $dataArray[0]);
                    }
                }
                $returnArray[] = $oneRecordArray;
            }
        }
        return $returnArray;
    }

    function countQueryResult($dataSourceName)
    {
        return $this->mainTableCount;
    }

    function setToDB($dataSourceName)
    {
        $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : 'id';

        if ( isset($tableInfo['query']) )   {
            foreach ($tableInfo['query'] as $condition ) {
                if ( ! $this->dbSettings->primaryKeyOnly || $condition['field'] == $primaryKey ) {
                    $op = $condition['operator'] == '=' ? 'eq' : $condition['operator'];
                    $convertedValue = $this->formatter->formatterToDB(
                        "{$dataSourceName}{$this->dbSettings->separator}{$condition['field']}", $condition['value']);
                    $this->fx->AddDBParam($condition['field'], $convertedValue, $op);
                }
            }
        }

        foreach ($this->dbSettings->extraCriteria as $value) {
            if ( ! $this->dbSettings->primaryKeyOnly || $value['field'] == $primaryKey ) {
                $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
                $convertedValue = $this->formatter->formatterToDB(
                    "{$dataSourceName}{$this->dbSettings->separator}{$value['field']}", $value['value']);
                $this->fx->AddDBParam($value['field'], $convertedValue, $op);
            }
        }
        if (isset($context['authentication'])) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization("update");
            $authInfoTarget = $this->getTargetForAuthorization("update");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->currentUser) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, $this->dbSettings->currentUser, "eq");
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->getGroupsOfUser($this->dbSettings->currentUser);
                $groupCriteria = array();
                if (strlen($this->dbSettings->currentUser) == 0 || count($groupCriteria) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                }
            } else {
                $authorizedUsers = $this->getAuthorizedUsers("update");
                $authorizedGroups = $this->getAuthorizedGroups("update");
                $belongGroups = $this->getGroupsOfUser($this->dbSettings->currentUser);
                if (!in_array($this->dbSettings->currentUser, $authorizedUsers)
                    && array_intersect($belongGroups, $authorizedGroups)
                ) {
                    $authFailure = true;
                }
            }
            if ($authFailure) {
                return false;
            }
        }
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setErrorMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);

        if ($result['errorCode'] > 0) {
            $this->logger->setErrorMessage(
                "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>");
            return false;
        }
        if ($result['foundCount'] == 1) {
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));
                $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
                $this->fx->SetRecordID($recId);
                $counter = 0;
                foreach ($this->dbSettings->fieldsRequired as $field) {
                    $value = $this->dbSettings->fieldsValues[$counter];
                    $counter++;
                    $convVal = $this->stringReturnOnly((is_array($value)) ? implode("\n", $value) : $value);
                    $convVal = $this->formatter->formatterToDB(
                        "{$dataSourceName}{$this->dbSettings->separator}{$field}", $convVal);
                    $this->fx->AddDBParam($field, $convVal);
                }
                if ($counter < 1) {
                    $this->logger->setErrorMessage('No data to update.');
                    return false;
                }
                if (isset($context['global'])) {
                    foreach ($context['global'] as $condition) {
                        if ($condition['db-operation'] == 'update') {
                            $this->fx->SetFMGlobal($condition['field'], $condition['value']);
                        }
                    }
                }
                if (isset($context['script'])) {
                    foreach ($context['script'] as $condition) {
                        if ($condition['db-operation'] == 'update') {
                            if ($condition['situation'] == 'pre') {
                                $this->fx->PerformFMScriptPrefind($condition['definition']);
                            } else if ($condition['situation'] == 'presort') {
                                $this->fx->PerformFMScriptPresort($condition['definition']);
                            } else if ($condition['situation'] == 'post') {
                                $this->fx->PerformFMScript($condition['definition']);
                            }
                        }
                    }
                }
                $result = $this->fx->DoFxAction("update", TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    $this->logger->setErrorMessage(get_class($result) . ': ' . $result->getDebugInfo());
                    return false;
                }
                if ($result['errorCode'] > 0) {
                    $this->logger->setErrorMessage(
                        "FX reports error at edit action: table={$this->getEntityForUpdate()}, "
                            . "code={$result['errorCode']}, url={$result['URL']}<hr>");
                    return false;
                }
                $this->logger->setDebugMessage($result['URL']);
                break;
            }
        } else {

        }
        return true;
    }

    function newToDB($dataSourceName)
    {
        $context = $this->dbSettings->getDataSourceTargetArray();
        $keyFieldName = isset($context['key']) ? $context['key'] : 'id';

        $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
        $countFields = count($this->dbSettings->fieldsRequired);
        for ($i = 0; $i < $countFields; $i++) {
            $field = $this->dbSettings->fieldsRequired[$i];
            $value = $this->dbSettings->fieldsValues[$i];
            if ($field != $keyFieldName) {
                $this->fx->AddDBParam(
                    $field,
                    $this->formatter->formatterToDB(
                        "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->separator}{$field}",
                        $this->unifyCRLF((is_array($value)) ? implode("\r", $value) : $value)
                    )
                );
            }
        }
        if (isset($context['default-values'])) {
            foreach ($context['default-values'] as $itemDef) {
                $field = $itemDef['field'];
                $value = $itemDef['value'];
                if ($field != $keyFieldName) {
                    $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->separator}{$field}";
                    $convVal = $this->unifyCRLF((is_array($value)) ? implode("\r", $value) : $value);
                    $this->fx->AddDBParam($field, $this->formatter->formatterToDB($filedInForm, $convVal));
                }
            }
        }
        if (isset($context['authentication'])) {
            $authInfoField = $this->getFieldForAuthorization("new");
            $authInfoTarget = $this->getTargetForAuthorization("new");
            if ($authInfoTarget == 'field-user') {
                $this->fx->AddDBParam($authInfoField, strlen($this->dbSettings->currentUser) == 0 ? randomString(10) : $this->dbSettings->currentUser);
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->getGroupsOfUser($this->dbSettings->currentUser);
                $this->fx->AddDBParam($authInfoField, strlen($belongGroups[0]) == 0 ? randomString(10) : $belongGroups[0]);
            } else {
                $authorizedUsers = $this->getAuthorizedUsers("new");
                $authorizedGroups = $this->getAuthorizedGroups("new");
                $belongGroups = $this->getGroupsOfUser($this->dbSettings->currentUser);
                if (!in_array($this->dbSettings->currentUser, $authorizedUsers)
                    && array_intersect($belongGroups, $authorizedGroups)
                ) {
                    $authFailure = true;
                }
            }
        }
        if (isset($context['global'])) {
            foreach ($context['global'] as $condition) {
                if ($condition['db-operation'] == 'new') {
                    $this->fx->SetFMGlobal($condition['field'], $condition['value']);
                }
            }
        }
        if (isset($context['script'])) {
            foreach ($context['script'] as $condition) {
                if ($condition['db-operation'] == 'new') {
                    if ($condition['situation'] == 'pre') {
                        $this->fx->PerformFMScriptPrefind($condition['definition']);
                    } else if ($condition['situation'] == 'presort') {
                        $this->fx->PerformFMScriptPresort($condition['definition']);
                    } else if ($condition['situation'] == 'post') {
                        $this->fx->PerformFMScript($condition['definition']);
                    }
                }
            }
        }
        $result = $this->fx->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->errorMessage[] = get_class($result) . ': ' . $result->getDebugInfo();
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        if ($result['errorCode'] > 0 && $result['errorCode'] != 401) {
            $this->logger->setErrorMessage("FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>");
            return false;
        }
        foreach ($result['data'] as $row) {
            $keyValue = $row[$keyFieldName][0];
        }
        return $keyValue;
    }

    function deleteFromDB($dataSourceName)
    {
        $context = $this->dbSettings->getDataSourceTargetArray();
        $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
        foreach ($this->dbSettings->extraCriteria as $value) {
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $this->fx->AddDBParam($value['field'], $value['value'], $op);
        }
        if (isset($context['authentication'])) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization("delete");
            $authInfoTarget = $this->getTargetForAuthorization("delete");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->currentUser) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, $this->dbSettings->currentUser, "eq");
                    $hasFindParams = true;
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->getGroupsOfUser($this->dbSettings->currentUser);
                $groupCriteria = array();
                if (strlen($this->dbSettings->currentUser) == 0 || count($groupCriteria) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                    $hasFindParams = true;
                }
            } else {
                $authorizedUsers = $this->getAuthorizedUsers("delete");
                $authorizedGroups = $this->getAuthorizedGroups("delete");
                $belongGroups = $this->getGroupsOfUser($this->dbSettings->currentUser);
                if (!in_array($this->dbSettings->currentUser, $authorizedUsers)
                    && array_intersect($belongGroups, $authorizedGroups)
                ) {
                    $authFailure = true;
                }
            }
            if ($authFailure) {
                return false;
            }
        }
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->errorMessage[] = get_class($result) . ': ' . $result->getDebugInfo();
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        if ($result['errorCode'] > 0) {
            $this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
            return false;
        }
        if ($result['foundCount'] != 0) {
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));
                $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
                $this->fx->SetRecordID($recId);
                if (isset($context['global'])) {
                    foreach ($context['global'] as $condition) {
                        if ($condition['db-operation'] == 'delete') {
                            $this->fx->SetFMGlobal($condition['field'], $condition['value']);
                        }
                    }
                }
                if (isset($context['script'])) {
                    foreach ($context['script'] as $condition) {
                        if ($condition['db-operation'] == 'delete') {
                            if ($condition['situation'] == 'pre') {
                                $this->fx->PerformFMScriptPrefind($condition['definition']);
                            } else if ($condition['situation'] == 'presort') {
                                $this->fx->PerformFMScriptPresort($condition['definition']);
                            } else if ($condition['situation'] == 'post') {
                                $this->fx->PerformFMScript($condition['definition']);
                            }
                        }
                    }
                }
                $result = $this->fx->DoFxAction("delete", TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    $this->logger->setErrorMessage(get_class($result) . ': ' . $result->getDebugInfo());
                    return false;
                }
                if ($result['errorCode'] > 0) {
                    $this->logger->setErrorMessage(
                        "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>");
                    return false;
                }
                $this->logger->setDebugMessage($result['URL']);
                return true;
                break;
            }
        }
        return false;
    }

    function authSupportStoreChallenge($username, $challenge, $clientId)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($username === 0) {
            $uid = 0;
        } else {
            $uid = $this->authSupportGetUserIdFromUsername($username);
            if ($uid === false) {
                $this->logger->setDebugMessage("User '{$username}' does't exist.");
                return false;
            }
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fx->AddDBParam('user_id', $uid, 'eq');
        $this->fx->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
//            $this->fx->setCharacterEncoding('UTF-8');
//            $this->fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
//            $this->fx->setDBData($this->getDbSpecDatabase(), $hashTable, 1);
            $this->setupFXforAuth($hashTable, 1);
            $this->fx->SetRecordID($recId);
            $this->fx->AddDBParam('hash', $challenge);
            $this->fx->AddDBParam('expired', $currentDTFormat);
            $this->fx->AddDBParam('clienthost', $clientId);
            $this->fx->AddDBParam('user_id', $uid);
            $result = $this->fx->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
            $this->logger->setDebugMessage($result['URL']);
            return true;
        }
        $this->setupFXforAuth($hashTable, 1);
//        $this->fx->setCharacterEncoding('UTF-8');
//        $this->fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
//        $this->fx->setDBData($this->getDbSpecDatabase(), $hashTable, 1);
        $this->fx->AddDBParam('hash', $challenge);
        $this->fx->AddDBParam('expired', $currentDTFormat);
        $this->fx->AddDBParam('clienthost', $clientId);
        $this->fx->AddDBParam('user_id', $uid);
        $result = $this->fx->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        return true;
    }
    function authSupportCheckMediaToken($user)
    {

    }

    function authSupportRetrieveChallenge($username, $clientId, $isDelete = true)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($username === 0) {
            $uid = 0;
        } else {
            $uid = $this->authSupportGetUserIdFromUsername($username);
            if ($uid === false) {
                $this->logger->setDebugMessage("User '{$username}' does't exist.");
                return false;
            }
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fx->AddDBParam('user_id', $uid, 'eq');
        $this->fx->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $hashValue = $row['hash'][0];
            if ( $isDelete )    {
                $this->setupFXforAuth($hashTable, 1);
                $this->fx->SetRecordID($recId);
                $result = $this->fx->DoFxAction("delete", TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                    return false;
                }
            }
            return $hashValue;
        }
        return false;
    }

    function removeOutdatedChallenges()
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }

        $currentDT = new DateTime();
        $timeValue = $currentDT->format("U");

        $this->setupFXforAuth($hashTable, 100000000);
        $this->fx->AddDBParam('expired', date('Y-m-d H:i:s', $timeValue - $this->dbSettings->getExpiringSeconds()));
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->setupFXforAuth($hashTable, 1);
            $this->fx->SetRecordID($recId);
            $result = $this->fx->DoFxAction("delete", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        return true;
    }

    function authSupportRetrieveHashedPassword($username)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->setupFXforAuth($userTable, 1);
        $this->fx->AddDBParam('username', $username, 'eq');
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        foreach ($result['data'] as $key => $row) {
            return $row['hashedpasswd'][0];
        }
        return false;
    }

    function authSupportGetSalt($username)
    {
        $hashedpw = $this->authSupportRetrieveHashedPassword($username);
        return substr($hashedpw, -8);
    }

    function authSupportCreateUser($username, $hashedpassword)
    {
        if ($this->authSupportRetrieveHashedPassword($username) !== false) {
            $this->logger->setErrorMessage('User Already exist: ' . $username);
            return false;
        }
        $userTable = $this->dbSettings->getUserTable();
        $this->setupFXforAuth($userTable, 1);
        $this->fx->AddDBParam('username', $username);
        $this->fx->AddDBParam('hashedpasswd', $hashedpassword);
        $result = $this->fx->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        return true;
    }

    function authSupportChangePassword($username, $hashednewpassword)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->setupFXforAuth($userTable, 1);
        $this->fx->AddDBParam('username', $username, 'eq');
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->setupFXforAuth($userTable, 1);
            $this->fx->SetRecordID($recId);
            $this->fx->AddDBParam("hashedpasswd", $hashednewpassword);
            $result = $this->fx->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
            break;
        }
        return true;
    }

    function authSupportGetUserIdFromUsername($username)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($username === 0) {
            return 0;
        }

        $this->setupFXforAuth($userTable, 1);
        $this->fx->AddDBParam('username', $username, "eq");
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        foreach ($result['data'] as $row) {
            return $row['id'][0];
        }
        return false;
    }

    function authSupportGetGroupNameFromGroupId($groupid)
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable == null) {
            return null;
        }

        $this->setupFXforAuth($groupTable, 1);
        $this->fx->AddDBParam('id', $groupid);
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($result['URL']);
        foreach ($result['data'] as $key => $row) {
            return $row['groupname'][0];
        }
        return false;
    }

    function getGroupsOfUser($user)
    {
        $corrTable = $this->dbSettings->getGroupTable();
        if ($corrTable == null) {
            return array();
        }

        $userid = $this->authSupportGetUserIdFromUsername($user);
        $this->firstLevel = true;
        $this->belongGroups = array();
        $this->resolveGroup($userid);
        $this->candidateGroups = array();
        foreach ($this->belongGroups as $groupid) {
            $this->candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
        }
        return $this->candidateGroups;
    }

    var $candidateGroups;
    var $belongGroups;
    var $firstLevel;

    function resolveGroup($groupid)
    {
        $this->setupFXforAuth($this->dbSettings->getCorrTable(), 1);
        if ($this->firstLevel) {
            $this->fx->AddDBParam('user_id', $groupid);
            $this->firstLevel = false;
        } else {
            $this->fx->AddDBParam('group_id', $groupid);
            $this->belongGroups[] = $groupid;
        }
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        foreach ($result['data'] as $key => $row) {
            if (!in_array($row['dest_group_id'][0], $this->belongGroups)) {
                if (!$this->resolveGroup($row['dest_group_id'][0])) {
                    return false;
                }
            }
        }
    }
}

?>