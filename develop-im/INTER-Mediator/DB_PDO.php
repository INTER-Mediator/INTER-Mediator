<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

/**
 * Class DB_PDO
 */
class DB_PDO extends DB_AuthCommon implements DB_Access_Interface
{
    /**
     * @var null
     */
    private $link = null;
    /**
     * @var int
     */
    private $mainTableCount = 0;
    /**
     * @var null
     */
    private $fieldInfo = null;

    private $isAlreadySetup = false;

    /**
     * @param $str
     */
    private function errorMessageStore($str)
    {
        $errorInfo = var_export($this->link->errorInfo(), true);
        $this->logger->setErrorMessage("Query Error: [{$str}] Code={$this->link->errorCode()} Info ={$errorInfo}");
    }

    /**
     * @return bool
     */
    public function setupConnection()
    {
        if ( $this->isAlreadySetup )    {
            return true;
        }
        try {
            $this->link = new PDO($this->dbSettings->getDbSpecDSN(),
                $this->dbSettings->getDbSpecUser(),
                $this->dbSettings->getDbSpecPassword(),
                is_array($this->dbSettings->getDbSpecOption()) ? $this->dbSettings->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('Connection Error: ' . $ex->getMessage());
            return false;
        }
        $this->isAlreadySetup = true;
        return true;
    }

    public function setupWithDSN($dsnString)
    {
        if ( $this->isAlreadySetup )    {
            return true;
        }
        try {
            $this->link = new PDO($dsnString);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('Connection Error: ' . $ex->getMessage());
            return false;
        }
        $this->isAlreadySetup = true;
        return true;
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
                    if (isset($condition['value']) && $condition['value'] != null) {
                        $escapedValue = $this->link->quote($condition['value']);
                        if (isset($condition['operator'])) {
                            $queryClauseArray[$chunkCount][]
                                = "{$condition['field']} {$condition['operator']} {$escapedValue}";
                        } else {
                            $queryClauseArray[$chunkCount][]
                                = "{$condition['field']} = {$escapedValue}";
                        }
                    } else {
                        $queryClauseArray[$chunkCount][]
                            = "{$condition['field']} {$condition['operator']}";
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
                if ($condition['field'] == '__operation__') {
                    $chunkCount++;
                    if ($condition['operator'] == 'ex') {
                        $insideOp = ' OR ';
                        $outsideOp = ' AND ';
                    }
                } else if (!$this->dbSettings->getPrimaryKeyOnly() || $condition['field'] == $primaryKey) {
                    if (isset($condition['value']) && $condition['value'] != null) {
                        $escapedValue = $this->link->quote($condition['value']);
                        if (isset($condition['operator'])) {
                            $queryClauseArray[$chunkCount][]
                                = "{$condition['field']} {$condition['operator']} {$escapedValue}";
                        } else {
                            $queryClauseArray[$chunkCount][]
                                = "{$condition['field']} = {$escapedValue}";
                        }
                    } else {
                        $queryClauseArray[$chunkCount][]
                            = "{$condition['field']} {$condition['operator']}";
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
                        $escapedValue = $this->link->quote($foreignDef['value']);
                        $op = isset($relDef['operator']) ? $relDef['operator'] : '=';
                        $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                            . "({$relDef['foreign-key']}{$op}{$escapedValue})";
                    }
                }
            }
        }

        if (isset($tableInfo['authentication'])) {
            $this->logger->setDebugMessage("#####".var_export($tableInfo['authentication'],true));
        }

        $keywordAuth = ($currentOperation == "select") ? "load" : $currentOperation;
        if (isset($tableInfo['authentication'])
            && (isset($tableInfo['authentication']['all']) || isset($tableInfo['authentication'][$keywordAuth]))
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
                if (isset($condition['direction'])) {
                    $sortClause[] = "{$condition['field']} {$condition['direction']}";
                } else {
                    $sortClause[] = "{$condition['field']}";
                }
            }
        }
        if (isset($tableInfo['sort'])) {
            foreach ($tableInfo['sort'] as $condition) {
                $sortClause[] = "{$condition['field']} {$condition['direction']}";
            }
        }
        return implode(',', $sortClause);
    }

    /**
     * @param $dataSourceName
     * @return array|bool
     */
    function getFromDB($dataSourceName)
    {
        $this->fieldInfo = null;
        $this->mainTableCount = 0;
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->dbSettings->getEntityForRetrieve();
        $signedUser = $this->authSupportUnifyUsernameAndEmail($this->dbSettings->getCurrentUser());

        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'load') {
                    if ($condition['situation'] == 'pre') {
                        $sql = $condition['definition'];
                        $this->logger->setDebugMessage($sql);
                        $result = $this->link->query($sql);
                        if ($result === false) {
                            $this->errorMessageStore('Pre-script:' + $sql);
                        }
                    }
                }
            }
        }

        $viewOrTableName = isset($tableInfo['view']) ? $tableInfo['view'] : $tableName;

        $queryClause = $this->getWhereClause('load', true, true, $signedUser);
        if ($queryClause != '') {
            $queryClause = "WHERE {$queryClause}";
        }
        $sortClause = $this->getSortClause();
        if ($sortClause != '') {
            $sortClause = "ORDER BY {$sortClause}";
        }

        // Count all records matched with the condtions
        $sql = "SELECT count(*) FROM {$viewOrTableName} {$queryClause}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return array();
        }
        $this->mainTableCount = $result->fetchColumn(0);

        // Create SQL
        $limitParam = 100000000;
        if ($this->dbSettings->getRecordCount() > 0) {
            $limitParam = $this->dbSettings->getRecordCount();
        }
        if (isset($tableInfo['records'])) {
            $limitParam = $tableInfo['records'];
        }
        $skipParam = 0;
        if (isset($tableInfo['paging']) and $tableInfo['paging'] == true) {
            $skipParam = $this->dbSettings->getStart();
        }
        $fields = '*';
        if (isset($tableInfo['specify-fields'])) {
            $fields = implode(',', array_unique($this->dbSettings->getFieldsRequired()));
        }
        $sql = "SELECT {$fields} FROM {$viewOrTableName} {$queryClause} {$sortClause} "
            . " LIMIT {$limitParam} OFFSET {$skipParam}";
        $this->logger->setDebugMessage($sql);

        // Query
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return array();
        }
        $sqlResult = array();
        $isFirstRow = true;
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rowArray = array();
            foreach ($row as $field => $val) {
                if ( $isFirstRow )  {
                    $this->fieldInfo[] = $field;
                }
                $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                $rowArray[$field] = $this->formatter->formatterFromDB($filedInForm, $val);
            }
            $sqlResult[] = $rowArray;
            $isFirstRow = false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'load') {
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
    function countQueryResult($dataSourceName)
    {
        return $this->mainTableCount;
    }

    /**
     * @param $dataSourceName
     * @return bool
     */
    function setToDB($dataSourceName)
    {
        $this->fieldInfo = null;
        $tableName = $this->dbSettings->getEntityForUpdate();
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
            $convertedValue = $this->formatter->formatterToDB($field, $convertedValue);
            $setClause[] = "{$field}=?";
            $setParameter[] = $convertedValue;
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
        $sql = "UPDATE {$tableName} SET {$setClause} {$queryClause}";
        $prepSQL = $this->link->prepare($sql);

        $this->logger->setDebugMessage(
            $prepSQL->queryString . " with " . str_replace("\n", " ", var_export($setParameter, true)));

        $result = $prepSQL->execute($setParameter);
        if ($result === false) {
            $this->errorMessageStore('Update:' + $prepSQL->erroInfo);
            return false;
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
    function newToDB($dataSourceName, $bypassAuth)
    {
        $this->fieldInfo = null;
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->dbSettings->getEntityForUpdate();

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
                if ($condition['db-operation'] == 'new' && $condition['situation'] == 'pre') {
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
            $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
            $convertedValue = (is_array($value)) ? implode("\n", $value) : $value;
            $setValues[] = $this->link->quote($this->formatter->formatterToDB($filedInForm, $convertedValue));
            $setColumnNames[] = $field;
        }
        if (isset($tableInfo['default-values'])) {
            foreach ($tableInfo['default-values'] as $itemDef) {
                $field = $itemDef['field'];
                $value = $itemDef['value'];
                $filedInForm = "{$tableName}{$this->dbSettings->getSeparator()}{$field}";
                $convertedValue = (is_array($value)) ? implode("\n", $value) : $value;
                $setValues[] = $this->link->quote($this->formatter->formatterToDB($filedInForm, $convertedValue));
                $setColumnNames[] = $field;
            }
        }
        if (!$bypassAuth && isset($tableInfo['authentication'])) {
            $authInfoField = $this->getFieldForAuthorization("new");
            $authInfoTarget = $this->getTargetForAuthorization("new");
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

        if (strpos($this->dbSettings->getDbSpecDSN(), 'mysql:') === 0) { /**/
            $keyField = isset($tableInfo['key']) ? $tableInfo['key'] : 'id';
            $setClause = (count($setColumnNames) == 0) ? "SET {$keyField}=DEFAULT" :
                '(' . implode(',', $setColumnNames) . ') VALUES(' . implode(',', $setValues) . ')';
        } else { // sqlite, pgsql
            $setClause = (count($setColumnNames) == 0) ? "DEFAULT VALUES" :
                '(' . implode(',', $setColumnNames) . ') VALUES(' . implode(',', $setValues) . ')';
        }
        $sql = "INSERT INTO {$tableName} {$setClause}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $seqObject = isset($tableInfo['sequence']) ? $tableInfo['sequence'] : $tableName;
        $lastKeyValue = $this->link->lastInsertId($seqObject);

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'new' && $condition['situation'] == 'post') {
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
    function deleteFromDB($dataSourceName)
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
        $sql = "DELETE FROM {$tableName} WHERE {$queryClause}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if (!$result) {
            $this->errorMessageStore('Delete Error:' . $sql);
            return false;
        }

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

    /**
     * @param $username
     * @param $challenge
     * @param $clientId
     * @return bool
     *
     * Using 'issuedhash'
     */
    function authSupportStoreChallenge($username, $challenge, $clientId)
    {

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($username === 0) {
            $uid = 0;
        } else {
            $signedUser = $this->authSupportUnifyUsernameAndEmail($username);
            $uid = $this->authSupportGetUserIdFromUsername($signedUser);
            if ($uid === false) {
                $this->logger->setDebugMessage("[authSupportStoreChallenge] User '{$signedUser}' does't exist.");
                return false;
            }
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

        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('c');
 //       $currentDTFormat = $currentDT->format('Y-m-d H:i:s');

        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $sql = "UPDATE {$hashTable} SET hash=" . $this->link->quote($challenge)
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
//        $sql = "insert {$hashTable} set user_id={$uid},clienthost="
//            . $this->link->quote($clientId)
//            . ",hash=" . $this->link->quote($challenge)
//            . ",expired=" . $this->link->quote($currentDTFormat);
        $sql = "INSERT INTO {$hashTable} (user_id, clienthost, hash, expired) "
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
    function authSupportCheckMediaToken($user)
    {
        $signedUser = $this->authSupportUnifyUsernameAndEmail($user);

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($signedUser === 0) {
            $uid = 0;
        } else {
            $uid = $this->authSupportGetUserIdFromUsername($signedUser);
            if ($uid === false) {
                $this->logger->setDebugMessage("[authSupportCheckMediaToken] User '{$signedUser}' does't exist.");
                return false;
            }
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT id,hash,expired FROM {$hashTable} "
            . "WHERE user_id={$uid} and clienthost=" . $this->link->quote('_im_media');
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $expiredDT = new DateTime($row['expired']);
            $hashValue = $row['hash'];
            // For 5.3
//            $intervalDT = $expiredDT->diff(new DateTime(), true);
//            $seconds = (($intervalDT->days * 24 + $intervalDT->h) * 60 + $intervalDT->i) * 60 + $intervalDT->s;
            // For 5.2
            $currentDT = new DateTime();
            $seconds = $currentDT->format("U") - $expiredDT->format("U");
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
    function authSupportRetrieveChallenge($username, $clientId, $isDelete = true)
    {
//        $this->logger->setDebugMessage("[authSupportRetrieveChallenge] username={$username}.");

        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if ($username === 0) {
            $uid = 0;
        } else {
            $signedUser = $this->authSupportUnifyUsernameAndEmail($username);
            $uid = $this->authSupportGetUserIdFromUsername($signedUser);
            if ($uid === false) {
                $this->logger->setDebugMessage("[authSupportRetrieveChallenge] User '{$signedUser}' does't exist.");
                return false;
            }
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT id,hash,expired FROM {$hashTable} "
            . "WHERE user_id={$uid} AND clienthost=" . $this->link->quote($clientId) . " ORDER BY expired DESC";
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }

//        $this->logger->setDebugMessage("[authSupportRetrieveChallenge] sql={$sql}.");

        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $expiredDT = new DateTime($row['expired']);
            $hashValue = $row['hash'];
            $recordId = $row['id'];
            if ($isDelete) {
                $sql = "delete from {$hashTable} where id={$recordId}";
                $result = $this->link->query($sql);
                if ($result === false) {
                    $this->errorMessageStore('Delete:' . $sql);
                    return false;
                }
            }
            // For 5.3
//            $intervalDT = $expiredDT->diff(new DateTime(), true);
//            $seconds = (($intervalDT->days * 24 + $intervalDT->h) * 60 + $intervalDT->i) * 60 + $intervalDT->s;
            // For 5.2
            $currentDT = new DateTime();
            $seconds = $currentDT->format("U") - $expiredDT->format("U");
            // End of version blanching.
            if ($seconds > $this->dbSettings->getExpiringSeconds()) { // Judge timeout.
                return false;
            }
//            $this->logger->setDebugMessage("[authSupportRetrieveChallenge] return={$hashValue}.");
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

        $currentDT = new DateTime();
        // sub method and DateInterval class work on over 5.3
        //    $currentDT->sub(new DateInterval("PT" . $this->dbSettings->getExpiringSeconds() . "S"));
//        $currentDTStr = $this->link->quote($currentDT->format('Y-m-d H:i:s'));

        // For 5.2
        $timeValue = $currentDT->format("U");
        $currentDTStr = $this->link->quote(date('Y-m-d H:i:s', $timeValue - $this->dbSettings->getExpiringSeconds()));
        // End of for 5.2
        $sql = "delete from {$hashTable} where expired < {$currentDTStr}";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        return true;
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
        $sql = "SELECT hashedpasswd FROM {$userTable} WHERE username=" . $this->link->quote($signedUser);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return $row['hashedpasswd'];
        }
        return false;
    }

//    function authSupportGetSalt($username)
//    {
//        $hashedpw = $this->authSupportRetrieveHashedPassword($username);
//        return substr($hashedpw, -8);
//    }
//
    /**
     * @param $username
     * @param $hashedpassword
     * @return bool
     *
     * Using 'authuser'
     */
    function authSupportCreateUser($username, $hashedpassword)
    {
        if ($this->authSupportRetrieveHashedPassword($username) !== false) {
            $this->logger->setErrorMessage('User Already exist: ' . $username);
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $userTable = $this->dbSettings->getUserTable();
        $sql = "INSERT INTO {$userTable} (username, hashedpasswd) "
            . "VALUES ({$this->link->quote($username)}, {$this->link->quote($hashedpassword)})";
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
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
        $sql = "UPDATE {$userTable} SET hashedpasswd=" . $this->link->quote($hashednewpassword)
            . " WHERE username=" . $this->link->quote($signedUser);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Update:' . $sql);
            return false;
        }
        return true;
    }

    /**
     * @param $username
     * @return bool|int
     *
     * Using 'authuser'
     */
    function authSupportGetUserIdFromUsername($username)
    {
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
        $sql = "SELECT id FROM {$userTable} WHERE username=" . $this->link->quote($username);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
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
        $sql = "SELECT groupname FROM {$groupTable} WHERE id=" . $this->link->quote($groupid);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
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
        $corrTable = $this->dbSettings->getCorrTable();
        if ($corrTable == null) {
            return array();
        }

        $userid = $this->authSupportGetUserIdFromUsername($user);
        if ($userid === false && $this->dbSettings->getEmailAsAccount()) {
            $userid = $this->authSupportGetUserIdFromEmail($user);
        }
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
    private $candidateGroups;
    /**
     * @var
     */
    private $belongGroups;
    /**
     * @var
     */
    private $firstLevel;

    /**
     * @param $groupid
     * @return bool
     *
     * Using 'authcor'
     */
    private function resolveGroup($groupid)
    {
        $corrTable = $this->dbSettings->getCorrTable();

        if ($this->firstLevel) {
            $sql = "SELECT * FROM {$corrTable} WHERE user_id = " . $this->link->quote($groupid);
            $this->firstLevel = false;
        } else {
            $sql = "SELECT * FROM {$corrTable} WHERE group_id = " . $this->link->quote($groupid);
            //    $this->belongGroups[] = $groupid;
        }
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->logger->setDebugMessage('Select:' . $sql);
            return false;
        }
        if ($result->columnCount() === 0) {
            return false;
        }
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
        $sql = "SELECT groupname FROM {$tableName} WHERE {$userField}="
            . $this->link->quote($user) . " AND {$keyField}=" . $this->link->quote($keyValue);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            return true;
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
        $sql = "SELECT id FROM {$userTable} WHERE email=" . $this->link->quote($email);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
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
        $sql = "SELECT id FROM {$userTable} WHERE id=" . $this->link->quote($userid);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
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
        if (! $this->dbSettings->getEmailAsAccount() || strlen($username) == 0)  {
            return $username;
        }
        $userTable = $this->dbSettings->getUserTable();
        if ($userTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT username,email FROM {$userTable} WHERE username=" .
            $this->link->quote($username) . " or email=" . $this->link->quote($username);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $usernameCandidate = '';
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['username'] == $username) {
                $usernameCandidate = $username;
            }
            if ($row['email'] == $username) {
                $usernameCandidate = $row['username'];
            }
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
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('Y-m-d H:i:s');
        $sql = "INSERT INTO {$hashTable} (hash,expired,clienthost,user_id) VALUES("
            . implode(',', array($this->link->quote($hash), $this->link->quote($currentDTFormat),
                $this->link->quote($clienthost), $this->link->quote($userid))) . ')';
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
        }
        return false;
    }


    /**
     * @param $userid
     * @param $randdata
     * @param $hash
     * @return bool
     *
     * Using 'issuedhash'
     */
    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        $sql = "SELECT hash,expired FROM {$hashTable} WHERE"
            . " user_id=" . $this->link->quote($userid)
            . " AND clienthost=" . $this->link->quote($randdata);
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $hashValue = $row['hash'];
            $expiredDT = $row['expired'];

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
        }
        return false;
    }

    function authSupportUserEnrollmentStart($userid, $hash)
    {
        $hashTable = $this->dbSettings->getHashTable();
        if ($hashTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        // For PHP 5.3
//        $currentDT = new DateTime();
//        $currentDT->add(new DateInterval('P1H'));
//        $currentDTFormat = $currentDT->format('Y-m-d H:i:s');

        // For PHP 5.2
        $currentDT = time() + 3600;
        $currentDTFormat = date('Y-m-d H:i:s', $currentDT);

        $sql = "INSERT INTO {$hashTable} (hash,expired,user_id) VALUES(" . implode(',', array(
                $this->link->quote($hash),
                $this->link->quote($currentDTFormat),
                $this->link->quote($userid))) . ')';
        $this->logger->setDebugMessage($sql);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        return true;
    }

    function authSupportUserEnrollmentActivateUser($hash, $password)
    {
        $hashTable = $this->dbSettings->getHashTable();
        $userTable = $this->dbSettings->getUserTable();
        if ($hashTable == null || $userTable == null) {
            return false;
        }
        if (!$this->setupConnection()) { //Establish the connection
            return false;
        }
        // For PHP 5.3
//        $currentDT = new DateTime();
//        $currentDT->add(new DateInterval('P1H'));
//        $currentDTFormat = $currentDT->format('Y-m-d H:i:s');

        // For PHP 5.2
        $currentDT = time();
        $currentDTFormat = date('Y-m-d H:i:s', $currentDT);

        $sql = "SELECT user_id FROM {$hashTable} WHERE hash = " . $this->link->quote($hash) .
            " AND clienthost IS NULL AND expired > " . $this->link->quote($currentDTFormat);
        $this->logger->setDebugMessage($sql);
        $resultHash = $this->link->query($sql);
        if ($resultHash === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        foreach ($resultHash->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $userID = $row['user_id'];
            if ($userID < 1 )   {
                return false;
            }
            $resultArray = array('user_id'=>$userID);
            $sql = "UPDATE {$userTable} SET hashedpasswd=" . $this->link->quote($password)
                . " WHERE id=" . $this->link->quote($userID);
            $this->logger->setDebugMessage($sql);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Update:' . $sql);
                return false;
            }
            $sql = "SELECT email,realname FROM {$userTable} WHERE id=" . $this->link->quote($userID);
            $this->logger->setDebugMessage($sql);
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
                return false;
            }
            foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $userRow) {
                $resultArray['email'] = $userRow['email'];
                $resultArray['realname'] = $userRow['realname'];
                return $resultArray;
            }
        }
        return false;
    }
}

?>