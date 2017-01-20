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

/**
 * Class DB_PDO
 */
require_once("DB_Support/DB_PDO_Handler.php");

class DB_PDO extends DB_AuthCommon implements DB_Access_Interface, DB_Interface_Registering
{
    public $link = null;       // Connection with PDO's link
    private $handler = null;    // Handle for each database engine.
    private $mainTableCount = 0;
    private $mainTableTotalCount = 0;
    private $fieldInfo = null;
    private $isAlreadySetup = false;
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

    public function requireUpdatedRecord($value)
    {
        $this->isRequiredUpdated = $value;
    }

    public function queriedPrimaryKeys()
    {
        return $this->queriedPrimaryKeys;
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

    public function __constract()
    {

    }

    public function isExistRequiredTable()
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        if ($regTable == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            $this->errorMessageStore("Can't open db connection.");
            return false;
        }
        $sql = "SELECT id FROM {$regTable} LIMIT 1";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore("The table '{$regTable}' doesn't exist in the database.");
            return false;
        }
        return true;

    }

    public function register($clientId, $entity, $condition, $pkArray)
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $sql = "{$this->handler->sqlINSERTCommand()}{$regTable} (clientid,entity,conditions,registereddt) VALUES("
            . implode(',', array(
                $this->link->quote($clientId),
                $this->link->quote($entity),
                $this->link->quote($condition),
                $this->link->quote($currentDTFormat),
            )) . ')';
        $this->logger->setDebugMessage($sql);
        $result = $this->link->exec($sql);
        if ($result !== 1) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $newContextId = $this->link->lastInsertId("registeredcontext_id_seq");
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0) {
            // SQLite supports multiple records inserting, but it reported error.
            // PDO driver doesn't recognize it, does it ?
            foreach ($pkArray as $pk) {
                $qPk = $this->link->quote($pk);
                $sql = "{$this->handler->sqlINSERTCommand()}{$pksTable} (context_id,pk) VALUES ({$newContextId},{$qPk})";
                $this->logger->setDebugMessage($sql);
                $result = $this->link->exec($sql);
                if ($result < 1) {
                    $this->logger->setDebugMessage($this->link->errorInfo());
                    $this->errorMessageStore('Insert:' . $sql);
                    return false;
                }
            }
        } else {
            $sql = "{$this->handler->sqlINSERTCommand()}{$pksTable} (context_id,pk) VALUES ";
            $isFirstRow = true;
            foreach ($pkArray as $pk) {
                $qPk = $this->link->quote($pk);
                if (!$isFirstRow) {
                    $sql .= ",";
                }
                $sql .= "({$newContextId},{$qPk})";
                $isFirstRow = false;
            }
            $this->logger->setDebugMessage($sql);
            $result = $this->link->exec($sql);
            if ($result < 1) {
                $this->logger->setDebugMessage($this->link->errorInfo());
                $this->errorMessageStore('Insert:' . $sql);
                return false;
            }
        }
        return $newContextId;
    }

    public function unregister($clientId, $tableKeys)
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }

        $criteria = array("clientid=" . $this->link->quote($clientId));
        if ($tableKeys) {
            $subCriteria = array();
            foreach ($tableKeys as $regId) {
                $subCriteria[] = "id=" . $this->link->quote($regId);
            }
            $criteria[] = "(" . implode(" OR ", $subCriteria) . ")";
        }
        $criteriaString = implode(" AND ", $criteria);

        $contextIds = array();
        // SQLite initially doesn't support delete cascade. To support it,
        // the PRAGMA statement as below should be executed. But PHP 5.2 doens't
        // work, so it must delete
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0) {
            $sql = "PRAGMA foreign_keys = ON";
            $this->logger->setDebugMessage($sql);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Pragma:' . $sql);
                return false;
            }
            $versionSign = explode('.', phpversion());
            if ($versionSign[0] <= 5 && $versionSign[1] <= 2) {
                $sql = "SELECT id FROM {$regTable} WHERE {$criteriaString}";
                $this->logger->setDebugMessage($sql);
                $result = $this->link->query($sql);
                if ($result === false) {
                    $this->errorMessageStore('Select:' . $sql);
                    return false;
                }
                foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $contextIds[] = $row['id'];
                }
            }
        }
        $sql = "{$this->handler->sqlDELETECommand()}FROM {$regTable} WHERE {$criteriaString}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->exec($sql);
        if ($result === false) {
            $this->errorMessageStore('Delete:' . $sql);
            return false;
        }
        if (strpos($this->dbSettings->getDbSpecDSN(), 'sqlite:') === 0 && count($contextIds) > 0) {
            foreach ($contextIds as $cId) {
                $sql = "{$this->handler->sqlDELETECommand()}FROM {$pksTable} WHERE context_id=" . $this->link->quote($cId);
                $this->logger->setDebugMessage($sql);
                $result = $this->link->exec($sql);
                if ($result === false) {
                    $this->errorMessageStore('Delete:' . $sql);
                    return false;
                }
            }
        }
        return true;
    }

    public function matchInRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $originPK = $pkArray[0];
        $sql = "SELECT DISTINCT clientid FROM " . $pksTable . "," . $regTable . " WHERE " .
            "context_id = id AND clientid <> " . $this->link->quote($clientId) .
            " AND entity = " . $this->link->quote($entity) .
            " AND pk = " . $this->link->quote($originPK) .
            " ORDER BY clientid";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
        }
        return array_unique($targetClients);
    }

    public function appendIntoRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT id,clientid FROM {$regTable} WHERE entity = " . $this->link->quote($entity);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
            $sql = "{$this->handler->sqlINSERTCommand()}{$pksTable} (context_id,pk) VALUES(" . $this->link->quote($row['id']) .
                "," . $this->link->quote($pkArray[0]) . ")";
            $this->logger->setDebugMessage($sql);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Insert:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("Inserted count: " . $result->rowCount(), 2);
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    public function removeFromRegisterd($clientId, $entity, $pkArray)
    {
        $regTable = $this->handler->quotedEntityName($this->dbSettings->registerTableName);
        $pksTable = $this->handler->quotedEntityName($this->dbSettings->registerPKTableName);
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT id,clientid FROM {$regTable} WHERE entity = " . $this->link->quote($entity);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        $this->logger->setDebugMessage(var_export($result, true));
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $targetClients = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $targetClients[] = $row['clientid'];
            $sql = "{$this->handler->sqlDELETECommand()}FROM {$pksTable} WHERE context_id = " . $this->link->quote($row['id']) .
                " AND pk = " . $this->link->quote($pkArray[0]);
            $this->logger->setDebugMessage($sql);
            $resultDelete = $this->link->query($sql);
            if ($resultDelete === false) {
                $this->errorMessageStore('Delete:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("Deleted count: " . $resultDelete->rowCount(), 2);
        }
        return array_values(array_diff(array_unique($targetClients), array($clientId)));
    }

    /**
     * @param $str
     */
    private function errorMessageStore($str)
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
            $this->link = new PDO($this->dbSettings->getDbSpecDSN(),
                $this->dbSettings->getDbSpecUser(),
                $this->dbSettings->getDbSpecPassword(),
                is_array($this->dbSettings->getDbSpecOption()) ? $this->dbSettings->getDbSpecOption() : array());
            $this->handler = DB_PDO_Handler::generateHandler($this, $this->dbSettings->getDbSpecDSN());
            $this->handler->optionalOperationInSetup();
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('Connection Error: ' . $ex->getMessage() .
                ", DSN=" . $this->dbSettings->getDbSpecDSN() .
                ", User=" . $this->dbSettings->getDbSpecUser());
            return false;
        }
        $this->isAlreadySetup = true;
        return true;
    }

    public function setupWithDSN($dsnString)
    {
        if ($this->isAlreadySetup) {
            return true;
        }
        try {
            $this->link = new PDO($dsnString);
            $this->handler = DB_PDO_Handler::generateHandler($this, $dsnString);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('Connection Error: ' . $ex->getMessage() . ", DSN=" . $dsnString);
            return false;
        }
        $this->isAlreadySetup = true;
        return true;
    }

    public static function defaultKey()
    {
        return "id";
    }

    public function getDefaultKey()
    {
        return "id";
    }

    private function getKeyFieldOfContext($context)
    {
        if (isset($context) && isset($context['key'])) {
            return $context['key'];
        }
        return $this->getDefaultKey();
    }

    /**
     * @param $fname
     * @return mixed
     */
    private function sanitizeFieldName($fname)
    {
        return $fname;
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
    private function getWhereClause($currentOperation, $includeContext = true, $includeExtra = true, $signedUser = '')
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $queryClause = '';
        $primaryKey = isset($tableInfo['key']) ? $tableInfo['key'] : 'id';

        $queryClauseArray = array();
        if ($includeContext && isset($tableInfo['query'][0])) {
            $chunkCount = 0;
            $oneClause = array();
            $insideOp = ' AND ';
            $outsideOp = ' OR ';
            foreach ($tableInfo['query'] as $condition) {
                if ($condition['field'] == '__operation__') {
                    $chunkCount++;
                    if ($condition['operator'] == 'ex') {
                        $insideOp = ' OR ';
                        $outsideOp = ' AND ';
                    }
                } else if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    $escapedField = $this->handler->quotedEntityName($condition['field']);
                    if (isset($condition['value']) && $condition['value'] != null) {
                        $escapedValue = $this->link->quote($condition['value']);
                        if (isset($condition['operator'])) {
                            $condition = $this->normalizedCondition($condition);
                            if (!$this->handler->isPossibleOperator($condition['operator'])) {
                                throw new Exception("Invalid Operator.: {$condition['operator']}");
                            }
                            $queryClauseArray[$chunkCount][]
                                = "{$escapedField} {$condition['operator']} {$escapedValue}";
                        } else {
                            $queryClauseArray[$chunkCount][]
                                = "{$escapedField} = {$escapedValue}";
                        }
                    } else {
                        if (!$this->handler->isPossibleOperator($condition['operator'])) {
                            throw new Exception("Invalid Operator.: {$condition['operator']}");
                        }
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
                    $this->queriedPrimaryKeys = array($condition['value']);
                }
                if ($condition['field'] == '__operation__') {
                    $chunkCount++;
                    if ($condition['operator'] == 'ex') {
                        $insideOp = ' OR ';
                        $outsideOp = ' AND ';
                    }
                } else if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    $escapedField = $this->handler->quotedEntityName($condition['field']);
                    if (isset($condition['value']) && $condition['value'] != null) {
                        $condition = $this->normalizedCondition($condition);
                        $escapedValue = $this->link->quote($condition['value']);
                        if (isset($condition['operator'])) {
                            if (!$this->handler->isPossibleOperator($condition['operator'])) {
                                throw new Exception("Invalid Operator.");
                            }
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
                                = "{$escapedField} = {$escapedValue}";
                        }
                    } else {
                        if (!$this->handler->isPossibleOperator($condition['operator'])) {
                            throw new Exception("Invalid Operator.: {$condition['operator']}");
                        }
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
                        $op = isset($relDef['operator']) ? $relDef['operator'] : '=';
                        if (!$this->handler->isPossibleOperator($op)) {
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
            $authInfoField = $this->getFieldForAuthorization($keywordAuth);
            $authInfoTarget = $this->getTargetForAuthorization($keywordAuth);
            if ($authInfoTarget == 'field-user') {
                if (strlen($signedUser) == 0) {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "({$authInfoField}=" . $this->link->quote($signedUser) . ")";
                }
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authSupportGetGroupsOfUser($signedUser);
                $groupCriteria = array();
                foreach ($belongGroups as $oneGroup) {
                    $groupCriteria[] = "{$authInfoField}=" . $this->link->quote($oneGroup);
                }
                if (strlen($signedUser) == 0 || count($groupCriteria) == 0) {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "(" . implode(' OR ', $groupCriteria) . ")";
                }
            } else {
                $authorizedUsers = $this->getAuthorizedUsers($keywordAuth);
                $authorizedGroups = $this->getAuthorizedGroups($keywordAuth);
                $belongGroups = $this->authSupportGetGroupsOfUser($signedUser);
                if (count($authorizedUsers) > 0 || count($authorizedGroups) > 0) {
                    if (!in_array($signedUser, $authorizedUsers)
                        && count(array_intersect($belongGroups, $authorizedGroups)) == 0
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
                    if (!$this->handler->isPossibleOrderSpecifier($condition['direction'])) {
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
                if (isset($condition['direction']) && !$this->handler->isPossibleOrderSpecifier($condition['direction'])) {
                    throw new Exception("Invalid Sort Specifier.");
                }
                $escapedField = $this->handler->quotedEntityName($condition['field']);
                $sortClause[] = "{$escapedField} {$condition['direction']}";
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
        $tableName = $this->dbSettings->getEntityForRetrieve();
        $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());

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
        if ($sortClause != '') {
            $sortClause = "ORDER BY {$sortClause}";
        }

        $isAggregate = ($this->dbSettings->getAggregationSelect() != null);

        $viewOrTableName = $isAggregate ? $this->dbSettings->getAggregationFrom()
            : $this->handler->quotedEntityName(isset($tableInfo['view']) ? $tableInfo['view'] : $tableName);

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
            : (isset($tableInfo['specify-fields']) ?
                implode(',', array_unique($this->dbSettings->getFieldsRequired())) : "*");
        $groupBy = ($isAggregate && $this->dbSettings->getAggregationGroupBy())
            ? ("GROUP BY " . $this->dbSettings->getAggregationGroupBy()) : "";
        $offset = "OFFSET {$skipParam}";

        if ($isAggregate && !$isPaging) {
            $offset = '';
        } else {
            // Count all records matched with the condtions
            $sql = "SELECT count(*) FROM {$viewOrTableName} {$queryClause} {$groupBy}";
            $this->logger->setDebugMessage($sql);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
                return array();
            }
            $this->mainTableCount = $isAggregate ? $result->rowCount() : $result->fetchColumn(0);

            if ($queryClause === '') {
                $this->mainTableTotalCount = $this->mainTableCount;
            } else {
                // Count all records
                $sql = "SELECT count(*) FROM {$viewOrTableName} {$groupBy}";
                $this->logger->setDebugMessage($sql);
                $result = $this->link->query($sql);
                if ($result === false) {
                    $this->errorMessageStore('Select:' . $sql);
                    return array();
                }
                $this->mainTableTotalCount = $isAggregate ? $result->rowCount() : $result->fetchColumn(0);
            }
        }

        $sql = "SELECT {$fields} FROM {$viewOrTableName} {$queryClause} {$groupBy} {$sortClause} "
            . " LIMIT {$limitParam} {$offset}";
        $this->logger->setDebugMessage($sql);
        $this->queriedEntity = $viewOrTableName;
        $this->queriedCondition = "{$queryClause} {$sortClause} LIMIT {$limitParam} {$offset}";

        // Query
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return array();
        }
        $this->queriedPrimaryKeys = array();
        $keyField = $this->getKeyFieldOfContext($tableInfo);
        $sqlResult = array();
        $isFirstRow = true;
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rowArray = array();
            foreach ($row as $field => $val) {
                if ($isFirstRow) {
                    $this->fieldInfo[] = $field;
                }
                $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
            }
            $sqlResult[] = $rowArray;
            if ($keyField && isset($rowArray[$keyField])) {
                $this->queriedPrimaryKeys[] = $rowArray[$keyField];
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
                        }
                    }
                }
            }
        }
        return $sqlResult;
    }

    /**
     * @param $dataSourceName
     * @return null
     */
    function getFieldInfo($dataSourceName)
    {
        return $this->fieldInfo;
    }

    /**
     * @param $dataSourceName
     * @return int
     */
    public
    function countQueryResult()
    {
        return $this->mainTableCount;
    }

    /**
     * @param $dataSourceName
     * @return int
     */
    public
    function getTotalCount()
    {
        return $this->mainTableTotalCount;
    }

    /**
     * @param $dataSourceName
     * @return bool
     */
    function updateDB()
    {
        $this->fieldInfo = null;
        $tableName = $this->handler->quotedEntityName($this->dbSettings->getEntityForUpdate());
        $fieldInfos = $this->handler->getNullableNumericFields($this->dbSettings->getEntityForUpdate());
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());

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
                $setClause[] = "{$field}=NULL";
            } else {
                $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                $convertedValue = $this->formatter->formatterToDB($filedInForm, $convertedValue);
                $setClause[] = "{$field}=?";
                $setParameter[] = $convertedValue;
            }
        }
        if (count($setClause) < 1) {
            $this->logger->setErrorMessage('No data to update.');
            return false;
        }
        $setClause = implode(',', $setClause);

        $queryClause = $this->getWhereClause('update', false, true, $signedUser);
        if ($queryClause != '') {
            $queryClause = "WHERE {$queryClause}";
        }
        $sql = "{$this->handler->sqlUPDATECommand()}{$tableName} SET {$setClause} {$queryClause}";
        $prepSQL = $this->link->prepare($sql);
        $this->queriedEntity = $tableName;

        $this->logger->setDebugMessage(
            $prepSQL->queryString . " with " . str_replace("\n", " ", var_export($setParameter, true)));

        $result = $prepSQL->execute($setParameter);
        if ($result === false) {
            $this->errorMessageStore('Update:' . $sql);
            return false;
        }

        if ($this->isRequiredUpdated) {
            $targetTable = $this->handler->quotedEntityName($this->dbSettings->getEntityForRetrieve());
            $sql = "SELECT * FROM {$targetTable} {$queryClause}";
            $result = $this->link->query($sql);
            $this->logger->setDebugMessage($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
            } else {
                $this->queriedPrimaryKeys = array();
                $keyField = $this->getKeyFieldOfContext($tableInfo);
                $sqlResult = array();
                $isFirstRow = true;
                foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $rowArray = array();
                    foreach ($row as $field => $val) {
                        if ($isFirstRow) {
                            $this->fieldInfo[] = $field;
                        }
                        $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                        $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
                    }
                    $sqlResult[] = $rowArray;
                    $this->queriedPrimaryKeys[] = $rowArray[$keyField];
                    $isFirstRow = false;
                }
                $this->updatedRecord = $sqlResult;
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
    public
    function createInDB($bypassAuth)
    {
        $this->fieldInfo = null;
        $fieldInfos = $this->handler->getNullableNumericFields($this->dbSettings->getEntityForUpdate());
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->handler->quotedEntityName($this->dbSettings->getEntityForUpdate());
        $viewName = $this->handler->quotedEntityName($this->dbSettings->getEntityForRetrieve());

        if (!$bypassAuth && isset($tableInfo['authentication'])) {
            $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());
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
            } else {
                $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                $convertedValue = (is_array($value)) ? implode("\n", $value) : $value;
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
                    $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                    $convertedValue = (is_array($value)) ? implode("\n", $value) : $value;
                    $setValues[] = $this->link->quote(
                        $this->formatter->formatterToDB($filedInForm, $convertedValue));
                    $setColumnNames[] = $field;
                }
            }
        }
        if (!$bypassAuth && isset($tableInfo['authentication'])) {
            $authInfoField = $this->getFieldForAuthorization("create");
            $authInfoTarget = $this->getTargetForAuthorization("create");
            if ($authInfoTarget == 'field-user') {
                $setColumnNames[] = $authInfoField;
                $setValues[] = $this->link->quote(
                    strlen($signedUser) == 0 ? randomString(10) : $signedUser);
            } else if ($authInfoTarget == 'field-group') {
                $belongGroups = $this->authSupportGetGroupsOfUser($signedUser);
                $setColumnNames[] = $authInfoField;
                $setValues[] = $this->link->quote(
                    strlen($belongGroups[0]) == 0 ? randomString(10) : $belongGroups[0]);
            }
        }

        $keyField = isset($tableInfo['key']) ? $tableInfo['key'] : 'id';
        $setClause = $this->handler->sqlSETClause($setColumnNames, $keyField, $setValues);
        $sql = "{$this->handler->sqlINSERTCommand()}{$tableName} {$setClause}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $seqObject = isset($tableInfo['sequence']) ? $tableInfo['sequence'] : $tableName;
        $lastKeyValue = $this->link->lastInsertId($seqObject);

        $this->queriedPrimaryKeys = array($lastKeyValue);
        $this->queriedEntity = $tableName;

        if ($this->isRequiredUpdated) {
            $sql = "SELECT * FROM " . $viewName
                . " WHERE " . $keyField . "=" . $this->link->quote($lastKeyValue);
            $result = $this->link->query($sql);
            $this->logger->setDebugMessage($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
            } else {
                $sqlResult = array();
                $isFirstRow = true;
                foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $rowArray = array();
                    foreach ($row as $field => $val) {
                        if ($isFirstRow) {
                            $this->fieldInfo[] = $field;
                        }
                        $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                        $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
                    }
                    $sqlResult[] = $rowArray;
                    $isFirstRow = false;
                }
                $this->updatedRecord = $sqlResult;
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
        $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());

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
            $this->errorMessageStore('Don\'t delete with no ciriteria.');
            return false;
        }
        $sql = "{$this->handler->sqlDELETECommand()}FROM {$tableName} WHERE {$queryClause}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if (!$result) {
            $this->errorMessageStore('Delete Error:' . $sql);
            return false;
        }
        $this->queriedEntity = $tableName;

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
        $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());

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
        $lastKeyValue = $this->handler->copyRecords($tableInfo, $queryClause, null, null);
        if ($lastKeyValue === false) {
            return false;
        }
        $this->queriedPrimaryKeys = array($lastKeyValue);
        $this->queriedEntity = $tableName;
        //======
        $assocArray = $this->dbSettings->getAssociated();
        if ($assocArray) {
            foreach ($assocArray as $assocInfo) {
                $assocContextDef = $this->dbSettings->getDataSourceDefinition($assocInfo['name']);
                $queryClause = $this->handler->quotedEntityName($assocInfo["field"]) . "=" .
                    $this->link->quote($assocInfo["value"]);
                $this->handler->copyRecords($assocContextDef, $queryClause, $assocInfo["field"], $lastKeyValue);
            }
        }
        //======
        if ($this->isRequiredUpdated) {
            $sql = "{$this->handler->sqlSELECTCommand()}* FROM " . $tableName
                . " WHERE " . $tableInfo['key'] . "=" . $this->link->quote($lastKeyValue);
            $result = $this->link->query($sql);
            $this->logger->setDebugMessage($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
            } else {
                $sqlResult = array();
                $isFirstRow = true;
                foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $rowArray = array();
                    foreach ($row as $field => $val) {
                        if ($isFirstRow) {
                            $this->fieldInfo[] = $field;
                        }
                        $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                        $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
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

    /**
     * @param $username
     * @param $challenge
     * @param $clientId
     * @return bool
     *
     * Using 'issuedhash'
     */
    function authSupportStoreChallenge($uid, $challenge, $clientId)
    {
        $this->logger->setDebugMessage("[authSupportStoreChallenge] $uid, $challenge, $clientId");

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "select id from {$hashTable} where user_id={$uid} and clienthost=" . $this->link->quote($clientId);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
        $currentDTFormat = IMUtil::currentDTString();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $sql = "{$this->handler->sqlUPDATECommand()}{$hashTable} SET hash=" . $this->link->quote($challenge)
                . ",expired=" . $this->link->quote($currentDTFormat)
                . " WHERE id={$row['id']}";
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('UPDATE:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
            return true;
        }
        $sql = "{$this->handler->sqlINSERTCommand()}{$hashTable} (user_id, clienthost, hash, expired) "
            . "VALUES ({$uid},{$this->link->quote($clientId)},"
            . "{$this->link->quote($challenge)},{$this->link->quote($currentDTFormat)})";
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportStoreChallenge] {$sql}");
        return true;
    }

    /**
     * @param $user
     * @return bool
     *
     * Using 'issuedhash'
     */
    function authSupportCheckMediaToken($uid)
    {
        $this->logger->setDebugMessage("[authSupportCheckMediaToken] {$uid}", 2);

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 0) {
            $uid = 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}id,hash,expired FROM {$hashTable} "
            . "WHERE user_id={$uid} and clienthost=" . $this->link->quote('_im_media');
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportCheckMediaToken] {$sql}");

        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hashValue = $row['hash'];
            $seconds = IMUtil::secondsFromNow($row['expired']);
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return false;
            }
            return $hashValue;
        }
        return false;
    }

    /**
     * @param $username
     * @param $clientId
     * @param bool $isDelete
     * @return bool
     *
     * Using 'issuedhash'
     */
    function authSupportRetrieveChallenge($uid, $clientId, $isDelete = true)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($uid < 1) {
            $uid = 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}id,hash,expired FROM {$hashTable}"
            . " WHERE user_id={$uid} AND clienthost=" . $this->link->quote($clientId)
            . " ORDER BY expired DESC";
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportRetrieveChallenge] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hashValue = $row['hash'];
            $recordId = $row['id'];
            if ($isDelete) {
                $sql = "delete from {$hashTable} where id={$recordId}";
                $result = $this->link->query($sql);
                if ($result === false) {
                    $this->errorMessageStore('Delete:' . $sql);
                    return false;
                }
                $this->logger->setDebugMessage("[authSupportRetrieveChallenge] {$sql}");
            }
            $seconds = IMUtil::secondsFromNow($row['expired']);
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return false;
            }
            return $hashValue;
        }
        return false;
    }

    /**
     * @return bool
     *
     * Using 'issuedhash'
     */
    function authSupportRemoveOutdatedChallenges()
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTStr = $this->link->quote(IMUtil::currentDTString($this->dbSettings->getExpiringSeconds()));
        $sql = "delete from {$hashTable} where expired < {$currentDTStr}";
        $this->logger->setDebugMessage("[authSupportRemoveOutdatedChallenges] {$sql}");
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        return true;
    }

    /**
     * @param $username
     * @param $credential
     * @return bool(true: create user, false: reuse user)|null in error
     */
    function authSupportOAuthUserHandling($keyValues)
    {
        $user_id = $this->authSupportGetUserIdFromUsername($keyValues["username"]);

        $returnValue = null;
        $userTable = $this->dbSettings->getUserTable();
        if (!$this->setupConnection()) { //Establish the connection
            $this->errorMessageStore("PDO class can't set up a connection.");
            return $returnValue;
        }

        $currentDTFormat = $this->link->quote(IMUtil::currentDTString());
        $keys = array("limitdt");
        $values = array($currentDTFormat);
        $updates = array("limitdt=" . $currentDTFormat);
        if (is_array($keyValues)) {
            foreach ($keyValues as $key => $value) {
                $keys[] = $key;
                $values[] = $this->link->quote($value);
                $updates[] = "$key=" . $this->link->quote($value);
            }
        }
        if ($user_id > 0) {
            $returnValue = false;
            $sql = "{$this->handler->sqlUPDATECommand()}{$userTable} SET " . implode(",", $updates)
                . " WHERE id=" . $user_id;
        } else {
            $returnValue = true;
            $sql = "{$this->handler->sqlINSERTCommand()}{$userTable} (" . implode(",", $keys) . ") "
                . "VALUES (" . implode(",", $values) . ")";
        }
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('[authSupportOAuthUserHandling] ' . $sql);
            return $returnValue;
        }
        $this->logger->setDebugMessage("[authSupportOAuthUserHandling] {$sql}");
        return $returnValue;
    }

    /**
     * @param $username
     * @return bool
     *
     * Using 'authuser'
     */
    function authSupportRetrieveHashedPassword($username)
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}hashedpasswd FROM {$userTable} WHERE username=" . $this->link->quote($signedUser);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportRetrieveHashedPassword] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $limitSeconds = $this->dbSettings->getLDAPExpiringSeconds();
            if (isset($row['limitdt']) && !is_null($row['limitdt'])
                && IMUtil::secondsFromNow($row['limitdt']) < $limitSeconds
            ) {
                return false;
            }
            return $row['hashedpasswd'];
        }
        return false;
    }

    /**
     * @param $username
     * @param $hashedpassword
     * @param $isLDAP
     * @return bool
     *
     * Using 'authuser'
     */
    function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($isLDAP !== true) {
            if ($this->authSupportRetrieveHashedPassword($username) !== false) {
                $this->logger->setErrorMessage('User Already exist: ' . $username);
                return false;
            }
            if (!$this->setupConnection()) { //Establish the connection
                return false;
            }
            $sql = "{$this->handler->sqlINSERTCommand()}{$userTable} (username, hashedpasswd) "
                . "VALUES ({$this->link->quote($username)}, {$this->link->quote($hashedpassword)})";
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Insert:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
        } else {
            $user_id = -1;
            $timeUp = false;
            $hpw = null;
            if (!$this->setupConnection()) { //Establish the connection
                return false;
            }

            $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username=" . $this->link->quote($username);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
                return false;
            }
            $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (isset($row['limitdt']) && !is_null($row['limitdt'])) {
                    if (time() - strtotime($row['limitdt']) > $this->dbSettings->getLDAPExpiringSeconds()) {
                        $timeUp = true;
                        $hpw = $row['hashedpasswd'];
                    }
                }
                $user_id = $row['id'];
            }
            $currentDTFormat = IMUtil::currentDTString();
            if ($user_id > 0) {
                $setClause = "limitdt=" . $this->link->quote($currentDTFormat);
                if ($timeUp) {
                    $hexSalt = substr($hpw, -8, 8);
                    $prevPwHash = sha1($ldapPassword . hex2bin($hexSalt)) . $hexSalt;
                    if ($prevPwHash != $hpw) {
                        $setClause .= ",hashedpasswd=" . $this->link->quote($hashedpassword);
                    }
                }
                $sql = "{$this->handler->sqlUPDATECommand()}{$userTable} SET {$setClause} WHERE id=" . $user_id;
                $result = $this->link->query($sql);
                $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
                if ($result === false) {
                    $this->errorMessageStore('Update:' . $sql);
                    return false;
                }
                if ($timeUp) {
                    $this->logger->setDebugMessage("LDAP cached account time over.");
                    return false;
                }
            } else {
                $sql = "{$this->handler->sqlINSERTCommand()}{$userTable} (username, hashedpasswd,limitdt) VALUES "
                    . "({$this->link->quote($username)},"
                    . " {$this->link->quote($hashedpassword)}, "
                    . " {$this->link->quote($currentDTFormat)})";
                $result = $this->link->query($sql);
                if ($result === false) {
                    $this->errorMessageStore('Insert:' . $sql);
                    return false;
                }
                $this->logger->setDebugMessage("[authSupportCreateUser] {$sql}");
            }
        }
        return true;
    }

    /**
     * @param $username
     * @param $hashednewpassword
     * @return bool
     *
     * Using 'authuser'
     */
    function authSupportChangePassword($username, $hashednewpassword)
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($username);

        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd=" . $this->link->quote($hashednewpassword)
            . " WHERE username=" . $this->link->quote($signedUser);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Update:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportChangePassword] {$sql}");
        return true;
    }

    function authTableGetUserIdFromUsername($username)
    {
        return $this->privateGetUserIdFromUsername($username, false);
    }

    function authSupportGetUserIdFromUsername($username)
    {
        return $this->privateGetUserIdFromUsername($username, true);
    }

    private $overLimitDTUser;

    private
    function privateGetUserIdFromUsername($username, $isCheckLimit)
    {
        $this->logger->setDebugMessage("[authSupportGetUserIdFromUsername]username={$username}", 2);

        $this->overLimitDTUser = false;
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($username === 0) {
            return 0;
        }

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$userTable} WHERE username=" . $this->link->quote($username);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[privateGetUserIdFromUsername] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($isCheckLimit && isset($row['limitdt']) && !is_null($row['limitdt'])) {
                if (time() - strtotime($row['limitdt']) > $this->dbSettings->getLDAPExpiringSeconds()) {
                    $this->overLimitDTUser = false;
                }
            }
            return $row['id'];
        }
        return false;
    }


    /**
     * @param $groupid
     * @return bool|null
     *
     * Using 'authgroup'
     */
    function authSupportGetGroupNameFromGroupId($groupid)
    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ($groupTable === null) {
            return null;
        }

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}groupname FROM {$groupTable} WHERE id=" . $this->link->quote($groupid);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportGetGroupNameFromGroupId] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row['groupname'];
        }
        return false;
    }

    /**
     * @param $user
     * @return array|bool
     *
     * Using 'authcor'
     */
    function authSupportGetGroupsOfUser($user)
    {
        $ldap = new LDAPAuth();
        $oAuth = new OAuthAuth();
        if ($ldap->isActive || $oAuth->isActive) {
            return $this->privateGetGroupsOfUser($user, true);
        } else {
            return $this->privateGetGroupsOfUser($user, false);
        }
    }

    function authTableGetGroupsOfUser($user)
    {
        return $this->privateGetGroupsOfUser($user, false);
    }

    private
    function privateGetGroupsOfUser($user, $isCheckLimit)
    {
        $corrTable = $this->dbSettings->getCorrTable();
        if ($corrTable == null) {
            return array();
        }

        $userid = $this->privateGetUserIdFromUsername($user, $isCheckLimit);
        if ($userid === false && $this->dbSettings->getEmailAsAccount()) {
            $userid = $this->authSupportGetUserIdFromEmail($user);
        }

        $this->logger->setDebugMessage("[authSupportGetGroupsOfUser]user={$user}, userid={$userid}");

        if (!$this->setupConnection()) { //Establish the connection
            return false;
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

    /**
     * @var
     */
    private
        $candidateGroups;
    /**
     * @var
     */
    private
        $belongGroups;
    /**
     * @var
     */
    private
        $firstLevel;

    /**
     * @param $groupid
     * @return bool
     *
     * Using 'authcor'
     */
    private
    function resolveGroup($groupid)
    {
        $corrTable = $this->dbSettings->getCorrTable();

        if ($this->firstLevel) {
            $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE user_id = " . $this->link->quote($groupid);
            $this->firstLevel = false;
        } else {
            $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$corrTable} WHERE group_id = " . $this->link->quote($groupid);
            //    $this->belongGroups[] = $groupid;
        }
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->logger->setDebugMessage('Select:' . $sql);
            return false;
        }
        if ($result->columnCount() === 0) {
            return false;
        }
        $this->logger->setDebugMessage("[resolveGroup] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (!in_array($row['dest_group_id'], $this->belongGroups)) {
                $this->belongGroups[] = $row['dest_group_id'];
                $this->resolveGroup($row['dest_group_id']);
            }
        }
    }

    /**
     * @param $tableName
     * @param $userField
     * @param $user
     * @param $keyField
     * @param $keyValue
     * @return bool
     *
     * Using any table.
     */
    function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $user = $this->authSupportUnifyUsernameAndEmail($user);
        $sql = "{$this->handler->sqlSELECTCommand()}* FROM {$tableName} WHERE {$userField}="
            . $this->link->quote($user) . " AND {$keyField}=" . $this->link->quote($keyValue);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportCheckMediaPrivilege] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row;
        }
        return false;
    }

    /**
     * @param $email
     * @return bool|int
     *
     * Using 'authuser'
     */
    function authSupportGetUserIdFromEmail($email)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($email === 0) {
            return 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}id FROM {$userTable} WHERE email=" . $this->link->quote($email);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportGetUserIdFromEmail] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row['id'];
        }
        return false;
    }

    /**
     * @param $userid
     * @return bool|int
     *
     * Using 'authuser'
     */
    function authSupportGetUsernameFromUserId($userid)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if ($userid === 0) {
            return 0;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}username FROM {$userTable} WHERE id=" . $this->link->quote($userid);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportGetUsernameFromUserId] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row['username'];
        }
        return false;
    }

    /**
     * @param $username
     * @return bool|string
     *
     * Using 'authuser'
     */
    function authSupportUnifyUsernameAndEmail($username)
    {
        if (!$this->dbSettings->getEmailAsAccount() || strlen($username) == 0) {
            return $username;
        }
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}username,email FROM {$userTable} WHERE username=" .
            $this->link->quote($username) . " or email=" . $this->link->quote($username);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUnifyUsernameAndEmail] {$sql}");
        $usernameCandidate = '';
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['username'] == $username) {
                $usernameCandidate = $username;
            }
            if ($row['email'] == $username) {
                $usernameCandidate = $row['username'];
            }
//            $limitSeconds = $this->dbSettings->getLDAPExpiringSeconds();
//            if (isset($row['limitdt']) && !is_null($row['limitdt'])
//                && IMUtil::secondsFromNow($row['limitdt']) < $limitSeconds) {
//                return "_im_auth_failed_";
//            }
        }
        return $usernameCandidate;
    }

    /**
     * @param $userid
     * @param $clienthost
     * @param $hash
     * @return bool
     *
     * Using 'issuedhash'
     */
    function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $sql = "{$this->handler->sqlINSERTCommand()}{$hashTable} (hash,expired,clienthost,user_id) VALUES("
            . implode(',', array($this->link->quote($hash), $this->link->quote($currentDTFormat),
                $this->link->quote($clienthost), $this->link->quote($userid))) . ')';
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportStoreIssuedHashForResetPassword] {$sql}");
        return true;
    }


    /**
     * @param $userid
     * @param $randdata
     * @param $hash
     * @return bool
     *
     * Using 'issuedhash'
     */
    public
    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlSELECTCommand()}hash,expired FROM {$hashTable} WHERE"
            . " user_id=" . $this->link->quote($userid)
            . " AND clienthost=" . $this->link->quote($randdata);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportCheckIssuedHashForResetPassword] {$sql}");
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hashValue = $row['hash'];
            if (IMUtil::secondsFromNow($row['expired']) > 3600) {
                return false;
            }
            if ($hash == $hashValue) {
                return true;
            }
        }
        return false;
    }

    public
    function authSupportUserEnrollmentStart($userid, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString();
        $sql = "{$this->handler->sqlINSERTCommand()}{$hashTable} (hash,expired,user_id) VALUES(" . implode(',', array(
                $this->link->quote($hash),
                $this->link->quote($currentDTFormat),
                $this->link->quote($userid))) . ')';
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentStart] {$sql}");
        return true;
    }

    public
    function authSupportUserEnrollmentEnrollingUser($hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $currentDTFormat = IMUtil::currentDTString(3600);
        $sql = "{$this->handler->sqlSELECTCommand()}user_id FROM {$hashTable} WHERE hash = " . $this->link->quote($hash) .
            " AND clienthost IS NULL AND expired > " . $this->link->quote($currentDTFormat);
        $resultHash = $this->link->query($sql);
        if ($resultHash === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentEnrollingUser] {$sql}");
        foreach ($resultHash->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $userID = $row['user_id'];
            if ($userID < 1) {
                return false;
            }
            return $userID;
        }
        return false;
    }

    public
    function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW)
    {
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "{$this->handler->sqlUPDATECommand()}{$userTable} SET hashedpasswd=" . $this->link->quote($password)
            . (($rawPWField !== false) ? "," . $rawPWField . "=" . $this->link->quote($rawPW) : "")
            . " WHERE id=" . $this->link->quote($userID);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Update:' . $sql);
            return false;
        }
        $this->logger->setDebugMessage("[authSupportUserEnrollmentActivateUser] {$sql}");
        return $userID;
    }

    public
    function normalizedCondition($condition)
    {
        if (!isset($condition['field'])) {
            $condition['field'] = '';
        }
        if (!isset($condition['value'])) {
            $condition['value'] = '';
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
        }
        return $condition;
    }

    public
    function isContainingFieldName($fname, $fieldnames)
    {
        return in_array($fname, $fieldnames);
    }

    public
    function isNullAcceptable()
    {
        return true;
    }

    public
    function queryForTest($table, $conditions = null)
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
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $oneRecord = array();
            foreach ($row as $field => $value) {
                $oneRecord[$field] = $value;
            }
            $recordSet[] = $oneRecord;
        }
        return $recordSet;
    }

    public
    function deleteForTest($table, $conditions = null)
    {
        if ($table == null) {
            $this->errorMessageStore("The table doesn't specified.");
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            $this->errorMessageStore("Can't open db connection.");
            return false;
        }
        $sql = "{$this->handler->sqlDELETECommand()}FROM " . $this->handler->quotedEntityName($table);
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

    public
    function isSupportAggregation()
    {
        return true;
    }
}
