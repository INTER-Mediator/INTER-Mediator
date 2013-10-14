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
require_once('FX/FX.php');
if (error_get_last() !== null) {
// If FX.php isn't installed in valid directories, it shows error message and finishes.
    echo 'INTER-Mediator Error: Data Access Class "FileMaker_FX" requires FX.php on any right directory.';
    var_dump(error_get_last());
    return;
}
error_reporting($currentEr);

class DB_FileMaker_FX extends DB_AuthCommon implements DB_Access_Interface
{
    private $fx = null;
    private $fxAuth = null;
    private $mainTableCount = 0;
    private $fieldInfo = null;

    public function setupConnection()
    {

    }

    public static function defaultKey()
    {
        return "-recid";
    }

    public function getDefaultKey()
    {
        return "-recid";
    }

    private function setupFXforAuth($layoutName, $recordCount)
    {
        $this->fxAuth = $this->setupFX_Impl($layoutName, $recordCount,
            $this->dbSettings->getDbSpecUser(), $this->dbSettings->getDbSpecPassword());
    }

    private function setupFXforDB($layoutName, $recordCount)
    {
        $this->fx = $this->setupFX_Impl($layoutName, $recordCount,
            $this->dbSettings->getAccessUser(), $this->dbSettings->getAccessPassword());
    }

    private function setupFX_Impl($layoutName, $recordCount, $user, $password)
    {
        $fx = new FX(
            $this->dbSettings->getDbSpecServer(),
            $this->dbSettings->getDbSpecPort(),
            $this->dbSettings->getDbSpecDataType(),
            $this->dbSettings->getDbSpecProtocol()
        );
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($user, $password);
        $fx->setDBData($this->dbSettings->getDbSpecDatabase(), $layoutName, $recordCount);
        return $fx;
    }

    private function stringWithoutPassword($str)
    {
        return str_replace($this->dbSettings->getAccessPassword(), "********", $str);
    }

    private function stringReturnOnly($str)
    {
        return str_replace("\n\r", "\r", str_replace("\n", "\r", $str));
    }

    private function unifyCRLF($str)
    {
        return str_replace("\n", "\r", str_replace("\r\n", "\r", $str));
    }

    public function getFieldInfo($dataSourceName)
    {
        return $this->fieldInfo;
    }

    public function getFromDB($dataSourceName)
    {
        $this->logger->setDebugMessage("##getEntityForRetrieve={$this->dbSettings->getEntityForRetrieve()}",2);
        $this->fieldInfo = null;

        $context = $this->dbSettings->getDataSourceTargetArray();
        
        $usePortal = false;
        if (count($this->dbSettings->getForeignFieldAndValue()) > 0) {
            foreach ($context['relation'] as $relDef) {
                if (isset($relDef['portal']) && $relDef['portal']) {
                    $usePortal = true;
                    $context['records'] = 1;
                    $context['paging'] = true;
                    $this->dbSettings->setDbSpecDataType(str_replace('fmpro', 'fmalt', strtolower($this->dbSettings->getDbSpecDataType())));
                }
            }
        }
        if ($this->dbSettings->getPrimaryKeyOnly()) {
            $this->dbSettings->setDbSpecDataType(str_replace('fmpro', 'fmalt', strtolower($this->dbSettings->getDbSpecDataType())));
        }

        $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(),
            isset($context['records']) ? $context['records'] : 100000000);
        $this->fx->FMSkipRecords(
            (isset($context['paging']) and $context['paging'] === true) ? $this->dbSettings->getStart() : 0);

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
        } elseif ($usePortal && isset($context['view'])) {
            $this->dbSettings->setDataSourceName($context['view']);
            $parentTable = $this->dbSettings->getDataSourceTargetArray();
            if (isset($parentTable['query'])) {
                foreach ($parentTable['query'] as $condition) {
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
            $this->dbSettings->setDataSourceName($context['name']);
        }

        if ($this->dbSettings->getExtraCriteria()) {
            foreach ($this->dbSettings->getExtraCriteria() as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $this->fx->SetLogicalOR();
                } else {
                    $op = $condition['operator'] == '=' ? 'eq' : $condition['operator'];
                    $this->fx->AddDBParam($condition['field'], $condition['value'], $op);
                    $hasFindParams = true;
                }
            }
        }
        
        if (count($this->dbSettings->getForeignFieldAndValue()) > 0) {
            foreach ($context['relation'] as $relDef) {
                foreach ($this->dbSettings->getForeignFieldAndValue() as $foreignDef) {
                    if (isset($relDef['join-field']) && $relDef['join-field'] == $foreignDef['field']) {
                        $foreignField = $relDef['foreign-key'];
                        $foreignValue = $foreignDef['value'];
                        $foreignOperator = isset($relDef['operator']) ? $relDef['operator'] : 'eq';
                        $formattedValue = $this->formatter->formatterToDB(
                            "{$dataSourceName}{$this->dbSettings->getSeparator()}{$foreignField}", $foreignValue);
                        $this->fx->AddDBParam($foreignField, $formattedValue, $foreignOperator);
                        $hasFindParams = true;
                    }
                }
            }
        }

        if (isset($context['authentication'])
            && (isset($context['authentication']['all']) || isset($context['authentication']['load']))
        ) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization("load");
            $authInfoTarget = $this->getTargetForAuthorization("load");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    $this->fx->AddDBParam($authInfoField, $signedUser, "eq");
                    $hasFindParams = true;
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                    $hasFindParams = true;
                }
            } else {
                if ($this->dbSettings->isDBNative()) {
                } else {
                    $authorizedUsers = $this->getAuthorizedUsers("load");
                    $authorizedGroups = $this->getAuthorizedGroups("load");
                    $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                    if (!in_array($this->dbSettings->getCurrentUser(), $authorizedUsers)
                        && count(array_intersect($belongGroups, $authorizedGroups)) == 0
                    ) {
                        $authFailure = true;
                    }
                }
            }
            if ($authFailure) {
                $this->logger->setErrorMessage("Authorization Error.");
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
        } elseif ($usePortal && isset($context['view'])) {
            $this->dbSettings->setDataSourceName($context['view']);
            $parentTable = $this->dbSettings->getDataSourceTargetArray();
            if (isset($parentTable['sort'])) {
                foreach ($parentTable['sort'] as $condition) {
                    if (isset($condition['direction'])) {
                        $this->fx->AddSortParam($condition['field'], $condition['direction']);
                    } else {
                        $this->fx->AddSortParam($condition['field']);
                    }
                }
            }
            $this->dbSettings->setDataSourceName($context['name']);
        }

        if (count($this->dbSettings->getExtraSortKey()) > 0) {
            foreach ($this->dbSettings->getExtraSortKey() as $condition) {
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
//        $this->logger->setErrorMessage(var_export($this->fx, true));

        try {
            if ($hasFindParams) {
                $this->fxResult = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
            } else {
                $this->fxResult = $this->fx->DoFxAction("show_all", TRUE, TRUE, 'full');
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage(var_export($this->fx, true));
        }

        if (!is_array($this->fxResult)) {
            if ($this->dbSettings->isDBNative()) {
                $this->logger->setErrorMessage(
                    $this->stringWithoutPassword(get_class($this->fxResult)
                    . ': ' . $this->fxResult->getDebugInfo()));
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                    $this->stringWithoutPassword(get_class($this->fxResult)
                    . ': ' . $this->fxResult->getDebugInfo()));
            }
            return null;
        }
        if ($this->fxResult['errorCode'] != 0 && $this->fxResult['errorCode'] != 401) {
            $this->logger->setErrorMessage(
                $this->stringWithoutPassword("FX reports error at find action: "
                . "code={$this->fxResult['errorCode']}, url={$this->fxResult['URL']}"));
            return null;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($this->fxResult['URL']));
        $this->mainTableCount = $this->fxResult['foundCount'];

        $isFirstRecord = true;
        $returnArray = array();
        if (isset($this->fxResult['data'])) {
            foreach ($this->fxResult['data'] as $key => $oneRecord) {
                $oneRecordArray = array();
                
                $recId = substr($key, 0, strpos($key, '.'));
                $oneRecordArray['-recid'] = $recId;
                
                foreach ($oneRecord as $field => $dataArray) {
                    if ($isFirstRecord) {
                        $this->fieldInfo[] = $field;
                    }
                    if (count($dataArray) == 1) {
                        if ($usePortal) {
                            $existsRelated = false;
                            foreach ($dataArray as $portalKey => $portalValue) {
                                if (strpos($field, '::') !== false) {
                                    $existsRelated = true;
                                }
                                $oneRecordArray[$portalKey]['-recid'] = $recId;  // parent record id
                                $oneRecordArray[$portalKey][$field] = $this->formatter->formatterFromDB(
                                    "{$dataSourceName}{$this->dbSettings->getSeparator()}$field", $portalValue);
                            }
                            if ($existsRelated == false) {
                                $oneRecordArray = array();
                            }
                        } else {
                            $oneRecordArray[$field] = $this->formatter->formatterFromDB(
                                "{$dataSourceName}{$this->dbSettings->getSeparator()}$field", $dataArray[0]);
                        }
                    } else {
                        foreach ($dataArray as $portalKey => $portalValue) {
                            if ($usePortal && strpos($field, '::') !== false) {
                                if (strpos($field, $dataSourceName . '::') !== false) {
                                    $oneRecordArray[$portalKey]['-recid'] = $recId;  // parent record id
                                    $oneRecordArray[$portalKey][$field] = $this->formatter->formatterFromDB(
                                        "{$dataSourceName}{$this->dbSettings->getSeparator()}$field", $portalValue);
                                }
                            } else {
                                $oneRecordArray[$field][] = $this->formatter->formatterFromDB(
                                    "{$dataSourceName}{$this->dbSettings->getSeparator()}$field", $portalValue);
                            }
                        }
                    }
                }
                if ($usePortal) {
                    foreach ($oneRecordArray as $portalArrayField => $portalArray) {
                        if ($portalArrayField !== '-recid') {
                            $returnArray[] = $portalArray;
                        }
                    }
                    $this->mainTableCount = count($returnArray);
                } else {
                    $returnArray[] = $oneRecordArray;
                }
                $isFirstRecord = false;
            }
        }
        return $returnArray;
    }

    public function countQueryResult($dataSourceName)
    {
        return $this->mainTableCount;
    }

    public function setToDB($dataSourceName)
    {
        $this->fieldInfo = null;

        $context = $this->dbSettings->getDataSourceTargetArray();
        
        $usePortal = false;
        if (isset($context['relation'])) {
            foreach ($context['relation'] as $relDef) {
                if (isset($relDef['portal']) && $relDef['portal']) {
                    $usePortal = true;
                    $context['paging'] = true;
                    $this->dbSettings->setDbSpecDataType(str_replace('fmpro', 'fmalt', strtolower($this->dbSettings->getDbSpecDataType())));
                }
            }
        }

        if ($usePortal) {
            $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(), 1);
        } else {
            $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
        }
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : '-recid';

        if (isset($tableInfo['query'])) {
            foreach ($tableInfo['query'] as $condition) {
                if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    $op = $condition['operator'] == '=' ? 'eq' : $condition['operator'];
                    $convertedValue = $this->formatter->formatterToDB(
                        "{$dataSourceName}{$this->dbSettings->getSeparator()}{$condition['field']}", $condition['value']);
                    $this->fx->AddDBParam($condition['field'], $convertedValue, $op);
                }
            }
        }

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            if (!$this->dbSettings->getPrimaryKeyOnly() || $value['field'] == $primaryKey) {
                $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
                $convertedValue = $this->formatter->formatterToDB(
                    "{$dataSourceName}{$this->dbSettings->getSeparator()}{$value['field']}", $value['value']);
                $this->fx->AddDBParam($value['field'], $convertedValue, $op);
            }
        }
        if (isset($tableInfo['authentication'])
            && (isset($tableInfo['authentication']['all']) || isset($tableInfo['authentication']['update']))
        ) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization("update");
            $authInfoTarget = $this->getTargetForAuthorization("update");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    $this->fx->AddDBParam($authInfoField, $signedUser, "eq");
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                }
            } else {
                if ($this->dbSettings->isDBNative()) {
                } else {
                    $authorizedUsers = $this->getAuthorizedUsers("update");
                    $authorizedGroups = $this->getAuthorizedGroups("update");
                    $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                    if (!in_array($this->dbSettings->getCurrentUser(), $authorizedUsers)
                        && array_intersect($belongGroups, $authorizedGroups)
                    ) {
                        $authFailure = true;
                    }
                }
            }
            if ($authFailure) {
                return false;
            }
        }
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                    $this->stringWithoutPassword(get_class($result) . ': ' . $result->getDebugInfo()));
            }
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
//        $this->logger->setDebugMessage($this->stringWithoutPassword(var_export($this->dbSettings->getFieldsRequired(),true)));

        if ($result['errorCode'] > 0) {
            $this->logger->setErrorMessage($this->stringWithoutPassword(
                "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>"));
            return false;
        }
        if ($result['foundCount'] == 1) {
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));
                if ($usePortal) {
                    $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(), 1);
                } else {
                    $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
                }
                $this->fx->SetRecordID($recId);
                $counter = 0;
                $fieldValues = $this->dbSettings->getValue();
                foreach ($this->dbSettings->getFieldsRequired() as $field) {
                    $dotPos = strpos($field, '.');
                    $originalfield = substr($field, 0, $dotPos);
                    $value = $fieldValues[$counter];
                    $counter++;
                    $convVal = $this->stringReturnOnly((is_array($value)) ? implode("\n", $value) : $value);
                    $convVal = $this->formatter->formatterToDB(
                        "{$dataSourceName}{$this->dbSettings->getSeparator()}{$originalfield}", $convVal);
                    $this->fx->AddDBParam($field, $convVal);
                }
                if ($counter < 1) {
                    $this->logger->setErrorMessage('No data to update.');
                    return false;
                }
                if (isset($tableInfo['global'])) {
                    foreach ($tableInfo['global'] as $condition) {
                        if ($condition['db-operation'] == 'update') {
                            $this->fx->SetFMGlobal($condition['field'], $condition['value']);
                        }
                    }
                }
                if (isset($tableInfo['script'])) {
                    foreach ($tableInfo['script'] as $condition) {
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
                    $this->logger->setErrorMessage($this->stringWithoutPassword(
                        get_class($result) . ': ' . $result->getDebugInfo()));
                    return false;
                }
                if ($result['errorCode'] > 0) {
                    $this->logger->setErrorMessage($this->stringWithoutPassword(
                        "FX reports error at edit action: table={$this->dbSettings->getEntityForUpdate()}, "
                        . "code={$result['errorCode']}, url={$result['URL']}<hr>"));
                    return false;
                }
                $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
                break;
            }
        } else {

        }
        return true;
    }

    public function newToDB($dataSourceName, $bypassAuth)
    {
        $this->fieldInfo = null;

        $context = $this->dbSettings->getDataSourceTargetArray();
        
        $usePortal = false;
        if (isset($context['relation'])) {
            foreach ($context['relation'] as $relDef) {
                if (isset($relDef['portal']) && $relDef['portal']) {
                    $usePortal = true;
                    $context['paging'] = true;
                    $this->dbSettings->setDbSpecDataType(str_replace('fmpro', 'fmalt', strtolower($this->dbSettings->getDbSpecDataType())));
                }
            }
        }

        $keyFieldName = isset($context['key']) ? $context['key'] : '-recid';

        $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
        $requiredFields = $this->dbSettings->getFieldsRequired();
        $countFields = count($requiredFields);
        $fieldValues = $this->dbSettings->getValue();
        for ($i = 0; $i < $countFields; $i++) {
            $field = $requiredFields[$i];
            $value = $fieldValues[$i];
            if ($field != $keyFieldName) {
                $this->fx->AddDBParam(
                    $field,
                    $this->formatter->formatterToDB(
                        "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}",
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
                    $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
                    $convVal = $this->unifyCRLF((is_array($value)) ? implode("\r", $value) : $value);
                    $this->fx->AddDBParam($field, $this->formatter->formatterToDB($filedInForm, $convVal));
                }
            }
        }
        if (!$bypassAuth && isset($context['authentication'])
            && (isset($context['authentication']['all']) || isset($context['authentication']['new']))
        ) {
            $authInfoField = $this->getFieldForAuthorization("new");
            $authInfoTarget = $this->getTargetForAuthorization("new");
            if ($authInfoTarget == 'field-user') {
                $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                $this->fx->AddDBParam($authInfoField,
                    strlen($this->dbSettings->getCurrentUser()) == 0 ? randomString(10) : $signedUser);
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                $this->fx->AddDBParam($authInfoField,
                    strlen($belongGroups[0]) == 0 ? randomString(10) : $belongGroups[0]);
            } else {
                if ($this->dbSettings->isDBNative()) {
                } else {
                    $authorizedUsers = $this->getAuthorizedUsers("new");
                    $authorizedGroups = $this->getAuthorizedGroups("new");
                    $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                    if (!in_array($this->dbSettings->getCurrentUser(), $authorizedUsers)
                        && array_intersect($belongGroups, $authorizedGroups)
                    ) {
                        $authFailure = true;
                    }
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
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->errorMessage[] = get_class($result) . ': ' . $result->getDebugInfo();
            }
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        if ($result['errorCode'] > 0 && $result['errorCode'] != 401) {
            $this->logger->setErrorMessage($this->stringWithoutPassword(
                "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>"));
            return false;
        }
        foreach ($result['data'] as $key => $row) {
            if ($keyFieldName == '-recid') {
                $recId = substr($key, 0, strpos($key, '.'));
                $keyValue = $recId;
            } else {
                $keyValue = $row[$keyFieldName][0];
            }
        }
        return $keyValue;
    }

    public function deleteFromDB($dataSourceName)
    {
        $this->fieldInfo = null;

        $context = $this->dbSettings->getDataSourceTargetArray();
        
        $usePortal = false;
        if (isset($context['relation'])) {
            foreach ($context['relation'] as $relDef) {
                if (isset($relDef['portal']) && $relDef['portal']) {
                    $usePortal = true;
                    $context['paging'] = true;
                    $this->dbSettings->setDbSpecDataType(str_replace('fmpro', 'fmalt', strtolower($this->dbSettings->getDbSpecDataType())));
                }
            }
        }

        if ($usePortal) {
            $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(), 1);
        } else {
            $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
        }

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $this->fx->AddDBParam($value['field'], $value['value'], $op);
        }
        if (isset($context['authentication'])
            && (isset($context['authentication']['all']) || isset($context['authentication']['delete']))
        ) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization("delete");
            $authInfoTarget = $this->getTargetForAuthorization("delete");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    $this->fx->AddDBParam($authInfoField, $signedUser, "eq");
                    $hasFindParams = true;
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                $groupCriteria = array();
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($groupCriteria) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                    $hasFindParams = true;
                }
            } else {
                if ($this->dbSettings->isDBNative()) {
                } else {
                    $authorizedUsers = $this->getAuthorizedUsers("delete");
                    $authorizedGroups = $this->getAuthorizedGroups("delete");
                    $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                    if (!in_array($this->dbSettings->getCurrentUser(), $authorizedUsers)
                        && array_intersect($belongGroups, $authorizedGroups)
                    ) {
                        $authFailure = true;
                    }
                }
            }
            if ($authFailure) {
                return false;
            }
        }
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->errorMessage[] = get_class($result) . ': ' . $result->getDebugInfo();
            }
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
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
                    if ($this->dbSettings->isDBNative()) {
                        $this->dbSettings->setRequireAuthentication(true);
                    } else {

                        $this->logger->setErrorMessage($this->stringWithoutPassword(
                            get_class($result) . ': ' . $result->getDebugInfo()));
                    }
                    return false;
                }
                if ($result['errorCode'] > 0) {
                    $this->logger->setErrorMessage($this->stringWithoutPassword(
                        "FX reports error at delete action: code={$result['errorCode']}, url={$result['URL']}<hr>"));
                    return false;
                }
                $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
            }
        }
        return true;
    }

    public function authSupportStoreChallenge($uid, $challenge, $clientId)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fxAuth->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $this->setupFXforAuth($hashTable, 1);
            $this->fxAuth->SetRecordID($recId);
            $this->fxAuth->AddDBParam('hash', $challenge);
            $this->fxAuth->AddDBParam('expired', $currentDTFormat);
            $this->fxAuth->AddDBParam('clienthost', $clientId);
            $this->fxAuth->AddDBParam('user_id', $uid);
            $result = $this->fxAuth->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
            $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
            return true;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('hash', $challenge);
        $this->fxAuth->AddDBParam('expired', $currentDTFormat);
        $this->fxAuth->AddDBParam('clienthost', $clientId);
        $this->fxAuth->AddDBParam('user_id', $uid);
        $result = $this->fxAuth->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        return true;
    }

    public function authSupportCheckMediaToken($uid)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }$this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fxAuth->AddDBParam('clienthost', '_im_media', 'eq');
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $expiredDT = new DateTime($row['expired'][0]);
            $hashValue = $row['hash'][0];
            $currentDT = new DateTime();
            $seconds = $currentDT->format("U") - $expiredDT->format("U");
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return false;
            }
            return $hashValue;
        }
        return false;
    }

    public function authSupportRetrieveChallenge($uid, $clientId, $isDelete = true)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fxAuth->AddDBParam('clienthost', $clientId, 'eq');
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $hashValue = $row['hash'][0];
            if ($isDelete) {
                $this->setupFXforAuth($hashTable, 1);
                $this->fxAuth->SetRecordID($recId);
                $result = $this->fxAuth->DoFxAction("delete", TRUE, TRUE, 'full');
                if (!is_array($result)) {
                    $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                    return false;
                }
            }
            return $hashValue;
        }
        return false;
    }

    public function authSupportRemoveOutdatedChallenges()
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }

        $currentDT = new DateTime();
        $timeValue = $currentDT->format("U");

        $this->setupFXforAuth($hashTable, 100000000);
        $this->fxAuth->AddDBParam('expired', date('m/d/Y H:i:s', $timeValue - $this->dbSettings->getExpiringSeconds()), 'lt');
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->setupFXforAuth($hashTable, 1);
            $this->fxAuth->SetRecordID($recId);
            $result = $this->fxAuth->DoFxAction("delete", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        return true;
    }

    public function authSupportRetrieveHashedPassword($username)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->setupFXforAuth($userTable, 1);
        $this->fxAuth->AddDBParam('username', $username, 'eq');
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        if ((!is_array($result) || count($result['data']) < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->setupFXforAuth($userTable, 1);
            $this->fxAuth->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
            $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        }
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        foreach ($result['data'] as $key => $row) {
            return $row['hashedpasswd'][0];
        }
        return false;
    }

//    function authSupportGetSalt($username)
//    {
//        $hashedpw = $this->authSupportRetrieveHashedPassword($username);
//        return substr($hashedpw, -8);
//    }
//
    public function authSupportCreateUser($username, $hashedpassword)
    {
        if ($this->authSupportRetrieveHashedPassword($username) !== false) {
            $this->logger->setErrorMessage('User Already exist: ' . $username);
            return false;
        }
        $userTable = $this->dbSettings->getUserTable();
        $this->setupFXforAuth($userTable, 1);
        $this->fxAuth->AddDBParam('username', $username);
        $this->fxAuth->AddDBParam('hashedpasswd', $hashedpassword);
        $result = $this->fxAuth->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        return true;
    }

    public function authSupportChangePassword($username, $hashednewpassword)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->setupFXforAuth($userTable, 1);
        $this->fxAuth->AddDBParam('username', $username, 'eq');
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ((!is_array($result) || count($result['data']) < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->setupFXforAuth($userTable, 1);
            $this->fxAuth->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->setupFXforAuth($userTable, 1);
            $this->fxAuth->SetRecordID($recId);
            $this->fxAuth->AddDBParam("hashedpasswd", $hashednewpassword);
            $result = $this->fxAuth->DoFxAction("update", TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
            break;
        }
        return true;
    }

    public function authSupportGetUserIdFromUsername($username)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($username === 0) {
            return 0;
        }

        $this->setupFXforAuth($userTable, 1);
        $this->fxAuth->AddDBParam('username', $username, "eq");
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['id'][0];
        }
        return false;
    }

    public function authSupportGetUsernameFromUserId($userid)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($userid === 0) {
            return 0;
        }

        $this->setupFXforAuth($userTable, 1);
        $this->fxAuth->AddDBParam('id', $userid, "eq");
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['username'][0];
        }
        return false;
    }

    public function authSupportGetUserIdFromEmail($email)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($email === 0) {
            return 0;
        }

        $this->setupFXforAuth($userTable, 1);
        $this->fxAuth->AddDBParam('email', str_replace("@", "\\@", $email), "eq");
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $row) {
            return $row['id'][0];
        }
        return false;
    }

    public function authSupportUnifyUsernameAndEmail($username)
    {
        if (!$this->dbSettings->getEmailAsAccount() || $this->dbSettings->isDBNative()) {
            return $username;
        }

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null || is_null($username) || $username === 0 || $username === '') {
            return false;
        }

        $this->setupFXforAuth($userTable, 55555);
        $this->fxAuth->AddDBParam('username', $username, "eq");
        $this->fxAuth->AddDBParam('email', str_replace("@", "\\@", $username), "eq");
        $this->fxAuth->SetLogicalOR();
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        $usernameCandidate = '';
        foreach ($result['data'] as $row) {
            if ($row['username'][0] == $username) {
                $usernameCandidate = $username;
            }
            if ($row['email'][0] == $username) {
                $usernameCandidate = $row['username'][0];
            }
        }
        return $usernameCandidate;
    }

    public function authSupportGetGroupNameFromGroupId($groupid)
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable == null) {
            return null;
        }

        $this->setupFXforAuth($groupTable, 1);
        $this->fxAuth->AddDBParam('id', $groupid);
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $key => $row) {
            return $row['groupname'][0];
        }
        return false;
    }

    public function authSupportGetGroupsOfUser($user)
    {
        $corrTable = $this->dbSettings->getCorrTable();
        if ($corrTable == null) {
            return array();
        }

        $userid = $this->authSupportGetUserIdFromUsername($user);
        if ($userid === false) {
            $userid = $this->authSupportGetUserIdFromEmail($user);
        }
        $this->firstLevel = true;
        $this->belongGroups = array();
        $this->resolveGroup($userid);
        $this->candidateGroups = array();
        foreach ($this->belongGroups as $groupid) {
            $this->candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
        }
        return $this->candidateGroups;
    }

    private $candidateGroups;
    private $belongGroups;
    private $firstLevel;

    private function resolveGroup($groupid)
    {
        $this->setupFXforAuth($this->dbSettings->getCorrTable(), 1);
        if ($this->firstLevel) {
            $this->fxAuth->AddDBParam('user_id', $groupid);
            $this->firstLevel = false;
        } else {
            $this->fxAuth->AddDBParam('group_id', $groupid);
            $this->belongGroups[] = $groupid;
        }
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $key => $row) {
            if (!in_array($row['dest_group_id'][0], $this->belongGroups)) {
                if (!$this->resolveGroup($row['dest_group_id'][0])) {
                    return false;
                }
            }
        }
    }

    public function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('hash', $hash);
        $this->fxAuth->AddDBParam('expired', $currentDTFormat);
        $this->fxAuth->AddDBParam('clienthost', $clienthost);
        $this->fxAuth->AddDBParam('user_id', $userid);
        $result = $this->fxAuth->DoFxAction("new", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        return true;
    }

    public function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('user_id', $userid, 'eq');
        $this->fxAuth->AddDBParam('clienthost', $randdata, 'eq');
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $hashValue = $row['hash'][0];
            $expiredDT = $row['expired'][0];

            $expired = strptime($expiredDT, "%m/%d/%Y %H:%M:%S");
            $expiredValue = mktime($expired['tm_hour'], $expired['tm_min'], $expired['tm_sec'],
                $expired['tm_mon'] + 1, $expired['tm_mday'], $expired['tm_year'] + 1900);
            $currentDT = new DateTime();
            $timeValue = $currentDT->format("U");
            if ($timeValue > $expiredValue + 3600) {
                return false;
            }
            if ($hash == $hashValue) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        $this->setupFXforAuth($tableName, 1);
        $this->fxAuth->AddDBParam($userField, $user, 'eq');
        $this->fxAuth->AddDBParam($keyField, $keyValue, 'eq');
        $result = $this->fxAuth->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutPassword($result['URL']));
        foreach ($result['data'] as $key => $row) {
            return true;
        }
        return false;
    }

}
