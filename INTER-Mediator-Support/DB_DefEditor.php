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
    if (is_array($value)) {
        $value = implode(",", $value);
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
    private $recordCount;
    private $isRequiredUpdated = false;
    private $updatedRecord = null;

    private $spacialValue = array('IM_TODAY');

    function readFromDB()
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;

        $result = array();
        $dataSourceName = $this->dbSettings->getDataSourceName();

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
                        'soft-delete' => getValueFromArray($context, 'soft-delete'),
                        'post-reconstruct' => getValueFromArray($context, 'post-reconstruct'),
                        'post-dismiss-message' => getValueFromArray($context, 'post-dismiss-message'),
                        'post-move-url' => getValueFromArray($context, 'post-move-url'),
                        'repeat-control' => getValueFromArray($context, 'repeat-control'),
                        'navi-control' => getValueFromArray($context, 'navi-control'),
                        'post-repeater' => getValueFromArray($context, 'post-repeater'),
                        'post-enclosure' => getValueFromArray($context, 'post-enclosure'),
                        'aggregation-select' => getValueFromArray($context, 'aggregation-select'),
                        'aggregation-from' => getValueFromArray($context, 'aggregation-from'),
                        'aggregation-group-by' => getValueFromArray($context, 'aggregation-group-by'),
                        'buttonnames-insert' => getValueFromArray($context, 'button-names', 'insert'),
                        'buttonnames-delete' => getValueFromArray($context, 'button-names', 'delete'),
                        'buttonnames-copy' => getValueFromArray($context, 'button-names', 'copy'),
                        'buttonnames-navi-detail' => getValueFromArray($context, 'button-names', 'navi-detail'),
                        'buttonnames-navi-back' => getValueFromArray($context, 'button-names', 'navi-back'),
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
                        'send-mail-load-from' => getValueFromArray($context, 'send-mail', 'load', 'from'),
                        'send-mail-load-to' => getValueFromArray($context, 'send-mail', 'load', 'to'),
                        'send-mail-load-cc' => getValueFromArray($context, 'send-mail', 'load', 'cc'),
                        'send-mail-load-bcc' => getValueFromArray($context, 'send-mail', 'load', 'bcc'),
                        'send-mail-load-subject' => getValueFromArray($context, 'send-mail', 'load', 'subject'),
                        'send-mail-load-body' => getValueFromArray($context, 'send-mail', 'load', 'body'),
                        'send-mail-load-from-constant' => getValueFromArray($context, 'send-mail', 'load', 'from-constant'),
                        'send-mail-load-to-constant' => getValueFromArray($context, 'send-mail', 'load', 'to-constant'),
                        'send-mail-load-cc-constant' => getValueFromArray($context, 'send-mail', 'load', 'cc-constant'),
                        'send-mail-load-bcc-constant' => getValueFromArray($context, 'send-mail', 'load', 'bcc-constant'),
                        'send-mail-load-subject-constant' => getValueFromArray($context, 'send-mail', 'load', 'subject-constant'),
                        'send-mail-load-body-constant' => getValueFromArray($context, 'send-mail', 'load', 'body-constant'),
                        'send-mail-load-body-template' => getValueFromArray($context, 'send-mail', 'load', 'body-template'),
                        'send-mail-load-body-fields' => getValueFromArray($context, 'send-mail', 'load', 'body-fields'),
                        'send-mail-load-f-option' => getValueFromArray($context, 'send-mail', 'load', 'f-option'),
                        'send-mail-load-body-wrap' => getValueFromArray($context, 'send-mail', 'load', 'body-wrap'),
                        'send-mail-edit-from' => getValueFromArray($context, 'send-mail', 'edit', 'from'),
                        'send-mail-edit-to' => getValueFromArray($context, 'send-mail', 'edit', 'to'),
                        'send-mail-edit-cc' => getValueFromArray($context, 'send-mail', 'edit', 'cc'),
                        'send-mail-edit-bcc' => getValueFromArray($context, 'send-mail', 'edit', 'bcc'),
                        'send-mail-edit-subject' => getValueFromArray($context, 'send-mail', 'edit', 'subject'),
                        'send-mail-edit-body' => getValueFromArray($context, 'send-mail', 'edit', 'body'),
                        'send-mail-edit-from-constant' => getValueFromArray($context, 'send-mail', 'edit', 'from-constant'),
                        'send-mail-edit-to-constant' => getValueFromArray($context, 'send-mail', 'edit', 'to-constant'),
                        'send-mail-edit-cc-constant' => getValueFromArray($context, 'send-mail', 'edit', 'cc-constant'),
                        'send-mail-edit-bcc-constant' => getValueFromArray($context, 'send-mail', 'edit', 'bcc-constant'),
                        'send-mail-edit-subject-constant' => getValueFromArray($context, 'send-mail', 'edit', 'subject-constant'),
                        'send-mail-edit-body-constant' => getValueFromArray($context, 'send-mail', 'edit', 'body-constant'),
                        'send-mail-edit-body-template' => getValueFromArray($context, 'send-mail', 'edit', 'body-template'),
                        'send-mail-edit-body-fields' => getValueFromArray($context, 'send-mail', 'edit', 'body-fields'),
                        'send-mail-edit-f-option' => getValueFromArray($context, 'send-mail', 'edit', 'f-option'),
                        'send-mail-edit-body-wrap' => getValueFromArray($context, 'send-mail', 'edit', 'body-wrap'),
                        'send-mail-new-from' => getValueFromArray($context, 'send-mail', 'new', 'from'),
                        'send-mail-new-to' => getValueFromArray($context, 'send-mail', 'new', 'to'),
                        'send-mail-new-cc' => getValueFromArray($context, 'send-mail', 'new', 'cc'),
                        'send-mail-new-bcc' => getValueFromArray($context, 'send-mail', 'new', 'bcc'),
                        'send-mail-new-subject' => getValueFromArray($context, 'send-mail', 'new', 'subject'),
                        'send-mail-new-body' => getValueFromArray($context, 'send-mail', 'new', 'body'),
                        'send-mail-new-from-constant' => getValueFromArray($context, 'send-mail', 'new', 'from-constant'),
                        'send-mail-new-to-constant' => getValueFromArray($context, 'send-mail', 'new', 'to-constant'),
                        'send-mail-new-cc-constant' => getValueFromArray($context, 'send-mail', 'new', 'cc-constant'),
                        'send-mail-new-bcc-constant' => getValueFromArray($context, 'send-mail', 'new', 'bcc-constant'),
                        'send-mail-new-subject-constant' => getValueFromArray($context, 'send-mail', 'new', 'subject-constant'),
                        'send-mail-new-body-constant' => getValueFromArray($context, 'send-mail', 'new', 'body-constant'),
                        'send-mail-new-body-template' => getValueFromArray($context, 'send-mail', 'new', 'body-template'),
                        'send-mail-new-body-fields' => getValueFromArray($context, 'send-mail', 'new', 'body-fields'),
                        'send-mail-new-f-option' => getValueFromArray($context, 'send-mail', 'new', 'f-option'),
                        'send-mail-new-body-wrap' => getValueFromArray($context, 'send-mail', 'new', 'body-wrap'),
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
                            'notify' => getValueFromArray($rel, 'notify'),
                        );
                        $seq++;
                    }
                }
                break;
            case 'script':
                $contextID = $this->dbSettings->getForeignKeysValue('id');
                if (isset($globalDataSource[$contextID]['script'])) {
                    foreach ($globalDataSource[$contextID]['script'] as $operation => $rel) {
                        $result[] = array(
                            'id' => $seq + $contextID * 10000,
                            'db-operation' => getValueFromArray($rel, 'db-operation'),
                            'situation' => getValueFromArray($rel, 'situation'),
                            'definition' => getValueFromArray($rel, 'definition'),
                            'parameter' => getValueFromArray($rel, 'parameter'),
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
                    foreach ($globalDataSource[$contextID]['global'] as $operation => $rel) {
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
            case 'options':
                $result[] = array(
                    'id' => $seq,
                    'theme' => getValueFromArray($globalOptions, 'theme'),
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
                    'authentication-storing' => getValueFromArray(
                        $globalOptions, 'authentication', 'storing'),
                    'authentication-email-as-username' => getValueFromArray(
                        $globalOptions, 'authentication', 'email-as-username'),
                    'authentication-user' => getValueFromArray(
                        $globalOptions, 'authentication', 'user'),
                    'authentication-group' => getValueFromArray(
                        $globalOptions, 'authentication', 'group'),
                    'authentication-issuedhash-dsn' => getValueFromArray(
                        $globalOptions, 'authentication', 'issuedhash-dsn'),
                    'authentication-password-policy' => getValueFromArray(
                        $globalOptions, 'authentication', 'password-policy'),
                    'smtp-server' => getValueFromArray($globalOptions, 'smtp', 'server'),
                    'smtp-port' => getValueFromArray($globalOptions, 'smtp', 'port'),
                    'smtp-username' => getValueFromArray($globalOptions, 'smtp', 'username'),
                    'smtp-password' => getValueFromArray($globalOptions, 'smtp', 'password'),
                    'pusher-app_id' => getValueFromArray($globalOptions, 'pusher', 'app_id'),
                    'pusher-key' => getValueFromArray($globalOptions, 'pusher', 'key'),
                    'pusher-secret' => getValueFromArray($globalOptions, 'pusher', 'secret'),
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
                    foreach ($globalOptions['browser-compatibility'] as $agent => $vNum) {
                        $result[] = array(
                            'id' => $seq,
                            'agent' => $agent,
                            'version' => $vNum,
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
            case 'local-context':
                if (isset($globalOptions['local-context'])) {
                    foreach ($globalOptions['local-context'] as $rel) {
                        $result[] = array(
                            'id' => $seq,
                            'key' => getValueFromArray($rel, 'key'),
                            'value' => getValueFromArray($rel, 'value'),
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
                    'debug' => $globalDebug === false ? 'false' : $globalDebug
                );
                $seq++;
                break;
        }
        $this->recordCount = $seq;
        return $result;
    }

    function countQueryResult()
    {
        return $this->recordCount;
    }

    function getTotalCount()
    {
        return $this->recordCount;
    }

    function updateDB()
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;

        $dataSourceName = $this->dbSettings->getDataSourceName();
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
            'validation' => array('field', 'rule', 'message', 'notify'),
            'script' => array('db-operation', 'situation', 'definition', 'parameter'),
            'global' => array('db-operation', 'field', 'value'),
            'calculation' => array('field', 'expression'),
            'file-upload' => array('field', 'context', 'container'),
            'send-mail' => array('db-operation', 'from', 'to', 'cc', 'bcc', 'subject', 'body',
                'from-constant', 'to-constant', 'cc-constant', 'bcc-constant', 'subject-constant',
                'body-constant', 'body-template', 'body-fields', 'f-option', 'body-wrap'),
        );
        $allKeysOptions = array(
            'aliases' => array('alias', 'original'),
            'browser-compatibility' => array('browserdef'),
            'formatter' => array('field', 'converter-class', 'parameter'),
            'local-context' => array('key', 'value'),
        );

        $keysShouldInteger = array(
            'records', 'maxrecords', 'smtp-port',
            'send-mail-load-body-wrap', 'send-mail-edit-body-wrap', 'send-mail-new-body-wrap',
        );

        $keysShouldBoolean = array(
            'paging', 'email-as-username', 'portal', 'media-handling', 'post-reconstruct',
            'container', 'soft-delete', 'f-option',
        );

        $keysShouldArray = array(
            'protect-writing', 'protect-reading', 'authentication-all-user', 'authentication-all-group',
            'authentication-load-user', 'authentication-load-group',
            'authentication-update-user', 'authentication-update-group',
            'authentication-new-user', 'authentication-new-group',
            'authentication-delete-user', 'authentication-delete-group',
        );

        // $this->logger->setDebugMessage("dataSourceName={$dataSourceName}");
        $result = null;

        switch ($dataSourceName) {
            case 'contexts':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                if ($theKey == "authentication-media-handling") {
                    if (!isset($globalDataSource[$contextID]["authentication"])) {
                        $globalDataSource[$contextID]["authentication"] = array();
                    }
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (preg_match("/^false$/i", $setValue)) {
                        $setValue = false;
                    } else if (preg_match("/^true$/i", $setValue)) {
                        $setValue = true;
                    }
                    if ($setValue === true || $setValue === false) {
                        $globalDataSource[$contextID]["authentication"]["media-handling"] = $setValue;
                    } else if (isset($globalDataSource[$contextID]["authentication"]["media-handling"])) {
                        unset($globalDataSource[$contextID]["authentication"]["media-handling"]);
                    }
                } else if (strpos($theKey, "authentication-") === 0) {
                    $authKeyArray = explode("-", $theKey);
                    if (!isset($globalDataSource[$contextID][$authKeyArray[0]])) {
                        $globalDataSource[$contextID][$authKeyArray[0]] = array();
                    }
                    if (!isset($globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]])) {
                        $globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]] = array();
                    }
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldArray) !== false) {
                        $setValue = explode(",", str_replace(" ", "", $setValue));
                    }
                    if ((is_array($setValue) && count($setValue) > 0 && strlen($setValue[0]) > 0)
                        || strlen($setValue) > 0
                    ) {
                        $globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]][$authKeyArray[2]] = $setValue;
                    } else if (isset($globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]][$authKeyArray[2]])) {
                        unset($globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]][$authKeyArray[2]]);
                        if (count($globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]]) === 0) {
                            unset($globalDataSource[$contextID][$authKeyArray[0]][$authKeyArray[1]]);
                            if (count($globalDataSource[$contextID][$authKeyArray[0]]) === 0) {
                                unset($globalDataSource[$contextID][$authKeyArray[0]]);
                            }
                        }
                    }
                } else if (strpos($theKey, "protect-") === 0) {
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldArray) !== false) {
                        $setValue = explode(",", str_replace(" ", "", $setValue));
                    }
                    if ((is_array($setValue) && count($setValue) > 0 && strlen($setValue[0]) > 0)
                        || strlen($setValue) > 0
                    ) {
                        $globalDataSource[$contextID][$theKey] = $setValue;
                    } else if (isset($globalDataSource[$contextID][$theKey])) {
                        unset($globalDataSource[$contextID][$theKey]);
                    }
                } else if (strpos($theKey, "buttonnames-") === 0) {
                    $firstKey = "button-names";
                    $secondKey = substr($theKey, 12);
                    if (!isset($globalDataSource[$contextID][$firstKey])) {
                        $globalDataSource[$contextID][$firstKey] = array();
                    }
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (strlen($setValue) > 0 || $setValue === false) {
                        $globalDataSource[$contextID][$firstKey][$secondKey] = $setValue;
                    } else if (isset($globalDataSource[$contextID][$firstKey][$secondKey])) {
                        unset($globalDataSource[$contextID][$firstKey][$secondKey]);
                        if (count($globalDataSource[$contextID][$firstKey]) === 0) {
                            unset($globalDataSource[$contextID][$firstKey]);
                        }
                    }
                } else if (strpos($theKey, "send-mail-") === 0) {
                    $firstKey = "send-mail";
                    $keyRest = substr($theKey, 10);
                    $secondKey = substr($keyRest, 0, strpos($keyRest, "-"));
                    $thirdKey = substr($keyRest, strpos($keyRest, "-") + 1);
                    if (!isset($globalDataSource[$contextID][$firstKey])) {
                        $globalDataSource[$contextID][$firstKey] = array();
                    }
                    if (!isset($globalDataSource[$contextID][$firstKey][$secondKey])) {
                        $globalDataSource[$contextID][$firstKey][$secondKey] = array();
                    }
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldInteger) !== false) {
                        $setValue = ($setValue === '') ? '' : (int)$setValue;
                    } else if (array_search($thirdKey, $keysShouldBoolean) !== false) {
                        if (preg_match("/^false$/i", $setValue)) {
                            $setValue = false;
                        } else if (preg_match("/^true$/i", $setValue)) {
                            $setValue = true;
                        }
                    }
                    if (strlen($setValue) > 0 || $setValue === false) {
                        $globalDataSource[$contextID][$firstKey][$secondKey][$thirdKey] = $setValue;
                    } else if (isset($globalDataSource[$contextID][$firstKey][$secondKey][$thirdKey])) {
                        unset($globalDataSource[$contextID][$firstKey][$secondKey][$thirdKey]);
                        if (count($globalDataSource[$contextID][$firstKey][$secondKey]) === 0) {
                            unset($globalDataSource[$contextID][$firstKey][$secondKey]);
                            if (count($globalDataSource[$contextID][$firstKey]) === 0) {
                                unset($globalDataSource[$contextID][$firstKey]);
                            }
                        }
                    }
                } else {
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldInteger) !== false) {
                        $setValue = ($setValue === '') ? '' : (int)$setValue;
                    } else if (array_search($theKey, $keysShouldBoolean) !== false) {
                        if (preg_match("/(false)/i", $setValue)) {
                            $setValue = false;
                        } else if (preg_match("/(true)/i", $setValue)) {
                            $setValue = true;
                        }
                    }
                    if (strlen($setValue) > 0 || $setValue === false) {
                        $globalDataSource[$contextID][$theKey] = $setValue;
                    } else if (isset($globalDataSource[$contextID][$theKey])) {
                        unset($globalDataSource[$contextID][$theKey]);
                    }
                }
                $result = array($globalDataSource[$contextID]);
                break;
            case 'relation':
            case 'query':
            case 'sort':
            case 'default-values':
            case 'validation':
            case 'calculation':
            case 'file-upload':
            case 'global':
            case 'script':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                foreach ($allKeys[$dataSourceName] as $key) {
                    $fieldValue = $this->dbSettings->getValueOfField($key);
                    if (array_search($key, $keysShouldInteger) !== false) {
                        $fieldValue = ($fieldValue === '') ? '' : (int)$fieldValue;
                    } else if (array_search($key, $keysShouldBoolean) !== false) {
                        if (preg_match("/(false)/i", $fieldValue)) {
                            $fieldValue = false;
                        } else if (preg_match("/(true)/i", $fieldValue)) {
                            $fieldValue = true;
                        } else {
                            $fieldValue = null;
                        }
                    }
                    $contextIndex = floor($contextID / 10000);
                    $itemIndex = $contextID % 10000;
                    if (!is_null($fieldValue)) {
                        $globalDataSource[$contextIndex][$dataSourceName][$itemIndex][$key] = $fieldValue;
                    } else if ($key === $theKey &&
                        isset($globalDataSource[$contextIndex][$dataSourceName][$itemIndex][$key])
                    ) {
                        unset($globalDataSource[$contextIndex][$dataSourceName][$itemIndex][$key]);
                    }
                }
                $result = array($globalDataSource[$contextIndex][$dataSourceName][$itemIndex]);
                break;
            case 'options':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                if (strpos($theKey, "authentication-") === 0) {
                    $authKey = substr($theKey, 15);
                    if (!isset($globalOptions["authentication"][$authKey])) {
                        $globalOptions["authentication"][$authKey] = array();
                    }
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if ($authKey === "email-as-username") {
                        if (preg_match("/^false$/i", $setValue)) {
                            $setValue = false;
                        } else if (preg_match("/^true$/i", $setValue)) {
                            $setValue = true;
                        }
                        if ($setValue === true || $setValue === false) {
                            $globalOptions["authentication"]["email-as-username"] = $setValue;
                        } else if (isset($globalOptions["authentication"]["email-as-username"])) {
                            unset($globalOptions["authentication"]["email-as-username"]);
                        }
                    } else if ($authKey === "user" || $authKey === "group") {
                        $setValue = explode(",", str_replace(" ", "", $setValue));
                        if ((is_array($setValue) && count($setValue) > 0 && strlen($setValue[0]) > 0)
                            || strlen($setValue) > 0
                        ) {
                            $globalOptions["authentication"][$authKey] = $setValue;
                        } else if (isset($globalOptions["authentication"][$authKey])) {
                            unset($globalOptions["authentication"][$authKey]);
                        }
                    } else {
                        if (strlen($setValue) > 0 || $setValue === false) {
                            $globalOptions["authentication"][$authKey] = $setValue;
                        } else if (isset($globalOptions["authentication"][$authKey])) {
                            unset($globalOptions["authentication"][$authKey]);
                        }
                    }
                    if (count($globalOptions["authentication"]) === 0) {
                        unset($globalOptions["authentication"]);
                    }
                } else if (strpos($theKey, "smtp-") === 0) {
                    $authKey = substr($theKey, 5);
                    if (!isset($globalOptions["smtp"][$authKey])) {
                        $globalOptions["smtp"][$authKey] = array();
                    }
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldInteger) !== false) {
                        $setValue = ($setValue === '') ? '' : (int)$setValue;
                    }
                    if (strlen($setValue) > 0 || $setValue === false) {
                        $globalOptions["smtp"][$authKey] = $setValue;
                    } else if (isset($globalOptions["smtp"][$authKey])) {
                        unset($globalOptions["smtp"][$authKey]);
                        if (count($globalOptions["smtp"]) === 0) {
                            unset($globalOptions["smtp"]);
                        }
                    }
                } else if (strpos($theKey, "pusher-") === 0) {
                    $authKey = substr($theKey, 7);
                    if (!isset($globalOptions["pusher"][$authKey])) {
                        $globalOptions["pusher"][$authKey] = array();
                    }
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldInteger) !== false) {
                        $setValue = ($setValue === '') ? '' : (int)$setValue;
                    }
                    if (strlen($setValue) > 0 || $setValue === false) {
                        $globalOptions["pusher"][$authKey] = $setValue;
                    } else if (isset($globalOptions["pusher"][$authKey])) {
                        unset($globalOptions["pusher"][$authKey]);
                        if (count($globalOptions["pusher"]) === 0) {
                            unset($globalOptions["pusher"]);
                        }
                    }
                } else {
                    $setValue = $this->dbSettings->getValueOfField($theKey);
                    if (array_search($theKey, $keysShouldInteger) !== false) {
                        $setValue = ($setValue === '') ? '' : (int)$setValue;
                    } else if (array_search($theKey, $keysShouldBoolean) !== false) {
                        if (preg_match("/(false)/i", $setValue)) {
                            $setValue = false;
                        } else if (preg_match("/(true)/i", $setValue)) {
                            $setValue = true;
                        }
                    }
                    if (strlen($setValue) > 0 || $setValue === false) {
                        $globalOptions[$theKey] = $setValue;
                    } else if (isset($globalOptions[$theKey])) {
                        unset($globalOptions[$theKey]);
                    }
                }
                $result = array($globalOptions);
                break;
            case 'aliases':
            case 'formatter':
            case 'local-context':
                $recordID = $contextID % 10000;
                foreach ($allKeysOptions[$dataSourceName] as $key) {
                    $fieldValue = $this->dbSettings->getValueOfField($key);
                    if (!is_null($fieldValue)) {
                        $globalOptions[$dataSourceName][$recordID][$key] = $fieldValue;
                        break;
                    }
                }
                $result = array($globalOptions[$dataSourceName][$recordID]);
                break;
            case 'browser-compatibility':
                $recordID = $contextID % 10000;
                $key = $this->dbSettings->getFieldOfIndex(1);
                $pValue = $this->dbSettings->getValueOfField($key);
                // $this->logger->setDebugMessage("key={$key}, pValue={$pValue}");
                if (!is_null($pValue)) {
                    $currentAgents = array_keys($globalOptions[$dataSourceName]);
                    $tempBCArray = array();
                    if ($key == 'agent') {
                        //$agentIndex = array_keys($currentAgents, $pValue);
                        for ($i = 0; $i < count($currentAgents); $i++) {
                            if ($i == $recordID) {
                                $tempBCArray[$pValue]
                                    = $globalOptions[$dataSourceName][$currentAgents[$i]];
                            } else {
                                $tempBCArray[$currentAgents[$i]]
                                    = $globalOptions[$dataSourceName][$currentAgents[$i]];
                            }
                        }
                    } else if ($key == 'version') {
                        for ($i = 0; $i < count($currentAgents); $i++) {
                            if ($i == $recordID) {
                                $tempBCArray[$currentAgents[$i]] = $pValue;
                            } else {
                                $tempBCArray[$currentAgents[$i]]
                                    = $globalOptions[$dataSourceName][$currentAgents[$i]];
                            }
                        }
                    }
                    $globalOptions[$dataSourceName] = $tempBCArray;
                }
                $result = array($globalOptions);
                break;
            case 'dbsettings':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                $globalDBSpecs[$theKey] = $this->dbSettings->getValueOfField($theKey);
                $result = array($globalDBSpecs);
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
                $result = array($globalDBSpecs['external-db']);
                break;
            case 'debug':
                $theKey = $this->dbSettings->getFieldOfIndex(1);
                $globalDebug = $this->dbSettings->getValueOfField($theKey);
                $globalDebug = ($globalDebug === 'false' || $globalDebug === '') ? false : intval($globalDebug);
                $result = array(array('id' => 0, 'debug' => $globalDebug));
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
        $newFileContent .= ");\n";

        $sq = "'";
        foreach ($this->spacialValue as $term) {
            $newFileContent = str_replace($sq . $term . $sq, $term, $newFileContent);
            $fileWriteResult = file_put_contents($filePath, $newFileContent);
            if ($fileWriteResult === false) {
                $this->logger->setErrorMessage("The file {$filePath} doesn't have the permission to write.");
                return null;
            }
        }
        $this->updatedRecord = $result;
        return $result;
    }

    function createInDB($bypassAuth)
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;
        $dataSourceName = $this->dbSettings->getDataSourceName();

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
                    'parameter' => '= new value =',
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
                $index = count($globalOptions['browser-compatibility']);
                $globalOptions['browser-compatibility']["agent{$index}"] = '= version =';
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
            case 'local-context':
                if (!isset($globalOptions['local-context'])) {
                    $globalOptions['local-context'] = array();
                }
                $globalOptions['local-context'][] = array(
                    'key' => '= new value =',
                    'value' => '= new value =',
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

    function deleteFromDB()
    {
        global $globalDataSource, $globalOptions, $globalDBSpecs, $globalDebug;

        $dataSourceName = $this->dbSettings->getDataSourceName();
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
            case 'calculation':
            case 'file-upload':
                $recordID = $contextID % 10000;
                $contextID = floor($contextID / 10000);
                if (count($globalDataSource[$contextID][$dataSourceName]) < 2) {
                    unset($globalDataSource[$contextID][$dataSourceName]);
                } else {
                    unset($globalDataSource[$contextID][$dataSourceName][$recordID]);
                }
                break;
            case 'global':
            case 'script':
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
            case 'formatter':
            case 'local-context':
                $recordID = $contextID % 10000;
                unset($globalOptions[$dataSourceName][$recordID]);
                if (count($globalOptions[$dataSourceName]) < 1) {
                    unset($globalOptions[$dataSourceName]);
                }
                break;
            case 'browser-compatibility':
                $recordID = $contextID % 10000;
                $keys = array_keys($globalOptions[$dataSourceName]);
                unset($globalOptions[$dataSourceName][$keys[$recordID]]);
                if (count($globalOptions[$dataSourceName]) < 1) {
                    unset($globalOptions[$dataSourceName]);
                }
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

    public
    function getDefaultKey()
    {
        // TODO: Implement getDefaultKey() method.
    }

    public
    function isPossibleOperator($operator)
    {
        // TODO: Implement isPossibleOperator() method.
    }

    public
    function isPossibleOrderSpecifier($specifier)
    {
        // TODO: Implement isPossibleOrderSpecifier() method.
    }

    public
    function requireUpdatedRecord($value)
    {
        $this->isRequiredUpdated = $value;
    }

    public
    function updatedRecord()
    {
        return $this->updatedRecord;
    }

    public
    function isContainingFieldName($fname, $fieldnames)
    {
        // TODO: Implement isContainingFieldName() method.
    }

    public
    function isNullAcceptable()
    {
        // TODO: Implement isNullAcceptable() method.
    }

    public
    function softDeleteActivate($field, $value)
    {
        // TODO: Implement softDeleteActivate() method.
    }

    public
    function copyInDB()
    {
        return false;
    }

    public
    function isSupportAggregation()
    {
        return false;

    }

    public
    function getFieldInfo($dataSourceName)
    {
        // TODO: Implement getFieldInfo() method.
    }

    public
    function setupConnection()
    {
        return true;
    }

    public
    static function defaultKey()
    {
        // TODO: Implement defaultKey() method.
    }

    public
    function authSupportStoreChallenge($uid, $challenge, $clientId)
    {
        // TODO: Implement authSupportStoreChallenge() method.
    }

    public
    function authSupportRemoveOutdatedChallenges()
    {
        // TODO: Implement authSupportRemoveOutdatedChallenges() method.
    }

    public
    function authSupportRetrieveChallenge($uid, $clientId, $isDelete = true)
    {
        // TODO: Implement authSupportRetrieveChallenge() method.
    }

    public
    function authSupportCheckMediaToken($uid)
    {
        // TODO: Implement authSupportCheckMediaToken() method.
    }

    public
    function authSupportRetrieveHashedPassword($username)
    {
        // TODO: Implement authSupportRetrieveHashedPassword() method.
    }

    public
    function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null)
    {
        // TODO: Implement authSupportCreateUser() method.
    }

    public
    function authSupportChangePassword($username, $hashednewpassword)
    {
        // TODO: Implement authSupportChangePassword() method.
    }

    public
    function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        // TODO: Implement authSupportCheckMediaPrivilege() method.
    }

    public
    function authSupportGetUserIdFromEmail($email)
    {
        // TODO: Implement authSupportGetUserIdFromEmail() method.
    }

    public
    function authSupportGetUserIdFromUsername($username)
    {
        // TODO: Implement authSupportGetUserIdFromUsername() method.
    }

    public
    function authSupportGetUsernameFromUserId($userid)
    {
        // TODO: Implement authSupportGetUsernameFromUserId() method.
    }

    public
    function authSupportGetGroupNameFromGroupId($groupid)
    {
        // TODO: Implement authSupportGetGroupNameFromGroupId() method.
    }

    public
    function authSupportGetGroupsOfUser($user)
    {
        // TODO: Implement authSupportGetGroupsOfUser() method.
    }

    public
    function authSupportUnifyUsernameAndEmail($username)
    {
        // TODO: Implement authSupportUnifyUsernameAndEmail() method.
    }

    public
    function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        // TODO: Implement authSupportStoreIssuedHashForResetPassword() method.
    }

    public
    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        // TODO: Implement authSupportCheckIssuedHashForResetPassword() method.
    }

    public function authSupportUserEnrollmentStart($userid, $hash)
    {
        // TODO: Implement authSupportUserEnrollmentStart() method.
    }

    public function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW)
    {
        // TODO: Implement authSupportUserEnrollmentActivateUser() method.
    }

    public function authSupportUserEnrollmentEnrollingUser($hash)
    {
        // TODO: Implement authSupportUserEnrollmentEnrollingUser() method.
    }
}
