<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

require_once('DB_Base.php');

class DB_PDO extends DB_Base implements DB_Interface
{

    function authSupportStoreChallenge($username, $challenge, $clientId)   {
        $hashTable = $this->getHashTable();
        if ( $hashTable == null )   {
            return false;
        }
        $uid = $this->authSupportGetUserIdFromUsername($username);
        if ( $uid === false )   {
            $this->errorMessageStore("User '{$username}' does't exist.");
            return false;
        }
        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
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
        $hashTable = $this->getHashTable();
        if ( $hashTable == null )   {
            return false;
        }
        $uid = $this->authSupportGetUserIdFromUsername($username);
        if ( $uid === false )   {
            $this->errorMessageStore("User '{$username}' does't exist.");
            return false;
        }
        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
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
            if ( $seconds > $this->getExpiringSeconds() )   {   // Judge timeout.
                return false;
            }
            return $hashValue;
        }
        return false;
    }

    function removeOutdatedChallenges() {
        $hashTable = $this->getHashTable();
        if ( $hashTable == null )   {
            return false;
        }

        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
            return false;
        }

        $currentDT = new DateTime();
        $currentDT->sub(new DateInterval( "PT".$this->getExpiringSeconds()."S" ));
        $currentDTStr = $this->link->quote($currentDT->format( 'Y-m-d H:i:s' ));
        $sql = "delete from {$hashTable} where expired < {$currentDTStr}";
        $this->setDebugMessage( $sql );
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return false;
        }
        return true;
    }

    function authSupportRetrieveHashedPassword($username)   {
        $userTable = $this->getUserTable();
        if ( $userTable == null )   {
            return false;
        }

        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
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
            $this->errorMessage[] = 'User Already exist: ' . $username;
            return false;
        }
        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
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
        $userTable = $this->getUserTable();
        if ( $userTable == null )   {
            return false;
        }

        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
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
        $groupTable = $this->getGroupTable();
        if ( $groupTable == null )   {
            return null;
        }

        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
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
        $corrTable = $this->getCorrTable();
        if ( $corrTable == null )   {
            return array();
        }

        $userid = $this->authSupportGetUserIdFromUsername($user);
        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
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
        $corrTable = $this->getCorrTable();

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

    var $sqlResult = array();
    var $link = null;

    function __construct()
    {
    }

    function errorMessageStore($str)
    {
        $errorInfo = var_export($this->link->errorInfo(), true);
        $this->errorMessage[] = "Query Error: [{$str}] Code={$this->link->errorCode()} Info = {$errorInfo}";
    }

    /*
     * Generate SQL style WHERE clause.
     */
    function getWhereClause( $currentOperation, $includeContext = true, $includeExtra = true )
    {
        $tableInfo = $this->getDataSourceTargetArray();
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
        if ($includeExtra && isset($this->extraCriteria[0])) {
            $chunkCount = 0;
            $oneClause = array();
            $insideOp = ' AND ';
            $outsideOp = ' OR ';
            foreach ($this->extraCriteria as $condition) {
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

        if (count($this->foreignFieldAndValue) > 0) {
            foreach ($this->foreignFieldAndValue as $foreignDef) {
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
            $authorizedUsers = $this->getAuthorizedUsers( $currentOperation );
            $authorizedGroups = $this->getAuthorizedGroups( $currentOperation );
            if ( $authInfoTarget != 'field-user' ) {
                if ( count( $authorizedUsers ) > 0 && ! in_array( $this->currentUser, $authorizedUsers )) {
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "({$authInfoField}=" . $this->link->quote( $this->currentUser ) . ")";
                }
            } else if ( $authInfoTarget != 'field-group' ) {
                if ( count( $authorizedGroups ) > 0 ) {
                    $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                    $groupCriteria = array();
                    foreach ( $belongGroups as $oneGroup )  {
                        $groupCriteria[] = "{$authInfoField}=" . $this->link->quote( $oneGroup );
                    }
                    $queryClause = (($queryClause != '') ? "({$queryClause}) AND " : '')
                        . "(" . implude( ' OR ', $groupCriteria ) . ")";
                }
            } else {
                $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                if ( ! in_array( $this->currentUser, $authorizedUsers )
                    && array_intersect( $belongGroups, $authorizedGroups )) {
                    $queryClause = 'FALSE';
                }
            }
        }
        return $queryClause;
    }


    /* Genrate SQL Sort and Where clause */
    function getSortClause()
    {
        $tableInfo = $this->getDataSourceTargetArray();
        $sortClause = array();
        if (count($this->extraSortKey)>0) {
            foreach ($this->extraSortKey as $condition) {
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
        $tableInfo = $this->getDataSourceTargetArray();
        $tableName = $this->getEntityForRetrieve();

        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
            return array();
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'load') {
                    if ($condition['situation'] == 'pre') {
                        $sql = $condition['definition'];
                        $this->setDebugMessage( $sql );
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
        $this->setDebugMessage( $sql );
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
        if ($this->recordCount > 0) {
            $limitParam = $this->recordCount;
        }
        $skipParam = 0;
        if (isset($tableInfo['paging']) and $tableInfo['paging'] == true) {
            $skipParam = $this->start;
        }
        $sql = "SELECT * FROM {$viewOrTableName} {$queryClause} {$sortClause} "
            . " LIMIT {$limitParam} OFFSET {$skipParam}";
        $this->setDebugMessage( $sql );

        // Query
        $result = $this->link->query($sql);
        if ($result === false) {
            $this->errorMessageStore('Select:' . $sql);
            return array();
        }
        $this->sqlResult = array();
        foreach ($result->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $rowArray = array();
            foreach ($row as $field => $val) {
                //	$filedInForm = $field;
                $filedInForm = "{$tableName}{$this->separator}{$field}";
                $rowArray[$field] = $this->formatterFromDB($filedInForm, $val);
            }
            $this->sqlResult[] = $rowArray;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'load') {
                    if ($condition['situation'] == 'post') {
                        $sql = $condition['definition'];
                        $this->setDebugMessage( $sql );
                        $result = $this->link->query($sql);
                        if ($result === false) {
                            $this->errorMessageStore('Post-script:' . $sql);
                        }
                    }
                }
            }
        }
        return $this->sqlResult;
    }

    function setToDB($dataSourceName)
    {
        $tableName = $this->getEntityForUpdate();
        $tableInfo = $this->getDataSourceTargetArray();
        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
            return false;
        }
        if (isset($tableInfo['script'])) {
            foreach($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'update' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->setDebugMessage( $sql );
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
        foreach ($this->fieldsRequired as $field) {
            $value = $this->fieldsValues[$counter];
            $counter++;
            $convVal = (is_array($value)) ? implode("\n", $value) : $value;
            $convVal = $this->formatterToDB($field, $convVal);
            //    $convVal = $this->link->quote( $this->formatterToDB( $field, $convVal ));
            $setClause[] = "{$field}=?";
            $setParameter[] = $convVal;
        }
        if (count($setClause) < 1) {
            $this->errorMessage[] = 'No data to update.';
            return false;
        }
        $setClause = implode(',', $setClause);

        $queryClause = $this->getWhereClause( 'update', false, true );
        if ($queryClause != '') {
            $queryClause = "WHERE {$queryClause}";
        }
        $sql = "UPDATE {$tableName} SET {$setClause} {$queryClause}";
        $prepSQL = $this->link->prepare($sql);
        $this->setDebugMessage( $prepSQL->queryString );
        $result = $prepSQL->execute($setParameter);
        if ($result === false) {
            $this->errorMessageStore('Update:' + $prepSQL->erroInfo);
            return false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'update' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->setDebugMessage( $sql );
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
        $tableInfo = $this->getDataSourceTargetArray();
        $tableName = $this->getEntityForUpdate();

        $setClause = array();
        $authorizeJudge = true;
        if ( isset( $tableInfo['authentication'] )) {
            $authorizeJudge = false;
            $currentOperation = "new";
            $authInfoField = $this->getFieldForAuthorization( $currentOperation );
            $authInfoTarget = $this->getTargetForAuthorization( $currentOperation );
            $authorizedUsers = $this->getAuthorizedUsers( $currentOperation );
            $authorizedGroups = $this->getAuthorizedGroups( $currentOperation );
            if ( $authInfoTarget != 'field-user' ) {
                if ( count( $authorizedUsers ) > 0 && ! in_array( $this->currentUser, $authorizedUsers )) {
                    $authorizeJudge = true;
                    $setClause[] = "{$authInfoField}=" . $this->link->quote($this->currentUser);
                }
            } else if ( $authInfoTarget != 'field-group' ) {
                if ( count( $authorizedGroups ) > 0 ) {
                    $intersectGroups = array_intersect( $this->getGroupsOfUser( $this->currentUser ), $authorizedGroups );
                    sort( $intersectGroups );
                    if ( count( $intersectGroups ) > 0 )    {
                        $authorizeJudge = true;
                        $setClause[] = "{$authInfoField}=" . $this->link->quote( $intersectGroups[0] );
                    }
                }
            } else {
                $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                if ( ! in_array( $this->currentUser, $authorizedUsers )
                    && array_intersect( $belongGroups, $authorizedGroups )) {
                    $authorizeJudge = true;
                }
            }
        }

        if ( !$authorizeJudge ) {
            $this->debugMessage[] = 'No Authrization:';
            return false;
        }

        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
            return false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'new' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->setDebugMessage( $sql );
                    $result = $this->link->query($sql);
                    if (!$result) {
                        $this->errorMessageStore('Pre-script:' . $sql);
                        return false;
                    }
                }
            }
        }

        $countFields = count($this->fieldsRequired);
        for ($i = 0; $i < $countFields; $i++) {
            $field = $this->fieldsRequired[$i];
            $value = $this->fieldsValues[$i];
            $filedInForm = "{$tableName}{$this->separator}{$field}";
            $convVal = (is_array($value)) ? implode("\n", $value) : $value;
            $convVal = $this->link->quote($this->formatterToDB($filedInForm, $convVal));
            $setClause[] = "{$field}={$convVal}";
        }


        $setClause = (count($setClause) == 0) ? "{$tableInfo['key']}=DEFAULT"
            : implode(',', $setClause);
        $sql = "INSERT {$tableName} SET {$setClause}";
        $this->setDebugMessage( $sql );
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
                    $this->setDebugMessage( $sql );
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
        $tableInfo = $this->getDataSourceTargetArray();
        $tableName = $this->getEntityForUpdate();

        try {
            $this->link = new PDO($this->getDbSpecDSN(),
                $this->getDbSpecUser(),
                $this->getDbSpecPassword(),
                is_array($this->getDbSpecOption()) ? $this->getDbSpecOption() : array());
        } catch (PDOException $ex) {
            $this->errorMessage[] = 'Connection Error: ' . $ex->getMessage();
            return false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'delete' && $condition['situation'] == 'pre') {
                    $sql = $condition['definition'];
                    $this->setDebugMessage( $sql );
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
        $this->setDebugMessage( $sql );
        $result = $this->link->query($sql);
        if (!$result) {
            $this->errorMessageStore('Delete Error:' . $sql);
            return false;
        }

        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'delete' && $condition['situation'] == 'post') {
                    $sql = $condition['definition'];
                    $this->setDebugMessage( $sql );
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
}

?>