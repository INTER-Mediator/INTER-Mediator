<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

$currentEr = error_reporting();
error_reporting(0);
require_once('lib/FX/FX.php');
require_once('lib/FX/datasource_classes/RetrieveFM7Data.class.php');
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
    private $fxAlt = null;
    private $mainTableCount = 0;
    private $fieldInfo = null;
    private $updatedRecord = null;

    /**
     * @param $str
     */
    private function errorMessageStore($str)
    {
        $this->logger->setErrorMessage("Query Error: [{$str}] Error Code={$this->fx->lastErrorCode}");
    }

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

    public function requireUpdatedRecord($value)
    {
        // always can get the new record for FileMaker Server.
    }

    public function updatedRecord()
    {
        return $this->updatedRecord;
    }

    public function isExistRequiredTable()
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        if ($regTable == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        
        $this->setupFXforDB($regTable, 1);
        $this->fxResult = $this->fx->DoFxAction('show_all', TRUE, TRUE, 'full');
        if ($this->fxResult['errorCode'] != 0 && $this->fxResult['errorCode'] != 401) {
            $this->errorMessageStore("The table '{$regTable}' doesn't exist in the database.");
            return false;
        }
        return true;
    }

    public function register($clientId, $entity, $condition, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        $this->setupFXforDB($regTable, 1);
        $this->fx->AddDBParam('clientid', $clientId);
        $this->fx->AddDBParam('entity', $entity);
        $this->fx->AddDBParam('conditions', $condition);
        $this->fx->AddDBParam('registereddt', $currentDTFormat);
        $result = $this->fx->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at insert action: " . 
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        }
        foreach ($result['data'] as $recmodid => $recordData) {
            foreach ($recordData as $field => $value) {
                if ($field == 'id') {
                    $newContextId = $value[0];
                }
            }
        }
        foreach ($pkArray as $pk) {
            $this->setupFXforDB($pksTable, 1);
            $this->fx->AddDBParam('context_id', $newContextId);
            $this->fx->AddDBParam('pk', $pk);
            $result = $this->fx->DoFxAction('new', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(
                    $this->stringWithoutCredential("FX reports error at insert action: " . 
                        "code={$result['errorCode']}, url={$result['URL']}"));
                $this->errorMessageStore(
                    $this->stringWithoutCredential("FX reports error at insert action: " . 
                        "code={$result['errorCode']}, url={$result['URL']}"));
                return false;
            }
        }
        return $newContextId;
    }

    public function unregister($clientId, $tableKeys)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;

        $hasFindParams = false;
        $this->setupFXforDB($regTable, 'all');
        if ($tableKeys) {
            $subCriteria = array();
            foreach ($tableKeys as $regId)   {
                $hasFindParams = true;
                $this->fx->AddDBParam('id', $regId, 'eq');
            }
        }

        if ($hasFindParams) {
            $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        } else {
            $result = $this->fx->DoFxAction('show_all', TRUE, TRUE, 'full');
        }
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at find action: " . 
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        } else {
            if ($result['foundCount'] > 0) {
                $this->setupFXforDB($regTable, '');
                foreach ($result['data'] as $key => $row) {
                    $recId = substr($key, 0, strpos($key, '.'));
                    $this->fx->SetRecordID($recId);
                    $this->fx->DoFxAction('delete', TRUE, TRUE, 'full');
                }
            }
        }
        return true;
    }

    public function matchInRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $originPK = $pkArray[0];
        $this->setupFXforDB($regTable, 'all');
        $this->fx->AddDBParam('clientid', $clientId, 'neq');
        $this->fx->AddDBParam('entity', $entity, 'eq');
        $this->fx->AddSortParam('clientid');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $contextIds = array();
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at find action: " . 
                    "code={$result['errorCode']}, url={$result['URL']}"));
        } else {
            if ($result['foundCount'] > 0) {
                foreach ($result['data'] as $recmodid => $recordData) {
                    foreach ($recordData as $field => $value) {
                        if ($field == 'id') {
                            $targetId = $value[0];
                        }
                        if ($field == 'clientid') {
                            $targetClient = $value[0];
                        }
                    }
                    $contextIds[] = array($targetId, $targetClient);
                }
            }
        }

        foreach ($contextIds as $key => $context) {
            $this->setupFXforDB($pksTable, '1');
            $this->fx->AddDBParam('context_id', $context[0], 'eq');
            $this->fx->AddDBParam('pk', $originPK, 'eq');
            $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
                $this->errorMessageStore(
                    $this->stringWithoutCredential("FX reports error at find action: " . 
                        "code={$result['errorCode']}, url={$result['URL']}"));
            } else {
                if ($result['foundCount'] > 0) {
                    $targetClients[] = $context[1];
                }
            }
        }

        return array_unique($targetClients);
    }

    public function appendIntoRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;

        $this->setupFXforDB($regTable, 'all');
        $this->fx->AddDBParam('entity', $entity, 'eq');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at find action: " . 
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        } else {
            if ($result['foundCount'] > 0) {
                foreach ($result['data'] as $recmodid => $recordData) {
                    foreach ($recordData as $field => $value) {
                        if ($field == 'id') {
                            $targetId = $value[0];
                        }
                        if ($field == 'clientid') {
                            $targetClients[] = $value[0];
                        }
                    }
                    $this->setupFXforDB($pksTable, 1);
                    $this->fx->AddDBParam('context_id', $targetId);
                    $this->fx->AddDBParam('pk', $pkArray[0]);
                    $result = $this->fx->DoFxAction('new', TRUE, TRUE, 'full');
                    if (!is_array($result)) {
                        $this->errorMessageStore(
                            $this->stringWithoutCredential("FX reports error at insert action: " . 
                                "code={$result['errorCode']}, url={$result['URL']}"));
                        return false;
                    }
                    $this->logger->setDebugMessage("Inserted count: " . $result['foundCount'], 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    public function removeFromRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;
        $this->setupFXforDB($regTable, 'all');
        $this->fx->AddDBParam('entity', $entity, 'eq');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $this->logger->setDebugMessage(var_export($result, true));
        $targetClients = array();
        if ($result['errorCode'] != 0 && $result['errorCode'] != 401) {
            $this->errorMessageStore(
                $this->stringWithoutCredential("FX reports error at find action: " . 
                    "code={$result['errorCode']}, url={$result['URL']}"));
            return false;
        } else {
            if ($result['foundCount'] > 0) {
                foreach ($result['data'] as $recmodid => $recordData) {
                    foreach ($recordData as $field => $value) {
                        if ($field == 'id') {
                            $targetId = $value[0];
                        }
                        if ($field == 'clientid') {
                            $targetClients[] = $value[0];
                        }
                    }
                    $this->setupFXforDB($pksTable, 'all');
                    $this->fx->AddDBParam('context_id', $targetId, 'eq');
                    $this->fx->AddDBParam('pk', $pkArray[0], 'eq');
                    $resultForRemove = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
                    if ($resultForRemove['foundCount'] > 0) {
                        $this->setupFXforDB($pksTable, '');
                        foreach ($resultForRemove['data'] as $key => $row) {
                            $recId = substr($key, 0, strpos($key, '.'));
                            $this->fx->SetRecordID($recId);
                            $this->fx->DoFxAction('delete', TRUE, TRUE, 'full');
                        }
                    }
                    $this->logger->setDebugMessage("Deleted count: " . $resultForRemove['foundCount'], 2);
                }
            }
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    private function setupFXforAuth($layoutName, $recordCount)
    {
        $this->fx = null;
        $this->fxAuth = $this->setupFX_Impl($layoutName, $recordCount,
            $this->dbSettings->getDbSpecUser(), $this->dbSettings->getDbSpecPassword());
    }

    private function setupFXforDB($layoutName, $recordCount)
    {
        $this->fxAuth = null;
        $this->fx = $this->setupFX_Impl($layoutName, $recordCount,
            $this->dbSettings->getAccessUser(), $this->dbSettings->getAccessPassword());
    }

    private function setupFXforDB_Alt($layoutName, $recordCount)
    {
        $this->fxAlt = $this->setupFX_Impl($layoutName, $recordCount,
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

    private function stringWithoutCredential($str)
    {
        if (is_null($this->fx)) {
            $str = str_replace($this->dbSettings->getDbSpecUser(), "********", $str);
            return str_replace($this->dbSettings->getDbSpecPassword(), "********", $str);
        } else {
            $str = str_replace($this->dbSettings->getAccessUser(), "********", $str);
            return str_replace($this->dbSettings->getAccessPassword(), "********", $str);
        }
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

    public function getSchema($dataSourceName)
    {
        $this->fieldInfo = null;

        $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(), '');
        $this->dbSettings->setDbSpecDataType(
            str_replace('fmpro', 'fmalt',
                strtolower($this->dbSettings->getDbSpecDataType())));
        $result = $this->fx->FMView();

        if (!is_array($result)) {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                    $this->stringWithoutCredential(get_class($result) . ': ' . $result->getDebugInfo()));
            }
            return false;
        }

        $returnArray = array();
        foreach ($result['fields'] as $key => $fieldInfo) {
            $returnArray[$fieldInfo['name']] = '';
        }

        return $returnArray;
    }

    public function getFromDB($dataSourceName)
    {
        //    $this->logger->setDebugMessage("##getEntityForRetrieve={$this->dbSettings->getEntityForRetrieve()}", 2);
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
            $this->dbSettings->setDbSpecDataType(
                str_replace('fmpro', 'fmalt',
                    strtolower($this->dbSettings->getDbSpecDataType())));
        }

        $limitParam = 100000000;
        if ($this->dbSettings->getRecordCount() > 0) {
            $limitParam = $this->dbSettings->getRecordCount();
        }
        if (isset($context['records'])) {
            $limitParam = $context['records'];
        } elseif (isset($context['maxrecords'])) {
            $limitParam = $context['maxrecords'];
        }
        if (isset($context['maxrecords'])
            && intval($context['maxrecords']) >= $this->dbSettings->getRecordCount()
            && $this->dbSettings->getRecordCount() > 0
        ) {
            $limitParam = $this->dbSettings->getRecordCount();
        }
        $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(), $limitParam);

        $this->fx->FMSkipRecords(
            (isset($context['paging']) and $context['paging'] === true) ? $this->dbSettings->getStart() : 0);

        $hasFindParams = false;
        if (isset($context['query'])) {
            foreach ($context['query'] as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $this->fx->SetLogicalOR();
                } else {
                    if (isset($condition['operator'])) {
                        if (!$this->isPossibleOperator($condition['operator'])) {
                            throw new Exception("Invalid Operator.: {$condition['operator']}");
                        }
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
                            if (!$this->isPossibleOperator($condition['operator'])) {
                                throw new Exception("Invalid Operator.: {$condition['operator']}");
                            }
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

        $childRecordId = null;
        $childRecordIdValue = null;
        if ($this->dbSettings->getExtraCriteria()) {
            foreach ($this->dbSettings->getExtraCriteria() as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $this->fx->SetLogicalOR();
                } else {
                    $op = $condition['operator'] == '=' ? 'eq' : $condition['operator'];
                    if ($condition['field'] == "-recid" && $condition['operator'] == 'undefined') {
                        $op = "eq";
                    }
                    if (!$this->isPossibleOperator($op)) {
                        throw new Exception("Invalid Operator.: {$condition['field']}/{$condition['operator']}");
                    }
                    $this->fx->AddDBParam($condition['field'], $condition['value'], $op);
                    $hasFindParams = true;
                    if ($condition['field'] == $this->getDefaultKey()) {
                        $this->fx->FMSkipRecords(0);
                    }
                    if ($usePortal) {
                        if (strpos($condition['field'], '::') !== false) {
                            $childRecordId = $condition['field'];
                            $childRecordIdValue = $condition['value'];
                        }
                    }
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
                        if (!$usePortal) {
                            if (!$this->isPossibleOperator($foreignOperator)) {
                                throw new Exception("Invalid Operator.: {$condition['operator']}");
                            }
                            $this->fx->AddDBParam($foreignField, $formattedValue, $foreignOperator);
                            $hasFindParams = true;
                        }
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
                    if (!$this->isPossibleOrderSpecifier($condition['direction'])) {
                        throw new Exception("Invalid Sort Specifier.");
                    }
                    $this->fx->AddSortParam($condition['field'], $this->_adjustSortDirection($condition['direction']));
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
                        if (!$this->isPossibleOrderSpecifier($condition['direction'])) {
                            throw new Exception("Invalid Sort Specifier.");
                        }
                        $this->fx->AddSortParam($condition['field'], $this->_adjustSortDirection($condition['direction']));
                    } else {
                        $this->fx->AddSortParam($condition['field']);
                    }
                }
            }
            $this->dbSettings->setDataSourceName($context['name']);
        }

        if (count($this->dbSettings->getExtraSortKey()) > 0) {
            foreach ($this->dbSettings->getExtraSortKey() as $condition) {
                if (!$this->isPossibleOrderSpecifier($condition['direction'])) {
                    throw new Exception("Invalid Sort Specifier.");
                }
                $this->fx->AddSortParam($condition['field'], $this->_adjustSortDirection($condition['direction']));
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

        $queryString = '-db=' . urlencode($this->fx->database);
        $queryString .= '&-lay=' . urlencode($this->fx->layout);
        $skipRequest = '';
        if ($this->fx->currentSkip > 0) {
            $skipRequest = '&-skip=' . $this->fx->currentSkip;
        }
        $queryString .= '&-max=' . $this->fx->groupSize . $skipRequest;
        $fxUtility = new RetrieveFM7Data($this->fx);
        $currentSort = $fxUtility->CreateCurrentSort();
        $currentSearch = $fxUtility->CreateCurrentSearch();
        if ($hasFindParams) {
            $queryString .= $currentSort . $currentSearch . '&-find';
        } else {
            $queryString .= $currentSort . $currentSearch . '&-findall';
        }
        
        $recordArray = array();
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 
                $this->fx->urlScheme . '://' . $this->fx->dataServer . $this->fx->dataPortSuffix . '/fmi/xml/fmresultset.xml');
            curl_setopt($ch, CURLOPT_USERPWD, $this->dbSettings->getAccessUser() . ':' . $this->dbSettings->getAccessPassword());
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $xml = curl_exec($ch);
            curl_close($ch);
            libxml_use_internal_errors(true);
            $parsedData = simplexml_load_string($xml);
            if ($parsedData === false) {
                $errorMessage = 'Failed loading XML' . "\n";
                foreach(libxml_get_errors() as $error) {
                    $errorMessage .= $error->message;
                }
                $this->logger->setErrorMessage($errorMessage);
                return array();
            }
            $data = json_decode(json_encode($parsedData), true);
            $i = 0;
            if (isset($data['resultset']['record']) && isset($data['resultset']['@attributes'])) {
                foreach($data['resultset']['record'] as $record) {
                    if (intval($data['resultset']['@attributes']['fetch-size']) == 1) {
                        $record = $data['resultset']['record'];
                    }
                    $dataArray = array('-recid' => $record['@attributes']['record-id']);
                    foreach ($record['field'] as $field) {
                        $dataArray = $dataArray + array(
                            $field['@attributes']['name'] => isset($field['data']) && !is_null($field['data']) ? $field['data'] : ''
                        );
                    }
                    
                    $relatedsetArray = array();
                    if (isset($record['relatedset'])) {
                        foreach ($record['relatedset'] as $relatedset) {
                            $j = 0;
                            if (isset($relatedset['record'])) {
                                foreach ($relatedset['record'] as $relatedrecord) {
                                    $relatedArray = array('-recid' => $record['@attributes']['record-id']);
                                    $relatedArray += array(
                                        $relatedset['@attributes']['table'] . '::-recid' => $relatedrecord['@attributes']['record-id']
                                    );
                                    foreach ($relatedrecord['field'] as $relatedfield) {
                                        $relatedArray += array(
                                            $relatedfield['@attributes']['name'] => 
                                                isset($relatedfield['data']) && !is_null($relatedfield['data']) ? $relatedfield['data'] : ''
                                        );
                                    }
                                    if (isset($relatedsetArray[$j]) && !is_null($relatedsetArray[$j])) {
                                        $relatedsetArray[$j] += $relatedArray;
                                    } else {
                                        $relatedsetArray[$j] = $relatedArray;
                                    }
                                    $j++;
                                }
                            }
                        }
                    }
                    
                    foreach ($relatedsetArray as $j => $relatedset) {
                        $dataArray = $dataArray + array($j => $relatedset);
                    }
                    
                    array_push($recordArray, $dataArray);
                    if (intval($data['resultset']['@attributes']['fetch-size']) == 1) {
                        break;
                    }
                    $i++;
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage('INTER-Mediator reports error at find action: Exception error occurred.');
        }

        /*
        if (!is_array($this->fxResult)) {
            if ($this->dbSettings->isDBNative()) {
                $this->logger->setErrorMessage(
                $this->stringWithoutCredential(get_class($this->fxResult)
                . ': ' . $this->fxResult->getDebugInfo()));
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                $this->stringWithoutCredential(get_class($this->fxResult)
                . ': ' . $this->fxResult->getDebugInfo()));
            }
            return null;
        }
        */
        
        $errorCode = intval($data['error']['@attributes']['code']);
        if ($errorCode != 0 && $errorCode != 401) {
            $this->logger->setErrorMessage('INTER-Mediator reports error at find action: ' . 
                'errorcode=' . $errorCode . ', querystring=' . $queryString);
            return null;
        }
        $this->logger->setDebugMessage($queryString);
        
        $this->mainTableCount = intval($data['datasource']['@attributes']['total-count']);

        return $recordArray;
    }

    private function createRecordset($resultData, $dataSourceName, $usePortal, $childRecordId, $childRecordIdValue)
    {
        $isFirstRecord = true;
        $returnArray = array();
        foreach ($resultData as $key => $oneRecord) {
            $oneRecordArray = array();

            $recId = substr($key, 0, strpos($key, '.'));
            $oneRecordArray[$this->getDefaultKey()] = $recId;

            $existsRelated = false;
            foreach ($oneRecord as $field => $dataArray) {
                if ($isFirstRecord) {
                    $this->fieldInfo[] = $field;
                }
                if (count($dataArray) == 1) {
                    if ($usePortal) {
                        if (strpos($field, '::') !== false) {
                            $existsRelated = true;
                        }
                        foreach ($dataArray as $portalKey => $portalValue) {
                            $oneRecordArray[$portalKey][$this->getDefaultKey()] = $recId; // parent record id
                            $oneRecordArray[$portalKey][$field] = $this->formatter->formatterFromDB(
                                "{$dataSourceName}{$this->dbSettings->getSeparator()}$field", $portalValue);
                        }
                        if ($existsRelated == false) {
                            $oneRecordArray = array();
                            $oneRecordArray[0][$this->getDefaultKey()] = $recId; // parent record id
                        }
                    } else {
                        $oneRecordArray[$field] = $this->formatter->formatterFromDB(
                            "{$dataSourceName}{$this->dbSettings->getSeparator()}$field", $dataArray[0]);
                    }
                } else {
                    foreach ($dataArray as $portalKey => $portalValue) {
                        if (strpos($field, '::') !== false) {
                            $existsRelated = true;
                            $oneRecordArray[$portalKey][$this->getDefaultKey()] = $recId; // parent record id
                            $oneRecordArray[$portalKey][$field] = $this->formatter->formatterFromDB(
                                "{$dataSourceName}{$this->dbSettings->getSeparator()}$field", $portalValue);
                        } else {
                            $oneRecordArray[$field][] = $this->formatter->formatterFromDB(
                                "{$dataSourceName}{$this->dbSettings->getSeparator()}$field", $portalValue);
                        }
                    }
                }
            }
            if ($usePortal) {
                foreach ($oneRecordArray as $portalArrayField => $portalArray) {
                    if ($portalArrayField !== $this->getDefaultKey()) {
                        $returnArray[] = $portalArray;
                    }
                }
                if ($existsRelated == false) {
                    $this->mainTableCount = 0;
                } else {
                    $this->mainTableCount = count($returnArray);
                }
            } else {
                if ($childRecordId == null) {
                    $returnArray[] = $oneRecordArray;
                } else {
                    foreach ($oneRecordArray as $portalArrayField => $portalArray) {
                        if (isset($oneRecordArray[$childRecordId])
                            && $childRecordIdValue == $oneRecordArray[$childRecordId]
                        ) {
                            $returnArray = array();
                            $returnArray[] = $oneRecordArray;
                            return $returnArray;
                        }
                        if (isset($oneRecordArray[$portalArrayField][$childRecordId])
                            && $childRecordIdValue == $oneRecordArray[$portalArrayField][$childRecordId]
                        ) {
                            $returnArray = array();
                            $returnArray[] = $oneRecordArray[$portalArrayField];
                            return $returnArray;
                        }
                    }
                }
            }
            $isFirstRecord = false;
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
                    $this->dbSettings->setDbSpecDataType(
                        str_replace('fmpro', 'fmalt', strtolower($this->dbSettings->getDbSpecDataType())));
                }
            }
        }

        if ($usePortal) {
            $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(), 1);
        } else {
            $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
        }
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : $this->getDefaultKey();

        if (isset($tableInfo['query'])) {
            foreach ($tableInfo['query'] as $condition) {
                if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    $op = $condition['operator'] == '=' ? 'eq' : $condition['operator'];
                    if (!$this->isPossibleOperator($op)) {
                        throw new Exception("Invalid Operator.");
                    }
                    $convertedValue = $this->formatter->formatterToDB(
                        "{$dataSourceName}{$this->dbSettings->getSeparator()}{$condition['field']}",
                        $condition['value']);
                    $this->fx->AddDBParam($condition['field'], $convertedValue, $op);
                }
            }
        }

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            if (!$this->dbSettings->getPrimaryKeyOnly() || $value['field'] == $primaryKey) {
                $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
                if (!$this->isPossibleOperator($op)) {
                    throw new Exception("Invalid Operator.: {$condition['operator']}");
                }
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
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                    $this->stringWithoutCredential(get_class($result) . ': ' . $result->getDebugInfo()));
            }
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
//        $this->logger->setDebugMessage($this->stringWithoutCredential(var_export($this->dbSettings->getFieldsRequired(),true)));

        if ($result['errorCode'] > 0) {
            $this->logger->setErrorMessage($this->stringWithoutCredential(
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
                    if (strpos($field, '.') !== false) {
                        // remove dot + recid number if contains recid (example: "TO::FIELD.0" -> "TO::FIELD")
                        $dotPos = strpos($field, '.');
                        $originalfield = substr($field, 0, $dotPos);
                    } else {
                        $originalfield = $field;
                    }
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
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        get_class($result) . ': ' . $result->getDebugInfo()));
                    return false;
                }
                if ($result['errorCode'] > 0) {
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        "FX reports error at edit action: table={$this->dbSettings->getEntityForUpdate()}, "
                        . "code={$result['errorCode']}, url={$result['URL']}<hr>"));
                    return false;
                }
                $this->updatedRecord = $this->createRecordset($result['data'], $dataSourceName, null, null, null);
                $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
                    $this->dbSettings->setDbSpecDataType(
                        str_replace('fmpro', 'fmalt',
                            strtolower($this->dbSettings->getDbSpecDataType())));
                }
            }
        }

        $keyFieldName = isset($context['key']) ? $context['key'] : $this->getDefaultKey();

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
        $result = $this->fx->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->errorMessage[] = get_class($result) . ': ' . $result->getDebugInfo();
            }
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        if ($result['errorCode'] > 0 && $result['errorCode'] != 401) {
            $this->logger->setErrorMessage($this->stringWithoutCredential(
                "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>"));
            return false;
        }
        foreach ($result['data'] as $key => $row) {
            if ($keyFieldName == $this->getDefaultKey()) {
                $recId = substr($key, 0, strpos($key, '.'));
                $keyValue = $recId;
            } else {
                $keyValue = $row[$keyFieldName][0];
            }
        }
        $this->updatedRecord = $this->createRecordset($result['data'], $dataSourceName, null, null, null);
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
                    $this->dbSettings->setDbSpecDataType(
                        str_replace('fmpro', 'fmalt',
                            strtolower($this->dbSettings->getDbSpecDataType())));
                }
            }
        }

        if ($usePortal) {
            $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(), 10000000);
        } else {
            $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 10000000);
        }

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            if (!$this->isPossibleOperator($op)) {
                throw new Exception("Invalid Operator.");
            }
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
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->errorMessage[] = get_class($result) . ': ' . $result->getDebugInfo();
            }
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        //$this->logger->setDebugMessage($this->stringWithoutCredential(var_export($result['data'],true)));
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

                        $this->logger->setErrorMessage($this->stringWithoutCredential(
                            get_class($result) . ': ' . $result->getDebugInfo()));
                    }
                    return false;
                }
                if ($result['errorCode'] > 0) {
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        "FX reports error at delete action: code={$result['errorCode']}, url={$result['URL']}<hr>"));
                    return false;
                }
                $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
            $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam('user_id', $uid, 'eq');
        $this->fxAuth->AddDBParam('clienthost', '_im_media', 'eq');
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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

        $this->setupFXforDB($userTable, 1);
        $this->fx->AddDBParam('username', $username, 'eq');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        if ((!is_array($result) || $result['foundCount'] < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->setupFXforDB($userTable, 1);
            $this->fx->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        $this->setupFXforDB($userTable, 1);
        $this->fx->AddDBParam('username', $username);
        $this->fx->AddDBParam('hashedpasswd', $hashedpassword);
        $result = $this->fx->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        return true;
    }

    public function authSupportChangePassword($username, $hashednewpassword)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        $this->setupFXforDB($userTable, 1);
        $this->fx->AddDBParam('username', $username, 'eq');
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if ((!is_array($result) || count($result['data']) < 1) && $this->dbSettings->getEmailAsAccount()) {
            $this->setupFXforDB($userTable, 1);
            $this->fx->AddDBParam('email', str_replace("@", "\\@", $username), 'eq');
            $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
                return false;
            }
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $this->setupFXforDB($userTable, 1);
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

    public function authSupportGetUserIdFromUsername($username)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($username === 0) {
            return 0;
        }

        $this->setupFXforDB_Alt($userTable, 1);
        $this->fxAlt->AddDBParam('username', $username, "eq");
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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

        $this->setupFXforDB($userTable, 1);
        $this->fx->AddDBParam('id', $userid, "eq");
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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

        $this->setupFXforDB_Alt($userTable, 1);
        $this->fxAlt->AddDBParam('email', str_replace("@", "\\@", $email), "eq");
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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

        $this->setupFXforDB_Alt($userTable, 55555);
        $this->fxAlt->AddDBParam('username', $username, "eq");
        $this->fxAlt->AddDBParam('email', str_replace("@", "\\@", $username), "eq");
        $this->fxAlt->SetLogicalOR();
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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

        $this->setupFXforDB($groupTable, 1);
        $this->fx->AddDBParam('id', $groupid);
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        $this->setupFXforDB($this->dbSettings->getCorrTable(), 1);
        if ($this->firstLevel) {
            $this->fx->AddDBParam('user_id', $groupid);
            $this->firstLevel = false;
        } else {
            $this->fx->AddDBParam('group_id', $groupid);
            $this->belongGroups[] = $groupid;
        }
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            return true;
        }
        return false;
    }

    public function isPossibleOperator($operator)
    {
        return !(FALSE === array_search(strtoupper($operator), array(
                'EQ', 'CN', 'BW', 'EW', 'GT', 'GTE', 'LT', 'LTE', 'NEQ', 'AND', 'OR',
            )));
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        return !(array_search(strtoupper($specifier), array('ASCEND', 'DESCEND', 'ASC', 'DESC')) === FALSE);
    }

    protected function _adjustSortDirection($direction)
    {
        if (strtoupper($direction) == 'ASC') {
            $direction = 'ASCEND';
        } else if (strtoupper($direction) == 'DESC') {
            $direction = 'DESCEND';
        }

        return $direction;
    }

    public function isContainingFieldName($fname, $fieldnames)
    {
        if (in_array($fname, $fieldnames)) {
            return true;
        }

        if (strpos($fname, "::") !== false) {
            $lastPeriodPosition = strrpos($fname, ".");
            if ($lastPeriodPosition !== false) {
                if (in_array(substr($fname, 0, $lastPeriodPosition), $fieldnames)) {
                    return true;
                }
            }
        }
        if ($fname == "-delete.related") {
            return true;
        }
        return false;
    }

    public function isNullAcceptable()
    {
        return false;
    }

    public function queryForTest($table, $conditions = null)
    {
        if ($table == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        $this->setupFXforAuth($table, 'all');
        if (count($conditions) > 0) {
            foreach ($conditions as $field => $value) {
                $this->fxAuth->AddDBParam($field, $value, 'eq');
            }
        }
        if (count($conditions) > 0) {
            $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        } else {
            $result = $this->fxAuth->DoFxAction('show_all', TRUE, TRUE, 'full');
        }
        if ($result === false) {
            return false;
        }
        $recordSet = array();
        foreach ($result['data'] as $key => $row) {
        //foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $oneRecord = array();
            foreach ($row as $field => $value) {
                $oneRecord[$field] = $value[0];
            }
            $recordSet[] = $oneRecord;
        }
        return $recordSet;
    }
    
    function authSupportGetSalt($username)
    {
        // TODO: Implement authSupportGetSalt() method.
    }

    function removeOutdatedChallenges()
    {
        // TODO: Implement removeOutdatedChallenges() method.
    }
}
