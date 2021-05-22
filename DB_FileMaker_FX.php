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
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Spec_Handler_FileMaker_FX.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Auth_Common.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Notification_Common.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Auth_Handler_FileMaker_FX.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DB_Support' . DIRECTORY_SEPARATOR . 'DB_Notification_Handler_FileMaker_FX.php');

class DB_FileMaker_FX extends DB_UseSharedObjects implements DB_Interface
{
    public $fx = null;
    public $fxAuth = null;
    public $fxAlt = null;
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
        $this->logger->setErrorMessage("Query Error: [{$str}] Error Code={$this->fx->lastErrorCode}");
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

    public function setupFXforAuth($layoutName, $recordCount)
    {
        $this->fx = null;
        $this->fxAuth = $this->setupFX_Impl($layoutName, $recordCount,
            $this->dbSettings->getDbSpecUser(), $this->dbSettings->getDbSpecPassword());
    }

    public function setupFXforDB($layoutName, $recordCount)
    {
        $this->fxAuth = null;
        $this->fx = $this->setupFX_Impl($layoutName, $recordCount,
            $this->dbSettings->getAccessUser(), $this->dbSettings->getAccessPassword());
    }

    public function setupFXforDB_Alt($layoutName, $recordCount)
    {
        $this->fxAlt = $this->setupFX_Impl($layoutName, $recordCount,
            $this->dbSettings->getAccessUser(), $this->dbSettings->getAccessPassword());
    }

    private function setupFX_Impl($layoutName, $recordCount, $user, $password)
    {
        $fxPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'FX';
        $fxFiles = array(
            'FX.php',
            'lib/datasource_classes' . DIRECTORY_SEPARATOR . 'RetrieveFM7Data.class.php',
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

    public function setupHandlers($dsn = false)
    {
        $this->authHandler = new DB_Auth_Handler_FileMaker_FX($this);
        $this->notifyHandler = new DB_Notification_Handler_FileMaker_FX($this);
        $this->specHandler = new DB_Spec_Handler_FileMaker_FX();
    }

    public function stringWithoutCredential($str)
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
        } else {
            return array($field, $value);
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
        if (count($this->dbSettings->getForeignFieldAndValue()) > 0 || isset($context['relation'])) {
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
        $portalParentKeyField = NULL;

        $hasFindParams = false;
        if (isset($context['query'])) {
            foreach ($context['query'] as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $this->fx->SetLogicalOR();
                    $useOrOperation = true;
                } else {
                    if (isset($condition['operator'])) {
                        $condition = $this->normalizedCondition($condition);
                        if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
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
            if (isset($parentTable['paging']) && $parentTable['paging'] === true) {
                $this->fx->FMSkipRecords($this->dbSettings->getStart());
                $portalParentKeyField = isset($parentTable['key']) ? $parentTable['key'] : '';
            }
            if (isset($parentTable['query'])) {
                foreach ($parentTable['query'] as $condition) {
                    if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                        $this->fx->SetLogicalOR();
                        $useOrOperation = true;
                    } else {
                        if (isset($condition['operator'])) {
                            $condition = $this->normalizedCondition($condition);
                            if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
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
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['field']}/{$condition['operator']}");
                    }

                    $tableInfo = $this->dbSettings->getDataSourceTargetArray();
                    $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : $this->specHandler->getDefaultKey();
                    if ($condition['field'] == $primaryKey && isset($condition['value'])) {
                        $this->notifyHandler->setQueriedPrimaryKeys(array($condition['value']));
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
                    if ($condition['field'] === $primaryKey) {
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
                        if (!$usePortal) {
                            if (!$this->specHandler->isPossibleOperator($foreignOperator)) {
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
            $authInfoField = $this->authHandler->getFieldForAuthorization("read");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("read");
            if (strlen($authInfoField) > 0 && $this->_field_exists($authInfoField) === FALSE) {
                $authFailure = TRUE;
            }
            if ($authInfoTarget == 'field-user' && $authFailure === FALSE) {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    if ($useOrOperation) {
                        throw new Exception("Condition Incompatible.: The authorization for each record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                    }
                    $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    $this->fx->AddDBParam($authInfoField, $signedUser, 'eq');
                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $authInfoField, '=' . $signedUser, 'eq');
                    $hasFindParams = true;

                    $queryValues[] = 'q' . $qNum;
                    $qNum++;
                    $neqConditions[] = FALSE;
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                    $authFailure = true;
                } else {
                    if ($useOrOperation) {
                        throw new Exception("Condition Incompatible.: The authorization for each record and OR operation can't set both on the query. This is the limitation of the Custom Web of FileMaker Server.");
                    }
                    $this->fx->AddDBParam($authInfoField, $belongGroups[0], 'eq');
                    $searchConditions[] = $this->setSearchConditionsForCompoundFound(
                        $authInfoField, '=' . $belongGroups[0], 'eq');
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
//                    $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
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
                $this->softDeleteField, $this->softDeleteValue, 'neq');
            $hasFindParams = true;

            $queryValues[] = 'q' . $qNum;
            $qNum++;
            $neqConditions[] = TRUE;
        }

        if (isset($context['sort'])) {
            foreach ($context['sort'] as $condition) {
                if (isset($condition['direction'])) {
                    if (!$this->specHandler->isPossibleOrderSpecifier($condition['direction'])) {
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
                        if (!$this->specHandler->isPossibleOrderSpecifier($condition['direction'])) {
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
                if (!$this->specHandler->isPossibleOrderSpecifier($condition['direction'])) {
                    throw new Exception("Invalid Sort Specifier.");
                }
                $this->fx->AddSortParam($condition['field'], $this->_adjustSortDirection($condition['direction']));
            }
        }
        if (isset($context['global'])) {
            foreach ($context['global'] as $condition) {
                if (isset($condition['db-operation']) && in_array($condition['db-operation'], array('load', 'read'))) {
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
                if (isset($condition['db-operation']) && in_array($condition['db-operation'], array('load', 'read'))) {
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
                if (isset($condition['db-operation']) && in_array($condition['db-operation'], array('load', 'read'))) {
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
                    $currentSearch .= '&-q' . $qNum . '=' . urlencode($fieldName)
                        . '&-q' . $qNum . '.value=' . urlencode($fieldValue);
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

        $this->notifyHandler->setQueriedEntity($this->fx->layout);
        $this->notifyHandler->setQueriedCondition($queryString);

        $recordArray = array();
        $this->notifyHandler->setQueriedPrimaryKeys(array());
        $keyField = isset($context['key']) ? $context['key'] : $this->specHandler->getDefaultKey();
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
                        $dataArray = array($this->specHandler->getDefaultKey() => $record['@attributes']['record-id']);
                    }
                    if ($keyField == $this->specHandler->getDefaultKey()) {
                        $this->notifyHandler->addQueriedPrimaryKeys($record['@attributes']['record-id']);
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
                            if ($fieldName == $keyField && $keyField != $this->specHandler->getDefaultKey()) {
                                $this->notifyHandler->addQueriedPrimaryKeys($field['data']);
                            }
                        }
                        if (!$usePortal || ($usePortal === true && $fieldName === $portalParentKeyField && !empty($portalParentKeyField))) {
                            if (is_array($fieldValue) && count($fieldValue) === 0){
                                $dataArray += array($fieldName => '');
                            } else {
                                $dataArray += array($fieldName => $fieldValue);
                            }
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
                                                $relatedFieldName =>
                                                    $relatedFieldValue === array() ? '' : $relatedFieldValue
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
        $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : $this->specHandler->getDefaultKey();

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
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
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
                if (!$this->specHandler->isPossibleOperator($value['operator'])) {
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
            $authInfoField = $this->authHandler->getFieldForAuthorization("update");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("update");
            if (strlen($authInfoField) > 0 && $this->_field_exists($authInfoField) === FALSE) {
                $authFailure = TRUE;
            }
            if ($authInfoTarget == 'field-user' && $authFailure === FALSE) {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $authInfoField, $signedUser) === TRUE) {
                        $this->fx->AddDBParam($authInfoField, '=' . $signedUser, 'eq');
                    }
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0 || count($belongGroups) == 0) {
                    $authFailure = true;
                } else {
                    if ($cwpkit->_checkDuplicatedFXCondition($fxUtility->CreateCurrentSearch(), $authInfoField, $belongGroups[0]) === TRUE) {
                        $this->fx->AddDBParam($authInfoField, '=' . $belongGroups[0], 'eq');
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
            $this->notifyHandler->setQueriedPrimaryKeys(array());
            $keyField = isset($context['key']) ? $context['key'] : $this->specHandler->getDefaultKey();
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));
                if ($keyField == $this->specHandler->getDefaultKey()) {
                    $this->notifyHandler->addQueriedPrimaryKeys($recId);
                } else {
                    $this->notifyHandler->addQueriedPrimaryKeys($row[$keyField][0]);
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

                $this->notifyHandler->setQueriedEntity($this->fx->layout);

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

        $keyFieldName = isset($context['key']) ? $context['key'] : $this->specHandler->getDefaultKey();

        $recordData = array();

        $this->setupFXforDB($this->dbSettings->getEntityForUpdate(), 1);
        $requiredFields = $this->dbSettings->getFieldsRequired();
        $countFields = count($requiredFields);
        $fieldValues = $this->dbSettings->getValue();
        for ($i = 0; $i < $countFields; $i++) {
            $field = $requiredFields[$i];
            $value = $fieldValues[$i];
            if ($field != $keyFieldName) {
                // for handling checkbox on Post Only mode
                if (isset($recordData[$field]) && !empty($recordData[$field])) {
                    $value = $recordData[$field] . "\r" . $value;
                    unset($recordData[$field]);
                }
                $recordData += array(
                    $field =>
                    $this->formatter->formatterToDB(
                        "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}",
                        $this->unifyCRLF((is_array($value)) ? implode("\r", $value) : $value))
                );

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
            $authFailure = FALSE;
            $authInfoField = $this->authHandler->getFieldForAuthorization("create");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("create");
            if (strlen($authInfoField) > 0 && $this->_field_exists($authInfoField) === FALSE) {
                $authFailure = TRUE;
            }
            if ($authInfoTarget == 'field-user' && $authFailure === FALSE) {
                $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                $this->fx->AddDBParam($authInfoField,
                    strlen($this->dbSettings->getCurrentUser()) == 0 ? randomString(10) : $signedUser);
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                $this->fx->AddDBParam($authInfoField,
                    strlen($belongGroups[0]) == 0 ? randomString(10) : $belongGroups[0]);
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
            if ($keyFieldName == $this->specHandler->getDefaultKey()) {
                $recId = substr($key, 0, strpos($key, '.'));
                $keyValue = $recId;
            } else {
                $keyValue = $row[$keyFieldName][0];
            }
        }

        $this->notifyHandler->setQueriedPrimaryKeys(array($keyValue));
        $this->notifyHandler->setQueriedEntity($this->fx->layout);

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
            if (!$this->specHandler->isPossibleOperator($value['operator'])) {
                throw new Exception("Invalid Operator.");
            }
            $this->fx->AddDBParam($value['field'], $value['value'], $value['operator']);
        }
        if (isset($context['authentication'])
            && (isset($context['authentication']['all'])
                || isset($context['authentication']['delete']))
        ) {
            $authFailure = FALSE;
            $authInfoField = $this->authHandler->getFieldForAuthorization("delete");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("delete");
            if (strlen($authInfoField) > 0 && $this->_field_exists($authInfoField) === FALSE) {
                $authFailure = TRUE;
            }
            if ($authInfoTarget == 'field-user' && $authFailure === FALSE) {
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
                    $this->fx->AddDBParam($authInfoField, '=' . $signedUser, 'eq');
                    $hasFindParams = true;
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($this->dbSettings->getCurrentUser());
                if (strlen($this->dbSettings->getCurrentUser()) == 0) {
                    $authFailure = true;
                } else {
                    $this->fx->AddDBParam($authInfoField, '=' . $belongGroups[0], 'eq');
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
            $keyField = isset($context['key']) ? $context['key'] : $this->specHandler->getDefaultKey();
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));
                if ($keyField == $this->specHandler->getDefaultKey()) {
                    $this->notifyHandler->addQueriedPrimaryKeys($recId);
                } else {
                    $this->notifyHandler->addQueriedPrimaryKeys($row[$keyField][0]);
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

                $this->notifyHandler->setQueriedEntity($this->fx->layout);

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

    protected function _adjustSortDirection($direction)
    {
        if (strtoupper($direction) == 'ASC') {
            $direction = 'ascend';
        } else if (strtoupper($direction) == 'DESC') {
            $direction = 'descend';
        }

        return $direction;
    }

    protected function _field_exists($fieldName) {
        $config = array(
            'urlScheme' => $this->fx->urlScheme,
            'dataServer' => $this->fx->dataServer,
            'dataPort' => $this->fx->dataPort,
            'DBUser' => $this->dbSettings->getAccessUser(),
            'DBPassword' => $this->dbSettings->getAccessPassword(),
        );
        $cwpkit = new CWPKit($config);

        $queryString = '-db=' . urlencode($this->fx->database);
        $queryString .= '&-lay=' . urlencode($this->fx->layout);
        $queryString .= '&-view';

        $parsedData = $cwpkit->query($queryString);
        $data = json_decode(json_encode($parsedData), true);

        foreach($data['metadata']['field-definition'] as $field) {
            if ($field['@attributes']['name'] === $fieldName) {
                return true;
            }
        }

        return false;
    }

    public function deleteForTest($table, $conditions = null)
    {
        // TODO: Implement deleteForTest() method.
    }
}
