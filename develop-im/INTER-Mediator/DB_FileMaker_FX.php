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
    function authSupportStoreChallenge($username, $challenge)   {}
    function authSupportRetrieveChallenge($username)    {}
    function authSupportRetrieveHashedPassword($username)   {}
    function authSupportCreateUser($username, $hashedpassword)  {}
    function authSupportChangePassword($username, $hashedoldpassword, $hashednewpassword)   {}

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
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
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
                }
            }
        }
        if (count($this->foreignFieldAndValue) > 0) {
            foreach ($this->foreignFieldAndValue as $foreignDef) {
                foreach ($tableInfo['relation'] as $relDef) {
                    if ($relDef['foreign-key'] == $foreignDef['field']) {
                        $op = (isset($relDef['operator'])) ? $relDef['operator'] : 'eq';
                        $fx->AddDBParam($foreignDef['field'],
                            $this->formatterToDB("{$contextName}{$this->separator}{$value['field']}",
                                $foreignDef['value']), $op);
                    }
                }
            }
        }
        //    if ( $this->parentKeyValue != null && isset( $tableInfo['foreign-key'] ))	{
        //       $fx->AddDBParam( $tableInfo['foreign-key'], $this->parentKeyValue, 'eq' );
        //   }
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
        $fxResult = $fx->DoFxAction(FX_ACTION_FIND, TRUE, TRUE, 'full');
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
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $this->getEntityForUpdate(), 1);
        //	$fx->AddDBParam( $keyFieldName, $data[$keyFieldName], 'eq' );
        foreach ($this->extraCriteria as $value) {
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $convertedValue
                = $this->formatterToDB("{$contextName}{$this->separator}{$value['field']}", $value['value']);
            $fx->AddDBParam($value['field'], $convertedValue, $op);
        }
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->errorMessage[] = get_class($result) . ': '. $result->getDebugInfo();
            return false;
        }
        if ($this->isDebug) {
            $this->debugMessage[] = $result['URL'];
        }
        if ($result['errorCode'] > 0) {
            $this->errorMessage[] = "FX reports error at find action: code={$result['errorCode']}, url={$result['URL']}<hr>";
            return false;
        }
        if ($result['foundCount'] == 1) {
            foreach ($result['data'] as $key => $row) {
                $recId = substr($key, 0, strpos($key, '.'));

                $fx->setCharacterEncoding('UTF-8');
                $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
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
                if ($this->isDebug) $this->debugMessage[] = $result['URL'];
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
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
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
        if ($this->isDebug) {
            $this->debugMessage[] = $result['URL'];
        }
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
        $fx->setDBUserPass($this->getDbSpecUser(), $this->getDbSpecPassword());
        $fx->setDBData($this->getDbSpecDatabase(), $this->getEntityForUpdate(), 1);

        foreach ($this->extraCriteria as $value) {
            $op = $value['operator'] == '=' ? 'eq' : $value['operator'];
            $fx->AddDBParam($value['field'], $value['value'], $op);
        }
        $result = $fx->DoFxAction("perform_find", TRUE, TRUE, 'full');
        if ( ! is_array($result) )   {
            $this->errorMessage[] = get_class($result) . ': '. $result->getDebugInfo();
            return false;
        }
        if ($this->isDebug) {
            $this->debugMessage[] = $result['URL'];
        }
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
                if ($this->isDebug) {
                    $this->debugMessage[] = $result['URL'];
                }
                break;
            }
        }
        return true;
    }
}

?>