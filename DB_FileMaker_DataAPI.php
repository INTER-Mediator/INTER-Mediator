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
    public function errorMessageStore($str)
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
        if(!isset($_SESSION)){
            session_start();
        }
        if (in_array($layoutName, array($this->dbSettings->getUserTable(), $this->dbSettings->getHashTable()))) {
            $token = isset($_SESSION['X-FM-Data-Access-Token-Auth']) ? $_SESSION['X-FM-Data-Access-Token-Auth'] : '';
        } else {
            $token = isset($_SESSION['X-FM-Data-Access-Token']) ? $_SESSION['X-FM-Data-Access-Token'] : '';
        }
        try {
            if ($token === '') {
                throw new \Exception();
            }
            $fmDataObj = new \INTERMediator\FileMakerServer\RESTAPI\FMDataAPI(
                $this->dbSettings->getDbSpecDatabase(),
                '',
                '',
                $this->dbSettings->getDbSpecServer(),
                $this->dbSettings->getDbSpecPort(),
                $this->dbSettings->getDbSpecProtocol()
            );
            $fmDataObj->setSessionToken($token);
            $fmDataObj->setCertValidating(true);
            $fmDataObj->{$layoutName}->startCommunication();
            $fmDataObj->{$layoutName}->query(NULL, NULL, -1, 1);
        } catch (\Exception $e) {
            $fmDataObj = new \INTERMediator\FileMakerServer\RESTAPI\FMDataAPI(
                $this->dbSettings->getDbSpecDatabase(),
                $user,
                $password,
                $this->dbSettings->getDbSpecServer(),
                $this->dbSettings->getDbSpecPort(),
                $this->dbSettings->getDbSpecProtocol()
            );
            $fmDataObj->setCertValidating(true);
            try {
                $fmDataObj->{$layoutName}->startCommunication();
            } catch (\Exception $e) {
            }
        }
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
        if ($operator === NULL) {
            return array($field, $value);
        } else if ($operator === 'eq' || $operator === 'neq') {
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

    private function executeScripts($scriptContext)
    {
        $script = array();
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
                            $script = $script + array('script' => $scriptName);
                            if ($parameter !== '') {
                                $script = $script + array('script.param' => $parameter);
                            }
                            break;
                        case 'pre':
                            $script = $script + array('script.prerequest' => $scriptName);
                            if ($parameter !== '') {
                                $script = $script + array('script.prerequest.param' => $parameter);
                            }
                            break;
                        case 'presort':
                            $script = $script + array('script.presort' => $scriptName);
                            if ($parameter !== '') {
                                $script = $script + array('script.presort.param' => $parameter);
                            }
                            break;
                    }
                }
            }
        }

        return $script === array() ? NULL : $script;
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

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                    $this->stringWithoutCredential(get_class($result) . ': '. $this->fmData->{$layout}->getDebugInfo()));
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
        $portalParentKeyField = NULL;
        if (count($this->dbSettings->getForeignFieldAndValue()) > 0 || isset($context['relation'])) {
            foreach ($context['relation'] as $relDef) {
                if (isset($relDef['portal']) && $relDef['portal']) {
                    $usePortal = TRUE;
                    $context['records'] = 1;
                    $context['paging'] = TRUE;
                }
            }
            if ($usePortal === TRUE) {
                $this->dbSettings->setDataSourceName($context['view']);
                $parentTable = $this->dbSettings->getDataSourceTargetArray();
                $portalParentKeyField = $parentTable['key'];
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

        $hasFindParams = false;
        if (isset($context['query'])) {
            foreach ($context['query'] as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $useOrOperation = true;
                } else {
                    if (isset($condition['operator'])) {
                        $condition = $this->normalizedCondition($condition);
                        if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                            throw new \Exception("Invalid Operator.: {$condition['operator']}");
                        }
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $condition['field'], $condition['value'], $condition['operator']);
                    } else {
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $condition['field'], $condition['value']);
                    }
                    $hasFindParams = true;

                    if (isset($condition['operator']) && $condition['operator'] === 'neq') {
                        $neqConditions[] = TRUE;
                    } else {
                        $neqConditions[] = FALSE;
                    }
                }
            }
        }

        if ($this->dbSettings->getExtraCriteria()) {
            foreach ($this->dbSettings->getExtraCriteria() as $condition) {
                if ($condition['field'] == '__operation__' && strtolower($condition['operator']) == 'or') {
                    $useOrOperation = true;
                } else if ($condition['field'] == '__operation__' && strtolower($condition['operator']) == 'ex') {
                    $useOrOperation = true;
                } else {
                    $condition = $this->normalizedCondition($condition);
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new \Exception("Invalid Operator.: {$condition['field']}/{$condition['operator']}");
                    }

                    $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                    $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : $this->specHandler->getDefaultKey();
                    if ($condition['field'] == $primaryKey && isset($condition['value'])) {
                        $this->notifyHandler->setQueriedPrimaryKeys(array($condition['value']));
                    }

                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $condition['field'], $condition['value'], $condition['operator']);

                    if (isset($condition['operator']) && $condition['operator'] === 'neq') {
                        $neqConditions[] = TRUE;
                    } else {
                        $neqConditions[] = FALSE;
                    }

                    $hasFindParams = true;
                    if ($condition['field'] === $primaryKey) {
                        $skip = 0;
                    }
                }
            }
        }

        if (count($this->dbSettings->getForeignFieldAndValue()) > 0 || isset($context['relation'])) {
            foreach ($context['relation'] as $relDef) {
                foreach ($this->dbSettings->getForeignFieldAndValue() as $foreignDef) {
                    if (isset($relDef['join-field']) && $relDef['join-field'] == $foreignDef['field']) {
                        $foreignField = $relDef['foreign-key'];
                        $foreignValue = $foreignDef['value'];
                        $relDef = $this->normalizedCondition($relDef);
                        $foreignOperator = isset($relDef['operator']) ? $relDef['operator'] : 'eq';
                        $formattedValue = $this->formatter->formatterToDB(
                            "{$tableName}{$this->dbSettings->getSeparator()}{$foreignField}", $foreignValue);
                        if (!$this->specHandler->isPossibleOperator($foreignOperator)) {
                            throw new \Exception("Invalid Operator.: {$relDef['operator']}");
                        }
                        if ($useOrOperation) {
                            throw new \Exception("Condition Incompatible.: The OR operation and foreign key can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                        }
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $foreignField, $formattedValue, $foreignOperator);
                        $hasFindParams = true;

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
                        throw new \Exception("Condition Incompatible.: The authorization for each record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                    }
                    $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $authInfoField, '=' . $signedUser, 'eq');
                    $hasFindParams = true;

                    $neqConditions[] = FALSE;
                }
            } else
                if ($authInfoTarget == 'field-group') {
                    $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                    if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                        $authFailure = true;
                    } else {
                        if ($useOrOperation) {
                            throw new \Exception("Condition Incompatible.: The authorization for each record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                        }
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $authInfoField, '=' . $belongGroups[0], 'eq');
                        $hasFindParams = true;

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
                throw new \Exception("Condition Incompatible.: The soft-delete record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
            }
            $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                $this->softDeleteField, $this->softDeleteValue, 'neq');
            $hasFindParams = true;

            $neqConditions[] = TRUE;
        }

        $sort = array();
        if (isset($context['sort'])) {
            foreach ($context['sort'] as $condition) {
                if (isset($condition['direction'])) {
                    if (!$this->specHandler->isPossibleOrderSpecifier($condition['direction'])) {
                        throw new \Exception("Invalid Sort Specifier.");
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

        $conditions = array();
        if ($searchConditions !== array()) {
            if ($useOrOperation === TRUE) {
                $i = 0;
                foreach ($searchConditions as $searchCondition) {
                    if ($neqConditions[$i] === TRUE) {
                        $conditions[] = array(
                            $searchCondition[0] => $searchCondition[1],
                            'omit' => 'true'
                        );
                    } else {
                        array_unshift($conditions, array($searchCondition[0] => $searchCondition[1]));
                    }
                    $i++;
                }
            } else {
                $tmpCondition = array();
                $i = 0;
                foreach ($searchConditions as $searchCondition) {
                    if ($neqConditions[$i] === TRUE) {
                        $conditions[] = array(
                            $searchCondition[0] => $searchCondition[1],
                            'omit' => 'true'
                        );
                    } else {
                        $tmpCondition[$searchCondition[0]] = $searchCondition[1];
                    }
                    $i++;
                }
                if ($tmpCondition !== array()) {
                    array_unshift($conditions, $tmpCondition);
                }
            }
        }
        if ($conditions === array()) {
            $conditions = NULL;
        }

        if (isset($tableInfo['global'])) {
            foreach ($tableInfo['global'] as $condition) {
                if (isset($condition['db-operation']) && in_array($condition['db-operation'], array('load', 'read'))) {
                    $this->fmData->{$layout}->setGlobalField(
                        array($condition['field'] => $condition['value'])
                    );
                }
            }
        }

        $script = NULL;
        if (isset($context['script'])) {
            foreach ($context['script'] as $condition) {
                if (isset($condition['db-operation']) && in_array($condition['db-operation'], array('load', 'read'))) {
                    $script = $this->executeScripts($context['script']);
                }
            }
        }

        $request = filter_input_array(INPUT_POST);
        if (!is_null($request)) {
            foreach ($request as $key => $val) {
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
        }

        $portal = array();
        $portalNames = array();
        $recordId = NULL;
        $result = NULL;
        $scriptResultPrerequest = NULL;
        $scriptResultPresort = NULL;
        $scriptResult = NULL;
        try {
            if ($conditions && count($conditions) === 1 && isset($conditions[0]['recordId'])) {
                $recordId = str_replace('=', '', $conditions[0]['recordId']);
                if (is_numeric($recordId)) {
                    $conditions[0]['recordId'] = $recordId;
                    $result = $this->fmData->{$layout}->getRecord($recordId);
                }
            } else {
                $result = $this->fmData->{$layout}->query($conditions, $sort, $skip + 1, 1);
            }

            $this->notifyHandler->setQueriedEntity($layout);
            $this->notifyHandler->setQueriedCondition("/fmi/rest/api/find/{$this->dbSettings->getDbSpecDatabase()}/{$layout}" . ($recordId ? "/{$recordId}" : ""));

            if (!is_null($result)) {
                $portalNames = $result->getPortalNames();
                if (count($portalNames) >= 1) {
                    foreach ($portalNames as $key => $portalName) {
                        $portal = array_merge($portal, array($key => $portalName));
                    }
                    if (!is_numeric($recordId)) {
                        $result = $this->fmData->{$layout}->query(
                            $conditions,
                            $sort,
                            $skip + 1,
                            $limitParam,
                            $portal,
                            $script
                        );
                        $scriptResultPrerequest = $this->fmData->{$layout}->getScriptResultPrerequest();
                        $scriptResultPresort = $this->fmData->{$layout}->getScriptResultPresort();
                        $scriptResult = $this->fmData->{$layout}->getScriptResult();
                    }
                } else {
                    $result = $this->fmData->{$layout}->query(
                        $conditions,
                        $sort,
                        $skip + 1,
                        $limitParam,
                        $portal,
                        $script
                    );
                    $scriptResultPrerequest = $this->fmData->{$layout}->getScriptResultPrerequest();
                    $scriptResultPresort = $this->fmData->{$layout}->getScriptResultPresort();
                    $scriptResult = $this->fmData->{$layout}->getScriptResult();
                }
            }
            $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
        } catch (\Exception $e) {
            // Don't output error messages if no (related) records
            if (strpos($e->getMessage(), 'Error Code: 101, Error Message: Record is missing') === false &&
                strpos($e->getMessage(), 'Error Code: 401, Error Message: No records match the request') === false) {
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
                    $dataArray = $dataArray + array(
                        $fieldName => $this->formatter->formatterFromDB(
                            $this->getFieldForFormatter($tableName, $fieldName), strval($record->{$fieldName})
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

            $productInfo = $this->fmData->getProductInfo();
            if (is_object($productInfo) && property_exists($productInfo, 'version')) {
                $productVersion = intval($productInfo->version);
            } else {
                $productVersion = 17;
            }
            if ($scriptResultPrerequest !== NULL || $scriptResultPresort !== NULL || $scriptResult !== NULL) {
                // Avoid multiple executing FileMaker script
                if ($scriptResultPresort === NULL && $scriptResult === NULL) {
                    $scriptResult = $scriptResultPrerequest;
                } else if ($scriptResult === NULL) {
                    $scriptResult = $scriptResultPresort;
                }
                if (strpos($scriptResult, '/') !== false) {
                    $mainTableCount = substr($scriptResult, 0, strpos($scriptResult, '/'));
                    $mainTableTotalCount = substr($scriptResult, strpos($scriptResult, '/') + 1, strlen($scriptResult));
                    $this->mainTableCount = intval($mainTableCount);
                    $this->mainTableTotalCount = intval($mainTableTotalCount);
                } else {
                    $this->mainTableCount = intval($scriptResult);
                    $this->mainTableTotalCount = intval($scriptResult);
                }
            } else {
                if ($conditions && count($conditions) === 1 && isset($conditions[0]['recordId']) && is_numeric($recordId)) {
                    $this->mainTableCount = 1;
                } else {
                    if ($productVersion >= 18) {
                        $this->mainTableCount = $result->getFoundCount();
                    } else {
                        $result = $this->fmData->{$layout}->query($conditions, NULL, 1, 100000000, NULL, $script);
                        $this->mainTableCount = $result->count();
                    }
                }
                if ($productVersion >= 18) {
                    $this->mainTableTotalCount = $result->getTotalCount();
                } else {
                    $result = $this->fmData->{$layout}->query(NULL, NULL, 1, 100000000, NULL, $script);
                    $this->mainTableTotalCount = $result->count();
                }
            }
        }

        $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));

        $token = $this->fmData->getSessionToken();
        if (in_array($layout, array($this->dbSettings->getUserTable(), $this->dbSettings->getHashTable()))) {
            if (!isset($_SESSION['X-FM-Data-Access-Token-Auth'])) {
                $_SESSION['X-FM-Data-Access-Token-Auth'] = $token;
            }
        } else {
            if (!isset($_SESSION['X-FM-Data-Access-Token'])) {
                $_SESSION['X-FM-Data-Access-Token'] = $token;
            }
        }

        return $recordArray;
    }

    private function createRecordset($resultData)
    {
        $returnArray = array();
        $tableName = $this->dbSettings->getEntityForRetrieve();

        foreach ($resultData as $oneRecord) {
            $oneRecordArray = array();

            $recId = $resultData->getRecordId();
            $oneRecordArray[$this->specHandler->getDefaultKey()] = $recId;

            $existsRelated = false;
            foreach ($resultData->getFieldNames() as $key => $field) {
                $oneRecordArray[$field] = $this->formatter->formatterFromDB(
                    "{$tableName}{$this->dbSettings->getSeparator()}$field", $oneRecord->$field);
                foreach ($resultData->getPortalNames() as $portalName) {
                    foreach ($resultData->{$portalName} as $relatedRecord) {
                        $oneRecordArray[$portalName][$relatedRecord->getRecordId()] = array();
                        foreach ($resultData->{$portalName}->getFieldNames() as $relatedKey => $relatedField) {
                            if (strpos($relatedField, '::') !== false &&
                                !in_array($relatedField, array('recordId', 'modId'))) {
                                $oneRecordArray[$portalName][$relatedRecord->getRecordId()][$this->specHandler->getDefaultKey()] = $relatedRecord->getRecordId();
                                $oneRecordArray[$portalName][$relatedRecord->getRecordId()][$relatedField] = $this->formatter->formatterFromDB(
                                    "{$tableName}{$this->dbSettings->getSeparator()}$relatedField", $relatedRecord->$relatedField);
                            }
                        }
                    }
                }
            }
            $returnArray[] = $oneRecordArray;
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
        $data = array();

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
                        throw new \Exception("Invalid Operator.");
                    }
                    $convertedValue = $this->formatter->formatterToDB(
                        "{$tableSourceName}{$this->dbSettings->getSeparator()}{$condition['field']}",
                        $condition['value']);
                    $data += array($condition['field'] => $convertedValue);
                }
            }
        }

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            if (!$this->dbSettings->getPrimaryKeyOnly() || $value['field'] == $primaryKey) {
                $value = $this->normalizedCondition($value);
                if (!$this->specHandler->isPossibleOperator($value['operator'])) {
                    throw new \Exception("Invalid Operator.: {$condition['operator']}");
                }
                $convertedValue = $this->formatter->formatterToDB(
                    "{$tableSourceName}{$this->dbSettings->getSeparator()}{$value['field']}", $value['value']);
                $data += array($value['field'] => $convertedValue);
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
                    $data += array($authInfoField => '=' . $signedUser);
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                    $authFailure = true;
                } else {
                    $data += array($authInfoField => '=' . $belongGroups[0]);
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

        $pKeyFieldName = filter_input(INPUT_POST, 'condition0field');
        $pKey = filter_input(INPUT_POST, 'condition0value');
        if ($pKey === NULL || $pKey === FALSE || isset($data[$pKeyFieldName])) {
            $condition = array($data);
        } else {
            $condition = array(array($primaryKey => filter_input(INPUT_POST, 'condition0value')));
        }
        $result = NULL;
        $data = array();
        $portal = array();
        if (count($condition) === 1 && isset($condition[0]) && isset($condition[0]['recordId'])) {
            $recordId = str_replace('=', '', $condition[0]['recordId']);
            if (is_numeric($recordId)) {
                $result = $this->fmData->{$layout}->getRecord($recordId);
            }
        } else {
            $result = $this->fmData->{$layout}->query($condition, NULL, 1, 1);
            if (!is_null($result)) {
                $portalNames = $result->getPortalNames();
                if (count($portalNames) >= 1) {
                    foreach ($portalNames as $key => $portalName) {
                        $portal = array_merge($portal, array($key => $portalName));
                    }
                    $result = $this->fmData->{$layout}->query($condition, NULL, 1, 1, $portal);
                }
            }
        }

        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                    $this->stringWithoutCredential(get_class($result) . ': '. $this->fmData->{$layout}->getDebugInfo()));
            }
            return false;
        }

        $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
//        $this->logger->setDebugMessage($this->stringWithoutCredential(var_export($this->dbSettings->getFieldsRequired(),true)));

        if ($this->fmData->errorCode() > 0) {
            $this->logger->setErrorMessage($this->stringWithoutCredential(
                "FileMaker Data API reports error at find action: code={$this->fmData->errorCode()}, url={$this->fmData->{$layout}->getDebugInfo()}<hr>"));
            return false;
        }

        if ($result->count() === 1) {
            $this->notifyHandler->setQueriedPrimaryKeys(array());
            $keyField = isset($context['key']) ? $context['key'] : $this->specHandler->getDefaultKey();
            foreach ($result as $record) {
                $recId = $record->getRecordId();
                if ($keyField == $this->specHandler->getDefaultKey()) {
                    $this->notifyHandler->addQueriedPrimaryKeys($recId);
                } else {
                    $this->notifyHandler->addQueriedPrimaryKeys($record->{$keyField});
                }
                if ($usePortal) {
                    $this->setupFMDataAPIforDB($this->dbSettings->getEntityForRetrieve(), 1);
                } else {
                    $this->setupFMDataAPIforDB($this->dbSettings->getEntityForUpdate(), 1);
                }
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
                    $data += array($field => $convVal);
                }
                if ($counter < 1) {
                    $this->logger->setErrorMessage('No data to update.');
                    return false;
                }
                if (isset($tableInfo['global'])) {
                    foreach ($tableInfo['global'] as $condition) {
                        if ($condition['db-operation'] == 'update') {
                            $this->fmData->{$layout}->setGlobalField(
                                array($condition['field'] => $condition['value'])
                            );
                        }
                    }
                }
                $script = NULL;
                if (isset($context['script'])) {
                    foreach ($context['script'] as $condition) {
                        if ($condition['db-operation'] == 'update') {
                            $script = $this->executeScripts($context['script']);
                        }
                    }
                }

                $this->notifyHandler->setQueriedEntity($layout);
                $this->fmData->{$layout}->keepAuth = true;

                $fieldName = filter_input(INPUT_POST, '_im_field');
                $useContainer = FALSE;
                if (isset($context['file-upload'])) {
                    foreach ($context['file-upload'] as $item) {
                        if (isset($item['field']) &&
                            $item['field'] === $fieldName &&
                            isset($item['container']) &&
                            (boolean)$item['container'] === TRUE) {
                            $useContainer = TRUE;
                        }
                    }
                }

                if ($useContainer === TRUE) {
                    $data[$fieldName] = str_replace(array("\r\n", "\r", "\n"), "\r", $data[$fieldName]);
                    $meta = explode("\r", $data[$fieldName]);
                    $fileName = $meta[0];
                    $contaierData = $meta[1];

                    $tmpDir = ini_get('upload_tmp_dir');
                    if ($tmpDir === '') {
                        $tmpDir = sys_get_temp_dir();
                    }
                    $temp = 'IM_TEMP_' .
                        str_replace(DIRECTORY_SEPARATOR, '-', base64_encode(randomString(12))) .
                        '.jpg';
                    if (mb_substr($tmpDir, 1) === DIRECTORY_SEPARATOR) {
                        $tempPath = $tmpDir . $temp;
                    } else {
                        $tempPath = $tmpDir . DIRECTORY_SEPARATOR . $temp;
                    }
                    $fp = fopen($tempPath, 'w');
                    if ($fp !== false) {
                        $tempMeta = stream_get_meta_data($fp);
                        fwrite($fp, base64_decode($contaierData));
                        // INTER-Mediator doesn't support repeating fields now.
                        $this->fmData->{$layout}->uploadFile($tempMeta['uri'], $recId, $fieldName, NULL, $fileName);
                        fclose($fp);
                    }
                } else {
                    $originalfield = filter_input(INPUT_POST, 'field_0');
                    $value = filter_input(INPUT_POST, 'value_0');
                    $convVal = $this->formatter->formatterToDB(
                        $this->getFieldForFormatter($tableSourceName, $originalfield), $value);
                    if ($originalfield !== FALSE && $originalfield !== NULL) {
                        $data += array($originalfield => $convVal);
                    }
                    if (isset($data['recordId']) && !empty($recId)) {
                        unset($data['recordId']);
                    }

                    // for updating portal data
                    list($data, $portal) = $this->_getPortalDataForUpdating($data, $result);

                    $this->fmData->{$layout}->update($recId, $data, -1, $portal, $script);
                }
                $result = $this->fmData->{$layout}->getRecord($recId);
                if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        get_class($result) . ': '. $this->fmData->{$layout}->getDebugInfo()));
                    return false;
                }
                if ($this->fmData->errorCode() > 0) {
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        "FileMaker Data API reports error at edit action: table={$this->dbSettings->getEntityForUpdate()}, "
                        . "code={$this->fmData->errorCode()}, url={$this->fmData->{$layout}->getDebugInfo()}<hr>"));
                    return false;
                }
                $this->updatedRecord = $this->createRecordset($result);
                $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
                break;
            }
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
                    $this->fmData->{$layout}->setGlobalField(
                        array($condition['field'] => $condition['value'])
                    );
                }
            }
        }
        $script = NULL;
        if (isset($context['script'])) {
            foreach ($context['script'] as $condition) {
                if ($condition['db-operation'] == 'new' || $condition['db-operation'] == 'create') {
                    $script = $this->executeScripts($context['script']);
                }
            }
        }

        $layout = $this->dbSettings->getEntityForUpdate();
        $recId = $this->fmData->{$layout}->create($recordData, NULL, $script);
        $result = $this->fmData->{$layout}->getRecord($recId);
        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->errorMessage[] = get_class($result) . ': ' . $this->fmData->{$layout}->getDebugInfo();
            }
            return false;
        }

        $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
        if ($this->fmData->errorCode() > 0 && $this->fmData->errorCode() != 401) {
            $this->logger->setErrorMessage($this->stringWithoutCredential(
                "FileMaker Data API reports error at create action: code={$this->fmData->errorCode()}<hr>"));
            return false;
        }

        $this->notifyHandler->setQueriedPrimaryKeys(array($recId));
        $this->notifyHandler->setQueriedEntity($layout);

        $this->updatedRecord = $this->createRecordset($result);

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
                throw new \Exception("Invalid Operator.");
            }
            $condition += array($value['field'] => $value['value']);
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
                    $condition += array($authInfoField => '=' . $signedUser);
                    $hasFindParams = true;
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $condition += array($authInfoField => '=' . $belongGroups[0]);
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
                $this->errorMessage[] = get_class($result) . ': ' . $this->fmData->{$layout}->getDebugInfo();
            }
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
        if ($this->fmData->errorCode() > 0) {
            $this->errorMessage[] = "FileMaker Data API reports error at find action: code={$this->fmData->errorCode()}, url={$this->fmData->{$layout}->getDebugInfo()}<hr>";
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
                            $this->fmData->{$layout}->setGlobalField(
                                array($condition['field'] => $condition['value'])
                            );
                        }
                    }
                }
                $script = NULL;
                if (isset($context['script'])) {
                    foreach ($context['script'] as $condition) {
                        if ($condition['db-operation'] == 'delete') {
                            $script = $this->executeScripts($context['script']);
                        }
                    }
                }

                $this->notifyHandler->setQueriedEntity($layout);

                try {
                    $result = $this->fmData->{$layout}->delete($recId, $script);
                } catch (\Exception $e) {
                    if ($this->dbSettings->isDBNative()) {
                        $this->dbSettings->setRequireAuthentication(true);
                    } else {
                        $this->logger->setErrorMessage($this->stringWithoutCredential(
                            get_class($result) . ': '. $this->fmData->{$layout}->getDebugInfo()));
                    }
                    return false;
                }
                if ($this->fmData->errorCode() > 0) {
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        "FileMaker Data API reports error at delete action: code={$this->fmData->errorCode()}, url={$this->fmData->{$layout}->getDebugInfo()}<hr>"));
                    return false;
                }
                $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
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
        $recordSet = array();
        try {
            $result = $this->fmDataAuth->{$table}->query(array($conditions), NULL, 1, 100000000);
            foreach ($result as $record) {
                $oneRecord = array();
                foreach ($result->getFieldNames() as $key => $fieldName) {
                    $oneRecord[$fieldName] = $record->{$fieldName};
                }
                $recordSet[] = $oneRecord;
            }
        } catch (\Exception $e) {
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

    protected function _getPortalDataForUpdating($data, $result)
    {
        // for FileMaker Server 17
        $portal = NULL;
        $portalNames = $result->getPortalNames();
        if (count($portalNames) >= 1) {
            $portal = array();
            $portalRecord = array();
            foreach ($data as $fieldName => $value) {
                if (mb_strpos($fieldName, '::') !== false && mb_strpos($fieldName, '.') !== false) {
                    unset($data[$fieldName]);
                    $dotPos = mb_strpos($fieldName, '::');
                    $tableOccurrence = mb_substr($fieldName, 0, $dotPos);
                    $dotPos = mb_strpos($fieldName, '.');
                    $fullyQualifiedFieldName = mb_substr($fieldName, 0, $dotPos);
                    $relatedRecId = mb_substr($fieldName, $dotPos + 1, mb_strlen($fieldName));
                    $portalRecord[$fullyQualifiedFieldName] = $value;
                    if (!isset($portalRecord['recordId'])) {
                        $portalRecord['recordId'] = $relatedRecId;
                    }
                }
            }
            if (count($portalRecord) > 0) {
                $portal[$tableOccurrence] = array($portalRecord);
            } else {
                $portal = NULL;
            }
        }
        if ($data === array()) {
            $data = NULL;
        }

        return array($data, $portal);
    }

    public function deleteForTest($table, $conditions = null)
    {
        // TODO: Implement deleteForTest() method.
    }
}
