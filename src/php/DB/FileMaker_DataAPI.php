<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (https://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (https://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       https://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace INTERMediator\DB;

use Exception;
use INTERMediator\FileMakerServer\RESTAPI\FMDataAPI;
use INTERMediator\FileMakerServer\RESTAPI\Supporting\FileMakerRelation;
use INTERMediator\IMUtil;

/**
 *
 */
class FileMaker_DataAPI extends DBClass
{
    /**
     * @var FMDataAPI|null
     */
    public ?FMDataAPI $fmData = null;     // FMDataAPI class's instance
    /**
     * @var FMDataAPI|null
     */
    public ?FMDataAPI $fmDataAuth = null; // FMDataAPI class's instance
    /**
     * @var FMDataAPI|null
     */
    public ?FMDataAPI $fmDataAlt = null;  // FMDataAPI class's instance
    /**
     * @var string|null
     */
    private ?string $targetLayout = null;

    /**
     * @var int
     */
    private int $mainTableCount = 0;
    /**
     * @var int
     */
    private int $mainTableTotalCount = 0;
    /**
     * @var array|null
     */
    private ?array $fieldInfo = null;
    /**
     * @var array|null
     */
    private ?array $updatedRecord = null;
    /**
     * @var string|null
     */
    private ?string $softDeleteField = null;
    /**
     * @var string|null
     */
    private ?string $softDeleteValue = null;
    /**
     * @var bool
     */
    private bool $useSetDataToUpdatedRecord = false;

    /**
     * @return FMDataAPI
     */
    public function getFMDataInstance(): FMDataAPI
    {
        return $this->fmData;
    }

    /**
     * @param string $str
     * @return void
     */
    public function errorMessageStore(string $str)
    {
        $this->logger->setErrorMessage("[FileMaker_DataAPI] Error: {$str}]");
    }

    /**
     * @return bool
     */
    public function setupConnection(): bool
    {
        return true;
    }

    /**
     * @param bool $value
     * @return void
     */
    public function requireUpdatedRecord(bool $value): void
    {
        // always can get the new record for FileMaker Server.
    }

    /**
     * @return array|null
     */
    public function getUpdatedRecord(): ?array
    {
        return $this->updatedRecord;
    }

    /**
     * @return array|null
     */
    public function updatedRecord(): ?array
    {
        return $this->updatedRecord;
    }

    /**
     * @param array $record
     * @return void
     */
    public function setUpdatedRecord(array $record): void
    {
        $this->updatedRecord = $record;
    }

    /**
     * @param string $field
     * @param string $value
     * @param int $index
     * @return void
     */
    public function setDataToUpdatedRecord(string $field, string $value, int $index = 0): void
    {
        $this->updatedRecord[$index][$field] = $value;
        $this->useSetDataToUpdatedRecord = true;
    }

    /**
     * @return bool
     */
    public function getUseSetDataToUpdatedRecord(): bool
    {
        return $this->useSetDataToUpdatedRecord;
    }

    /**
     * @return void
     */
    public function clearUseSetDataToUpdatedRecord(): void
    {
        $this->useSetDataToUpdatedRecord = false;
    }

    /**
     * @param string $field
     * @param string $value
     * @return void
     */
    public function softDeleteActivate(string $field, string $value): void
    {
        $this->softDeleteField = $field;
        $this->softDeleteValue = $value;
    }

    /**
     * @param string $layoutName
     * @param int $recordCount
     * @return void
     */
    public function setupFMDataAPIforAuth(string $layoutName, int $recordCount): void
    {
        $this->fmData = null;
        $this->fmDataAuth = $this->setupFMDataAPI_Impl($layoutName, $recordCount,
            $this->dbSettings->getDbSpecUser(), $this->dbSettings->getDbSpecPassword());
    }

    /**
     * @param string $layoutName
     * @param int $recordCount
     * @return void
     */
    public function setupFMDataAPIforDB(string $layoutName, int $recordCount): void
    {
        $this->fmDataAuth = null;
        $this->fmData = $this->setupFMDataAPI_Impl($layoutName, $recordCount,
            $this->dbSettings->getAccessUser(), $this->dbSettings->getAccessPassword());
    }

    /**
     * @param string $layoutName
     * @param int $recordCount
     * @return void
     */
    public function setupFMDataAPIforDB_Alt(string $layoutName, int $recordCount): void
    {
        $this->fmDataAlt = $this->setupFMDataAPI_Impl($layoutName, $recordCount,
            $this->dbSettings->getAccessUser(), $this->dbSettings->getAccessPassword());
    }

    /**
     * @param string $layoutName
     * @param int $recordCount
     * @param string $user
     * @param string $password
     * @return FMDataAPI
     */
    private function setupFMDataAPI_Impl(string $layoutName, int $recordCount, string $user, string $password): FMDataAPI
    {
        $this->targetLayout = $layoutName;
        if (IMUtil::isRunAsWebApp()) {
            session_start();
        }
        if (in_array($layoutName, array($this->dbSettings->getUserTable(), $this->dbSettings->getHashTable()))) {
            $token = $_SESSION['X-FM-Data-Access-Token-Auth'] ?? '';
        } else {
            $token = $_SESSION['X-FM-Data-Access-Token'] ?? '';
        }
        try {
            if ($token === '') {
                throw new Exception();
            }
            $fmDataObj = new FMDataAPI(
                $this->dbSettings->getDbSpecDatabase(),
                '',
                '',
                $this->dbSettings->getDbSpecServer(),
                $this->dbSettings->getDbSpecPort(),
                $this->dbSettings->getDbSpecProtocol()
            );
            $fmDataObj->setSessionToken($token);
            $fmDataObj->setCertValidating($this->dbSettings->getCertVerifying());
            $fmDataObj->{$layoutName}->startCommunication();
            $fmDataObj->{$layoutName}->query(NULL, NULL, -1, 1);
        } catch (Exception $e) {
            $fmDataObj = new FMDataAPI(
                $this->dbSettings->getDbSpecDatabase(),
                $user,
                $password,
                $this->dbSettings->getDbSpecServer(),
                $this->dbSettings->getDbSpecPort(),
                $this->dbSettings->getDbSpecProtocol()
            );
            $fmDataObj->setCertValidating($this->dbSettings->getCertVerifying());
            try {
                $fmDataObj->{$layoutName}->startCommunication();
            } catch (Exception $e) {
            }
        }
        return $fmDataObj;
    }

    /**
     * @param string|null $dsn
     * @return void
     */
    public function setupHandlers(?string $dsn = null): void
    {
        $this->authHandler = new Support\DB_Auth_Handler_FileMaker_DataAPI($this);
        $this->notifyHandler = new Support\DB_Notification_Handler_FileMaker_DataAPI($this);
        $this->specHandler = new Support\DB_Spec_Handler_FileMaker_DataAPI();
    }

    /**
     * @return void
     */
    public function closeDBOperation(): void
    {
        if ($this->fmData) {
            $this->fmData->endCommunication();
        }
        if ($this->fmDataAuth) {
            $this->fmDataAuth->endCommunication();
        }
        if ($this->fmDataAlt) {
            $this->fmDataAlt->endCommunication();
        }
    }

    /**
     * @param ?string $str
     * @return string
     */
    public function stringWithoutCredential(?string $str): string
    {
        if (is_null($this->fmData)) {
            $str = str_replace($this->dbSettings->getDbSpecUser(), "********", $str ?? $str);
            return str_replace($this->dbSettings->getDbSpecPassword(), "********", $str);
        } else {
            $str = str_replace($this->dbSettings->getAccessUser(), "********", $str ?? "");
            return str_replace($this->dbSettings->getAccessPassword(), "********", $str);
        }
    }

    /**
     * @param string|null $str
     * @return string
     */
    private function stringReturnOnly(?string $str): string
    {
        return str_replace("\n\r", "\r", str_replace("\n", "\r", $str ?? ""));
    }

    /**
     * @param string|null $str
     * @return string
     */
    private function unifyCRLF(?string $str): string
    {
        return str_replace("\n", "\r", str_replace("\r\n", "\r", $str ?? ""));
    }


    /**
     * @param string $field
     * @param string $value
     * @param string|null $operator
     * @return ?array
     */
    private function setSearchConditionsForCompoundFound(string $field, string $value, ?string $operator = NULL): ?array
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
        return null;
    }

    /**
     * @param array|null $scriptContext
     * @return array|string[]|null
     */
    private function executeScripts(?array $scriptContext): ?array
    {
        $script = array();
        if (is_array($scriptContext)) {
            foreach ($scriptContext as $condition) {
                if (isset($condition['situation']) && isset($condition['definition'])) {
                    $scriptName = str_replace('&', '', $condition['definition']);
                    $parameter = '';
                    if (!empty($condition['parameter'])) {
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

    /**
     * @param string $dataSourceName
     * @return array|null
     */
    public function getFieldInfo(string $dataSourceName): ?array
    {
        return $this->fieldInfo;
    }

    /**
     * @param string $dataSourceName
     * @return array|false
     * @throws Exception
     */
    public function getSchema(string $dataSourceName)
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
                    $this->stringWithoutCredential(get_class($result) . ': ' . $this->fmData->{$layout}->getDebugInfo()));
            }
            return false;
        }

        $returnArray = array();
        foreach ($result->getFieldNames() as $fieldName) {
            $returnArray[$fieldName] = '';
        }

        return $returnArray;
    }

    /**
     * @return array|array[]|null
     * @throws Exception
     */
    public function readFromDB(): ?array
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

        $limitParam = $this->getLimitParam($context);
        $this->setupFMDataAPIforDB($this->dbSettings->getEntityForRetrieve(), $limitParam);
        $layout = $this->targetLayout;
        $skip = (isset($context['paging']) and $context['paging'] === true) ? $this->dbSettings->getStart() : 0;

        $searchConditions = array();
        $neqConditions = array();

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
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $condition['field'], $condition['value'], $condition['operator']);
                    } else {
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $condition['field'], $condition['value']);
                    }

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
                } else if ($condition['field'] == '__operation__' && strpos($condition['operator'], 'block/') === 0) {
                    // just ignore it
                } else {
                    $condition = $this->normalizedCondition($condition);
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['field']}/{$condition['operator']}");
                    }

                    $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                    $primaryKey = $tableInfo['key'] ?? $this->specHandler->getDefaultKey();
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
                        $foreignOperator = $relDef['operator'] ?? 'eq';
                        $formattedValue = $this->formatter->formatterToDB(
                            "{$tableName}{$this->dbSettings->getSeparator()}{$foreignField}", $foreignValue);
                        if (!$this->specHandler->isPossibleOperator($foreignOperator)) {
                            throw new Exception("Invalid Operator.: {$relDef['operator']}");
                        }
                        if ($useOrOperation) {
                            throw new Exception("Condition Incompatible.: The OR operation and foreign key can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                        }
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $foreignField, $formattedValue, $foreignOperator);

                        if ($foreignOperator === 'neq') {
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
                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $authInfoField, '=' . $signedUser, 'eq');
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
                        $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                            $authInfoField, '=' . $belongGroups[0], 'eq');
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
            $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                $this->softDeleteField, $this->softDeleteValue, 'neq');
            $neqConditions[] = TRUE;
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
                    $sortDirection = $request['sortkey' . $orderNum . 'direction'] ?? null;
                    if ($sort === NULL && $sortDirection) {
                        $sort = array(array($val, $sortDirection));
                    }
                }
            }
        }

        $portal = [];
        $portalNames = [];
        $recordId = NULL;
        $result = NULL;
        try {
            if (isset($context['portals']) && is_array($context['portals'])) {
                $portal = $context['portals'];
            } else {
                $result = $this->fmData->{$layout}->getMetadata();
                $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
                // Get the portal array from the metadata of the layout.
                if (!is_null($result)) {
                    if ($result->portalMetaData) {
                        foreach ($result->portalMetaData as $key => $portalName) {
                            $portal[] = $key;
                        }
                    }
                }
            }

            $result = null;
            if ($conditions && count($conditions) === 1 && isset($conditions[0]['recordId'])) {
                $recordId = str_replace('=', '', $conditions[0]['recordId']);
                if (is_numeric($recordId)) {
                    $conditions[0]['recordId'] = $recordId;
                    $result = $this->fmData->{$layout}->getRecord($recordId);
                }
                if (is_null($result)) {
                    $this->mainTableCount = 0;
                    $this->mainTableTotalCount = 0;
                } else {
                    $this->mainTableCount = 1;
                    $this->mainTableTotalCount = 1;
                }
            } else {
                $result = $this->fmData->{$layout}->query(
                    $conditions,
                    $sort,
                    $skip + 1,
                    $limitParam,
                    array_unique($portal),
                    $script
                );
                $this->mainTableCount = intval($this->fmData->getFoundCount());
                $this->mainTableTotalCount = intval($this->fmData->getTotalCount());
            }
            $this->notifyHandler->setQueriedEntity($layout);
            $this->notifyHandler->setQueriedCondition("/fmi/rest/api/find/{$this->dbSettings->getDbSpecDatabase()}/{$layout}" . ($recordId ? "/{$recordId}" : ""));
            $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
        } catch (Exception $e) {
            // Don't output error messages if no (related) records
            if (strpos($e->getMessage(), 'Error Code: 101, Error Message: Record is missing') === false &&
                strpos($e->getMessage(), 'Error Code: 401, Error Message: No records match the request') === false) {
                $this->logger->setErrorMessage("Exception:[6]{$e->getMessage()}");
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
                foreach ($result->getFieldNames() as $fieldName) {
                    $dataArray = $dataArray + array(
                            $fieldName => $this->formatter->formatterFromDB(
                                $this->getFieldForFormatter($tableName, $fieldName), strval($record->{$fieldName})
                            )
                        );
                }

                $relatedsetArray = array();
                if (count($portalNames) >= 1) {
                    $relatedArray = array();
                    foreach ($portalNames as $portalName) {
                        foreach ($result->{$portalName} as $portalRecord) {
                            $recId = $portalRecord->getRecordId();
                            foreach ($result->{$portalName}->getFieldNames() as $relatedFieldName) {
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
                    $recordArray[] = $dataArray;
                }
                if (intval($result->count()) == 1) {
                    break;
                }
            }
        }

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

    /**
     * @param FileMakerRelation|null $resultData
     * @return array
     */
    private function createRecordset(?FileMakerRelation $resultData): array
    {
        $returnArray = array();
        $tableName = $this->dbSettings->getEntityForRetrieve();

        foreach ($resultData as $oneRecord) {
            $oneRecordArray = array();

            $recId = $resultData->getRecordId();
            $oneRecordArray[$this->specHandler->getDefaultKey()] = $recId;

            foreach ($resultData->getFieldNames() as $field) {
                $oneRecordArray[$field] = $this->formatter->formatterFromDB(
                    "{$tableName}{$this->dbSettings->getSeparator()}$field", $oneRecord->$field);
                foreach ($resultData->getPortalNames() as $portalName) {
                    foreach ($resultData->{$portalName} as $relatedRecord) {
                        $oneRecordArray[$portalName][$relatedRecord->getRecordId()] = array();
                        foreach ($resultData->{$portalName}->getFieldNames() as $relatedField) {
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

    /**
     * @return int
     */
    public function countQueryResult(): int
    {
        return $this->mainTableCount;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->mainTableTotalCount;
    }

    /**
     * @param bool $bypassAuth
     * @return bool
     * @throws Exception
     */
    public function updateDB(bool $bypassAuth): bool
    {
        $this->fieldInfo = null;
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
        } else {
            $layout = $this->dbSettings->getEntityForUpdate();
        }
        $this->setupFMDataAPIforDB($layout, 1);
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $primaryKey = $tableInfo['key'] ?? $this->specHandler->getDefaultKey();

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
                    $data += array($condition['field'] => $convertedValue);
                }
            }
        }

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            if (!$this->dbSettings->getPrimaryKeyOnly() || $value['field'] == $primaryKey) {
                $value = $this->normalizedCondition($value);
                if (!$this->specHandler->isPossibleOperator($value['operator'])) {
                    throw new Exception("Invalid Operator.: {$value['operator']}");
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
                if (!$this->dbSettings->isDBNative()) {
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
        if (isset($condition[0]['recordId']) && count($condition) === 1) {
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

        if (get_class((object)$result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->logger->setErrorMessage(
                    $this->stringWithoutCredential(
                        get_class((object)$result) . ': ' . $this->fmData->{$layout}->getDebugInfo()));
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
            $keyField = $context['key'] ?? $this->specHandler->getDefaultKey();
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

                    if (strpos($value, "[increment]") === 0) {
                        $value = $record->$originalfield + intval(substr($value, 11));
                    } else if (strpos($value, "[decrement]") === 0) {
                        $value = $record->$originalfield - intval(substr($value, 11));
                    }

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
                // $this->fmData->{$layout}->keepAuth = true;

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
                    $data[$fieldName] = str_replace(array("\r\n", "\r", "\n"), "\r", $data[$fieldName] ?? "");
                    $meta = explode("\r", $data[$fieldName]);
                    $fileName = $meta[0];
                    $contaierData = $meta[1];

                    $tmpDir = ini_get('upload_tmp_dir');
                    if ($tmpDir === '') {
                        $tmpDir = sys_get_temp_dir();
                    }
                    $temp = 'IM_TEMP_' . str_replace(DIRECTORY_SEPARATOR, '-',
                            base64_encode(IMUtil::randomString(12)) ?? "") . '.jpg';
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

                    $this->fmData->{$layout}->update($recId, $data, -1, NULL, $script);
                }
                $result = $this->fmData->{$layout}->getRecord($recId);
                if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
                    $this->logger->setErrorMessage($this->stringWithoutCredential(
                        get_class($result) . ': ' . $this->fmData->{$layout}->getDebugInfo()));
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

    /**
     * @param bool $isReplace
     * @return string|null
     * @throws Exception
     */
    public function createInDB(bool $isReplace = false): ?string
    {
        $this->fieldInfo = null;

        $context = $this->dbSettings->getDataSourceTargetArray();

        if (isset($context['relation'])) {
            foreach ($context['relation'] as $relDef) {
                if (isset($relDef['portal']) && $relDef['portal']) {
                    $context['paging'] = true;
                }
            }
        }

        $keyFieldName = $context['key'] ?? $this->specHandler->getDefaultKey();

        $recordData = array();

        $this->setupFMDataAPIforDB($this->dbSettings->getEntityForUpdate(), 1);
        $requiredFields = $this->dbSettings->getFieldsRequired();
        $countFields = count($requiredFields);
        $fieldValues = $this->dbSettings->getValue();
        for ($i = 0; $i < $countFields; $i++) {
            $field = $requiredFields[$i];
            $value = $fieldValues[$i];
            if ($field != $keyFieldName) {
                if (isset($recordData[$field])) {
                    // for handling checkbox on Post Only mode
                    $value = $recordData[$field] . "\r" . $value;
                    unset($recordData[$field]);
                }
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
        if (isset($context['authentication'])
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
                        strlen($this->dbSettings->getCurrentUser()) == 0 ? IMUtil::randomString(10) : $signedUser
                );
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                $recordData += array(
                    $authInfoField =>
                        strlen($belongGroups[0]) == 0 ? IMUtil::randomString(10) : $belongGroups[0]
                );
            } else {
                if (!$this->dbSettings->isDBNative()) {
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
        $layout = $this->dbSettings->getEntityForUpdate();
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

        $recId = $this->fmData->{$layout}->create($recordData, NULL, $script);
        $result = $this->fmData->{$layout}->getRecord($recId);
        if (get_class($result) !== 'INTERMediator\\FileMakerServer\\RESTAPI\\Supporting\\FileMakerRelation') {
            if ($this->dbSettings->isDBNative()) {
                $this->dbSettings->setRequireAuthentication(true);
            } else {
                $this->errorMessageStore(get_class($result) . ": Code={$this->fmData->errorCode()}: " . $this->fmData->{$layout}->getDebugInfo());
            }
            return null;
        }

        $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
        if ($this->fmData->errorCode() > 0 && $this->fmData->errorCode() != 401) {
            $this->logger->setErrorMessage($this->stringWithoutCredential(
                "FileMaker Data API reports error at create action: code={$this->fmData->errorCode()}<hr>"));
            return null;
        }

        $this->notifyHandler->setQueriedPrimaryKeys(array($recId));
        $this->notifyHandler->setQueriedEntity($layout);

        $this->updatedRecord = $this->createRecordset($result);

        return $recId;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function deleteFromDB(): bool
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
        } else {
            $layout = $this->dbSettings->getEntityForUpdate();
        }
        $this->setupFMDataAPIforDB($layout, 10000000);

        foreach ($this->dbSettings->getExtraCriteria() as $value) {
            $value = $this->normalizedCondition($value);
            if (!$this->specHandler->isPossibleOperator($value['operator'])) {
                throw new Exception("Invalid Operator.");
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
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $condition += array($authInfoField => '=' . $belongGroups[0]);
                }
            } else {
                if (!$this->dbSettings->isDBNative()) {
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
                $this->errorMessageStore(get_class($result) . ": Code={$this->fmData->errorCode()}: " . $this->fmData->{$layout}->getDebugInfo());
            }
            return false;
        }
        $this->logger->setDebugMessage($this->stringWithoutCredential($this->fmData->{$layout}->getDebugInfo()));
        if ($this->fmData->errorCode() > 0) {
            $this->errorMessageStore("FileMaker Data API reports error at find action: code={$this->fmData->errorCode()}, url={$this->fmData->{$layout}->getDebugInfo()}");
            return false;
        }
        if ($result->count() > 0) {
            $keyField = $context['key'] ?? $this->specHandler->getDefaultKey();
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
                } catch (Exception $e) {
                    if ($this->dbSettings->isDBNative()) {
                        $this->dbSettings->setRequireAuthentication(true);
                    } else {
                        $this->logger->setErrorMessage($this->stringWithoutCredential(
                            get_class($result) . ': ' . $this->fmData->{$layout}->getDebugInfo()));
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

    /**
     * @return string|null
     */
    public function copyInDB(): ?string
    {
        $this->errorMessageStore("Copy operation is not implemented so far.");
        return null;
    }

    /**
     * @param string $entity
     * @param string $field
     * @return string
     */
    private function getFieldForFormatter(string $entity, string $field): string
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

    /**
     * @param array $condition
     * @return array
     */
    public function normalizedCondition(array $condition): array
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

    /**
     * @param string $table
     * @param array|null $conditions
     * @return array|null
     */
    public function queryForTest(string $table, ?array $conditions = null): ?array
    {
        if ($table == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return null;
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
        } catch (Exception $e) {
            return null;
        }
        return $recordSet;
    }

    /**
     * @param string $direction
     * @return string
     */
    protected function _adjustSortDirection(string $direction): string
    {
        if (strtoupper($direction) == 'ASC') {
            $direction = 'ascend';
        } else if (strtoupper($direction) == 'DESC') {
            $direction = 'descend';
        }

        return $direction;
    }

    /**
     * @param array $data
     * @param FileMakerRelation $result
     * @return array
     */
    protected function _getPortalDataForUpdating(array $data, FileMakerRelation $result): array
    {
        // for FileMaker Server 17
        $portal = NULL;
        $tableOccurrence = null;
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

    /**
     * @param string $table
     * @param array|null $conditions
     * @return bool
     */
    public function deleteForTest(string $table, ?array $conditions = null): bool
    {
        return false;
    }

    /*
* Transaction
*/
    /**
     * @return bool
     */
    public function hasTransaction(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return false;
    }

    /**
     * @return void
     */
    public function beginTransaction(): void
    {
    }

    /**
     * @return void
     */
    public function commitTransaction(): void
    {
    }

    /**
     * @return void
     */
    public function rollbackTransaction(): void
    {
    }

}
