<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

class DB_PDO extends UseSharedObjects implements Auth_Interface_DB
{
    var $link = null;
    var $mainTableCount = 0;

    private function errorMessageStore($str)
    {
        $errorInfo = var_export($this->link->errorInfo(), true);
        $this->logger->setErrorMessage("Query Error: [{$str}] Code={$this->link->errorCode()} Info ={$errorInfo}");
    }

    private function setupConnection()  {
        try {
            $this->link = new PDO($this->dbSettings->getDbSpecDSN(),
                $this->dbSettings->getDbSpecUser(),
                $this->dbSettings->getDbSpecPassword(),
                is_array($this->dbSettings->getDbSpecOption()) ? $this->dbSettings->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage( 'Connection Error: ' . $ex->getMessage() );
            return false;
        }
        return true;
    }

    /*
     * Generate SQL style WHERE clause.
     */
    private function getWhereClause( $currentOperation, $includeContext = true, $includeExtra = true )
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $queryClause = '';

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
                } else {
                    if (isset($condition['value'])) {
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
        if ($includeExtra && isset($this->dbSettings->extraCriteria[0])) {
            $chunkCount = 0;
            $oneClause = array();
            $insideOp = ' AND ';
            $outsideOp = ' OR ';
            foreach ($this->dbSettings->extraCriteria as $condition) {
                if ($condition['field'] == '__operation__') {
                    $chunkCount++;
                    if ($condition['operator'] == 'ex') {
                        $insideOp = ' OR ';
                        $outsideOp = ' AND ';
                    }
                } else {
                    if (isset($condition['value'])) {
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
            $queryClause = ($queryClause == '' ? '' : "($queryClause) AND " )
                . '(' . implode($outsideOp, $oneClause) . ')';
        }

        if (count($this->dbSettings->foreignFieldAndValue) > 0) {
            foreach ($this->dbSettings->foreignFieldAndValue as $foreignDef) {
                foreach ($tableInfo['relation'] as $relDef) {
                    if ($relDef['foreign-key'] == $foreignDef['field']) {
                        $escapedValue = $this->link->quote($foreignDef['value']);
                        if (isset($relDef['operator'])) {
                            $op = $relDef['operator'];
                        } else {
                            $op = 'eq';
                        }
                        $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                            . "({$foreignDef['field']}{$op}{$escapedValue})";
                    }
                }
            }
        }

        //$currentOperation = 'load';
        if ( isset( $tableInfo['authentication'] )) {
            $authInfoField = $this->getFieldForAuthorization( $currentOperation );
            $authInfoTarget = $this->getTargetForAuthorization( $currentOperation );
            if ( $authInfoTarget == 'field-user' ) {
                if ( strlen( $this->dbSettings->currentUser ) == 0 )    {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "({$authInfoField}=" . $this->link->quote( $this->dbSettings->currentUser ) . ")";
                }
            } else if ( $authInfoTarget == 'field-group' ) {
                $belongGroups = $this->getGroupsOfUser( $this->dbSettings->currentUser );
                $groupCriteria = array();
                foreach ( $belongGroups as $oneGroup )  {
                    $groupCriteria[] = "{$authInfoField}=" . $this->link->quote( $oneGroup );
                }
                if ( strlen( $this->dbSettings->currentUser ) == 0 || count( $groupCriteria ) == 0 )    {
                    $queryClause = 'FALSE';
                } else {
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "(" . implode( ' OR ', $groupCriteria ) . ")";
                }
            } else {
                $authorizedUsers = $this->getAuthorizedUsers( $currentOperation );
                $authorizedGroups = $this->getAuthorizedGroups( $currentOperation );
                $belongGroups = $this->getGroupsOfUser( $this->dbSettings->currentUser );
                if ( ! in_array( $this->dbSettings->currentUser, $authorizedUsers )
                    && array_intersect( $belongGroups, $authorizedGroups )) {
                    $queryClause = 'FALSE';
                }
            }
        }
        return $queryClause;
    }


    /* Genrate SQL Sort and Where clause */
    private function getSortClause()
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $sortClause = array();
        if (count($this->dbSettings->extraSortKey)>0) {
            foreach ($this->dbSettings->extraSortKey as $condition) {
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

    function getFromDB($dataSourceName)
    {
        $this->mainTableCount = 0;
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->dbSettings->getEntityForRetrieve();

        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'load') {
                    if ($condition['situation'] == 'pre') {
                        $sql = $condition['definition'];
                        $this->logger->setDebugMessage( $sql );
                        $result = $this->link->query($sql);
                        if ($result === false) {
                            $this->errorMessageStore('Pre-script:' + $sql);
                        }
                    }
                }
            }
        }

        $viewOrTableName = isset($tableInfo['view']) ? $tableInfo['view'] : $tableName;

        $queryClause = $this->getWhereClause( 'load', true, true );
        if ($queryClause != '') {
            $queryClause = "WHERE {$queryClause}";
        }
        $sortClause = $this->getSortClause();
        if ($sortClause != '') {
            $sortClause = "ORDER BY {$sortClause}";
        }

        // Count all records matched with the condtions
        $sql = "SELECT count(*) FROM {$viewOrTableName} {$queryClause}";
        $this->logger->setDebugMessage( $sql );
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return array();
        }
        $this->mainTableCount = $result->fetchColumn(0);

        // Create SQL
        $limitParam = 100000000;
        if (isset($tableInfo['records'])) {
            $limitParam = $tableInfo['records'];
        }
        if ($this->dbSettings->recordCount > 0) {
            $limitParam = $this->dbSettings->recordCount;
        }
        $skipParam = 0;
        if (isset($tableInfo['paging']) and $tableInfo['paging'] == true) {
            $skipParam = $this->dbSettings->start;
        }
        $sql = "SELECT * FROM {$viewOrTableName} {$queryClause} {$sortClause} "
            . " LIMIT {$limitParam} OFFSET {$skipParam}";
        $this->logger->setDebugMessage( $sql );

        // Query
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return array();
        }
        $sqlResult = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rowArray = array();
            foreach ($row as $field => $val) {
                $filedInForm = "{$tableName}{$this->dbSettings->separator}{$field}";
                $rowArray[$field] = $this->dbSettings->formatterFromDB($filedInForm, $val);
            }
            $sqlResult[] = $rowArray;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'load') {
                    if ($condition['situation'] == 'post') {
                        $sql = $condition['definition'];
                        $this->logger->setDebugMessage( $sql );
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

    function countQueryResult($dataSourceName)
    {
        return $this->mainTableCount;
    }

    function setToDB($dataSourceName)
    {
        $tableName = $this->dbSettings->getEntityForUpdate();
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        if (isset($tableInfo['script'])) {
            foreach($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'update' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage( $sql );
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
        foreach ($this->dbSettings->fieldsRequired as $field) {
            $value = $this->dbSettings->fieldsValues[$counter];
            $counter++;
            $convVal = (is_array($value)) ? implode("\n", $value) : $value;
            $convVal = $this->dbSettings->formatterToDB($field, $convVal);
            $setClause[] = "{$field}=?";
            $setParameter[] = $convVal;
        }
        if (count($setClause) < 1) {
            $this->logger->setErrorMessage( 'No data to update.' );
            return false;
        }
        $setClause = implode(',', $setClause);

        $queryClause = $this->getWhereClause( 'update', false, true );
        if ($queryClause != '') {
            $queryClause = "WHERE {$queryClause}";
        }
        $sql = "UPDATE {$tableName} SET {$setClause} {$queryClause}";
        $prepSQL = $this->link->prepare($sql);
        $this->logger->setDebugMessage( $prepSQL->queryString . " with " .
            str_replace("\n"," ", var_export($setParameter, true)) );
        $result = $prepSQL->execute($setParameter);
        if ($result === false) {
            $this->errorMessageStore('Update:' + $prepSQL->erroInfo);
            return false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'update' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage( $sql );
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

    function newToDB($dataSourceName)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->dbSettings->getEntityForUpdate();

        $setClause = array();
        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'new' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage( $sql );
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Pre-script:' . $sql);
                        return false;
                    }
                }
            }
        }

        $countFields = count($this->dbSettings->fieldsRequired);
        for ($i = 0; $i < $countFields; $i++) {
            $field = $this->dbSettings->fieldsRequired[$i];
            $value = $this->dbSettings->fieldsValues[$i];
            $filedInForm = "{$tableName}{$this->dbSettings->separator}{$field}";
            $convVal = (is_array($value)) ? implode("\n", $value) : $value;
            $convVal = $this->link->quote($this->dbSettings->formatterToDB($filedInForm, $convVal));
            $setClause[] = "{$field}={$convVal}";
        }
        if (isset($tableInfo['default-values'])) {
            foreach( $tableInfo['default-values'] as $itemDef ) {
                $field = $itemDef['field'];
                $value = $itemDef['value'];
                $filedInForm = "{$tableName}{$this->dbSettings->separator}{$field}";
                $convVal = (is_array($value)) ? implode("\n", $value) : $value;
                $convVal = $this->link->quote($this->dbSettings->formatterToDB($filedInForm, $convVal));
                $setClause[] = "{$field}={$convVal}";
            }
        }
        if ( isset( $tableInfo['authentication'] )) {
            $authInfoField = $this->getFieldForAuthorization( "new" );
            $authInfoTarget = $this->getTargetForAuthorization( "new" );
            if ( $authInfoTarget == 'field-user' ) {
                $setClause[] = "{$authInfoField}=" . $this->link->quote(
                    strlen($this->dbSettings->currentUser)==0 ? randomString(10) : $this->dbSettings->currentUser );
            } else if ( $authInfoTarget == 'field-group' ) {
                $belongGroups = $this->getGroupsOfUser( $this->dbSettings->currentUser );
                $setClause[] = "{$authInfoField}=" . $this->link->quote(
                    strlen($belongGroups[0])==0 ? randomString(10) : $belongGroups[0] );
            }
        }

        $setClause = (count($setClause) == 0) ? "{$tableInfo['key']}=DEFAULT" : implode(',', $setClause);
        $sql = "INSERT {$tableName} SET {$setClause}";
        $this->logger->setDebugMessage( $sql );
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
        }
        $lastKeyValue = $this->link->lastInsertId($tableInfo['key']);

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'new' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage( $sql );
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

    function deleteFromDB($dataSourceName)
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $tableName = $this->dbSettings->getEntityForUpdate();

        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'delete' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage( $sql );
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Pre-script:' . $sql);
                        return false;
                    }
                }
            }
        }
        $queryClause = $this->getWhereClause( 'delete', false, true );
        if ($queryClause == '') {
            $this->errorMessageStore('Don\'t delete with no ciriteria.');
            return false;
        }
        $sql = "DELETE FROM {$tableName} WHERE {$queryClause}";
        $this->logger->setDebugMessage( $sql );
        $result = $this->link->query($sql);
        if (!$result) {
            $this->errorMessageStore('Delete Error:' . $sql);
            return false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'delete' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->logger->setDebugMessage( $sql );
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

    function authSupportStoreChallenge($username, $challenge, $clientId)   {
        $hashTable = $this->dbSettings->getHashTable();
        if ( $hashTable == null )   {
            return false;
        }
        if ( $username === 0 )   {
            $uid = 0;
        } else {
            $uid = $this->authSupportGetUserIdFromUsername($username);
            if ( $uid === false )   {
                $this->logger->setDebugMessage("User '{$username}' does't exist.");
                return false;
            }
        }
        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        $sql = "select id from {$hashTable} where user_id={$uid} and clienthost=" . $this->link->quote($clientId);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('c');

        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $sql = "update {$hashTable} set hash=" . $this->link->quote($challenge)
                . ",expired=" . $this->link->quote($currentDTFormat)
                . "where id={$row['id']}";
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Select:' . $sql);
                return false;
            }
            return true;
        }
        $sql = "insert {$hashTable} set user_id={$uid},clienthost="
            . $this->link->quote($clientId)
            . ",hash=" . $this->link->quote($challenge)
            . ",expired=" . $this->link->quote($currentDTFormat);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        return true;
    }

    function authSupportRetrieveChallenge($username, $clientId)    {
        $hashTable = $this->dbSettings->getHashTable();
        if ( $hashTable == null )   {
            return false;
        }
        if ( $username === 0 )   {
            $uid = 0;
        } else {
            $uid = $this->authSupportGetUserIdFromUsername($username);
            if ( $uid === false )   {
                $this->logger->setDebugMessage("User '{$username}' does't exist.");
                return false;
            }
        }
        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        $sql = "select id,hash,expired from {$hashTable} "
            . "where user_id={$uid} and clienthost=" . $this->link->quote($clientId);
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $expiredDT = new DateTime($row['expired']);
            $hashValue = $row['hash'];
            $recordId = $row['id'];

            $sql = "delete from {$hashTable} where id={$recordId}";
            $result = $this->link->query($sql);
            if ($result === false) {
                $this->errorMessageStore('Delete:' . $sql);
                return false;
            }

            $intervalDT = $expiredDT->diff(new DateTime(), true);
            $seconds = (( $intervalDT->days * 24 + $intervalDT->h ) * 60 + $intervalDT->i ) * 60 + $intervalDT->s;
            if ( $seconds > $this->dbSettings->getExpiringSeconds() )   {   // Judge timeout.
                return false;
            }
            return $hashValue;
        }
        return false;
    }

    function removeOutdatedChallenges() {
        $hashTable = $this->dbSettings->getHashTable();
        if ( $hashTable == null )   {
            return false;
        }

        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }

        $currentDT = new DateTime();
        $currentDT->sub(new DateInterval( "PT".$this->dbSettings->getExpiringSeconds()."S" ));
        $currentDTStr = $this->link->quote($currentDT->format( 'Y-m-d H:i:s' ));
        $sql = "delete from {$hashTable} where expired < {$currentDTStr}";
        $this->logger->setDebugMessage( $sql );
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        return true;
    }

    function authSupportRetrieveHashedPassword($username)   {
        $userTable = $this->dbSettings->getUserTable();
        if ( $userTable == null )   {
            return false;
        }

        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        $sql = "select hashedpasswd from {$userTable} where username=" . $this->link->quote($username);
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

    function authSupportGetSalt($username)  {
        $hashedpw = $this->authSupportRetrieveHashedPassword($username);
        return substr( $hashedpw, -8 );
    }

    function authSupportCreateUser($username, $hashedpassword)  {
        if ( $this->authSupportRetrieveHashedPassword($username) !== false )    {
            $this->logger->setErrorMessage( 'User Already exist: ' . $username );
            return false;
        }
        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        $userTable = $this->getUserTable();
        $sql = "insert {$userTable} set username=" . $this->link->quote($username)
            . ",hashedpasswd='{$hashedpassword}'";
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Insert:' . $sql);
            return false;
        }
        return true;

    }

    function authSupportChangePassword($username, $hashedoldpassword, $hashednewpassword)   {

    }

    function authSupportGetUserIdFromUsername($username)    {
        $userTable = $this->dbSettings->getUserTable();
        if ( $userTable == null )   {
            return false;
        }
        if ( $username === 0 )   {
            return 0;
        }

        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        $sql = "select id from {$userTable} where username=" . $this->link->quote($username);
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

    function authSupportGetGroupNameFromGroupId($groupid)    {
        $groupTable = $this->dbSettings->getGroupTable();
        if ( $groupTable == null )   {
            return null;
        }

        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        $sql = "select groupname from {$groupTable} where id=" . $this->link->quote($groupid);
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

    function getGroupsOfUser( $user )   {
        $corrTable = $this->dbSettings->getCorrTable();
        if ( $corrTable == null )   {
            return array();
        }

        $userid = $this->authSupportGetUserIdFromUsername($user);
        if ( ! $this->setupConnection() )   {   //Establish the connection
            return false;
        }
        $this->firstLevel = true;
        $this->belongGroups = array();
        $this->resolveGroup($userid);
        $this->candidateGroups = array();
        foreach( $this->belongGroups as $groupid )  {
            $this->candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
        }
        return $this->candidateGroups;
    }

    var $candidateGroups;
    var $belongGroups;
    var $firstLevel;

    function resolveGroup( $groupid ) {
        $corrTable = $this->dbSettings->getCorrTable();

        if ( $this->firstLevel )    {
            $sql = "select * from {$corrTable} where user_id = " . $this->link->quote($groupid);
            $this->firstLevel = false;
        } else {
            $sql = "select * from {$corrTable} where group_id = " . $this->link->quote($groupid);
            $this->belongGroups[] = $groupid;
        }
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ( ! in_array( $row['dest_group_id'], $this->belongGroups ) ) {
                if ( ! $this->resolveGroup( $row['dest_group_id'] ))  {
                    return false;
                }
            }
        }
    }

}

?>