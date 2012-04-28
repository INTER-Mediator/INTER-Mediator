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

$currentEr = error_reporting();
error_reporting(0);
include_once('FX/FX.php');
if (error_get_last() !== null) { // If FX.php isn't installed in valid directories, it shows error message and finishes.
    echo 'INTER-Mediator Error: Data Access Class "FileMaker_FX" requires FX.php on any right directory.';
    return;
}
error_reporting($currentEr);

function dateArrayFromFMDate($d)
{
    if ($d == '') {
        return '';
    }
    $dateComp = date_parse_from_format('m/d/Y H:i:s', $d);
    $dt = new DateTime();
    $jYearStartDate = array('1989-1-8' => '平成', '1925-12-25' => '昭和', '1912-7-30' => '大正', '1868-1-25' => '明治');
    $gengoName = '';
    $gengoYear = 0;
    foreach ($jYearStartDate as $startDate => $gengo) {
        $dtStart = new DateTime($startDate);
        $dinterval = $dt->diff($dtStart);
        if ($dinterval->invert == 1) {
            $gengoName = $gengo;
            $gengoYear = $dt->format('Y') - $dtStart->format('Y') + 1;
            $gengoYear = ($gengoYear == 1) ? '元' : $gengoYear;
            break;
        }
    }
    $dt->setDate($dateComp['year'], $dateComp['month'], $dateComp['day']);
    $wStrArray = array('日', '月', '火', '水', '木', '金', '土');
    return array(
        'unixtime' => $dt->format('U'),
        'year' => $dt->format('Y'),
        'jyear' => $gengoName . $gengoYear . '年',
        'month' => $dt->format('m'),
        'day' => $dt->format('d'),
        'hour' => $dt->format('H'),
        'minute' => $dt->format('i'),
        'second' => $dt->format('s'),
        'weekdayName' => $wStrArray[$dt->format('w')],
        'weekday' => $dt->format('w'),
        'longdate' => $dt->format('Y/m/d'),
        'jlongdate' => $gengoName . ' ' . $gengoYear . $dt->format(' 年 n 月 j 日 ') . $wStrArray[$dt->format('w')] . '曜日',
    );
}

class DB_FileMaker_FX extends DB_Base implements DB_Interface
{
    function errorMessageStore($str)
    {
        //$errorInfo = var_export($this->link->errorInfo(), true);
        $this->setErrorMessage("Query Error: [{$str}] Code= Info =");
    }

    function authSupportStoreChallenge($username, $challenge, $clientId)   {
        $hashTable = $this->getHashTable();
        if ( $hashTable == null )   {
            return false;
        }
        if ( $username === 0 )   {
            $uid = 0;
        } else {
            $uid = $this->authSupportGetUserIdFromUsername($username);
            if ( $uid === false )   {
                $this->setDebugMessage("User '{$username}' does't exist.");
                return false;
            }
        }
        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $hashTable, 1);
        $fx->AddDBParam( 'user_id', $uid , 'eq');
        $fx->AddDBParam( 'clienthost', $clientId , 'eq');
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        $currentDT = new DateTime();
        $currentDTFormat = $currentDT->format('m/d/Y H:i:s');
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $fx->setCharacterEncoding('UTF-8');
            $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
            $fx->setDBData($this->getDbSpecDatabase(), $hashTable, 1);
            $fx->SetRecordID($recId);
            $fx->AddDBParam( 'hash', $challenge);
            $fx->AddDBParam( 'expired', $currentDTFormat);
            $fx->AddDBParam( 'clienthost', $clientId );
            $fx->AddDBParam( 'user_id', $uid );
            $result = $fx->DoFxAction("update", TRUE, TRUE, 'full');
            if ( ! is_array($result) )   {
                $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
                return false;
            }
            $this->setDebugMessage( $result['URL'] );
            return true;
        }
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $hashTable, 1);
        $fx->AddDBParam( 'hash', $challenge);
        $fx->AddDBParam( 'expired', $currentDTFormat);
        $fx->AddDBParam( 'clienthost', $clientId );
        $fx->AddDBParam( 'user_id', $uid );
        $result = $fx->DoFxAction("new", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        return true;
    }

    function authSupportRetrieveChallenge($username, $clientId)    {
        $hashTable = $this->getHashTable();
        if ( $hashTable == null )   {
            return false;
        }
        if ( $username === 0 )   {
            $uid = 0;
        } else {
            $uid = $this->authSupportGetUserIdFromUsername($username);
            if ( $uid === false )   {
                $this->setDebugMessage("User '{$username}' does't exist.");
                return false;
            }
        }
        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $hashTable, 1);
        $fx->AddDBParam( 'user_id', $uid , 'eq');
        $fx->AddDBParam( 'clienthost', $clientId , 'eq');
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));
            $expiredDT = new DateTime($row['expired'][0]);
            $hashValue = $row['hash'][0];
            $recordId = $row['id'][0];

            $fx->setCharacterEncoding('UTF-8');
            $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
            $fx->setDBData($this->getDbSpecDatabase(), $hashTable, 1);
            $fx->SetRecordID( $recId );
            $result = $fx->DoFxAction("delete", TRUE, TRUE, 'full');
            if ( ! is_array($result) )   {
                $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
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

        $currentDT = new DateTime();
        $currentDT->sub(new DateInterval( "PT".$this->getExpiringSeconds()."S" ));

        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $hashTable, 100000000);
        $fx->AddDBParam( 'expired', $currentDT->format( 'm/d/Y H:i:s' ) , 'lt');
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        foreach ($result['data'] as $key => $row) {
            $recId = substr($key, 0, strpos($key, '.'));

            $fx->setCharacterEncoding('UTF-8');
            $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
            $fx->setDBData($this->getDbSpecDatabase(), $hashTable, 1);
            $fx->SetRecordID( $recId );
            $result = $fx->DoFxAction("delete", TRUE, TRUE, 'full');
            if ( ! is_array($result) )   {
                $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
                return false;
            }
        }
        return true;
    }

    function authSupportRetrieveHashedPassword($username)   {
        $userTable = $this->getUserTable();
        if ( $userTable == null )   {
            return false;
        }

        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $userTable, 1);
        $fx->AddDBParam( 'username', $username , 'eq');
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        foreach ($result['data'] as $key => $row) {
            return $row['hashedpasswd'][0];
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
        $userTable = $this->getUserTable();
        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $userTable, 1);
        $fx->AddDBParam( 'username', $username );
        $fx->AddDBParam( 'hashedpasswd', $hashedpassword );
        $result = $fx->DoFxAction("new", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        return true;
    }

    function authSupportChangePassword($username, $hashedoldpassword, $hashednewpassword)   {

    }

    function authSupportGetUserIdFromUsername($username)    {
        $userTable = $this->getUserTable();
        if ( $userTable == null )   {
            return false;
        }
        if ( $username === 0 )   {
            return 0;
        }

        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $userTable, 1);
        $fx->AddDBParam( 'username', $username );
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        foreach ($result['data'] as $key => $row) {
            return $row['id'][0];
        }
        return false;
    }

    function authSupportGetGroupNameFromGroupId($groupid)    {
        $groupTable = $this->getGroupTable();
        if ( $groupTable == null )   {
            return null;
        }

        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $groupTable, 1);
        $fx->AddDBParam( 'id', $groupid );
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        foreach ($result['data'] as $key => $row) {
            return $row['groupname'][0];
        }
        return false;
    }

    function getGroupsOfUser( $user )   {
        $corrTable = $this->getGroupTable();
        if ( $corrTable == null )   {
            return array();
        }

        $userid = $this->authSupportGetUserIdFromUsername($user);
        $this->fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());

        $this->firstLevel = true;
        $this->belongGroups = array();
        $this->resolveGroup($userid);
        $this->candidateGroups = array();
        foreach( $this->belongGroups as $groupid )  {
            $this->candidateGroups[] = $this->authSupportGetGroupNameFromGroupId($groupid);
        }
        return $this->candidateGroups;
    }

    var $fx;
    var $candidateGroups;
    var $belongGroups;
    var $firstLevel;

    function resolveGroup( $groupid ) {
        $corrTable = $this->getGroupTable();

        $this->fx->setCharacterEncoding('UTF-8');
        $this->fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $this->fx->setDBData($this->getDbSpecDatabase(), $corrTable, 1);
        if ( $this->firstLevel )    {
            $this->fx->AddDBParam( 'user_id', $groupid );
            $this->firstLevel = false;
        } else {
            $this->fx->AddDBParam( 'group_id', $groupid );
            $this->belongGroups[] = $groupid;
        }
        $result = $this->fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->setDebugMessage( get_class($result) . ': '. $result->getDebugInfo());
            return false;
        }
        foreach ($result['data'] as $key => $row) {
            if ( ! in_array( $row['dest_group_id'][0], $this->belongGroups ) ) {
                if ( ! $this->resolveGroup( $row['dest_group_id'][0] ))  {
                    return false;
                }
            }
        }
    }





    function stringReturnOnly($str)    {
        return str_replace("\n\r", "\r",
            str_replace("\n", "\r", $str));
    }

    function getFromDB($dataSourceName)
    {
        $contextName = $this->dataSourceName;
        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $tableInfo = $this->getDataSourceTargetArray();
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getAccessUser(), $this->getAccessPassword());
        $limitParam = 100000000;
        if (isset($tableInfo['records'])) {
            $limitParam = $tableInfo['records'];
        }
        if ($this->recordCount > 0) {
            $limitParam = $this->recordCount;
        }

        $fx->setDBData($this->getDbSpecDatabase(), $this->getEntityForRetrieve(), $limitParam);
        $skipParam = 0;
        if (isset($tableInfo['paging']) and $tableInfo['paging'] == true) {
            $skipParam = $this->start;
        }
        $fx->FMSkipRecords($skipParam);

        $hasFindParams = false;
        if (isset($tableInfo['query'])) {
            foreach ($tableInfo['query'] as $condition) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $fx->SetLogicalOR();
                } else {
                    if (isset($condition['operator'])) {
                        $fx->AddDBParam($condition['field'], $condition['value'], $condition['operator']);
                    } else {
                        $fx->AddDBParam($condition['field'], $condition['value']);
                    }
                    $hasFindParams = true;
                }
            }
        }

        if (isset($this->extraCriteria)) {
            foreach ($this->extraCriteria as $value) {
                if ($condition['field'] == '__operation__' && $condition['operator'] == 'or') {
                    $fx->SetLogicalOR();
                } else {
                    $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
                    $fx->AddDBParam($value['field'], $value['value'], $op);
                    $hasFindParams = true;
                }
            }
        }
        if (count($this->foreignFieldAndValue) > 0) {
            foreach ($this->foreignFieldAndValue as $foreignDef) {
                foreach ($tableInfo['relation'] as $relDef) {
                    if ($relDef['foreign-key'] == $foreignDef['field']) {
                        $op = (isset($relDef['operator'])) ? $relDef['operator'] : 'eq';
                        $fx->AddDBParam($foreignDef['field'],
                            $this->formatterToDB("{$contextName}{$this->separator}{$foreignDef['field']}",
                                $foreignDef['value']), $op);
                        $hasFindParams = true;
                    }
                }
            }
        }

        if ( isset( $tableInfo['authentication'] )) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization( "load" );
            $authInfoTarget = $this->getTargetForAuthorization( "load" );
            if ( $authInfoTarget == 'field-user' ) {
                if ( strlen( $this->currentUser ) == 0 )    {
                    $authFailure = true;
                } else {
                    $fx->AddDBParam($authInfoField, $this->currentUser, "eq");
                    $hasFindParams = true;
                }
            } else if ( $authInfoTarget == 'field-group' ) {
                $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                $groupCriteria = array();
                if ( strlen( $this->currentUser ) == 0 || count( $groupCriteria ) == 0 )    {
                    $authFailure = true;
                } else {
                    $fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                    $hasFindParams = true;
                }
            } else {
                $authorizedUsers = $this->getAuthorizedUsers( "load" );
                $authorizedGroups = $this->getAuthorizedGroups( "load" );
                $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                if ( ! in_array( $this->currentUser, $authorizedUsers )
                    && array_intersect( $belongGroups, $authorizedGroups )) {
                    $authFailure = true;
                }
            }
            if ( $authFailure ) {
                return null;
            }
        }

        if (isset($tableInfo['sort'])) {
            foreach ($tableInfo['sort'] as $condition) {
                if (isset($condition['direction'])) {
                    $fx->AddSortParam($condition['field'], $condition['direction']);
                } else {
                    $fx->AddSortParam($condition['field']);
                }
            }
        }
        if (count($this->extraSortKey)>0) {
            foreach ($this->extraSortKey as $condition) {
                $fx->AddSortParam($condition['field'], $condition['direction']);
            }
        }
        if (isset($tableInfo['global'])) {
            foreach ($tableInfo['global'] as $condition) {
                if ($condition['db-operation'] == 'load') {
                    $fx->SetFMGlobal($condition['field'], $condition['value']);
                }
            }
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'load') {
                    if ($condition['situation'] == 'pre') {
                        $fx->PerformFMScriptPrefind($condition['definition']);
                    } else if ($condition['situation'] == 'presort') {
                        $fx->PerformFMScriptPresort($condition['definition']);
                    } else if ($condition['situation'] == 'post') {
                        $fx->PerformFMScript($condition['definition']);
                    }
                }
            }
        }
        if ( $hasFindParams )   {
            $fxResult = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        } else {
            $fxResult = $fx->DoFxAction("show_all", TRUE, TRUE, 'full');
        }

        //var_dump($fxResult);
        if ( ! is_array($fxResult) )   {
            $this->errorMessage[] = get_class($fxResult) . ': '. $fxResult->getDebugInfo() . var_export($fx,true);
            return null;
        }
        if ($fxResult['errorCode'] != 0 && $fxResult['errorCode'] != 401) {
            $this->errorMessage[] = "FX reports error at find action: "
                . "code={$fxResult['errorCode']}, url={$fxResult['URL']}";
            return null;
        }
        $this->setDebugMessage($fxResult['URL']);
        //$this->setDebugMessage( arrayToJS( $fxResult['data'], '' ));
        $this->mainTableCount = $fxResult['foundCount'];

        $returnArray = array();
        if (isset($fxResult['data'])) {
            foreach ($fxResult['data'] as $oneRecord) {
                $oneRecordArray = array();
                foreach ($oneRecord as $field => $dataArray) {
                    if (count($dataArray) == 1) {
                        $oneRecordArray[$field] = $this->formatterFromDB(
                            "{$contextName}{$this->separator}$field", $dataArray[0]);
                    }
                }
                $returnArray[] = $oneRecordArray;
            }
        }
        return $returnArray;
    }

    function unifyCRLF($str)
    {
        return str_replace("\n", "\r", str_replace("\r\n", "\r", $str));
    }

    function setToDB($dataSourceName)
    {
        $contextName = $this->dataSourceName;
        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $tableInfo = $this->getDataSourceTargetArray();
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getAccessUser(), $this->getAccessPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $this->getEntityForUpdate(), 1);
        //	$fx->AddDBParam( $keyFieldName, $data[$keyFieldName], 'eq' );
        foreach ($this->extraCriteria as $value) {
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $convertedValue
                = $this->formatterToDB("{$contextName}{$this->separator}{$value['field']}", $value['value']);
            $fx->AddDBParam($value['field'], $convertedValue, $op);
        }
        if ( isset( $tableInfo['authentication'] )) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization( "update" );
            $authInfoTarget = $this->getTargetForAuthorization( "update" );
            if ( $authInfoTarget == 'field-user' ) {
                if ( strlen( $this->currentUser ) == 0 )    {
                    $authFailure = true;
                } else {
                    $fx->AddDBParam($authInfoField, $this->currentUser, "eq");
                }
            } else if ( $authInfoTarget == 'field-group' ) {
                $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                $groupCriteria = array();
                if ( strlen( $this->currentUser ) == 0 || count( $groupCriteria ) == 0 )    {
                    $authFailure = true;
                } else {
                    $fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                }
            } else {
                $authorizedUsers = $this->getAuthorizedUsers( "update" );
                $authorizedGroups = $this->getAuthorizedGroups( "update" );
                $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                if ( ! in_array( $this->currentUser, $authorizedUsers )
                    && array_intersect( $belongGroups, $authorizedGroups )) {
                    $authFailure = true;
                }
            }
            if ( $authFailure ) {
                return false;
            }
        }
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->errorMessage[] = get_class($result) . ': '. $result->getDebugInfo();
            return false;
        }
        $this->setDebugMessage( $result['URL'] );

        if ($result['errorCode'] > 0) {
            $this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
            return false;
        }
        if ($result['foundCount'] == 1) {
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));

                $fx->setCharacterEncoding('UTF-8');
                $fx->setDBUserPass($this->getAccessUser(), $this->getAccessPassword());
                $fx->setDBData($this->getDbSpecDatabase(), $this->getEntityForUpdate(), 1);
                $fx->SetRecordID($recId);
                $counter = 0;
                foreach ($this->fieldsRequired as $field) {
                    $value = $this->fieldsValues[$counter];
                    $counter++;
                    $convVal = $this->stringReturnOnly((is_array($value)) ? implode("\n", $value) : $value);
                    $convVal = $this->formatterToDB("{$contextName}{$this->separator}{$field}", $convVal);
                    $fx->AddDBParam($field, $convVal);
                }
                if ($counter < 1) {
                    $this->errorMessage[] = 'No data to update.';
                    return false;
                }
                if (isset($tableInfo['global'])) {
                    foreach ($tableInfo['global'] as $condition) {
                        if ($condition['db-operation'] == 'update') {
                            $fx->SetFMGlobal($condition['field'], $condition['value']);
                        }
                    }
                }
                if (isset($tableInfo['script'])) {
                    foreach ($tableInfo['script'] as $condition) {
                        if ($condition['db-operation'] == 'update') {
                            if ($condition['situation'] == 'pre') {
                                $fx->PerformFMScriptPrefind($condition['definition']);
                            } else if ($condition['situation'] == 'presort') {
                                $fx->PerformFMScriptPresort($condition['definition']);
                            } else if ($condition['situation'] == 'post') {
                                $fx->PerformFMScript($condition['definition']);
                            }
                        }
                    }
                }
                $result = $fx->DoFxAction("update", TRUE, TRUE, 'full');
                if ( ! is_array($result) )   {
                    $this->errorMessage[] = get_class($result) . ': '. $result->getDebugInfo();
                    return false;
                }
                if ($result['errorCode'] > 0) {
                    $this->errorMessage[] = "FX reports error at edit action: table={$this->getEntityForUpdate()}, code={$result['errorCode']}, url={$result['URL']}<hr>";
                    return false;
                }
                $this->setDebugMessage( $result['URL'] );
                break;
            }
        } else {

        }
        return true;
    }

    function newToDB($dataSourceName)
    {
        $contextName = $this->dataSourceName;

        $tableInfo = $this->getDataSourceTargetArray();
        $keyFieldName = $tableInfo['key'];

        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getAccessUser(), $this->getAccessPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $this->getEntityForUpdate(), 1);
        $countFields = count($this->fieldsRequired);
        for ($i = 0; $i < $countFields; $i++) {
            $field = $this->fieldsRequired[$i];
            $value = $this->fieldsValues[$i];
            if ($field != $keyFieldName) {
                $filedInForm = "{$this->getEntityForUpdate()}{$this->separator}{$field}";
                $convVal = $this->unifyCRLF((is_array($value)) ? implode("\r", $value) : $value);
                $fx->AddDBParam($field, $this->formatterToDB($filedInForm, $convVal));
            }
        }
        if (isset($tableInfo['default-values'])) {
            foreach( $tableInfo['default-values'] as $itemDef ) {
                $field = $itemDef['field'];
                $value = $itemDef['value'];
                if ($field != $keyFieldName) {
                    $filedInForm = "{$this->getEntityForUpdate()}{$this->separator}{$field}";
                    $convVal = $this->unifyCRLF((is_array($value)) ? implode("\r", $value) : $value);
                    $fx->AddDBParam($field, $this->formatterToDB($filedInForm, $convVal));
                }
            }
        }
        if ( isset( $tableInfo['authentication'] )) {
            $authInfoField = $this->getFieldForAuthorization( "new" );
            $authInfoTarget = $this->getTargetForAuthorization( "new" );
            if ( $authInfoTarget == 'field-user' ) {
                $fx->AddDBParam( $authInfoField, strlen($this->currentUser)==0 ? randamString(10) : $this->currentUser );
             } else if ( $authInfoTarget == 'field-group' ) {
                $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                $fx->AddDBParam( $authInfoField, strlen($belongGroups[0])==0 ? randamString(10) : $belongGroups[0] );
            }
        }
        if (isset($tableInfo['global'])) {
            foreach ($tableInfo['global'] as $condition) {
                if ($condition['db-operation'] == 'new') {
                    $fx->SetFMGlobal($condition['field'], $condition['value']);
                }
            }
        }
        if (isset($tableInfo['script'])) {
            foreach ($tableInfo['script'] as $condition) {
                if ($condition['db-operation'] == 'new') {
                    if ($condition['situation'] == 'pre') {
                        $fx->PerformFMScriptPrefind($condition['definition']);
                    } else if ($condition['situation'] == 'presort') {
                        $fx->PerformFMScriptPresort($condition['definition']);
                    } else if ($condition['situation'] == 'post') {
                        $fx->PerformFMScript($condition['definition']);
                    }
                }
            }
        }
        $result = $fx->DoFxAction("new", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->errorMessage[] = get_class($result) . ': '. $result->getDebugInfo();
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        if ($result['errorCode'] > 0 && $result['errorCode'] != 401) {
            $this->errorMessage[] = "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>";
            return false;
        }
        foreach ($result['data'] as $row) {
            $keyValue = $row[$keyFieldName][0];
        }
        return $keyValue;
    }

    function deleteFromDB($dataSourceName)
    {
        $contextName = $this->dataSourceName;

        $tableInfo = $this->getDataSourceTargetArray();
        ;
        $fx = new FX(
            $this->getDbSpecServer(),
            $this->getDbSpecPort(),
            $this->getDbSpecDataType(),
            $this->getDbSpecProtocol());
        $fx->setCharacterEncoding('UTF-8');
        $fx->setDBUserPass($this->getAccessUser(), $this->getAccessPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $this->getEntityForUpdate(), 1);

        foreach ($this->extraCriteria as $value) {
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $fx->AddDBParam($value['field'], $value['value'], $op);
        }
        if ( isset( $tableInfo['authentication'] )) {
            $authFailure = FALSE;
            $authInfoField = $this->getFieldForAuthorization( "delete" );
            $authInfoTarget = $this->getTargetForAuthorization( "delete" );
            if ( $authInfoTarget == 'field-user' ) {
                if ( strlen( $this->currentUser ) == 0 )    {
                    $authFailure = true;
                } else {
                    $fx->AddDBParam($authInfoField, $this->currentUser, "eq");
                    $hasFindParams = true;
                }
            } else if ( $authInfoTarget == 'field-group' ) {
                $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                $groupCriteria = array();
                if ( strlen( $this->currentUser ) == 0 || count( $groupCriteria ) == 0 )    {
                    $authFailure = true;
                } else {
                    $fx->AddDBParam($authInfoField, $belongGroups[0], "eq");
                    $hasFindParams = true;
                }
            } else {
                $authorizedUsers = $this->getAuthorizedUsers( "delete" );
                $authorizedGroups = $this->getAuthorizedGroups( "delete" );
                $belongGroups = $this->getGroupsOfUser( $this->currentUser );
                if ( ! in_array( $this->currentUser, $authorizedUsers )
                    && array_intersect( $belongGroups, $authorizedGroups )) {
                    $authFailure = true;
                }
            }
            if ( $authFailure ) {
                return false;
            }
        }
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->errorMessage[] = get_class($result) . ': '. $result->getDebugInfo();
            return false;
        }
        $this->setDebugMessage( $result['URL'] );
        if ($result['errorCode'] > 0) {
            $this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
            return false;
        }
        if ($result['foundCount'] != 0) {
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));

                $fx->setCharacterEncoding('UTF-8');
                $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
                $fx->setDBData($this->getDbSpecDatabase(), $this->getEntityForUpdate(), 1);
                $fx->SetRecordID($recId);
                if (isset($tableInfo['global'])) {
                    foreach ($tableInfo['global'] as $condition) {
                        if ($condition['db-operation'] == 'delete') {
                            $fx->SetFMGlobal($condition['field'], $condition['value']);
                        }
                    }
                }
                if (isset($tableInfo['script'])) {
                    foreach ($tableInfo['script'] as $condition) {
                        if ($condition['db-operation'] == 'delete') {
                            if ($condition['situation'] == 'pre') {
                                $fx->PerformFMScriptPrefind($condition['definition']);
                            } else if ($condition['situation'] == 'presort') {
                                $fx->PerformFMScriptPresort($condition['definition']);
                            } else if ($condition['situation'] == 'post') {
                                $fx->PerformFMScript($condition['definition']);
                            }
                        }
                    }
                }
                $result = $fx->DoFxAction("delete", TRUE, TRUE, 'full');
                if ( ! is_array($result) )   {
                    $this->errorMessage[] = get_class($result) . ': '. $result->getDebugInfo();
                    return false;
                }
                if ($result['errorCode'] > 0) {
                    $this->errorMessage[] = "FX reports error at edit action: code={$result['errorCode']}, url={$result['URL']}<hr>";
                    return false;
                }
                $this->setDebugMessage( $result['URL'] );
                break;
            }
        }
        return true;
    }
}

?>