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

namespace INTERMediator\DB;

use DateTime;
use DateTimeZone;
use Exception;
use INTERMediator\IMUtil;
use PDOException;

/**
 * Class PDO
 */
class PDO extends UseSharedObjects implements DBClass_Interface
{
    public $link = null;       // Connection with PDO's link
    private $mainTableCount = 0;
    private $mainTableTotalCount = 0;
    private $fieldInfo = null;
    private $isAlreadySetup = false;
    private $isRequiredUpdated = false;
    private $updatedRecord = null;
    private $softDeleteField = null;
    private $softDeleteValue = null;
    private $isFollowingTimezones;
    private $isSuppressDVOnCopy;
    private $isSuppressDVOnCopyAssoc;

    public function __construct()
    {
        $params = IMUtil::getFromParamsPHPFile(["followingTimezones", "suppressDefaultValuesOnCopy", "suppressDefaultValuesOnCopyAssoc",], true);
        $this->isFollowingTimezones = $params["followingTimezones"] ?? false;
        $this->isSuppressDVOnCopy = $params["suppressDefaultValuesOnCopy"] ?? false;
        $this->isSuppressDVOnCopyAssoc = $params["suppressDefaultValuesOnCopyAssoc"] ?? false;
    }

    public function getUpdatedRecord()
    {
        return $this->updatedRecord;
    }

    public function updatedRecord(){
        return $this->updatedRecord;
    }

    /* Usually a setter method has just one parameter, but the same named method existed on previous version
       and possibly calling it from user program. So if it has more than one parameter, it might call old
       method and redirect to previous one. (msyk, 2021-11-03) */
    public function setUpdatedRecord($record, $value=false, $index = 0)
    {
        if($value === false) {
            $this->updatedRecord = $record;
        } else { // Previous use of this method redirect to setDataToUpdatedRecord
            $this->setDataToUpdatedRecord($record, $value, $index);
        }
    }

    public function setDataToUpdatedRecord($field, $value, $index = 0)
    {
        $this->updatedRecord[$index][$field] = $value;
        $this->useSetDataToUpdatedRecord = true;
    }

    private $useSetDataToUpdatedRecord = false;

    public function getUseSetDataToUpdatedRecord()
    {
        return $this->useSetDataToUpdatedRecord;
    }

    public function clearUseSetDataToUpdatedRecord()
    {
        $this->useSetDataToUpdatedRecord = false;
    }

    public function requireUpdatedRecord($value)
    {
        $this->isRequiredUpdated = $value;
    }

    public function softDeleteActivate($field, $value)
    {
        $this->softDeleteField = $field;
        $this->softDeleteValue = $value;
    }

    /**
     * @param $str
     */
    public function errorMessageStore($str)
    {
        if ($this->link) {
            $errorInfo = var_export($this->link->errorInfo(), true);
            $this->logger->setErrorMessage("Query Error: [{$str}] Code={$this->link->errorCode()} Info ={$errorInfo}");
        } else {
            $this->logger->setErrorMessage("Query Error: [{$str}]");
        }
    }

    /**
     * @return bool
     */
    public function setupConnection()
    {
        if ($this->isAlreadySetup) {
            return true;
        }
        try {
            $this->link = new \PDO($this->dbSettings->getDbSpecDSN(),
                $this->dbSettings->getDbSpecUser(),
                $this->dbSettings->getDbSpecPassword(),
                is_array($this->dbSettings->getDbSpecOption()) ? $this->dbSettings->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('Connection Error: ' . $ex->getMessage() .
                ", DSN=" . $this->dbSettings->getDbSpecDSN() .
                ", User=" . $this->dbSettings->getDbSpecUser());
            return false;
        }
        $this->isAlreadySetup = true;
        return true;
    }

    public function setupHandlers($dsn = false)
    {
        if ($dsn === false) {
            $dsn = $this->dbSettings->getDbSpecDSN();
        }
        if (!is_null($this->dbSettings)) {
            $this->handler = Support\DB_PDO_Handler::generateHandler($this, $dsn);
            $this->handler->optionalOperationInSetup();
            $this->specHandler = Support\DB_Spec_Handler_PDO::generateHandler($this, $dsn);
        }
        $this->authHandler = new Support\DB_Auth_Handler_PDO($this);
        $this->notifyHandler = new Support\DB_Notification_Handler_PDO($this);
    }

    public function setupWithDSN($dsnString)
    {
        if ($this->isAlreadySetup) {
            return true;
        }
        try {
            $this->link = new \PDO($dsnString);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('Connection Error: ' . $ex->getMessage() . ", DSN=" . $dsnString);
            return false;
        }
        $this->isAlreadySetup = true;
        return true;
    }

    /*
     * Generate SQL style WHERE clause.
     */
    /**
     * @param $currentOperation
     * @param bool $includeContext
     * @param bool $includeExtra
     * @param string $signedUser
     * @return string
     */
    private function getWhereClause(
        $currentOperation, $includeContext = true, $includeExtra = true, $signedUser = '', $bypassAuth = false)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $queryClause = '';
        $primaryKey = $tableInfo['key'] ?? 'id';

        $queryClauseArray = array();
        if ($includeContext && isset($tableInfo['query'][0])) {
            $chunkCount = 0;
            $oneClause = array();
            $insideOp = ' AND ';
            $outsideOp = ' OR ';
            foreach ($tableInfo['query'] as $condition) {
                if ($condition['field'] == '__operation__') {
                    $chunkCount++;
                    if (isset($condition['operator']) && $condition['operator'] == 'ex') {
                        $insideOp = ' OR ';
                        $outsideOp = ' AND ';
                    }
                } else if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    $escapedField = $this->handler->quotedEntityName($condition['field']);
                    $condition = $this->normalizedCondition($condition);
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator.: {$condition['operator']}");
                    }
                    if (isset($condition['value']) && !is_null($condition['value'])) {
                        $escapedValue = $this->link->quote($condition['value']);
                        if (isset($condition['operator'])) {
                            $queryClauseArray[$chunkCount][]
                                = "{$escapedField} {$condition['operator']} {$escapedValue}";
                        }
                    } else {
                        $queryClauseArray[$chunkCount][]
                            = "{$escapedField} {$condition['operator']}";
                    }
                }
            }
            foreach ($queryClauseArray as $oneTerm) {
                $oneClause[] = '(' . implode($insideOp, $oneTerm) . ')';
            }
            $queryClause = implode($outsideOp, $oneClause);
        }

        $queryClauseArray = array();
        $exCriteria = $this->dbSettings->getExtraCriteria();
        if ($includeExtra && isset($exCriteria[0])) {
            $chunkCount = 0;
            $oneClause = array();
            $insideOp = ' AND ';
            $outsideOp = ' OR ';
            foreach ($this->dbSettings->getExtraCriteria() as $condition) {
                if ($condition['field'] == $primaryKey && isset($condition['value'])) {
                    $this->notifyHandler->setQueriedPrimaryKeys(array($condition['value']));
                }
                if ($condition['field'] == '__operation__') {
                    $chunkCount++;
                    if (isset($condition['operator']) && $condition['operator'] == 'ex') {
                        $insideOp = ' OR ';
                        $outsideOp = ' AND ';
                    }
                } else if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    $escapedField = $this->handler->quotedEntityName($condition['field']);
                    $condition = $this->normalizedCondition($condition);
                    if (!$this->specHandler->isPossibleOperator($condition['operator'])) {
                        throw new Exception("Invalid Operator: {$condition['operator']}");
                    }
                    if (isset($condition['value']) && !is_null($condition['value'])) {
                        $escapedValue = $this->link->quote($condition['value']);
                        if (strtoupper(trim($condition['operator'])) == "IN") {
                            $escapedValue = "(";
                            $isFirst = true;
                            foreach (json_decode($condition['value']) as $item) {
                                $escapedValue .= (!$isFirst ? "," : "") . $this->link->quote($item);
                                $isFirst = false;
                            }
                            $escapedValue .= ")";
                        }
                        $queryClauseArray[$chunkCount][]
                            = "{$escapedField} {$condition['operator']} {$escapedValue}";
                    } else {
                        $queryClauseArray[$chunkCount][]
                            = "{$escapedField} {$condition['operator']}";
                    }
                }
            }
            foreach ($queryClauseArray as $oneTerm) {
                $oneClause[] = '(' . implode($insideOp, $oneTerm) . ')';
            }
            $queryClause = ($queryClause == '' ? '' : "($queryClause) AND ")
                . '(' . implode($outsideOp, $oneClause) . ')';
        }

        if (count($this->dbSettings->getForeignFieldAndValue()) > 0) {
            foreach ($tableInfo['relation'] as $relDef) {
                foreach ($this->dbSettings->getForeignFieldAndValue() as $foreignDef) {
                    if ($relDef['join-field'] == $foreignDef['field']) {
                        $escapedField = $this->handler->quotedEntityName($relDef['foreign-key']);
                        $escapedValue = $this->link->quote($foreignDef['value']);
                        $op = $relDef['operator'] ?? '=';
                        if (!$this->specHandler->isPossibleOperator($op)) {
                            throw new Exception("Invalid Operator.");
                        }
                        $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                            . "({$escapedField}{$op}{$escapedValue})";
                    }
                }
            }
        }
        $keywordAuth = (($currentOperation == "load") || ($currentOperation == "select"))
            ? "read" : $currentOperation;
        if (isset($tableInfo['authentication'])
            && ((isset($tableInfo['authentication']['all'])
                || isset($tableInfo['authentication'][$keywordAuth])))
        ) {
            $authInfoField = $this->authHandler->getFieldForAuthorization($keywordAuth);
            $authInfoTarget = $this->authHandler->getTargetForAuthorization($keywordAuth);
            if ($authInfoTarget == 'field-user') {
                if (strlen($signedUser) == 0 && !$bypassAuth) {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "({$authInfoField}=" . $this->link->quote($signedUser) . ")";
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($signedUser);
                $groupCriteria = array();
                foreach ($belongGroups as $oneGroup) {
                    $groupCriteria[] = "{$authInfoField}=" . $this->link->quote($oneGroup);
                }
                if ((strlen($signedUser) == 0 || count($groupCriteria) == 0) && !$bypassAuth) {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "(" . implode(' OR ', $groupCriteria) . ")";
                }
            } else {
                $authorizedUsers = $this->authHandler->getAuthorizedUsers($keywordAuth);
                $authorizedGroups = $this->authHandler->getAuthorizedGroups($keywordAuth);
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($signedUser);
                if (count($authorizedUsers) > 0 || count($authorizedGroups) > 0) {
                    if (!in_array($signedUser, $authorizedUsers)
                        && count(array_intersect($belongGroups, $authorizedGroups)) == 0
                        && !$bypassAuth
                    ) {
                        $queryClause = 'FALSE';
                    }
                }
            }
        }
        if (!is_null($this->softDeleteField) && !is_null($this->softDeleteValue)) {
            $dfEsc = $this->handler->quotedEntityName($this->softDeleteField);
            $dvEsc = $this->link->quote($this->softDeleteValue);
            if (strlen($queryClause) > 0) {
                $queryClause = "($queryClause) AND ($dfEsc <> $dvEsc OR $dfEsc IS NULL)";
            } else {
                $queryClause = "($dfEsc <> $dvEsc OR $dfEsc IS NULL)";
            }
        }
        return $queryClause;
    }

    /* Genrate SQL Sort and Where clause */
    /**
     * @return string
     */
    private function getSortClause()
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $sortClause = array();
        if (count($this->dbSettings->getExtraSortKey()) > 0) {
            foreach ($this->dbSettings->getExtraSortKey() as $condition) {
                $escapedField = $this->handler->quotedEntityName($condition['field']);
                if (isset($condition['direction'])) {
                    if (!$this->specHandler->isPossibleOrderSpecifier($condition['direction'])) {
                        throw new Exception("Invalid Sort Specifier.");
                    }
                    $sortClause[] = "{$escapedField} {$condition['direction']}";
                } else {
                    $sortClause[] = $escapedField;
                }
            }
        }
        if (isset($tableInfo['sort'])) {
            foreach ($tableInfo['sort'] as $condition) {
                if (isset($condition['direction']) && !$this->specHandler->isPossibleOrderSpecifier($condition['direction'])) {
                    throw new Exception("Invalid Sort Specifier.");
                }
                $escapedField = $this->handler->quotedEntityName($condition['field']);
                $direction = $condition['direction'] ?? "";
                $sortClause[] = "{$escapedField} {$direction}";
            }
        }
        return implode(',', $sortClause);
    }

    /**
     * @param $dataSourceName
     * @return array|bool
     */
    function readFromDB()
    {
        $this->fieldInfo = null;
        $this->mainTableCount = 0;
        $this->mainTableTotalCount = 0;
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
        $boolFields = $this->handler->getBooleanFields($this->dbSettings->getEntityForUpdate());

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'load' || $condition['db-operation'] == 'read') {
                    if ($condition['situation'] == 'pre') {
                        $sql = $condition['definition'];
                        $this->logger->setDebugMessage($sql);
                        $result = $this->link->query($sql);
                        if ($result === false) {
                            $this->errorMessageStore('Pre-script:' . $sql);
                        }
                    }
                }
            }
        }

        $queryClause = $this->getWhereClause('read', true, true, $signedUser);
        if ($queryClause != '') {
            $queryClause = "WHERE {$queryClause}";
        }
        $sortClause = $this->getSortClause();
        $isAggregate = ($this->dbSettings->getAggregationSelect() != null);
        $tableName = $this->dbSettings->getEntityForRetrieve();
        $viewOrTableName = $isAggregate ? $this->dbSettings->getAggregationFrom()
            : $this->handler->quotedEntityName($tableName);
        $countingName = $isAggregate ? $this->dbSettings->getAggregationFrom()
            : $this->handler->quotedEntityName($this->dbSettings->getEntityForCount());

        // Create SQL
        $limitParam = 100000000;
        if (isset($tableInfo['maxrecords'])) {
            if (intval($tableInfo['maxrecords']) < $this->dbSettings->getRecordCount()) {
                if (intval($tableInfo['maxrecords']) < intval($tableInfo['records'])) {
                    $limitParam = intval($tableInfo['records']);
                } else {
                    $limitParam = intval($tableInfo['maxrecords']);
                }
            } else {
                $limitParam = $this->dbSettings->getRecordCount();
            }
        } else if (isset($tableInfo['records'])) {
            if (intval($tableInfo['records']) < $this->dbSettings->getRecordCount()) {
                $limitParam = intval($tableInfo['records']);
            } else {
                $limitParam = $this->dbSettings->getRecordCount();
            }
        }
        $isPaging = (isset($tableInfo['paging']) and $tableInfo['paging'] === true);
        $skipParam = 0;
        if ($isPaging) {
            $skipParam = $this->dbSettings->getStart();
        }
        $fields = $isAggregate ? $this->dbSettings->getAggregationSelect()
            : (isset($tableInfo['specify-fields'])
                ? implode(',', array_unique($this->dbSettings->getFieldsRequired())) : "*");
        $groupBy = ($isAggregate && $this->dbSettings->getAggregationGroupBy())
            ? ("GROUP BY " . $this->dbSettings->getAggregationGroupBy()) : "";
        $offset = $skipParam;

        if ($isAggregate && !$isPaging) {
            $offset = '';
        } else {
            // Count all records matched with the condtions
            $sql = "{$this->handler->sqlSelectCommand()}count(*) FROM {$countingName} {$queryClause} {$groupBy}";
            $this->logger->setDebugMessage($sql);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
                return false;
            }
            $this->mainTableCount = $isAggregate ? $result->rowCount() : $result->fetchColumn(0);

            if ($queryClause === '') {
                $this->mainTableTotalCount = $this->mainTableCount;
            } else {
                // Count all records
                $sql = "{$this->handler->sqlSELECTCommand()}count(*) FROM {$countingName} {$groupBy}";
                $this->logger->setDebugMessage($sql);
                $result = $this->link->query($sql);
                if ($result === false) {
                    $this->errorMessageStore('Select:' . $sql);
                    return false;
                }
                $this->mainTableTotalCount = $isAggregate ? $result->rowCount() : $result->fetchColumn(0);
            }
        }
        $sql = "{$this->handler->sqlSELECTCommand()}{$fields} FROM {$viewOrTableName} {$queryClause} {$groupBy} "
            . $this->handler->sqlOrderByCommand($sortClause, $limitParam, $offset);
        $this->logger->setDebugMessage($sql);
        $this->notifyHandler->setQueriedEntity($isAggregate ? $this->dbSettings->getAggregationFrom() : $tableName);
        $this->notifyHandler->setQueriedCondition(
            "{$queryClause} {$this->handler->sqlOrderByCommand($sortClause, $limitParam, $offset)}");

        // Query
        $result = $this->link->query($sql);

        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->notifyHandler->setQueriedPrimaryKeys(array());
        $keyField = $this->getKeyFieldOfContext($tableInfo);
        $timeFields = ($this->isFollowingTimezones && !$this->dbSettings->getAggregationFrom())
            ? $this->handler->getTimeFields($this->dbSettings->getEntityForRetrieve()) : [];
        $sqlResult = array();
        $isFirstRow = true;
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $rowArray = array();
            foreach ($row as $field => $val) {
                if ($isFirstRow) {
                    $this->fieldInfo[] = $field;
                }
                $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
                // Convert the time explanation from UTC to server setup timezone
                if (in_array($field, $timeFields) && !is_null($rowArray[$field]) && $rowArray[$field] !== '') {
                    $dt = new DateTime($rowArray[$field], new DateTimeZone(date_default_timezone_get()));
                    $isTime = preg_match('/^\d{2}:\d{2}:\d{2}/', $rowArray[$field]);
                    $dt->setTimezone(new DateTimeZone('UTC'));
                    $rowArray[$field] = $dt->format($isTime ? 'H:i:s' : 'Y-m-d H:i:s');
                } else if (in_array($field, $boolFields)) {
                    $rowArray[$field] = $this->isTrue($rowArray[$field]);
                }
            }
            $sqlResult[] = $rowArray;
            if ($keyField && isset($rowArray[$keyField])) {
                $this->notifyHandler->addQueriedPrimaryKeys($rowArray[$keyField]);
            }
            $isFirstRow = false;
        }
        if ($isAggregate && !$isPaging) {
            $this->mainTableCount = count($sqlResult);
            $this->mainTableTotalCount = count($sqlResult);
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'load' || $condition['db-operation'] == 'read') {
                    if ($condition['situation'] == 'post') {
                        $sql = $condition['definition'];
                        $this->logger->setDebugMessage($sql);
                        $result = $this->link->query($sql);
                        if ($result === false) {
                            $this->errorMessageStore('Post-script:' . $sql);
                            return false;
                        }
                    }
                }
            }
        }
        return $sqlResult;
    }

    /**
     * @param $dataSourceName
     * @return int
     */
    public function countQueryResult()
    {
        return $this->mainTableCount;
    }

    /**
     * @param $dataSourceName
     * @return int
     */
    public function getTotalCount()
    {
        return $this->mainTableTotalCount;
    }

    /**
     * @param $dataSourceName
     * @return bool
     */
    function updateDB($bypassAuth)
    {
        $this->fieldInfo = null;
        $tableName = $this->handler->quotedEntityName($this->dbSettings->getEntityForUpdate());
        $fieldInfos = $this->handler->getNullableNumericFields($this->dbSettings->getEntityForUpdate());
        $timeFields = $this->isFollowingTimezones
            ? $this->handler->getTimeFields($this->dbSettings->getEntityForUpdate()) : [];
        $boolFields = $this->handler->getBooleanFields($this->dbSettings->getEntityForUpdate());
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'update' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Pre-script:' . $sql);
                        return false;
                    }
                }
            }
        }

        $setClause = array();
        $setParameter = array();
        $counter = 0;
        $fieldValues = $this->dbSettings->getValue();
        foreach ($this->dbSettings->getFieldsRequired() as $field) {
            $value = $fieldValues[$counter];
            $counter++;
            $convertedValue = (is_array($value)) ? implode("\n", $value) : $value;
            if (in_array($field, $fieldInfos) && $convertedValue === "") {
                $setClause[] = $this->handler->quotedEntityName($field) . "=NULL";
            } else if (in_array($field, $boolFields)) {
                $setClause[] = $this->handler->quotedEntityName($field)
                    . "=" . ($this->isTrue($convertedValue) ? "TRUE" : "FALSE");
            } else {
                $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
                $convertedValue = $this->formatter->formatterToDB($filedInForm, $convertedValue);
                // Convert the time explanation from UTC to server setup timezone
                if (in_array($field, $timeFields) && !is_null($convertedValue) && $convertedValue !== '') {
                    $dt = new DateTime($convertedValue, new DateTimeZone('UTC'));
                    $isTime = preg_match('/^\d{2}:\d{2}:\d{2}/', $convertedValue);
                    $dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
                    $convertedValue = $dt->format($isTime ? 'H:i:s' : 'Y-m-d H:i:s');
                }
                $setClause[] = $this->handler->quotedEntityName($field) . "=?";
                $setParameter[] = $convertedValue;
            }
        }
        if (count($setClause) < 1) {
            $this->logger->setErrorMessage('No data to update.');
            return false;
        }
        $setClause = implode(',', $setClause);

        $queryClause = $this->getWhereClause('update', false, true, $signedUser, $bypassAuth);
        if ($queryClause != '') {
            $queryClause = "WHERE {$queryClause}";
        }
        $sql = "{$this->handler->sqlUPDATECommand()}{$tableName} SET {$setClause} {$queryClause}";
        $prepSQL = $this->link->prepare($sql);
        $this->notifyHandler->setQueriedEntity($this->dbSettings->getEntityForUpdate());

        $this->logger->setDebugMessage(
            $prepSQL->queryString . " with " . str_replace("\n", " ", var_export($setParameter, true)));

        $result = $prepSQL->execute($setParameter);
        if ($result === false) {
            $this->errorMessageStore('Update:' . $sql);
            return false;
        }

        if ($this->isRequiredUpdated) {
            $targetTable = $this->handler->quotedEntityName($this->dbSettings->getEntityForRetrieve());
            $sql = $this->handler->sqlSELECTCommand() . "* FROM {$targetTable} {$queryClause}";
            $result = $this->link->query($sql);
            $this->logger->setDebugMessage($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
            } else {
                $this->notifyHandler->setQueriedPrimaryKeys(array());
                $keyField = $this->getKeyFieldOfContext($tableInfo);
                $sqlResult = array();
                $isFirstRow = true;
                foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $rowArray = array();
                    foreach ($row as $field => $val) {
                        if ($isFirstRow) {
                            $this->fieldInfo[] = $field;
                        }
                        $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
                        $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
                        // Convert the time explanation from UTC to server setup timezone
                        if (in_array($field, $timeFields) && !is_null($rowArray[$field]) && $rowArray[$field] !== '') {
                            $dt = new DateTime($rowArray[$field], new DateTimeZone(date_default_timezone_get()));
                            $isTime = preg_match('/^\d{2}:\d{2}:\d{2}/', $rowArray[$field]);
                            $dt->setTimezone(new DateTimeZone('UTC'));
                            $rowArray[$field] = $dt->format($isTime ? 'H:i:s' : 'Y-m-d H:i:s');
                        }
                    }
                    $sqlResult[] = $rowArray;
                    $this->notifyHandler->addQueriedPrimaryKeys($rowArray[$keyField]);
                    $isFirstRow = false;
                }
                $this->updatedRecord = count($sqlResult) ? $sqlResult : null;
            }
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'update' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Post-script:' . $sql);
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param $dataSourceName
     * @param $bypassAuth
     * @return bool
     */
    public function createInDB($isReplace = false)
    {
        $this->fieldInfo = null;
        $fieldInfos = $this->handler->getNullableNumericFields($this->dbSettings->getEntityForUpdate());
        $timeFields = $this->isFollowingTimezones
            ? $this->handler->getTimeFields($this->dbSettings->getEntityForUpdate()) : [];
        $boolFields = $this->handler->getBooleanFields($this->dbSettings->getEntityForUpdate());
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->handler->quotedEntityName($this->dbSettings->getEntityForUpdate());
        $viewName = $this->handler->quotedEntityName($this->dbSettings->getEntityForRetrieve());

        if (isset($tableInfo['authentication'])) {
            $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
        }

        $setColumnNames = array();
        $setValues = array();
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if (($condition['db-operation'] == 'new' || $condition['db-operation'] == 'create')
                    && $condition['situation'] == 'pre'
                ) {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Pre-script:' . $sql);
                        return false;
                    }
                }
            }
        }

        $requiredFields = $this->dbSettings->getFieldsRequired();
        $countFields = count($requiredFields);
        $fieldValues = $this->dbSettings->getValue();
        for ($i = 0; $i < $countFields; $i++) {
            $field = $requiredFields[$i];
            $value = $fieldValues[$i];
            if (in_array($field, $fieldInfos) && $value === "") {
                $setValues[] = "NULL";
            } else if (in_array($field, $boolFields)) {
                $setValues[] = $this->isTrue($value) ? "TRUE" : "FALSE";
            } else {
                $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
                $convertedValue = (is_array($value)) ? implode("\n", $value) : $value;
                // Convert the time explanation from UTC to server setup timezone
                if (in_array($field, $timeFields) && !is_null($convertedValue) && $convertedValue !== '') {
                    $dt = new DateTime($convertedValue, new DateTimeZone('UTC'));
                    $isTime = preg_match('/^\d{2}:\d{2}:\d{2}/', $convertedValue);
                    $dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
                    $convertedValue = $dt->format($isTime ? 'H:i:s' : 'Y-m-d H:i:s');
                }
                $setValues[] = $this->link->quote(
                    $this->formatter->formatterToDB($filedInForm, $convertedValue));
            }
            $setColumnNames[] = $field;
        }
        if (isset($tableInfo['default-values'])) {
            foreach ($tableInfo['default-values'] as $itemDef) {
                $field = $itemDef['field'];
                $value = $itemDef['value'];
                if (!in_array($field, $setColumnNames)) {
                    $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
                    $convertedValue = (is_array($value)) ? implode("\n", $value) : $value;
                    $setValues[] = $this->link->quote(
                        $this->formatter->formatterToDB($filedInForm, $convertedValue));
                    $setColumnNames[] = $field;
                }
            }
        }
        if (isset($tableInfo['authentication'])) {
            $authInfoField = $this->authHandler->getFieldForAuthorization("create");
            $authInfoTarget = $this->authHandler->getTargetForAuthorization("create");
            if ($authInfoTarget == 'field-user') {
                $setColumnNames[] = $authInfoField;
                $setValues[] = $this->link->quote(
                    strlen($signedUser) == 0 ? IMUtil::randomString(10) : $signedUser);
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authHandler->authSupportGetGroupsOfUser($signedUser);
                $setColumnNames[] = $authInfoField;
                $setValues[] = $this->link->quote(
                    strlen($belongGroups[0]) == 0 ? IMUtil::randomString(10) : $belongGroups[0]);
            }
        }

        $keyField = isset($tableInfo['key']) ? $tableInfo['key'] : 'id';
        $setClause = $this->handler->sqlSETClause($setColumnNames, $keyField, $setValues);
        if ($isReplace) {
            $sql = $this->handler->sqlREPLACECommand($tableName, $setClause);
        } else {
            $sql = $this->handler->sqlINSERTCommand($tableName, $setClause);
        }
        $this->logger->setDebugMessage($sql);
        $result = $this->link->exec($sql);
        if ($result === false || is_null($result)) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $seqObject = isset($tableInfo['sequence']) ? $tableInfo['sequence']
            : "{$this->dbSettings->getEntityForUpdate()}_{$keyField}_seq";
        $lastKeyValue = $this->link->lastInsertId($seqObject);
        if ($isReplace && $lastKeyValue == 0) { // lastInsertId returns 0 after replace command.
            $lastKeyValue = -999; // This means kind of error, so avoid to set non zero value.
        }

        $this->notifyHandler->setQueriedPrimaryKeys(array($lastKeyValue));
        $this->notifyHandler->setQueriedEntity($this->dbSettings->getEntityForUpdate());

        if ($this->isRequiredUpdated) {
            $sql = $this->handler->sqlSELECTCommand() . "* FROM " . $viewName
                . " WHERE " . $keyField . "=" . $this->link->quote($lastKeyValue);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
            } else {
                $sqlResult = array();
                $isFirstRow = true;
                foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $rowArray = array();
                    foreach ($row as $field => $val) {
                        if ($isFirstRow) {
                            $this->fieldInfo[] = $field;
                        }
                        $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
                        $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
                        // Convert the time explanation from UTC to server setup timezone
                        if (in_array($field, $timeFields) && !is_null($rowArray[$field]) && $rowArray[$field] !== '') {
                            $dt = new DateTime($rowArray[$field], new DateTimeZone(date_default_timezone_get()));
                            $isTime = preg_match('/^\d{2}:\d{2}:\d{2}/', $rowArray[$field]);
                            $dt->setTimezone(new DateTimeZone('UTC'));
                            $rowArray[$field] = $dt->format($isTime ? 'H:i:s' : 'Y-m-d H:i:s');
                        }
                    }
                    $sqlResult[] = $rowArray;
                    $isFirstRow = false;
                }
                $this->updatedRecord = count($sqlResult) ? $sqlResult : null;
            }
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if (($condition['db-operation'] == 'new' || $condition['db-operation'] == 'create')
                    && $condition['situation'] == 'post'
                ) {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Post-script:' . $sql);
                        return false;
                    }
                }
            }
        }
        return $lastKeyValue;
    }

    /**
     * @param $dataSourceName
     * @return bool
     */
    function deleteFromDB()
    {
        $this->fieldInfo = null;
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->handler->quotedEntityName($this->dbSettings->getEntityForUpdate());
        $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'delete' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Pre-script:' . $sql);
                        return false;
                    }
                }
            }
        }
        $queryClause = $this->getWhereClause('delete', false, true, $signedUser);
        if ($queryClause == '') {
            $this->errorMessageStore('Don\'t delete with no ciriteria. queryClause=' . $queryClause);
            return false;
        }
        $sql = "{$this->handler->sqlDELETECommand()}{$tableName} WHERE {$queryClause}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if (!$result) {
            $this->errorMessageStore('Delete Error:' . $sql);
            return false;
        }
        $this->notifyHandler->setQueriedEntity($this->dbSettings->getEntityForUpdate());

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'delete' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Post-script:' . $sql);
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function copyInDB()
    {
        $this->fieldInfo = null;
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->dbSettings->getEntityForUpdate();
        $signedUser = $this->authHandler->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
        $timeFields = $this->isFollowingTimezones
            ? $this->handler->getTimeFields($this->dbSettings->getEntityForUpdate()) : [];

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'copy' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Pre-script:' . $sql);
                        return false;
                    }
                }
            }
        }
        //======
        $queryClause = $this->getWhereClause('delete', false, true, $signedUser);
        if ($queryClause == '') {
            $this->errorMessageStore('Don\'t copy with no ciriteria.');
            return false;
        }
        $defaultValues = array();
        if (!$this->isSuppressDVOnCopy && isset($tableInfo['default-values'])) {
            foreach ($tableInfo['default-values'] as $itemDef) {
                $defaultValues[$itemDef['field']] = $itemDef['value'];
            }
        }
        $lastKeyValue = $this->handler->copyRecords($tableInfo, $queryClause, null, null, $defaultValues);
        if ($lastKeyValue === false || is_null($lastKeyValue)) {
            return false;
        }
        $this->notifyHandler->setQueriedPrimaryKeys(array($lastKeyValue));
        $this->notifyHandler->setQueriedEntity($tableName);
        //======
        $assocArray = $this->dbSettings->getAssociated();
        if ($assocArray) {
            foreach ($assocArray as $assocInfo) {
                $assocContextDef = $this->dbSettings->getDataSourceDefinition($assocInfo['name']);
                $queryClause = $this->handler->quotedEntityName($assocInfo["field"]) . "=" .
                    $this->link->quote($assocInfo["value"]);
                $defaultValues = array();
                if (!$this->isSuppressDVOnCopyAssoc && isset($assocContextDef['default-values'])) {
                    foreach ($assocContextDef['default-values'] as $itemDef) {
                        $defaultValues[$itemDef['field']] = $itemDef['value'];
                    }
                }
                $this->handler->copyRecords($assocContextDef, $queryClause, $assocInfo["field"], $lastKeyValue, $defaultValues);
            }
        }
        //======
        if ($this->isRequiredUpdated) {
            $sql = "{$this->handler->sqlSELECTCommand()}* FROM " . $this->handler->quotedEntityName($tableName)
                . " WHERE " . $tableInfo['key'] . "=" . $this->link->quote($lastKeyValue);
            $result = $this->link->query($sql);
            $this->logger->setDebugMessage($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
            } else {
                $sqlResult = array();
                $isFirstRow = true;
                foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $rowArray = array();
                    foreach ($row as $field => $val) {
                        if ($isFirstRow) {
                            $this->fieldInfo[] = $field;
                        }
                        $filedInForm = "{$this->dbSettings->getEntityForUpdate()}{$this->dbSettings->getSeparator()}{$field}";
                        $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
                        // Convert the time explanation from UTC to server setup timezone
                        if (in_array($field, $timeFields) && !is_null($rowArray[$field]) && $rowArray[$field] !== '') {
                            $dt = new DateTime($rowArray[$field], new DateTimeZone(date_default_timezone_get()));
                            $isTime = preg_match('/^\d{2}:\d{2}:\d{2}/', $rowArray[$field]);
                            $dt->setTimezone(new DateTimeZone('UTC'));
                            $rowArray[$field] = $dt->format($isTime ? 'H:i:s' : 'Y-m-d H:i:s');
                        }
                    }
                    $sqlResult[] = $rowArray;
                    $isFirstRow = false;
                }
                $this->updatedRecord = $sqlResult;
            }
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'copy' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage($sql);
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Post-script:' . $sql);
                        return false;
                    }
                }
            }
        }
        return $lastKeyValue;
    }

    public function normalizedCondition($condition)
    {
        if (!isset($condition['field'])) {
            $condition['field'] = '';
        }
        if (!isset($condition['value'])) {
            $condition['value'] = '';
        }
        if (!isset($condition['operator'])) {
            $condition['operator'] = '=';
        }

        if ($condition['operator'] == 'match*') {
            return array(
                'field' => $condition['field'],
                'operator' => 'LIKE',
                'value' => "{$condition['value']}%",
            );
        } else if ($condition['operator'] == '*match') {
            return array(
                'field' => $condition['field'],
                'operator' => 'LIKE',
                'value' => "%{$condition['value']}",
            );
        } else if ($condition['operator'] == '*match*') {
            return array(
                'field' => $condition['field'],
                'operator' => 'LIKE',
                'value' => "%{$condition['value']}%",
            );
        } else if ($this->specHandler->isOperatorWithoutValue($condition['operator'])) {
            return array(
                'field' => $condition['field'],
                'operator' => $condition['operator'],
            );
        }
        return $condition;
    }

    private function getKeyFieldOfContext($context)
    {
        if (isset($context) && isset($context['key'])) {
            return $context['key'];
        }
        return $this->specHandler->getDefaultKey();
    }

    /**
     * @param $dataSourceName
     * @return null
     */
    function getFieldInfo($dataSourceName)
    {
        return $this->fieldInfo;
    }

    private function isTrue($d)
    {
        if (strtolower($d) == 'true' || strtolower($d) == 't') {
            return true;
        } else if (intval($d) > 0) {
            return true;
        }
        return false;
    }

    public function queryForTest($table, $conditions = null)
    {
        if ($table == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            $this->errorMessageStore("Can't open db connection.");
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}* FROM " . $this->handler->quotedEntityName($table);
        if (is_array($conditions) && count($conditions) > 0) {
            $sql .= " WHERE ";
            $first = true;
            foreach ($conditions as $field => $value) {
                if (!$first) {
                    $sql .= " AND ";
                }
                $sql .= $this->handler->quotedEntityName($field) . "=" . $this->link->quote($value);
                $first = false;
            }
        }
        $result = $this->link->query($sql);
        if ($result === false) {
            var_dump($this->link->errorInfo());
            return false;
        }
        $this->logger->setDebugMessage("[queryForTest] {$sql}");
        $recordSet = array();
        foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $oneRecord = array();
            foreach ($row as $field => $value) {
                $oneRecord[$field] = $value;
            }
            $recordSet[] = $oneRecord;
        }
        return $recordSet;
    }

    public function deleteForTest($table, $conditions = null)
    {
        if ($table == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            $this->errorMessageStore("Can't open db connection.");
            return false;
        }
        $sql = "{$this->handler->sqlDELETECommand()}" . $this->handler->quotedEntityName($table);
        if (is_array($conditions) && count($conditions) > 0) {
            $sql .= " WHERE ";
            $first = true;
            foreach ($conditions as $field => $value) {
                if (!$first) {
                    $sql .= " AND ";
                }
                $sql .= $this->handler->quotedEntityName($field) . "=" . $this->link->quote($value);
                $first = false;
            }
        }
        $result = $this->link->exec($sql);
        if ($result === false) {
            var_dump($this->link->errorInfo());
            return false;
        }
        $this->logger->setDebugMessage("[deleteForTest] {$sql}");
        return true;
    }

    /*
     * Transaction
     */
    public function hasTransaction()
    {
        return true;
    }

    public function inTransaction()
    {
        return $this->link->inTransaction();
    }

    public function beginTransaction()
    {
        $this->link->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->link->commit();
    }

    public function rollbackTransaction()
    {
        $this->link->rollBack();
    }
}
