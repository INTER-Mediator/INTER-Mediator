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
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'CWPKit' . DIRECTORY_SEPARATOR . 'CWPKit.php');

class DB_FileMaker_FX extends DB_AuthCommon implements DB_Access_Interface
{
    private $fx = null;
    private $fxAuth = null;
    private $fxAlt = null;
    private $mainTableCount = 0;
    private $mainTableTotalCount = 0;
    private $fieldInfo = null;

    private $isRequiredUpdated = false;
    private $updatedRecord = null;
    private $queriedEntity = null;
    private $queriedCondition = null;
    private $queriedPrimaryKeys = null;
    private $softDeleteField = null;
    private $softDeleteValue = null;

    public function queriedEntity()
    {
        return $this->queriedEntity;
    }

    public function queriedCondition()
    {
        return $this->queriedCondition;
    }

    public function queriedPrimaryKeys()
    {
        return $this->queriedPrimaryKeys;
    }

    /**
     * @param $str
     */
    private function errorMessageStore($str)
    {
        $this->logger->setErrorMessage("Query Error: [{$str}] Error Code={$this->fx->lastErrorCode}");
    }

    public function setupConnection()
    {
        return true;
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

    public function setUpdatedRecord($field, $value, $index = 0)
    {
        $this->updatedRecord[$index][$field] = $value;
    }

    public function softDeleteActivate($field, $value)
    {
        $this->softDeleteField = $field;
        $this->softDeleteValue = $value;
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
        $newContextId = null;
        foreach ($result['data'] as $recmodid => $recordData) {
            foreach ($recordData as $field => $value) {
                if ($field == 'id') {
                    $newContextId = $value[0];
                }
            }
        }
        if (is_array($pkArray)) {
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
        }
        return $newContextId;
    }

    public function unregister($clientId, $tableKeys)
    {
        $regTable = $this->dbSettings->registerTableName;
        $pksTable = $this->dbSettings->registerPKTableName;

        $this->setupFXforDB($regTable, 'all');
        $this->fx->AddDBParam('clientid', $clientId, 'eq');
        if ($tableKeys) {
            $subCriteria = array();
            foreach ($tableKeys as $regId) {
                $this->fx->AddDBParam('id', $regId, 'eq');
            }
        }
        $result = $this->fx->DoFxAction('perform_find', TRUE, TRUE, 'full');

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
        $fxPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'FX';
        $fxFiles = array(
            'FX.php',
            'datasource_classes' . DIRECTORY_SEPARATOR . 'RetrieveFM7Data.class.php',
        );
        foreach ($fxFiles as $fxFile) {
            $path = $fxPath . DIRECTORY_SEPARATOR . $fxFile;
            if (is_file($path) && is_readable($path)) {
                require_once($path);
            } else {
                // If FX.php isn't installed in valid directories, it shows error message and finishes.
                throw new Exception('Data Access Class "FileMaker_FX" of INTER-Mediator requires ' .
                    basename($fxFile) . ' on any right directory.');
            }
        }

        $fxObj = new FX(
            $this->dbSettings->getDbSpecServer(),
            $this->dbSettings->getDbSpecPort(),
            $this->dbSettings->getDbSpecDataType(),
            $this->dbSettings->getDbSpecProtocol()
        );
        $fxObj->setCharacterEncoding('UTF-8');
        $fxObj->setDBUserPass($user, $password);
        $fxObj->setDBData($this->dbSettings->getDbSpecDatabase(), $layoutName, $recordCount);
        return $fxObj;
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

    private function setSearchConditionsForCompoundFound($field, $value, $operator = NULL) {
        if ($operator === NULL || $operator === 'neq') {
            return array($field, $value);
        } else if ($operator === 'eq') {
            return array($field, '=' . $value);
        } else if ($operator === 'cn') {
            return array($field, '*' . $value . '*');
        } else if ($operator === 'bw') {
            return array($field, $value . '*');
        } else if ($operator === 'ew') {
            return array($field, '*' . $value);
        } else if ($operator === 'gt') {
            return array($field, '>' . $value);
        } else if ($operator === 'gte') {
            return array($field, '>=' . $value);
        } else if ($operator === 'lt') {
            return array($field, '<' . $value);
        } else if ($operator === 'lte') {
            return array($field, '<=' . $value);
        }
    }

    private function executeScriptsforLoading($scriptContext)
    {
        $queryString = '';
        if (is_array($scriptContext)) {
            foreach ($scriptContext as $condition) {
                if (isset($condition['situation']) &&
                    isset($condition['definition']) && !empty($condition['definition'])) {
                    $scriptName = str_replace('&', '', $condition['definition']);
                    $parameter = '';
                    if (isset($condition['parameter']) && !empty($condition['parameter'])) {
                        $parameter = str_replace('&', '', $condition['parameter']);
                    }
                    switch ($condition['situation']) {
                        case 'post':
                            $queryString .= '&-script=' . $scriptName;
                            if ($parameter !== '') {
                                $queryString .= '&-script.param=' . $parameter;
                            }
                            break;
                        case 'pre':
                            $queryString .= '&-script.prefind=' . $scriptName;
                            if ($parameter !== '') {
                                $queryString .= '&-script.prefind.param=' . $parameter;
                            }
                            break;
                        case 'presort':
                            $queryString .= '&-script.presort=' . $scriptName;
                            if ($parameter !== '') {
                                $queryString .= '&-script.presort.param=' . $parameter;
                            }
                            break;
                    }
                }
            }
        }

        return $queryString;
    }

    private function executeScripts($fxphp, $condition)
    {
        if ($condition['situation'] == 'pre') {
            $fxphp->PerformFMScriptPrefind($condition['definition']);
            if (isset($condition['parameter']) && !empty($condition['parameter'])) {
                $fxphp->AddDBParam('-script.prefind.param', $condition['parameter']);
            }
        } else if ($condition['situation'] == 'presort') {
            $fxphp->PerformFMScriptPresort($condition['definition']);
            if (isset($condition['parameter']) && !empty($condition['parameter'])) {
                $fxphp->AddDBParam('-script.presort.param', $condition['parameter']);
            }
        } else if ($condition['situation'] == 'post') {
            $fxphp->PerformFMScript($condition['definition']);
            if (isset($condition['parameter']) && !empty($condition['parameter'])) {
                $fxphp->AddDBParam('-script.param', $condition['parameter']);
            }
        }

        return $fxphp;
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

    public function readFromDB()
    {
        $useOrOperation = false;
        $this->fieldInfo = null;
        $this->mainTableCount = 0;
        $this->mainTableTotalCount = 0;
        $context = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->dbSettings->getEntityForRetrieve();
        $dataSourceName = $this->dbSettings->getDataSourceName();

        $usePortal = false;
        if (count($this->dbSettings->getForeignFieldAndValue()) > 0) {
            foreach ($context['relation'] as $relDef) {
                if (isset($relDef['portal']) && $relDef['portal']) {
                    $usePortal = true;
                    $context['records'] = 1;
                    $context['paging'] = true;
                    $this->dbSettings->setDbSpecDataType(
                        str_replace('fmpro', 'fmalt', strtolower($this->dbSettings->getDbSpecDataType())));
                }
            }
        }
        if ($this->dbSettings->getPrimaryKeyOnly()) {
            $this->dbSettings->setDbSpecDataType(
                str_replace('fmpro', 'fmalt',
                    strtolower($this->dbSettings->getDbSpecDataType())));
        }

        $limitParam = 100000000;
        if (isset($context['maxrecords'])) {
            if (intval($context['maxrecords']) < $this->dbSettings->getRecordCount()) {
                if (intval($context['maxrecords']) < intval($context['records'])) {
                    $limitParam = intval($context['records']);
                } else {
                    $limitParam = intval($context['maxrecords']);
                }
            } else {
                $limitParam = $this->dbSettings->getRecordCount();
            }
        } else if (isset($context['records'])) {
            if (intval($context['records']) < $this->dbSettings->getRecordCount()) {
                $limitParam = intval($context['records']);
            } else {
                $limitParam = $this->dbSettings->getRecordCount();
            }
        }
        $this->setupFXforDB($this->dbSettings->getEntityForRetrieve(), $limitParam);

        $this->fx->FMSkipRecords(
            (isset($context['paging']) and $context['paging'] === true) ? $this->dbSettings->getStart() : 0);

        $searchConditions = array();
        $neqConditions = array();
        $queryValues = array();
        $qNum = 1;

        $hasFindParams = false;
        if (isset($context['query'])) {
            foreach ($context['query'] as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $this->fx->SetLogicalOR();
                    $useOrOperation = true;
                } else {
                    if (isset($condition['operator'])) {
                        $condition = $this->normalizedCondition($condition);
                        if (!$this->isPossibleOperator($condition['operator'])) {
                            throw new Exception("Invalid Operator.: {$condition['operator']}");
                        }
                        $this->fx->AddDBParam($condition['field'], $condition['value'], $condition['operator']);
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $condition['field'], $condition['value'], $condition['operator']);
                    } else {
                        $this->fx->AddDBParam($condition['field'], $condition['value']);
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $condition['field'], $condition['value']);
                    }
                    $hasFindParams = true;

                    $queryValues[] = 'q' . $qNum;
                    $qNum++;
                    if (isset($condition['operator']) && $condition['operator'] === 'neq') {
                        $neqConditions[] = TRUE;
                    } else {
                        $neqConditions[] = FALSE;
                    }
                }
            }
        } elseif ($usePortal && isset($context['view'])) {
            $this->dbSettings->setDataSourceName($context['view']);
            $parentTable = $this->dbSettings->getDataSourceTargetArray();
            if (isset($parentTable['query'])) {
                foreach ($parentTable['query'] as $condition) {
                    if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                        $this->fx->SetLogicalOR();
                        $useOrOperation = true;
                    } else {
                        if (isset($condition['operator'])) {
                            $condition = $this->normalizedCondition($condition);
                            if (!$this->isPossibleOperator($condition['operator'])) {
                                throw new Exception("Invalid Operator.: {$condition['operator']}");
                            }
                            $this->fx->AddDBParam($condition['field'], $condition['value'], $condition['operator']);
                            $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                                $condition['field'], $condition['value'], $condition['operator']);
                        } else {
                            $this->fx->AddDBParam($condition['field'], $condition['value']);
                            $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                                $condition['field'], $condition['value']);
                        }
                        $hasFindParams = true;

                        $queryValues[] = 'q' . $qNum;
                        $qNum++;
                        if (isset($condition['operator']) && $condition['operator'] === 'neq') {
                            $neqConditions[] = TRUE;
                        } else {
                            $neqConditions[] = FALSE;
                        }
                    }
                }
            }
            $this->dbSettings->setDataSourceName($context['name']);
        }

        $childRecordId = null;
        $childRecordIdValue = null;
        if ($this->dbSettings->getExtraCriteria()) {
            foreach ($this->dbSettings->getExtraCriteria() as $condition) {
                if ($condition['field'] == '__operation__' && strtolower($condition['operator']) == 'or') {
                    $this->fx->SetLogicalOR();
                    $useOrOperation = true;
                } else if ($condition['field'] == '__operation__' && strtolower($condition['operator']) == 'ex') {
                    $this->fx->SetLogicalOR();
                    $useOrOperation = true;
                } else {
                    $condition = $this->normalizedCondition($condition);
                    if (!$this->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['field']}/{$condition['operator']}");
                    }

                    $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                    $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : $this->getDefaultKey();
                    if ($condition['field'] == $primaryKey && isset($condition['value'])) {
                        $this->queriedPrimaryKeys = array($condition['value']);
                    }

                    $this->fx->AddDBParam($condition['field'], $condition['value'], $condition['operator']);
                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $condition['field'], $condition['value'], $condition['operator']);
                    $queryValues[] = 'q' . $qNum;
                    $qNum++;
                    if (isset($condition['operator']) && $condition['operator'] === 'neq') {
                        $neqConditions[] = TRUE;
                    } else {
                        $neqConditions[] = FALSE;
                    }

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
                        $relDef = $this->normalizedCondition($relDef);
                        $foreignOperator = isset($relDef['operator']) ? $relDef['operator'] : 'eq';
                        $formattedValue = $this->formatter->formatterToDB(
                            "{$tableName}{$this->dbSettings->getSeparator()}{$foreignField}", $foreignValue);
                        if (!$usePortal) {
                            if (!$this->isPossibleOperator($foreignOperator)) {
                                throw new Exception("Invalid Operator.: {$condition['operator']}");
                            }
                            if ($useOrOperation) {
                                throw new Exception("Condition Incompatible.: The OR operation and foreign key can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                            }
                            $this->fx->AddDBParam($foreignField, $formattedValue, $foreignOperator);
                            $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                                $foreignField, $formattedValue, $foreignOperator);
                            $hasFindParams = true;

                            $queryValues[] = 'q' . $qNum;
                            $qNum++;
                            if (isset($foreignOperator) && $foreignOperator === 'neq') {
                                $neqConditions[] = TRUE;
                            } else {
                                $neqConditions[] = FALSE;
                            }
                        }
                    }
                }
            }
        }

        if (isset($context['authentication'])
            && ((isset($context['authentication']['all'])
                || isset($context['authentication']["read"])
                || isset($context['authentication']["select"])
                || isset($context['authentication']["load"])))
        ) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization("read");
            $authInfoTarget = $this->getTargetForAuthorization("read");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    if ($useOrOperation) {
                        throw new Exception("Condition Incompatible.: The authorization for each record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                    }
                    $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    $this->fx->AddDBParam($authInfoField, $signedUser, 'eq');
                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $authInfoField, $signedUser, 'eq');
                    $hasFindParams = true;

                    $queryValues[] = 'q' . $qNum;
                    $qNum++;
                    $neqConditions[] = FALSE;
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                    $authFailure = true;
                } else {
                    if ($useOrOperation) {
                        throw new Exception("Condition Incompatible.: The authorization for each record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                    }
                    $this->fx->AddDBParam($authInfoField, $belongGroups[0], 'eq');
                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $authInfoField, $belongGroups[0], 'eq');
                    $hasFindParams = true;

                    $queryValues[] = 'q' . $qNum;
                    $qNum++;
                    $neqConditions[] = FALSE;
                }
//            } else {
//                if ($this->dbSettings->isDBNative()) {
//                } else {
//                    $authorizedUsers = $this->getAuthorizedUsers("load");
//                    $authorizedGroups = $this->getAuthorizedGroups("load");
//                    $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
//                    if (!in_array($this->dbSettings->getCurrentUser(), $authorizedUsers)
//                        && count(array_intersect($belongGroups, $authorizedGroups)) == 0
//                    ) {
//                        $authFailure = true;
//                    }
//                }
            }
            if ($authFailure) {
                $this->logger->setErrorMessage("Authorization Error.");
                return null;
            }
        }

        if (!is_null($this->softDeleteField) && !is_null($this->softDeleteValue)) {
            if ($useOrOperation) {
                throw new Exception("Condition Incompatible.: The soft-delete record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
            }
            $this->fx->AddDBParam($this->softDeleteField, $this->softDeleteValue, 'neq');
            $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                $this->softDeleteField, $this->softDeleteValue, 'eq');
            $hasFindParams = true;

            $queryValues[] = 'q' . $qNum;
            $qNum++;
            $neqConditions[] = FALSE;
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
                        $this->fx->AddSortParam(
                            $condition['field'], $this->_adjustSortDirection($condition['direction']));
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
                if ($condition['db-operation'] == 'load' || $condition['db-operation'] == 'read') {
                    $this->fx->SetFMGlobal($condition['field'], $condition['value']);
                }
            }
        }

        $queryString = '-db=' . urlencode($this->fx->database);
        $queryString .= '&-lay=' . urlencode($this->fx->layout);
        $queryString .= '&-lay.response=' . urlencode($this->fx->layout);
        $skipRequest = '';
        if ($this->fx->currentSkip > 0) {
            $skipRequest = '&-skip=' . $this->fx->currentSkip;
        }
        $queryString .= '&-max=' . $this->fx->groupSize . $skipRequest;
        if (isset($context['script'])) {
            foreach ($context['script'] as $condition) {
                if ($condition['db-operation'] == 'load' || $condition['db-operation'] == 'read') {
                    $queryString .= $this->executeScriptsforLoading($context['script']);
                }
            }
        }
        $fxUtility = new RetrieveFM7Data($this->fx);
        $currentSort = $fxUtility->CreateCurrentSort();
        $config = array(
            'urlScheme' => $this->fx->urlScheme,
            'dataServer' => $this->fx->dataServer,
            'dataPort' => $this->fx->dataPort,
            'DBUser' => $this->dbSettings->getAccessUser(),
            'DBPassword' => $this->dbSettings->getAccessPassword(),
        );
        $cwpkit = new CWPKit($config);

        $compoundFind = TRUE;
        if ($searchConditions === array() || (int)$cwpkit->getServerVersion() < 12) {
            $compoundFind = FALSE;
        } else {
            foreach ($searchConditions as $searchCondition) {
                if (isset($searchCondition[0]) && $searchCondition[0] === '-recid') {
                    $compoundFind = FALSE;
                }
            }
            foreach ($neqConditions as $key => $value) {
                if ($value === TRUE) {
                    $compoundFind = FALSE;
                }
            }
        }

        if ($compoundFind === FALSE) {
            $currentSearch = $fxUtility->CreateCurrentSearch();
            if ($hasFindParams) {
                $queryString = $cwpkit->_removeDuplicatedQuery(
                    $queryString . $currentSort . $currentSearch . '&-find'
                );
            } else {
                $queryString .= $currentSort . $currentSearch . '&-findall';
            }
        } else {
            $currentSearch = '';
            if (isset($context['script'])) {
                if ($condition['db-operation'] == 'load' || $condition['db-operation'] == 'read') {
                    $currentSearch = $this->executeScriptsforLoading($context['script']);
                }
            }
            $queryValue = '';
            $qNum = 1;
            if ($useOrOperation === TRUE) {
                foreach ($queryValues as $value) {
                    if ($queryValue === '') {
                        if ($neqConditions[$qNum - 1] === FALSE) {
                            $queryValue .= '(' . $value . ')';
                        } else {
                            $queryValue .= '!(' . $value . ')';
                        }
                    } else {
                        if ($neqConditions[$qNum - 1] === FALSE) {
                            $queryValue .= ';(' . $value . ')';
                        } else {
                            $queryValue .= ';!(' . $value . ')';
                        }
                    }
                    $qNum++;
                }
                $qNum = 1;
                foreach ($searchConditions as $searchCondition) {
                    $currentSearch .= '&-q' . $qNum . '=' . urlencode($searchCondition[0])
                        . '&-q' . $qNum . '.value=' . urlencode($searchCondition[1]);
                    $qNum++;
                }
            } else {
                $newConditions = array();
                foreach ($searchConditions as $searchCondition) {
                    if (array_key_exists($searchCondition[0], $newConditions)) {
                        $newConditions = array_merge($newConditions, array($searchCondition[0] => $newConditions[$searchCondition[0]] . ' ' . $searchCondition[1]));
                    } else {
                        $newConditions = array_merge($newConditions, array($searchCondition[0] => $searchCondition[1]));
                    }
                }

                $queryValues = array();
                foreach ($newConditions as $fieldName => $fieldValue) {
                    $currentSearch .= '&-q' . $qNum . '=' . $fieldName
                        . '&-q' . $qNum . '.value=' . $fieldValue;
                    $queryValues[] = 'q' . $qNum;
                    $qNum++;
                }

                $qNum = 1;
                foreach ($queryValues as $value) {
                    if ($queryValue === '') {
                        if ($neqConditions[$qNum - 1] === FALSE) {
                            $queryValue .= $value;
                        } else {
                            $queryValue .= '!' . $value;
                        }
                    } else {
                        if ($neqConditions[$qNum - 1] === FALSE) {
                            $queryValue .= ',' . $value;
                        } else {
                            $queryValue .= ',!' . $value;
                        }
                    }
                    $qNum++;
                }
                $queryValue = '(' . $queryValue . ')';
            }
            $queryString .= $currentSort . '&-query=' . $queryValue . $currentSearch . '&-findquery';
        }

        $this->queriedEntity = $this->fx->layout;
        $this->queriedCondition = $queryString;

        $recordArray = array();
        $this->queriedPrimaryKeys = array();
        $keyField = isset($context['key']) ? $context['key'] : $this->getDefaultKey();
        try {
            $parsedData = $cwpkit->query($queryString);
            if ($parsedData === false) {
                if ($this->dbSettings->isDBNative()) {
                    $this->dbSettings->setRequireAuthentication(true);
                }
                $errorMessage = 'Failed loading XML, check your setting about FileMaker Server.' . "\n";
                foreach (libxml_get_errors() as $error) {
                    $errorMessage .= $error->message;
                }
                $this->logger->setErrorMessage($errorMessage);
                return null;
            }
            $data = json_decode(json_encode($parsedData), true);
            $i = 0;
            $dataArray = array();
            if (isset($data['resultset']['record']) && isset($data['resultset']['@attributes'])) {
                foreach ($data['resultset']['record'] as $record) {
                    if (intval($data['resultset']['@attributes']['fetch-size']) == 1) {
                        $record = $data['resultset']['record'];
                    }
                    if (!$usePortal) {
                        $dataArray = array($this->getDefaultKey() => $record['@attributes']['record-id']);
                    }
                    if ($keyField == $this->getDefaultKey()) {
                        $this->queriedPrimaryKeys[] = $record['@attributes']['record-id'];
                    }
                    $multiFields = true;
                    foreach ($record['field'] as $field) {
                        if (!isset($field['@attributes'])) {
                            $field = $record['field'];
                            $multiFields = false;
                        }
                        $fieldName = $field['@attributes']['name'];
                        $fieldValue = '';
                        if (isset($field['data']) && !is_null($field['data'])) {
                            try {
                                $fieldValue = $this->formatter->formatterFromDB(
                                    "{$tableName}{$this->dbSettings->getSeparator()}{$fieldName}", $field['data']);
                            } catch (Exception $e) {
                                $fieldValue = $field['data'];
                            }
                            if ($fieldName == $keyField && $keyField != $this->getDefaultKey()) {
                                $this->queriedPrimaryKeys[] = $field['data'];
                            }
                        }
                        if (!$usePortal) {
                            $dataArray = $dataArray + array(
                                    $fieldName => $fieldValue
                                );
                        }
                        if ($multiFields === false) {
                            break;
                        }
                    }

                    $relatedsetArray = array();
                    if (isset($record['relatedset'])) {
                        if (isset($record['relatedset']['record'])) {
                            $record['relatedset'] = array($record['relatedset']);
                        }
                        $relatedArray = array();
                        foreach ($record['relatedset'] as $relatedset) {
                            if (isset($relatedset['record'])) {
                                $relRecords = $relatedset['record'];
                                if ($relatedset['@attributes']['count'] == 1) {
                                    $relRecords = array($relatedset['record']);
                                }
                                foreach ($relRecords as $relatedrecord) {
                                    if (isset($relatedset['@attributes']) && isset($relatedrecord['@attributes'])) {
                                        $tableOccurrence = $relatedset['@attributes']['table'];
                                        $recId = $relatedrecord['@attributes']['record-id'];
                                        if (!isset($relatedArray[$tableOccurrence])) {
                                            $relatedArray[$tableOccurrence] = array();
                                        }
                                    }
                                    $multiFields = true;
                                    if (isset($relatedrecord['field'])) {
                                        foreach ($relatedrecord['field'] as $relatedfield) {
                                            if (!isset($relatedfield['@attributes'])) {
                                                $relatedfield = $relatedrecord['field'];
                                                $multiFields = false;
                                            }
                                            $relatedFieldName = $relatedfield['@attributes']['name'];
                                            $relatedFieldValue = '';
                                            $fullyQualifiedFieldName = explode('::', $relatedFieldName);
                                            $tableOccurrence = $fullyQualifiedFieldName[0];
                                            if (isset($relatedfield['data']) && !is_null($relatedfield['data'])) {
                                                if (strpos($relatedFieldName, '::') !== false) {
                                                    $relatedFieldValue = $this->formatter->formatterFromDB(
                                                        "{$tableOccurrence}{$this->dbSettings->getSeparator()}{$relatedFieldName}",
                                                        $relatedfield['data']
                                                    );
                                                } else {
                                                    $relatedFieldValue = $this->formatter->formatterFromDB(
                                                        "{$tableName}{$this->dbSettings->getSeparator()}{$relatedFieldName}",
                                                        $relatedfield['data']
                                                    );
                                                }
                                            }
                                            if (!isset($relatedArray[$tableOccurrence][$recId])) {
                                                $relatedArray[$tableOccurrence][$recId] = array('-recid' => $recId);
                                            }
                                            $relatedArray[$tableOccurrence][$recId] += array(
                                                $relatedFieldName => $relatedFieldValue
                                            );
                                            if ($multiFields === false) {
                                                break;
                                            }
                                        }
                                        $relatedsetArray = array($relatedArray);
                                    }
                                }
                            }
                        }
                    }

                    foreach ($relatedsetArray as $j => $relatedset) {
                        $dataArray = $dataArray + array($j => $relatedset);
                    }
                    if ($usePortal) {
                        $recordArray = $dataArray;
                        $this->mainTableCount = count($recordArray);
                        break;
                    } else {
                        array_push($recordArray, $dataArray);
                    }
                    if (intval($data['resultset']['@attributes']['fetch-size']) == 1) {
                        break;
                    }
                    $i++;
                }
            }
        } catch (Exception $e) {
            $this->logger->setErrorMessage('INTER-Mediator reports error at find action: Exception error occurred.');
            return null;
        }

        $errorCode = intval($data['error']['@attributes']['code']);
        if ($errorCode != 0 && $errorCode != 401) {
            $this->logger->setErrorMessage('INTER-Mediator reports error at find action: ' .
                'errorcode=' . $errorCode . ', querystring=' . $queryString);
            return null;
        }
        $this->logger->setDebugMessage($queryString);

        if (!$usePortal) {
            $this->mainTableCount = intval($data['resultset']['@attributes']['count']);
            $this->mainTableTotalCount = intval($data['datasource']['@attributes']['total-count']);
        }

        return $recordArray;
    }

    private function createRecordset($resultData, $dataSourceName, $usePortal, $childRecordId, $childRecordIdValue)
    {
        $isFirstRecord = true;
        $returnArray = array();
        $tableName = $this->dbSettings->getEntityForRetrieve();
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
                                "{$tableName}{$this->dbSettings->getSeparator()}$field", $portalValue);
                        }
                        if ($existsRelated === false) {
                            $oneRecordArray = array();
                            $oneRecordArray[0][$this->getDefaultKey()] = $recId; // parent record id
                        }
                    } else {
                        $oneRecordArray[$field] = $this->formatter->formatterFromDB(
                            "{$tableName}{$this->dbSettings->getSeparator()}$field", $dataArray[0]);
                    }
                } else {
                    foreach ($dataArray as $portalKey => $portalValue) {
                        if (strpos($field, '::') !== false) {
                            $existsRelated = true;
                            $oneRecordArray[$portalKey][$this->getDefaultKey()] = $recId; // parent record id
                            $oneRecordArray[$portalKey][$field] = $this->formatter->formatterFromDB(
                                "{$tableName}{$this->dbSettings->getSeparator()}$field", $portalValue);
                        } else {
                            $oneRecordArray[$field][] = $this->formatter->formatterFromDB(
                                "{$tableName}{$this->dbSettings->getSeparator()}$field", $portalValue);
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
                if ($existsRelated === false) {
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

    public function countQueryResult()
    {
        return $this->mainTableCount;
    }

    public function getTotalCount()
    {
        return $this->mainTableTotalCount;
    }

    public function updateDB()
    {
        $this->fieldInfo = null;
        $dataSourceName = $this->dbSettings->getDataSourceName();
        $tableSourceName = $this->dbSettings->getEntityForUpdate();
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

        $fxUtility = new RetrieveFM7Data($this->fx);
        $config = array(
            'urlScheme' => $this->fx->urlScheme,
            'dataServer' => $this->fx->dataServer,
            'dataPort' => $this->fx->dataPort,
            'DBUser' => $this->dbSettings->getAccessUser(),
            'DBPassword' => $this->dbSettings->getAccessPassword(),
        );
        $cwpkit = new CWPKit($config);

        if (isset($tableInfo['query'])) {
            foreach ($tableInfo['query'] as $condition) {
                if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    $condition = $this->normalizedCondition($condition);
                    if (!$this->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.");
                    }
                    $convertedValue = $this->formatter->formatterToDB(
                        "{$tableSourceName}{$this->dbSettings->getSeparator()}{$condition['field']}",
                        $condition['value']);
                    $this->fx->AddDBParam($condition['field'], $convertedValue, $condition['operator']);
                }
            }
        }

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            if (!$this->dbSettings->getPrimaryKeyOnly() || $value['field'] == $primaryKey) {
                $value = $this->normalizedCondition($value);
                if (!$this->isPossibleOperator($value['operator'])) {
                    throw new Exception("Invalid Operator.: {$condition['operator']}");
                }
                $convertedValue = $this->formatter->formatterToDB(
                    "{$tableSourceName}{$this->dbSettings->getSeparator()}{$value['field']}", $value['value']);
                if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $value['field'], $convertedValue) === TRUE) {
                    $this->fx->AddDBParam($value['field'], $convertedValue, $value['operator']);
                }
            }
        }
        if (isset($tableInfo['authentication'])
            && (isset($tableInfo['authentication']['all'])
                || isset($tableInfo['authentication']['update']))
        ) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization("update");
            $authInfoTarget = $this->getTargetForAuthorization("update");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $authInfoField, $signedUser) === TRUE) {
                        $this->fx->AddDBParam($authInfoField, $signedUser, "eq");
                    }
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                    $authFailure = true;
                } else {
                    if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $authInfoField, $belongGroups[0]) === TRUE) {
                        $this->fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                    }
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
            $this->queriedPrimaryKeys = array();
            $keyField = isset($context['key']) ? $context['key'] : $this->getDefaultKey();
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));
                if ($keyField == $this->getDefaultKey()) {
                    $this->queriedPrimaryKeys[] = $recId;
                } else {
                    $this->queriedPrimaryKeys[] = $row[$keyField][0];
                }
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
                        $this->getFieldForFormatter($tableSourceName, $originalfield), $convVal);
                    if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $field, $convVal) === TRUE) {
                        $this->fx->AddDBParam($field, $convVal);
                    }
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
                            $this->fx = $this->executeScripts($this->fx, $condition);
                        }
                    }
                }

                $this->queriedEntity = $this->fx->layout;

                $result = $this->fx->DoFxAction('update', TRUE, TRUE, 'full');
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

    public function createInDB($bypassAuth)
    {
        $this->fieldInfo = null;

        $context = $this->dbSettings->getDataSourceTargetArray();
        $dataSourceName = $this->dbSettings->getDataSourceName();

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
            && (isset($context['authentication']['all'])
                || isset($context['authentication']['new'])
                || isset($context['authentication']['create']))
        ) {
            $authInfoField = $this->getFieldForAuthorization("create");
            $authInfoTarget = $this->getTargetForAuthorization("create");
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
                    $authorizedUsers = $this->getAuthorizedUsers("create");
                    $authorizedGroups = $this->getAuthorizedGroups("create");
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
                if ($condition['db-operation'] == 'new' || $condition['db-operation'] == 'create') {
                    $this->fx->SetFMGlobal($condition['field'], $condition['value']);
                }
            }
        }
        if (isset($context['script'])) {
            foreach ($context['script'] as $condition) {
                if ($condition['db-operation'] == 'new' || $condition['db-operation'] == 'create') {
                    $this->fx = $this->executeScripts($this->fx, $condition);
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

        $this->queriedPrimaryKeys = array($keyValue);
        $this->queriedEntity = $this->fx->layout;

        $this->updatedRecord = $this->createRecordset($result['data'], $dataSourceName, null, null, null);
        return $keyValue;
    }

    public function deleteFromDB()
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
            $value = $this->normalizedCondition($value);
            if (!$this->isPossibleOperator($value['operator'])) {
                throw new Exception("Invalid Operator.");
            }
            $this->fx->AddDBParam($value['field'], $value['value'], $value['operator']);
        }
        if (isset($context['authentication'])
            && (isset($context['authentication']['all'])
                || isset($context['authentication']['delete']))
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
            $keyField = isset($context['key']) ? $context['key'] : $this->getDefaultKey();
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));
                if ($keyField == $this->getDefaultKey()) {
                    $this->queriedPrimaryKeys[] = $recId;
                } else {
                    $this->queriedPrimaryKeys[] = $row[$keyField][0];
                }
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
                            $this->fx = $this->executeScripts($this->fx, $condition);
                        }
                    }
                }

                $this->queriedEntity = $this->fx->layout;

                $result = $this->fx->DoFxAction('delete', TRUE, TRUE, 'full');
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

    public function copyInDB()
    {
        $this->errorMessage[] = "Copy operation is not implemented so far.";
    }

    private function getFieldForFormatter($entity, $field)
    {
        if (strpos($field, "::") === false) {
            return "{$entity}{$this->dbSettings->getSeparator()}{$field}";
        }
        $fieldComp = explode("::", $field);
        $ds = $this->dbSettings->getDataSource();
        foreach ($ds as $contextDef) {
            if ($contextDef["name"] == $fieldComp[0] ||
                (isset($contextDef["table"]) && $contextDef["table"] == $fieldComp[0])
            ) {
                if ($contextDef["relation"] &&
                    $contextDef["relation"][0] &&
                    $contextDef["relation"][0]["portal"] &&
                    $contextDef["relation"][0]["portal"] = true
                ) {
                    return "{$fieldComp[0]}{$this->dbSettings->getSeparator()}{$field}";
                }
            }
        }
        return "{$entity}{$this->dbSettings->getSeparator()}{$field}";
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
        $this->fx->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
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

    public function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null)
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
        $username = $this->authSupportUnifyUsernameAndEmail($username);
        $this->fx->AddDBParam('username', str_replace("@", "\\@", $username), 'eq');
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

        $username = $this->authSupportUnifyUsernameAndEmail($username);

        $this->setupFXforDB_Alt($userTable, 1);
        $this->fxAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
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
        $this->fxAlt->AddDBParam('username', str_replace("@", "\\@", $username), "eq");
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

        $this->setupFXforDB_Alt($groupTable, 1);
        $this->fxAlt->AddDBParam('id', $groupid);
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
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
        $this->setupFXforDB_Alt($this->dbSettings->getCorrTable(), 1);
        if ($this->firstLevel) {
            $this->fxAlt->AddDBParam('user_id', $groupid);
            $this->firstLevel = false;
        } else {
            $this->fxAlt->AddDBParam('group_id', $groupid);
            $this->belongGroups[] = $groupid;
        }
        $result = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
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
        $user = $this->authSupportUnifyUsernameAndEmail($user);

        $this->setupFXforAuth($tableName, 1);
        $this->fxAuth->AddDBParam($userField, $user, 'eq');
        $this->fxAuth->AddDBParam($keyField, $keyValue, 'eq');
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $array = array();
        foreach ($result['data'] as $key => $row) {
            $keyExpode = explode(".", $key);
            $record = array("-recid" => $keyExpode[0], "-modid" => $keyExpode[1]);
            foreach ($row as $field => $value) {
                $record[$field] = implode("\n", $value);
            }
            $array[] = $record;
        }
        return $array;
    }

    public function authSupportUserEnrollmentStart($userid, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam("hash", $hash);
        $this->fxAuth->AddDBParam("expired", $this->currentDTString());
        $this->fxAuth->AddDBParam("user_id", $userid);
        $result = $this->fxAuth->DoFxAction('new', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        return true;
    }

    public
    function authSupportUserEnrollmentEnrollingUser($hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        $this->setupFXforAuth($hashTable, 1);
        $this->fxAuth->AddDBParam("hash", $hash, "eq");
        $this->fxAuth->AddDBParam("clienthost", "", "eq");
        $this->fxAuth->AddDBParam("expired", $this->currentDTString(3600), "gt");
        $result = $this->fxAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($result)) {
            $this->logger->setDebugMessage(get_class($result) . ': ' . $result->getDebugInfo());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        foreach ($result['data'] as $key => $row) {
            $userID = $row['user_id'][0];
            return $userID;
        }
        return false;

    }

    public
    function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW)
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        $this->setupFXforDB_Alt($userTable, 1);
        $this->fxAlt->AddDBParam('id', $userID);
        $resultUser = $this->fxAlt->DoFxAction('perform_find', TRUE, TRUE, 'full');
        if (!is_array($resultUser)) {
            $this->logger->setDebugMessage(get_class($resultUser) . ': ' . $resultUser->toString());
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($resultUser['URL']));
        foreach ($resultUser['data'] as $ukey => $urow) {
            $recId = substr($ukey, 0, strpos($ukey, '.'));
            $this->setupFXforDB_Alt($userTable, 1);
            $this->fxAlt->SetRecordID($recId);
            $this->fxAlt->AddDBParam('hashedpasswd', $password);
            if ($rawPWField !== false) {
                $this->fxAlt->AddDBParam($rawPWField, $rawPW);
            }
            $result = $this->fxAlt->DoFxAction('update', TRUE, TRUE, 'full');
            if (!is_array($result)) {
                $this->logger->setDebugMessage(get_class($result) . ': ' . $result->toString());
                return false;
            }
            $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
            return $userID;
        }
    }

    private
    function currentDTString($addSeconds = 0)
    {
//        $currentDT = new DateTime();
//        $timeValue = $currentDT->format("U");
//        $currentDTStr = $this->link->quote($currentDT->format('m/d/Y H:i:s'));

        // For 5.2
        $timeValue = time();
        $currentDTStr = date('m/d/Y H:i:s', $timeValue - $addSeconds);
        // End of for 5.2
        return $currentDTStr;
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

    public function normalizedCondition($condition)
    {
        if (!isset($condition['field'])) {
            $condition['field'] = '';
        }
        if (!isset($condition['value'])) {
            $condition['value'] = '';
        }

        if (($condition['field'] === '-recid' && $condition['operator'] === 'undefined') ||
            ($condition['operator'] === '=')
        ) {
            return array(
                'field' => $condition['field'],
                'operator' => 'eq',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '!=') {
            return array(
                'field' => $condition['field'],
                'operator' => 'neq',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '<') {
            return array(
                'field' => $condition['field'],
                'operator' => 'lt',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '<=') {
            return array(
                'field' => $condition['field'],
                'operator' => 'lte',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '>') {
            return array(
                'field' => $condition['field'],
                'operator' => 'gt',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '>=') {
            return array(
                'field' => $condition['field'],
                'operator' => 'gte',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === 'match*') {
            return array(
                'field' => $condition['field'],
                'operator' => 'bw',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '*match') {
            return array(
                'field' => $condition['field'],
                'operator' => 'ew',
                'value' => "{$condition['value']}",
            );
        } else if ($condition['operator'] === '*match*') {
            return array(
                'field' => $condition['field'],
                'operator' => 'cn',
                'value' => "{$condition['value']}",
            );
        } else {
            return $condition;
        }
    }

    protected function _adjustSortDirection($direction)
    {
        if (strtoupper($direction) == 'ASC') {
            $direction = 'ascend';
        } else if (strtoupper($direction) == 'DESC') {
            $direction = 'descend';
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

    public function isSupportAggregation()
    {
        return false;
    }

}
