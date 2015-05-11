<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2013 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

function IM_Dummy_Entry($datasource, $options, $dbspecification, $debug = false)
{
    global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;
    $globalDataSource = $datasource;
    $globalOptions = $options;
    $globalDBSpecs = $dbspecification;
    $globalDebug = $debug;
}

function getValueFromArray($ar, $index1, $index2 = null, $index3 = null)
{
    $value = null;
    if ($index1 !== null && $index2 !== null && $index3 !== null) {
        if (isset($ar[$index1]) && isset($ar[$index1][$index2]) && isset($ar[$index1][$index2][$index3])) {
            $value = $ar[$index1][$index2][$index3];
        }
    } else if ($index1 !== null && $index2 !== null && $index3 === null) {
        if (isset($ar[$index1]) && isset($ar[$index1][$index2])) {
            $value = $ar[$index1][$index2];
        }
    } else if ($index1 !== null && $index2 === null && $index3 === null) {
        if (isset($ar[$index1])) {
            $value = $ar[$index1];
        }
    }
    if ($value === true) {
        $value = "true";
    }
    if ($value === false) {
        $value = "false";
    }
    return $value;
}

function changeIncludeIMPath($src, $validStatement)
{
    $includeFunctions = array('require_once', 'include_once', 'require', 'include');
    foreach ($includeFunctions as $targetFunction) {
        $pattern = '/' . $targetFunction . '\\(.+INTER-Mediator.php.+\\);/';
        if (preg_match($pattern, $src)) {
            return preg_replace($pattern, $validStatement, $src);
        }
    }
}

class DB_DefEditor extends DB_AuthCommon implements DB_Access_Interface
{
    var $recordCount;

    var $spacialValue = array('IM_TODAY');

    function getFromDB($dataSourceName)
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;

        $result = array();

        $filePath = $this->dbSettings->getCriteriaValue('target');
        if (substr_count($filePath, '../') > 2) {
            $this->logger->setErrorMessage("You can't access files in inhibit area: {$dataSourceName}.");
            return null;
        }
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage(
                "The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            $this->recordCount = 0;
            return null;
        }
        $convert = str_replace("<?php", "",
            str_replace("?>", "",
                str_replace("IM_Entry", "IM_Dummy_Entry",
                    changeIncludeIMPath(
                        $fileContent,
                        "require_once('../INTER-Mediator.php');"
                    ))));
        eval($convert);

        $seq = 0;
        switch ($dataSourceName) {
            case 'contexts':
                foreach ($globalDataSource as $context) {
                    $result[] = array(
                        'id' => $seq,
                        'name' => getValueFromArray($context, 'name'),
                        'table' => getValueFromArray($context, 'table'),
                        'view' => getValueFromArray($context, 'view'),
                        'records' => getValueFromArray($context, 'records'),
                        'maxrecords' => getValueFromArray($context, 'maxrecords'),
                        'paging' => getValueFromArray($context, 'paging'),
                        'key' => getValueFromArray($context, 'key'),
                        'sequence' => getValueFromArray($context, 'sequence'),
                        'extending-class' => getValueFromArray($context, 'extending-class'),
                        'protect-writing' => getValueFromArray($context, 'protect-writing'),
                        'protect-reading' => getValueFromArray($context, 'protect-reading'),
                        'db-class' => getValueFromArray($context, 'db-class'),
                        'dsn' => getValueFromArray($context, 'dsn'),
                        'option' => getValueFromArray($context, 'option'),
                        'database' => getValueFromArray($context, 'database'),
                        'user' => getValueFromArray($context, 'user'),
                        'password' => getValueFromArray($context, 'password'),
                        'server' => getValueFromArray($context, 'server'),
                        'port' => getValueFromArray($context, 'port'),
                        'protocol' => getValueFromArray($context, 'protocol'),
                        'datatype' => getValueFromArray($context, 'datatype'),
                        'cache' => getValueFromArray($context, 'cache'),
                        'post-reconstruct' => getValueFromArray($context, 'post-reconstruct'),
                        'post-dismiss-message' => getValueFromArray($context, 'post-dismiss-message'),
                        'post-move-url' => getValueFromArray($context, 'post-move-url'),
                        'repeat-control' => getValueFromArray($context, 'repeat-control'),
                        'navi-control' => getValueFromArray($context, 'navi-control'),
                        'post-repeater' => getValueFromArray($context, 'post-repeater'),
                        'post-enclosure' => getValueFromArray($context, 'post-enclosure'),
                        'authentication-media-handling' => getValueFromArray($context, 'authentication', 'media-handling'),
                        'authentication-all-user' => getValueFromArray($context, 'authentication', 'all', 'user'),
                        'authentication-all-group' => getValueFromArray($context, 'authentication', 'all', 'group'),
                        'authentication-all-target' => getValueFromArray($context, 'authentication', 'all', 'target'),
                        'authentication-all-field' => getValueFromArray($context, 'authentication', 'all', 'field'),
                        'authentication-load-user' => getValueFromArray($context, 'authentication', 'load', 'user'),
                        'authentication-load-group' => getValueFromArray($context, 'authentication', 'load', 'group'),
                        'authentication-load-target' => getValueFromArray($context, 'authentication', 'load', 'target'),
                        'authentication-load-field' => getValueFromArray($context, 'authentication', 'load', 'field'),
                        'authentication-update-user' => getValueFromArray($context, 'authentication', 'update', 'user'),
                        'authentication-update-group' => getValueFromArray($context, 'authentication', 'update', 'group'),
                        'authentication-update-target' => getValueFromArray($context, 'authentication', 'update', 'target'),
                        'authentication-update-field' => getValueFromArray($context, 'authentication', 'update', 'field'),
                        'authentication-new-user' => getValueFromArray($context, 'authentication', 'new', 'user'),
                        'authentication-new-group' => getValueFromArray($context, 'authentication', 'new', 'group'),
                        'authentication-new-target' => getValueFromArray($context, 'authentication', 'new', 'target'),
                        'authentication-new-field' => getValueFromArray($context, 'authentication', 'new', 'field'),
                        'authentication-delete-user' => getValueFromArray($context, 'authentication', 'delete', 'user'),
                        'authentication-delete-group' => getValueFromArray($context, 'authentication', 'delete', 'group'),
                        'authentication-delete-target' => getValueFromArray($context, 'authentication', 'delete', 'target'),
                        'authentication-delete-field' => getValueFromArray($context, 'authentication', 'delete', 'field'),
                    );
                    $seq++;
                }
                break;
            case 'relation':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['relation'])) {
                    foreach ($globalDataSource[$contextID]['relation'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'foreign-key' => getValueFromArray($rel, 'foreign-key'),
                            'join-field' => getValueFromArray($rel, 'join-field'),
                            'operator' => getValueFromArray($rel, 'operator'),
                            'portal' => getValueFromArray($rel, 'portal'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'query':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['query'])) {
                    foreach ($globalDataSource[$contextID]['query'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'field' => getValueFromArray($rel, 'field'),
                            'value' => getValueFromArray($rel, 'value'),
                            'operator' => getValueFromArray($rel, 'operator'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'sort':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['sort'])) {
                    foreach ($globalDataSource[$contextID]['sort'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'field' => getValueFromArray($rel, 'field'),
                            'direction' => getValueFromArray($rel, 'direction'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'default-values':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['default-values'])) {
                    foreach ($globalDataSource[$contextID]['default-values'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'field' => getValueFromArray($rel, 'field'),
                            'value' => getValueFromArray($rel, 'value'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'validation':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['validation'])) {
                    foreach ($globalDataSource[$contextID]['validation'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'field' => getValueFromArray($rel, 'field'),
                            'rule' => getValueFromArray($rel, 'rule'),
                            'message' => getValueFromArray($rel, 'message'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'script':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['script'])) {
                    foreach ($globalDataSource[$contextID]['script'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'db-operation' => getValueFromArray($rel, 'db-operation'),
                            'situation' => getValueFromArray($rel, 'situation'),
                            'definition' => getValueFromArray($rel, 'definition'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'calculation':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['calculation'])) {
                    foreach ($globalDataSource[$contextID]['calculation'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'field' => getValueFromArray($rel, 'field'),
                            'expression' => getValueFromArray($rel, 'expression'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'global':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['global'])) {
                    foreach ($globalDataSource[$contextID]['global'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'db-operation' => getValueFromArray($rel, 'db-operation'),
                            'field' => getValueFromArray($rel, 'field'),
                            'value' => getValueFromArray($rel, 'value'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'file-upload':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['file-upload'])) {
                    foreach ($globalDataSource[$contextID]['file-upload'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'field' => getValueFromArray($rel, 'field'),
                            'context' => getValueFromArray($rel, 'context'),
                            'container' => getValueFromArray($rel, 'container'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'sending-email':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['sending-email'])) {
                    foreach ($globalDataSource[$contextID]['sending-email'] as $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'db-operation' => getValueFromArray($rel, 'db-operation'),
                            'from' => getValueFromArray($rel, 'from'),
                            'to' => getValueFromArray($rel, 'to'),
                            'cc' => getValueFromArray($rel, 'cc'),
                            'bcc' => getValueFromArray($rel, 'bcc'),
                            'subject' => getValueFromArray($rel, 'subject'),
                            'body' => getValueFromArray($rel, 'body'),
                            'from-constant' => getValueFromArray($rel, 'from-constant'),
                            'to-constant' => getValueFromArray($rel, 'to-constant'),
                            'cc-constant' => getValueFromArray($rel, 'cc-constant'),
                            'bcc-constant' => getValueFromArray($rel, 'bcc-constant'),
                            'subject-constant' => getValueFromArray($rel, 'subject-constant'),
                            'body-constant' => getValueFromArray($rel, 'body-constant'),
                            'body-template' => getValueFromArray($rel, 'body-template'),
                            'body-fields' => getValueFromArray($rel, 'body-fields'),
                            'f-option' => getValueFromArray($rel, 'f-option'),
                            'body-wrap' => getValueFromArray($rel, 'body-wrap'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'options':
                $result[] = array(
                    'id' => $seq,
                    'separator' => getValueFromArray($globalOptions, 'separator'),
                    'transaction' => getValueFromArray($globalOptions, 'transaction'),
                    'media-root-dir' => getValueFromArray($globalOptions, 'media-root-dir'),
                    'media-context' => getValueFromArray($globalOptions, 'media-context'),
                    'authentication-user-table' => getValueFromArray(
                        $globalOptions, 'authentication', 'user-table'),
                    'authentication-group-table' => getValueFromArray(
                        $globalOptions, 'authentication', 'group-table'),
                    'authentication-corresponding-table' => getValueFromArray(
                        $globalOptions, 'authentication', 'corresponding-table'),
                    'authentication-challenge-table' => getValueFromArray(
                        $globalOptions, 'authentication', 'challenge-table'),
                    'authentication-authexpired' => getValueFromArray(
                        $globalOptions, 'authentication', 'authexpired'),
                    'authentication-realm' => getValueFromArray(
                        $globalOptions, 'authentication', 'realm'),
                    'authentication-email-as-username' => getValueFromArray(
                        $globalOptions, 'authentication', 'email-as-username'),
                    'smtp-server' => getValueFromArray($globalOptions, 'smtp', 'server'),
                    'smtp-port' => getValueFromArray($globalOptions, 'smtp', 'port'),
                    'smtp-username' => getValueFromArray($globalOptions, 'smtp', 'username'),
                    'smtp-password' => getValueFromArray($globalOptions, 'smtp', 'password'),
                );
                $seq++;
                break;
            case 'aliases':
                if (isset($globalOptions['aliases'])) {
                    foreach ($globalOptions['aliases'] as $rel => $org) {
                        $result[] = array(
                            'id' => $seq,
                            'alias' => $rel,
                            'original' => $org,
                        );
                        $seq++;
                    }
                }
                break;
            case 'browser-compatibility':
                if (isset($globalOptions['browser-compatibility'])) {
                    foreach ($globalOptions['browser-compatibility'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'browserdef' => $rel,
                        );
                        $seq++;
                    }
                }
                break;
            case 'formatter':
                if (isset($globalOptions['formatter'])) {
                    foreach ($globalOptions['formatter'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'field' => getValueFromArray($rel, 'field'),
                            'converter-class' => getValueFromArray($rel, 'converter-class'),
                            'parameter' => getValueFromArray($rel, 'parameter'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'dbsettings':
                $result[] = array(
                    'id' => $seq,
                    'db-class' => getValueFromArray($globalDBSpecs, 'db-class'),
                    'dsn' => getValueFromArray($globalDBSpecs, 'dsn'),
                    'option' => getValueFromArray($globalDBSpecs, 'option'),
                    'database' => getValueFromArray($globalDBSpecs, 'database'),
                    'user' => getValueFromArray($globalDBSpecs, 'user'),
                    'password' => getValueFromArray($globalDBSpecs, 'password'),
                    'server' => getValueFromArray($globalDBSpecs, 'server'),
                    'port' => getValueFromArray($globalDBSpecs, 'port'),
                    'protocol' => getValueFromArray($globalDBSpecs, 'protocol'),
                    'datatype' => getValueFromArray($globalDBSpecs, 'datatype'),
                );
                $seq++;
                break;
            case 'external-db':
                if (isset($globalDBSpecs['external-db'])) {
                    foreach ($globalDBSpecs['external-db'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'db' => $rel,
                        );
                    }
                }
                break;
            case 'debug':
                $result[] = array(
                    'id' => 0,
                    'debug' => $globalDebug
                );
                $seq++;
                break;
        }
        $this->recordCount = $seq;
        return $result;
    }

    function countQueryResult($dataSourceName)
    {
        return $this->recordCount;
    }

    function setToDB($dataSourceName)
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;

        $filePath = $this->dbSettings->getValueOfField('target');
        $contextID = $this->dbSettings->getCriteriaValue('id');

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage(
                "The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            return null;
        }
        $funcStartPos = strpos($fileContent, "IM_Entry");
        $convert = str_replace("<?php", "",
            str_replace("?>", "",
                str_replace("IM_Entry", "IM_Dummy_Entry",
                    changeIncludeIMPath(
                        $fileContent,
                        "require_once('../INTER-Mediator.php');"
                    ))));
        eval($convert);

        $allKeys = array(
            'relation' => array('foreign-key', 'join-field', 'operator', 'portal'),
            'query' => array('field', 'value', 'operator'),
            'sort' => array('field', 'direction'),
            'default-values' => array('field', 'value'),
            'validation' => array('field', 'rule', 'message'),
            'script' => array('db-operation', 'situation', 'definition'),
            'global' => array('db-operation', 'field', 'value'),
            'calculation' => array('field', 'expression'),
            'file-upload' => array('field', 'context', 'container'),
            'sending-email' => array('db-operation', 'from', 'to', 'cc', 'bcc', 'subject', 'body',
                'from-constant', 'to-constant', 'cc-constant', 'bcc-constant', 'subject-constant',
                'body-constant', 'body-template', 'body-fields', 'f-option', 'body-wrap'),
        );
        $allKeysOptions = array(
            'aliases' => array('alias', 'original'),
            'browser-compatibility' => array('browserdef'),
            'formatter' => array('field', 'converter-class', 'parameter'),
        );

        $keysShouldInteger = array(
            'records', 'maxrecords', 'smtp-port',
        );

        $keysShouldBoolean = array(
            'paging', 'email-as-username', 'portal', 'media-handling', 'post-reconstruct', 'container'
        );

        switch ($dataSourceName) {
            case 'contexts':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                if ($theKey == "authentication-media-handling") {
                    if (!isset($globalDataSource[$contextID]["authentication"])) {
                        $globalDataSource[$contextID]["authentication"] = array();
                    }
                    $globalDataSource[$contextID]["authentication"]["media-handling"]
                        = $this->dbSettings->getValueOfField($theKey);
                } else if (strpos($theKey, "authentication-") === 0) {
                    $authKeyArray = explode("-", $theKey);
                    if (!isset($globalDataSource[$contextID][$authKeyArray[0]])) {
                        $globalDataSource[$contextID][$authKeyArray[0]] = array();
                    }
                    if (!isset($globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]])) {
                        $globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]] = array();
                    }
                    $globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]][$authKeyArray[2]]
                        = $this->dbSettings->getValueOfField($theKey);
                } else {
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldInteger) !== false) {
                        $setValue = ($setValue === '') ? '' : (int)$setValue;
                    } else if (array_search($theKey, $keysShouldBoolean) !== false) {
                        $setValue = (boolean)$setValue;
                    }
                    if (strlen($setValue) > 0) {
                        $globalDataSource[$contextID][$theKey] = $setValue;
                    } else if (isset($globalDataSource[$contextID][$theKey])) {
                        unset($globalDataSource[$contextID][$theKey]);
                    }
                }
                break;
            case 'relation':
            case 'query':
            case 'sort':
            case 'default-values':
            case 'validation':
            case 'global':
            case 'script':
            case 'calculation':
            case 'file-upload':
            case 'sending-email':
                $recordID = $contextID % 10000;
                $contextID = floor($contextID / 10000);
                foreach ($allKeys[$dataSourceName] as $key) {
                    $fieldValue = $this->dbSettings->getValueOfField($key);
                    if (array_search($key, $keysShouldInteger) !== false) {
                        $fieldValue = ($fieldValue === '') ? '' : (int)$fieldValue;
                    } else if (array_search($key, $keysShouldBoolean) !== false) {
                        $fieldValue = (boolean)$fieldValue;
                    }
                    if (!is_null($fieldValue)) {
                        $globalDataSource[$contextID][$dataSourceName][$recordID][$key] = $fieldValue;
                        break;
                    }
                }
                break;
            case 'options':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                if (strpos($theKey, "authentication-") === 0) {
                    $authKey = substr($theKey, 15);
                    if (!isset($globalOptions["authentication"][$authKey])) {
                        $globalOptions["authentication"][$authKey] = array();
                    }
                    $globalOptions["authentication"][$authKey]
                        = $this->dbSettings->getValueOfField($theKey);
                } else if (strpos($theKey, "smtp-") === 0) {
                    $authKey = substr($theKey, 5);
                    if (!isset($globalOptions["smtp"][$authKey])) {
                        $globalOptions["smtp"][$authKey] = array();
                    }
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldInteger) !== false) {
                        $setValue = ($setValue === '') ? '' : (int)$setValue;
                    }
                    $globalOptions["smtp"][$authKey] = $setValue;
                } else {
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldInteger) !== false) {
                        $setValue = ($setValue === '') ? '' : (int)$setValue;
                    } else if (array_search($theKey, $keysShouldBoolean) !== false) {
                        $setValue = (boolean)$setValue;
                    }
                    if (strlen($setValue) > 0) {
                        $globalOptions[$theKey] = $setValue;
                    } else if (isset($globalOptions[$theKey])) {
                        unset($globalOptions[$theKey]);
                    }
                }
                break;
            case 'aliases':
            case 'browser-compatibility':
            case 'formatter':
                $recordID = $contextID % 10000;
                foreach ($allKeysOptions[$dataSourceName] as $key) {
                    $fieldValue = $this->dbSettings->getValueOfField($key);
                    if (!is_null($fieldValue)) {
                        $globalOptions[$dataSourceName][$recordID][$key] = $fieldValue;
                        break;
                    }
                }
                break;
            case 'dbsettings':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                $globalDBSpecs[$theKey] = $this->dbSettings->getValueOfField($theKey);
                break;
            case 'external-db':
                $recordID = $contextID % 10000;
                $fieldValue = $this->dbSettings->getValueOfField('db');
                if (!is_null($fieldValue)) {
                    $globalDBSpecs[$dataSourceName][$recordID]['db'] = $fieldValue;
                }
                break;
                if (!isset($globalDBSpecs['external-db'])) {
                    $globalDBSpecs['external-db'] = array();
                }
                $globalDBSpecs['external-db'][] = array(
                    'db' => '= new value =',
                );
                break;
            case 'debug':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                $globalDebug = $this->dbSettings->getValueOfField($theKey);
                $globalDebug = ($globalDebug == 'false') ? false : $globalDebug;
                break;
            default:
                break;
        }

        $newFileContent = substr($fileContent, 0, $funcStartPos);
        $newFileContent .= "IM_Entry(";
        $newFileContent .= var_export($globalDataSource, true);
        $newFileContent .= ",\n";
        $newFileContent .= var_export($globalOptions, true);
        $newFileContent .= ",\n";
        $newFileContent .= var_export($globalDBSpecs, true);
        $newFileContent .= ",\n";
        $newFileContent .= var_export($globalDebug, true);
        $newFileContent .= ");\n?>";

        $sq = "'";
        foreach ($this->spacialValue as $term) {
            $newFileContent = str_replace($sq . $term . $sq, $term, $newFileContent);
        }

        $fileWriteResult = file_put_contents($filePath, $newFileContent);
        if ($fileWriteResult === false) {
            $this->logger->setErrorMessage("The file {$filePath} doesn't have the permission to write.");
            return null;
        }
    }

    function newToDB($dataSourceName, $bypassAuth)
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;

        // $this->logger->setErrorMessage(var_export($this->dbSettings, true));
        $filePath = $this->dbSettings->getValueOfField('target');
        $contextID = $this->dbSettings->getValueOfField('context_id');

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage(
                "The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            return null;
        }
        $funcStartPos = strpos($fileContent, "IM_Entry");
        $convert = str_replace("<?php", "",
            str_replace("?>", "",
                str_replace("IM_Entry", "IM_Dummy_Entry",
                    changeIncludeIMPath(
                        $fileContent,
                        "require_once('../INTER-Mediator.php');"
                    ))));
        eval($convert);

        switch ($dataSourceName) {
            case 'contexts':
                $globalDataSource[] = array('name' => '= new context =');
                break;
            case 'relation':
                if (!isset($globalDataSource[$contextID]['relation'])) {
                    $globalDataSource[$contextID]['relation'] = array();
                }
                $globalDataSource[$contextID]['relation'][] = array(
                    'foreign-key' => '= new value =',
                    'join-field' => '= new value =',
                    'operator' => '= new value =',
                );
                break;
            case 'query':
                if (!isset($globalDataSource[$contextID]['query'])) {
                    $globalDataSource[$contextID]['query'] = array();
                }
                $globalDataSource[$contextID]['query'][] = array(
                    'field' => '= new value =',
                    'value' => '= new value =',
                    'operator' => '= new value =',
                );
                break;
            case 'sort':
                if (!isset($globalDataSource[$contextID]['sort'])) {
                    $globalDataSource[$contextID]['sort'] = array();
                }
                $globalDataSource[$contextID]['sort'][] = array(
                    'field' => '= new value =',
                    'direction' => '= new value =',
                );
                break;
            case 'default-values':
                if (!isset($globalDataSource[$contextID]['default-values'])) {
                    $globalDataSource[$contextID]['default-values'] = array();
                }
                $globalDataSource[$contextID]['default-values'][] = array(
                    'field' => '= new value =',
                    'value' => '= new value =',
                );
                break;
            case 'validation':
                if (!isset($globalDataSource[$contextID]['validation'])) {
                    $globalDataSource[$contextID]['validation'] = array();
                }
                $globalDataSource[$contextID]['validation'][] = array(
                    'field' => '= new value =',
                    'rule' => '= new value =',
                    'message' => '= new value =',
                );
                break;
            case 'script':
                if (!isset($globalDataSource[$contextID]['script'])) {
                    $globalDataSource[$contextID]['script'] = array();
                }
                $globalDataSource[$contextID]['script'][] = array(
                    'db-operation' => '= new value =',
                    'situation' => '= new value =',
                    'definition' => '= new value =',
                );
                break;
            case 'global':
                if (!isset($globalDataSource[$contextID]['global'])) {
                    $globalDataSource[$contextID]['global'] = array();
                }
                $globalDataSource[$contextID]['global'][] = array(
                    'db-operation' => '= new value =',
                    'field' => '= new value =',
                    'value' => '= new value =',
                );
                break;
            case 'calculation':
                if (!isset($globalDataSource[$contextID]['calculation'])) {
                    $globalDataSource[$contextID]['calculation'] = array();
                }
                $globalDataSource[$contextID]['calculation'][] = array(
                    'field' => '= new value =',
                    'expression' => '= new value =',
                );
                break;
            case 'file-upload':
                if (!isset($globalDataSource[$contextID]['file-upload'])) {
                    $globalDataSource[$contextID]['file-upload'] = array();
                }
                $globalDataSource[$contextID]['file-upload'][] = array(
                    'field' => '= new value =',
                    'context' => '= new value =',
                    'container' => true,
                );
                break;
            case 'sending-email':
                if (!isset($globalDataSource[$contextID]['sending-email'])) {
                    $globalDataSource[$contextID]['sending-email'] = array();
                }
                $globalDataSource[$contextID]['sending-email'][] = array(
                    'db-operation' => '= new value =',
                    'from' => '= new value =',
                    'to' => '= new value =',
                    'cc' => '= new value =',
                    'bcc' => '= new value =',
                    'subject' => '= new value =',
                    'body' => '= new value =',
                    'from-constant' => '= new value =',
                    'to-constant' => '= new value =',
                    'cc-constant' => '= new value =',
                    'bcc-constant' => '= new value =',
                    'subject-constant' => '= new value =',
                    'body-constant' => '= new value =',
                    'body-template' => '= new value =',
                    'body-fields' => '= new value =',
                    'f-option' => '= new value =',
                    'body-wrap' => 68,
                );
                break;
            case 'options':
                break;
            case 'aliases':
                if (!isset($globalOptions['aliases'])) {
                    $globalOptions['aliases'] = array();
                }
                $globalOptions['aliases'][] = array(
                    'alias' => '= new value =',
                    'original' => '= new value =',
                );
                break;
            case 'browser-compatibility':
                if (!isset($globalOptions['browser-compatibility'])) {
                    $globalOptions['browser-compatibility'] = array();
                }
                $globalOptions['browser-compatibility'][] = array(
                    'browserdef' => '= new value =',
                );
                break;
            case 'formatter':
                if (!isset($globalOptions['formatter'])) {
                    $globalOptions['formatter'] = array();
                }
                $globalOptions['formatter'][] = array(
                    'field' => '= new value =',
                    'converter-class' => '= new value =',
                    'parameter' => '= new value =',
                );
                break;
            case 'dbsettings':
                break;
            case 'external-db':
                if (!isset($globalDBSpecs['external-db'])) {
                    $globalDBSpecs['external-db'] = array();
                }
                $globalDBSpecs['external-db'][] = array(
                    'db' => '= new value =',
                );
                break;
            case 'debug':
                break;
        }

        $newFileContent = substr($fileContent, 0, $funcStartPos);
        $newFileContent .= "IM_Entry(";
        $newFileContent .= var_export($globalDataSource, true);
        $newFileContent .= ",\n";
        $newFileContent .= var_export($globalOptions, true);
        $newFileContent .= ",\n";
        $newFileContent .= var_export($globalDBSpecs, true);
        $newFileContent .= ",\n";
        $newFileContent .= var_export($globalDebug, true);
        $newFileContent .= ");\n?>";

        $fileWriteResult = file_put_contents($filePath, $newFileContent);
        if ($fileWriteResult === false) {
            $this->logger->setErrorMessage("The file {$filePath} doesn't have the permission to write.");
            return null;
        }
    }

    function deleteFromDB($dataSourceName)
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;

        $filePath = $this->dbSettings->getValueOfField('target');
        $contextID = $this->dbSettings->getCriteriaValue('id');

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage(
                "The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            return null;
        }
        $funcStartPos = strpos($fileContent, "IM_Entry");
        $convert = str_replace("<?php", "",
            str_replace("?>", "",
                str_replace("IM_Entry", "IM_Dummy_Entry",
                    changeIncludeIMPath(
                        $fileContent,
                        "require_once('../INTER-Mediator.php');"
                    ))));
        eval($convert);

        switch ($dataSourceName) {
            case 'contexts':
                unset($globalDataSource[$contextID]);
                break;
            case 'relation':
            case 'query':
            case 'sort':
            case 'default-values':
            case 'validation':
            case 'global':
            case 'script':
            case 'calculation':
            case 'file-upload':
            case 'sending-email':
                $recordID = $contextID % 10000;
                $contextID = floor($contextID / 10000);
                if (count($globalDataSource[$contextID][$dataSourceName]) < 2) {
                    unset($globalDataSource[$contextID][$dataSourceName]);
                } else {
                    unset($globalDataSource[$contextID][$dataSourceName][$recordID]);
                }
                break;
            case 'options':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                if (strpos($theKey, "authentication-") === 0) {
                    $authKey = substr($theKey, 15);
                    if (!isset($globalOptions["authentication"][$authKey])) {
                        $globalOptions["authentication"][$authKey] = array();
                    }
                    $globalOptions["authentication"][$authKey]
                        = $this->dbSettings->getValueOfField($theKey);
                } else {
                    $globalOptions[$theKey] = $this->dbSettings->getValueOfField($theKey);
                }
                break;
            case 'aliases':
            case 'browser-compatibility':
            case 'formatter':
                $recordID = $contextID % 10000;
                unset($globalOptions[$dataSourceName][$recordID]);
                break;
            case 'debug':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                $globalDebug = $this->dbSettings->getValueOfField($theKey);
                break;
            default:
                break;
        }

        $newFileContent = substr($fileContent, 0, $funcStartPos);
        $newFileContent .= "IM_Entry(";
        $newFileContent .= var_export($globalDataSource, true);
        $newFileContent .= ",\n";
        $newFileContent .= var_export($globalOptions, true);
        $newFileContent .= ",\n";
        $newFileContent .= var_export($globalDBSpecs, true);
        $newFileContent .= ",\n";
        $newFileContent .= var_export($globalDebug, true);
        $newFileContent .= ");\n?>";

        $fileWriteResult = file_put_contents($filePath, $newFileContent);
        if ($fileWriteResult === false) {
            $this->logger->setErrorMessage("The file {$filePath} doesn't have the permission to write.");
            return null;
        }
    }

    function getFieldInfo($dataSourceName)
    {
        // TODO: Implement getFieldInfo() method.
    }

    function authSupportStoreChallenge($username, $challenge, $clientId)
    {
        // TODO: Implement authSupportStoreChallenge() method.
    }

    function authSupportRemoveOutdatedChallenges()
    {
        // TODO: Implement authSupportRemoveOutdatedChallenges() method.
    }

    function authSupportRetrieveChallenge($username, $clientId, $isDelete = true)
    {
        // TODO: Implement authSupportRetrieveChallenge() method.
    }

    function authSupportRetrieveHashedPassword($username)
    {
        // TODO: Implement authSupportRetrieveHashedPassword() method.
    }

    function authSupportCreateUser($username, $hashedpassword)
    {
        // TODO: Implement authSupportCreateUser() method.
    }

    function authSupportChangePassword($username, $hashednewpassword)
    {
        // TODO: Implement authSupportChangePassword() method.
    }

    function authSupportCheckMediaToken($user)
    {
        // TODO: Implement authSupportCheckMediaToken() method.
    }

    function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        // TODO: Implement authSupportCheckMediaPrivilege() method.
    }

    function authSupportGetUserIdFromEmail($email)
    {
        // TODO: Implement authSupportGetUserIdFromEmail() method.
    }

    function authSupportGetUserIdFromUsername($username)
    {
        // TODO: Implement authSupportGetUserIdFromUsername() method.
    }

    function authSupportGetUsernameFromUserId($userid)
    {
        // TODO: Implement authSupportGetUsernameFromUserId() method.
    }

    function authSupportGetGroupNameFromGroupId($groupid)
    {
        // TODO: Implement authSupportGetGroupNameFromGroupId() method.
    }

    function authSupportGetGroupsOfUser($user)
    {
        // TODO: Implement authSupportGetGroupsOfUser() method.
    }

    function authSupportUnifyUsernameAndEmail($username)
    {
        // TODO: Implement authSupportUnifyUsernameAndEmail() method.
    }

    function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        // TODO: Implement authSupportStoreIssuedHashForResetPassword() method.
    }

    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        // TODO: Implement authSupportCheckIssuedHashForResetPassword() method.
    }

    public function setupConnection()
    {
        // TODO: Implement setupConnection() method.
    }

    public static function defaultKey()
    {
        // TODO: Implement defaultKey() method.
    }

    public function getDefaultKey()
    {
        // TODO: Implement getDefaultKey() method.
    }

    public function isPossibleOperator($operator)
    {
        // TODO: Implement isPossibleOperator() method.
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        // TODO: Implement isPossibleOrderSpecifier() method.
    }

    public function requireUpdatedRecord($value)
    {
        // TODO: Implement requireUpdatedRecord() method.
    }

    public function updatedRecord()
    {
        // TODO: Implement updatedRecord() method.
    }

    public function isContainingFieldName($fname, $fieldnames)
    {
        // TODO: Implement isContainingFieldName() method.
    }

    public function isNullAcceptable()
    {
        // TODO: Implement isNullAcceptable() method.
    }

    public function softDeleteActivate($field, $value)
    {
        // TODO: Implement softDeleteActivate() method.
    }
}
