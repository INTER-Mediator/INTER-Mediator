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
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'FMDataAPI.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Spec_Handler_FileMaker_DataAPI.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Auth_Common.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Notification_Common.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Auth_Handler_FileMaker_DataAPI.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Notification_Handler_FileMaker_DataAPI.php');

class DB_FileMaker_DataAPI extends DB_UseSharedObjects implements DB_Interface
{
    public $fmData = null;     // FMDataAPI class's instance
    public $fmDataAuth = null; // FMDataAPI class's instance
    public $fmDataAlt = null;  // FMDataAPI class's instance
    private $targetLayout = null;
    private $recordCount = null;
    private $mainTableCount = 0;
    private $mainTableTotalCount = 0;
    private $fieldInfo = null;

    private $isRequiredUpdated = false;
    private $updatedRecord = null;
    private $softDeleteField = null;
    private $softDeleteValue = null;

    /**
     * @param $str
     */
    private function errorMessageStore($str)
    {
        $this->logger->setErrorMessage("Query Error: [{$str}] Error Code={$this->fmData->errorCode()}");
    }

    public function setupConnection()
    {
        return true;
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

    public function setupFMDataAPIforAuth($layoutName, $recordCount)
    {
        $this->fmData = null;
        $this->fmDataAuth = $this->setupFMDataAPI_Impl($layoutName, $recordCount,
            $this->dbSettings->getDbSpecUser(), $this->dbSettings->getDbSpecPassword());
    }

    public function setupFMDataAPIforDB($layoutName, $recordCount)
    {
        $this->fmDataAuth = null;
        $this->fmData = $this->setupFMDataAPI_Impl($layoutName, $recordCount,
            $this->dbSettings->getAccessUser(), $this->dbSettings->getAccessPassword());
    }

    public function setupFMDataAPIforDB_Alt($layoutName, $recordCount)
    {
        $this->fmDataAlt = $this->setupFMDataAPI_Impl($layoutName, $recordCount,
            $this->dbSettings->getAccessUser(), $this->dbSettings->getAccessPassword());
    }

    private function setupFMDataAPI_Impl($layoutName, $recordCount, $user, $password)
    {
        $this->targetLayout = $layoutName;
        $this->recordCount = $recordCount;
        $fmDataObj = new \INTERMediator\FileMakerServer\RESTAPI\FMDataAPI(
            $this->dbSettings->getDbSpecDatabase(),
            $user,
            $password,
            $this->dbSettings->getDbSpecServer(),
            $this->dbSettings->getDbSpecPort(),
            $this->dbSettings->getDbSpecProtocol()
        );
        $fmDataObj->setCertValidating(true);
        return $fmDataObj;
    }

    public function setupHandlers($dsn = false)
    {
        $this->authHandler = new DB_Auth_Handler_FileMaker_DataAPI($this);
        $this->notifyHandler = new DB_Notification_Handler_FileMaker_DataAPI($this);
        $this->specHandler = new DB_Spec_Handler_FileMaker_DataAPI();
    }

    public function stringWithoutCredential($str)
    {
        if (is_null($this->fmData)) {
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


    private function setSearchConditionsForCompoundFound($field, $value, $operator = NULL)
    {
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
                    isset($condition['definition']) && !empty($condition['definition'])
                ) {
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

        $this->setupFMDataAPIforDB($this->dbSettings->getEntityForRetrieve(), '');
        $layout = $this->targetLayout;
        $result = $this->fmData->{$layout}->query(NULL, NULL, 1, 1);

        $portal = array();
        if (!is_null($result)) {
            $portalNames = $result->getPortalNames();
            if (count($portalNames) >= 1) {
                foreach ($portalNames as $key => $portalName) {
                    $portal = array_merge($portal, array($key => $portalName));
                }
                $result = $this->fmData->{$layout}->query(NULL, NULL, 1, 1, $portal);
            }
        }

        //if (!is_array($result)) {
        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                    $this->stringWithoutCredential(get_class($result) . ': ' . $result->getDebugInfo()));
            }
            return false;
        }

        $returnArray = array();
        foreach ($result->getFieldNames() as $key => $fieldName) {
            $returnArray[$fieldName] = '';
        }

        return $returnArray;
    }

    public function readFromDB()
    {
        $useOrOperation = FALSE;
        $this->fieldInfo = NULL;
        $this->mainTableCount = 0;
        $this->mainTableTotalCount = 0;
        $context = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->dbSettings->getEntityForRetrieve();
        $dataSourceName = $this->dbSettings->getDataSourceName();

        $usePortal = FALSE;
        if (count($this->dbSettings->getForeignFieldAndValue()) > 0) {
            foreach ($context['relation'] as $relDef) {
                if (isset($relDef['portal']) && $relDef['portal']) {
                    $usePortal = TRUE;
                    $context['records'] = 1;
                    $context['paging'] = TRUE;
                }
            }
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
        $this->setupFMDataAPIforDB($this->dbSettings->getEntityForRetrieve(), $limitParam);
        $layout = $this->targetLayout;
        $skip = (isset($context['paging']) and $context['paging'] === true) ? $this->dbSettings->getStart() : 0;

        $searchConditions = array();
        $neqConditions = array();
        //$queryValues = array();
        $qNum = 1;

        $hasFindParams = false;
        if (isset($context['query'])) {
            foreach ($context['query'] as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $useOrOperation = true;
                } else {
                    if (isset($condition['operator'])) {
                        $condition = $this->normalizedCondition($condition);
                        if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                            throw new Exception("Invalid Operator.: {$condition['operator']}");
                        }
                        // [WIP] $this->fmData->AddDBParam($condition['field'], $condition['value'], $condition['operator']);
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $condition['field'], $condition['value'], $condition['operator']);
                    } else {
                        // [WIP] $this->fmData->AddDBParam($condition['field'], $condition['value']);
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $condition['field'], $condition['value']);
                    }
                    $hasFindParams = true;

                    // [WIP]
                    if (isset($condition['operator']) && $condition['operator'] === 'neq') {
                        $neqConditions[] = TRUE;
                    } else {
                        $neqConditions[] = FALSE;
                    }
                }
            }
        }

        $childRecordId = null;
        $childRecordIdValue = null;
        if ($this->dbSettings->getExtraCriteria()) {
            foreach ($this->dbSettings->getExtraCriteria() as $condition) {
                if ($condition['field'] == '__operation__' && strtolower($condition['operator']) == 'or') {
                    $useOrOperation = true;
                } else if ($condition['field'] == '__operation__' && strtolower($condition['operator']) == 'ex') {
                    $useOrOperation = true;
                } else {
                    $condition = $this->normalizedCondition($condition);
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['field']}/{$condition['operator']}");
                    }

                    $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                    $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : $this->specHandler->getDefaultKey();
                    if ($condition['field'] == $primaryKey && isset($condition['value'])) {
                        $this->notifyHandler->setQueriedPrimaryKeys(array($condition['value']));
                    }

                    // [WIP] $this->fmData->AddDBParam($condition['field'], $condition['value'], $condition['operator']);
                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $condition['field'], $condition['value'], $condition['operator']);
                    
                    //$queryValues[] = 'q' . $qNum;
                    //$qNum++;

                    // [WIP]
                    if (isset($condition['operator']) && $condition['operator'] === 'neq') {
                        $neqConditions[] = TRUE;
                    } else {
                        $neqConditions[] = FALSE;
                    }

                    $hasFindParams = true;
                    if ($condition['field'] == $this->specHandler->getDefaultKey()) {
                        // [WIP] $this->fmData->FMSkipRecords(0);
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
                        // [WIP] if (!$this->specHandler->isPossibleOperator($foreignOperator)) {
                        //    throw new Exception("Invalid Operator.: {$condition['operator']}");
                        //}
                        if ($useOrOperation) {
                            throw new Exception("Condition Incompatible.: The OR operation and foreign key can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                        }
                        // [WIP] $this->fmData->AddDBParam($foreignField, $formattedValue, $foreignOperator);
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $foreignField, $formattedValue, $foreignOperator);
                        $hasFindParams = true;

                        //$queryValues[] = 'q' . $qNum;
                        //$qNum++;

                        // [WIP]
                        if (isset($foreignOperator) && $foreignOperator === 'neq') {
                            $neqConditions[] = TRUE;
                        } else {
                            $neqConditions[] = FALSE;
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
            $authInfoField = $this->authHandler->getFieldForAuthorization("read");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("read");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    if ($useOrOperation) {
                        throw new Exception("Condition Incompatible.: The authorization for each record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                    }
                    $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    $this->fmData->AddDBParam($authInfoField, $signedUser, 'eq');
                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $authInfoField, $signedUser, 'eq');
                    $hasFindParams = true;

                    //$queryValues[] = 'q' . $qNum;
                    //$qNum++;
                    $neqConditions[] = FALSE;
                }
            } else
                if ($authInfoTarget == 'field-group') {
                    $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                    if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                        $authFailure = true;
                    } else {
                        if ($useOrOperation) {
                            throw new Exception("Condition Incompatible.: The authorization for each record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                        }
                        $this->fmData->AddDBParam($authInfoField, $belongGroups[0], 'eq');
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $authInfoField, $belongGroups[0], 'eq');
                        $hasFindParams = true;

                        //$queryValues[] = 'q' . $qNum;
                        //$qNum++;
                        $neqConditions[] = FALSE;
                    }
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
            $this->fmData->AddDBParam($this->softDeleteField, $this->softDeleteValue, 'neq');
            $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                $this->softDeleteField, $this->softDeleteValue, 'eq');
            $hasFindParams = true;

            //$queryValues[] = 'q' . $qNum;
            //$qNum++;
            $neqConditions[] = FALSE;
        }

        $sort = array();
        if (isset($context['sort'])) {
            foreach ($context['sort'] as $condition) {
                if (isset($condition['direction'])) {
                    if (!$this->specHandler->isPossibleOrderSpecifier($condition['direction'])) {
                        throw new Exception("Invalid Sort Specifier.");
                    }
                    $sort[] = array($condition['field'], $this->_adjustSortDirection($condition['direction']));
                } else {
                    $sort[] = array($condition['field']);
                }
            }
        }
        if ($sort === array()) {
            $sort = NULL;
        }

        $condition = array();
        if ($searchConditions !== array()) {
            if ($useOrOperation === TRUE) {
                foreach ($searchConditions as $searchCondition) {
                    $condition[] = array($searchCondition[0] => $searchCondition[1]);
                }
            } else {
                $tmpCondition = array();
                foreach ($searchConditions as $searchCondition) {
                    $tmpCondition[$searchCondition[0]] = $searchCondition[1];
                }
                $condition[] = $tmpCondition;
            }
        }
        if ($condition === array()) {
            $condition = NULL;
        }

        $request = filter_input_array(INPUT_POST);
        foreach ($request as $key => $val) {
            /*
            if (substr($key, 0, 6) !== 'field_') {
                unset($request[$key]);
            }
            if (substr($key, 0, 7) === 'foreign' && substr($key, -5, 5) === 'field') {
                if (count($this->dbSettings->getForeignFieldAndValue()) > 0) {
                    foreach ($context['relation'] as $relDef) {
                        foreach ($this->dbSettings->getForeignFieldAndValue() as $foreignDef) {
                            if (isset($relDef['join-field']) && $relDef['join-field'] == $foreignDef['field']) {
                                if (array_search($context['key'], $request) === FALSE) {
                                    $request[] = $context['key'];
                                }
                                if (array_search($relDef['foreign-key'], $request) === FALSE) {
                                    $request[] = $relDef['foreign-key'];
                                }
                            }
                        }
                    }
                }
            }
            */

            if (substr($key, 0, 7) === 'sortkey' && substr($key, -5, 5) === 'field') {
                $orderNum = substr($key, 7, 1);
                if (isset($request['sortkey' . $orderNum . 'direction'])) {
                    $sortDirection = $request['sortkey' . $orderNum . 'direction'];
                }
                if ($sort === NULL) {
                    $sort = array(array($val, $sortDirection));
                }
            }
        }

        $portal = array();
        $portalNames = array();
        $recordId = NULL;
        $result = NULL;
        try {
            if (count($condition) === 1 && isset($condition[0]['recordId'])) {
                $recordId = str_replace('=', '', $condition[0]['recordId']);
                if (is_numeric($recordId)) {
                    $result = $this->fmData->{$layout}->getRecord($recordId);
                }
            } else {
                $result = $this->fmData->{$layout}->query($condition, $sort, $skip + 1, 1);
            }
            if (!is_null($result)) {
                $portalNames = $result->getPortalNames();
                if (count($portalNames) >= 1) {
                    foreach ($portalNames as $key => $portalName) {
                        $portal = array_merge($portal, array($key => $portalName));
                    }
                    if (!is_numeric($recordId)) {
                        $result = $this->fmData->{$layout}->query($condition, $sort, $skip + 1, $limitParam, $portal);
                    }
                } else {
                    $result = $this->fmData->{$layout}->query($condition, $sort, $skip + 1, $limitParam);
                }
            }
        } catch (Exception $e) {
            // Don't output error messages if no related records
            if (strpos($e->getMessage(), 'Error Code: 401, Error Message: No records match the request') === false) {
                $this->logger->setErrorMessage("Exception: {$e->getMessage()}");
            }
        }

        $recordArray = array();
        if (!is_null($result)) {
            foreach ($result as $record) {
                $dataArray = array();
                if (!$usePortal) {
                    $dataArray = $dataArray + array(
                        'recordId' => $record->getRecordId(),
                    );
                }
                foreach ($result->getFieldNames() as $key => $fieldName) {
                //foreach ($request as $key => $fieldName) {
                    $dataArray = $dataArray + array(
                        $fieldName => $this->formatter->formatterFromDB(
                            $this->getFieldForFormatter($tableName, $fieldName), $record->{$fieldName}
                        )
                    );
                }
                
                $relatedsetArray = array();
                if (count($portalNames) >= 1) {
                    $relatedArray = array();
                    foreach ($portalNames as $key => $portalName) {
                        foreach ($result->{$portalName} as $portalRecord) {
                            $recId = $portalRecord->getRecordId();
                            foreach ($result->{$portalName}->getFieldNames() as $key => $relatedFieldName) {
                                if (strpos($relatedFieldName, '::') !== false) {
                                    $dotPos = strpos($relatedFieldName, '::');
                                    $tableOccurrence = substr($relatedFieldName, 0, $dotPos);
                                    if (!isset($relatedArray[$tableOccurrence][$recId])) {
                                        $relatedArray[$tableOccurrence][$recId] = array('recordId' => $recId);
                                    }
                                    if ($relatedFieldName !== 'recordId') {
                                        $relatedArray[$tableOccurrence][$recId] += array(
                                            $relatedFieldName =>                                     
                                                $this->formatter->formatterFromDB(
                                                    "{$tableOccurrence}{$this->dbSettings->getSeparator()}{$relatedFieldName}",
                                                    $portalRecord->{$relatedFieldName}
                                            )
                                        );
                                    }
                                }
                            }
                        }
                        $relatedsetArray = array($relatedArray);
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
                if (intval($result->count()) == 1) {
                    break;
                }
            }
            

            if ($recordId === NULL) {
                $result = $this->fmData->{$layout}->query($condition, NULL, 1, 100000000);
            }
            $this->mainTableCount = $result->count();
            $this->mainTableTotalCount = $result->count();
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
            $oneRecordArray[$this->specHandler->getDefaultKey()] = $recId;

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
                            $oneRecordArray[$portalKey][$this->specHandler->getDefaultKey()] = $recId; // parent record id
                            $oneRecordArray[$portalKey][$field] = $this->formatter->formatterFromDB(
                                "{$tableName}{$this->dbSettings->getSeparator()}$field", $portalValue);
                        }
                        if ($existsRelated === false) {
                            $oneRecordArray = array();
                            $oneRecordArray[0][$this->specHandler->getDefaultKey()] = $recId; // parent record id
                        }
                    } else {
                        $oneRecordArray[$field] = $this->formatter->formatterFromDB(
                            "{$tableName}{$this->dbSettings->getSeparator()}$field", $dataArray[0]);
                    }
                } else {
                    foreach ($dataArray as $portalKey => $portalValue) {
                        if (strpos($field, '::') !== false) {
                            $existsRelated = true;
                            $oneRecordArray[$portalKey][$this->specHandler->getDefaultKey()] = $recId; // parent record id
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
                    if ($portalArrayField !== $this->specHandler->getDefaultKey()) {
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
                }
            }
        }

        if ($usePortal) {
            $layout = $this->dbSettings->getEntityForRetrieve();
            $this->setupFMDataAPIforDB($layout, 1);
        } else {
            $layout = $this->dbSettings->getEntityForUpdate();
            $this->setupFMDataAPIforDB($layout, 1);
        }
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : $this->specHandler->getDefaultKey();

        if (isset($tableInfo['query'])) {
            foreach ($tableInfo['query'] as $condition) {
                if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    $condition = $this->normalizedCondition($condition);
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.");
                    }
                    $convertedValue = $this->formatter->formatterToDB(
                        "{$tableSourceName}{$this->dbSettings->getSeparator()}{$condition['field']}",
                        $condition['value']);
                    // [WIP] $this->fmData->AddDBParam($condition['field'], $convertedValue, $condition['operator']);
                }
            }
        }

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            if (!$this->dbSettings->getPrimaryKeyOnly() || $value['field'] == $primaryKey) {
                $value = $this->normalizedCondition($value);
                if (!$this->specHandler->isPossibleOperator($value['operator'])) {
                    throw new Exception("Invalid Operator.: {$condition['operator']}");
                }
                $convertedValue = $this->formatter->formatterToDB(
                    "{$tableSourceName}{$this->dbSettings->getSeparator()}{$value['field']}", $value['value']);
                /*
                [WIP]
                if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $value['field'], $convertedValue) === TRUE) {
                    $this->fmData->AddDBParam($value['field'], $convertedValue, $value['operator']);
                }
                */
            }
        }
        if (isset($tableInfo['authentication'])
            && (isset($tableInfo['authentication']['all'])
                || isset($tableInfo['authentication']['update']))
        ) {
            $authFailure = FALSE;
            $authInfoField = $this->authHandler->getFieldForAuthorization("update");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("update");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $authInfoField, $signedUser) === TRUE) {
                        $this->fmData->AddDBParam($authInfoField, $signedUser, "eq");
                    }
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                    $authFailure = true;
                } else {
                    if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $authInfoField, $belongGroups[0]) === TRUE) {
                        $this->fmData->AddDBParam($authInfoField, $belongGroups[0], "eq");
                    }
                }
            } else {
                if ($this->dbSettings->isDBNative()) {
                } else {
                    $authorizedUsers = $this->authHandler->getAuthorizedUsers("update");
                    $authorizedGroups = $this->authHandler->getAuthorizedGroups("update");
                    $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
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

        $condition = array(array($primaryKey => filter_input(INPUT_POST, 'condition0value')));
        $result = NULL;
        $portal = array();
        if (count($condition) === 1 && isset($condition[0]['recordId'])) {
            $recordId = str_replace('=', '', $condition[0]['recordId']);
            if (is_numeric($recordId)) {
                $result = $this->fmData->{$layout}->getRecord($recordId);
            }
        } else {
            $result = $this->fmData->{$layout}->query($condition, NULL, 1, 1);
            $portalNames = $result->getPortalNames();
            if (count($portalNames) >= 1) {
                foreach ($portalNames as $key => $portalName) {
                    $portal = array_merge($portal, array($key => $portalName));
                }
                $result = $this->fmData->{$layout}->query($condition, NULL, 1, 1, $portal);
            }
        }

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                    $this->stringWithoutCredential(get_class($result) . ': ' . $result->getDebugInfo()));
            }
            return false;
        }

        // [WIP] $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
//        $this->logger->setDebugMessage($this->stringWithoutCredential(var_export($this->dbSettings->getFieldsRequired(),true)));

        /* [WIP]
        if ($result['errorCode'] > 0) {
            $this->logger->setErrorMessage($this->stringWithoutCredential(
                "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>"));
            return false;
        }
        */
        if ($result->count() === 1) {
            $this->notifyHandler->setQueriedPrimaryKeys(array());
            $keyField = isset($context['key']) ? $context['key'] : $this->specHandler->getDefaultKey();
            //foreach ($result['data'] as $key => $row) {
            foreach ($result as $record) {
                $recId = $record->getRecordId();
                if ($keyField == $this->specHandler->getDefaultKey()) {
                    $this->notifyHandler->addQueriedPrimaryKeys($recId);
                } else {
                    $this->notifyHandler->addQueriedPrimaryKeys($record->{$keyField});
                }
                /*
                if ($usePortal) {
                    $this->setupFMDataAPIforDB($this->dbSettings->getEntityForRetrieve(), 1);
                } else {
                    $this->setupFMDataAPIforDB($this->dbSettings->getEntityForUpdate(), 1);
                }
                */
                //$this->fmData->SetRecordID($recId);
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
                    /* [WIP]
                    if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $field, $convVal) === TRUE) {
                        $this->fmData->AddDBParam($field, $convVal);
                    }
                    */
                }
                if ($counter < 1) {
                    $this->logger->setErrorMessage('No data to update.');
                    return false;
                }
                if (isset($tableInfo['global'])) {
                    foreach ($tableInfo['global'] as $condition) {
                        if ($condition['db-operation'] == 'update') {
                            $this->fmData->SetFMGlobal($condition['field'], $condition['value']);
                        }
                    }
                }
                /*
                // [WIP] FileMaker Data API (Trial) doesn't support executing FileMaker scripts
                if (isset($tableInfo['script'])) {
                    foreach ($tableInfo['script'] as $condition) {
                        if ($condition['db-operation'] == 'update') {
                            $this->fmData = $this->executeScripts($this->fmData, $condition);
                        }
                    }
                }
                */

                $this->notifyHandler->setQueriedEntity($this->fmData->layout);

                $originalfield = filter_input(INPUT_POST, 'field_0');
                $value = filter_input(INPUT_POST, 'value_0');
                $convVal = $this->formatter->formatterToDB(
                    $this->getFieldForFormatter($tableSourceName, $originalfield), $value);
                $this->fmData->{$layout}->update($recId, array($originalfield => $convVal));
                $result = $this->fmData->{$layout}->getRecord($recId);
                /* [WIP]
                if (!is_array($result)) {
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        get_class($result) . ': ' . $result->getDebugInfo()));
                    return false;
                }
                */
                /* [WIP]
                if ($result['errorCode'] > 0) {
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        "FX reports error at edit action: table={$this->dbSettings->getEntityForUpdate()}, "
                        . "code={$result['errorCode']}, url={$result['URL']}<hr>"));
                    return false;
                }
                */
                //$this->updatedRecord = $this->createRecordset($result, $dataSourceName, null, null, null);
                // [WIP] $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
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
                }
            }
        }

        $keyFieldName = isset($context['key']) ? $context['key'] : $this->specHandler->getDefaultKey();

        $recordData = array();

        $this->setupFMDataAPIforDB($this->dbSettings->getEntityForUpdate(), 1);
        $requiredFields = $this->dbSettings->getFieldsRequired();
        $countFields = count($requiredFields);
        $fieldValues = $this->dbSettings->getValue();
        for ($i = 0; $i < $countFields; $i++) {
            $field = $requiredFields[$i];
            $value = $fieldValues[$i];
            if ($field != $keyFieldName) {
                $recordData += array(
                    $field =>
                    $this->formatter->formatterToDB(
                        "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}",
                        $this->unifyCRLF((is_array($value)) ? implode("\r", $value) : $value))
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
                    $recordData += array($field => $this->formatter->formatterToDB($filedInForm, $convVal));
                }
            }
        }
        if (!$bypassAuth && isset($context['authentication'])
            && (isset($context['authentication']['all'])
                || isset($context['authentication']['new'])
                || isset($context['authentication']['create']))
        ) {
            $authInfoField = $this->authHandler->getFieldForAuthorization("create");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("create");
            if ($authInfoTarget == 'field-user') {
                $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                $recordData += array(
                    $authInfoField =>
                    strlen($this->dbSettings->getCurrentUser()) == 0 ? randomString(10) : $signedUser
                );
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                $recordData += array(
                    $authInfoField =>
                    strlen($belongGroups[0]) == 0 ? randomString(10) : $belongGroups[0]
                );
            } else {
                if ($this->dbSettings->isDBNative()) {
                } else {
                    $authorizedUsers = $this->authHandler->getAuthorizedUsers("create");
                    $authorizedGroups = $this->authHandler->getAuthorizedGroups("create");
                    $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
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
                    $this->fmData->SetFMGlobal($condition['field'], $condition['value']);
                }
            }
        }
        /*
        // [WIP] FileMaker Data API (Trial) doesn't support executing FileMaker scripts
        if (isset($context['script'])) {
            foreach ($context['script'] as $condition) {
                if ($condition['db-operation'] == 'new' || $condition['db-operation'] == 'create') {
                    $this->fmData = $this->executeScripts($this->fmData, $condition);
                }
            }
        }
        */

        $layout = $this->dbSettings->getEntityForUpdate();
        $recId = $this->fmData->{$layout}->create($recordData);
        $result = $this->fmData->{$layout}->getRecord($recId);
        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                // [WIP] $this->errorMessage[] = get_class($result) . ': ' . $result->getDebugInfo();
            }
            return false;
        }

        // [WIP] $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        if ($this->fmData->errorCode() > 0 && $this->fmData->errorCode() != 401) {
            $this->logger->setErrorMessage($this->stringWithoutCredential(
                "FileMaker Data API reports error at create action: code={$this->fmData->errorCode()}<hr>"));
            return false;
        }

        $this->notifyHandler->setQueriedPrimaryKeys(array($recId));
        $this->notifyHandler->setQueriedEntity($this->fmData->layout);

        // [WIP] $this->updatedRecord = $this->createRecordset($result['data'], $dataSourceName, null, null, null);
        return $recId;
    }

    public function deleteFromDB()
    {
        $this->fieldInfo = null;

        $context = $this->dbSettings->getDataSourceTargetArray();
        $condition = array();

        $usePortal = false;
        if (isset($context['relation'])) {
            foreach ($context['relation'] as $relDef) {
                if (isset($relDef['portal']) && $relDef['portal']) {
                    $usePortal = true;
                    $context['paging'] = true;
                }
            }
        }

        if ($usePortal) {
            $layout = $this->dbSettings->getEntityForRetrieve();
            $this->setupFMDataAPIforDB($layout, 10000000);
        } else {
            $layout = $this->dbSettings->getEntityForUpdate();
            $this->setupFMDataAPIforDB($layout, 10000000);
        }

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            $value = $this->normalizedCondition($value);
            if (!$this->specHandler->isPossibleOperator($value['operator'])) {
                throw new Exception("Invalid Operator.");
            }
            $condition += array($value['field'] => $value['value']);
            // $this->fmData->AddDBParam($value['field'], $value['value'], $value['operator']);  [WIP]
        }
        if (isset($context['authentication'])
            && (isset($context['authentication']['all'])
                || isset($context['authentication']['delete']))
        ) {
            $authFailure = FALSE;
            $authInfoField = $this->authHandler->getFieldForAuthorization("delete");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("delete");
            if ($authInfoTarget == 'field-user') {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    $this->fmData->AddDBParam($authInfoField, $signedUser, "eq");
                    $hasFindParams = true;
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                $groupCriteria = array();
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($groupCriteria) == 0) {
                    $authFailure = true;
                } else {
                    $this->fmData->AddDBParam($authInfoField, $belongGroups[0], "eq");
                    $hasFindParams = true;
                }
            } else {
                if ($this->dbSettings->isDBNative()) {
                } else {
                    $authorizedUsers = $this->authHandler->getAuthorizedUsers("delete");
                    $authorizedGroups = $this->authHandler->getAuthorizedGroups("delete");
                    $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
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

        if (isset($condition['recordId']) && is_numeric($condition['recordId'])) {
            $result = $this->fmData->{$layout}->getRecord($condition['recordId']);
        } else {
            $result = $this->fmData->{$layout}->query(array($condition), NULL, 1, 1);
        }
        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->errorMessage[] = get_class($result) . ': ' . $result->getDebugInfo();
            }
            return false;
        }
        // [WIP] $this->logger->setDebugMessage($this->stringWithoutCredential($result['URL']));
        //$this->logger->setDebugMessage($this->stringWithoutCredential(var_export($result['data'],true)));
        if ($this->fmData->errorCode() > 0) {
            $this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
            return false;
        }
        if ($result->count() > 0) {
            $keyField = isset($context['key']) ? $context['key'] : $this->specHandler->getDefaultKey();
            foreach ($result as $record) {
                $recId = $record->getRecordId();
                if ($keyField == $this->specHandler->getDefaultKey()) {
                    $this->notifyHandler->addQueriedPrimaryKeys($recId);
                } else {
                    $this->notifyHandler->addQueriedPrimaryKeys($record->{$keyField});
                }
                $this->setupFMDataAPIforDB($this->dbSettings->getEntityForUpdate(), 1);
                if (isset($context['global'])) {
                    foreach ($context['global'] as $condition) {
                        if ($condition['db-operation'] == 'delete') {
                            $this->fmData->SetFMGlobal($condition['field'], $condition['value']);
                        }
                    }
                }
                /* 
                // [WIP] FileMaker Data API (Trial) doesn't support executing FileMaker scripts
                if (isset($context['script'])) {
                    foreach ($context['script'] as $condition) {
                        if ($condition['db-operation'] == 'delete') {
                            $this->fmData = $this->executeScripts($this->fmData, $condition);
                        }
                    }
                }
                */

                $this->notifyHandler->setQueriedEntity($this->fmData->layout);

                $result = $this->fmData->{$layout}->delete($recId);
                //if (!is_array($result)) {
                if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                    if ($this->dbSettings->isDBNative()) {
                        $this->dbSettings->setRequireAuthentication(true);
                    } else {
                        /* [WIP]
                        $this->logger->setErrorMessage($this->stringWithoutCredential(
                            get_class($result) . ': ' . $result->getDebugInfo()));
                        */
                    }
                    return false;
                }
                if ($this->fmData->errorCode() > 0) {
                    /* [WIP]
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        "FileMaker Data API reports error at delete action: code={$result['errorCode']}, url={$result['URL']}<hr>"));
                    */
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
                if (isset($contextDef["relation"]) &&
                    isset($contextDef["relation"][0]) &&
                    isset($contextDef["relation"][0]["portal"]) &&
                    $contextDef["relation"][0]["portal"] = true
                ) {
                    return "{$fieldComp[0]}{$this->dbSettings->getSeparator()}{$field}";
                }
            }
        }
        return "{$entity}{$this->dbSettings->getSeparator()}{$field}";
    }

    public function normalizedCondition($condition)
    {
        if (!isset($condition['field'])) {
            $condition['field'] = '';
        }
        if (!isset($condition['value'])) {
            $condition['value'] = '';
        }

        if (($condition['field'] === 'recordId' && $condition['operator'] === 'undefined') ||
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

    public function queryForTest($table, $conditions = null)
    {
        if ($table == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        $this->setupFMDataAPIforAuth($table, 'all');
        if (count($conditions) > 0) {
            foreach ($conditions as $field => $value) {
                $this->fmDataAuth->AddDBParam($field, $value, 'eq');
            }
        }
        if (count($conditions) > 0) {
            $result = $this->fmDataAuth->DoFxAction('perform_find', TRUE, TRUE, 'full');
        } else {
            $result = $this->fmDataAuth->DoFxAction('show_all', TRUE, TRUE, 'full');
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

    protected function _adjustSortDirection($direction)
    {
        if (strtoupper($direction) == 'ASC') {
            $direction = 'ascend';
        } else if (strtoupper($direction) == 'DESC') {
            $direction = 'descend';
        }

        return $direction;
    }


    public function deleteForTest($table, $conditions = null)
    {
        // TODO: Implement deleteForTest() method.
    }
}
