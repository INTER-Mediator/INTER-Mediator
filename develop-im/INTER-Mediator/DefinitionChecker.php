<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2012 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2012/10/10
 * Time: 16:19
 * To change this template use File | Settings | File Templates.
 */
class DefinitionChecker
{

    function checkDefinitions($datasource, $options, $dbspecification)
    {
        $allMessage = '';
        $this->checkDefinition($datasource, $this->prohibitKeywordsForDataSource);
        if (strlen($this->message) > 0) {
            $allMessage .= "The Data Sources of the Definition: " . $this->message;
        }
        $this->checkDefinition($options, $this->prohibitKeywordsForOption);
        if (strlen($this->message) > 0) {
            $allMessage .= "The Options of the Definition: " . $this->message;
        }
        $this->checkDefinition($dbspecification, $this->prohibitKeywordsForDBSpec);
        if (strlen($this->message) > 0) {
            $allMessage .= "The DB Specification of the Definition: " . $this->message;
        }
        return $allMessage;
    }

    function checkDefinition($definition, $prohibit)
    {
        $this->message = '';
        $this->path = array();
        $this->currentProhibit = $prohibit;
        $this->moveChildren($definition);
    }

    function moveChildren($items)
    {
        $endPoint = $this->currentProhibit;
        $currentPath = '';
        foreach ($this->path as $value) {
            $nextEndPoint = $endPoint[$value];
            if ($nextEndPoint === null && is_integer($value)) {
                $nextEndPoint = $endPoint['*'];
            }
            if ($nextEndPoint === null && is_string($value)) {
                $nextEndPoint = $endPoint['#'];
            }
            $endPoint = $nextEndPoint;
            $currentPath .= "[{$value}]";
        }
//        $this->message .= "######Checked - $currentPath/endpoint=" . var_export($endPoint, true);

        if (is_array($endPoint)) {
            if (is_array($items)) {
                foreach ($items as $key => $value) {
                    array_push($this->path, $key);
                    $this->moveChildren($value);
                    array_pop($this->path);
                }
            } else {
                $this->message .= "$currentPath should be define as array. ";
            }
        } else {
            $judge = false;
            if ($endPoint === null) {
                $this->message .= "$currentPath includes an undefined keyword. ";
            } else if ($endPoint === 'string') {
                if (is_string($items)) {
                    $judge = true;
                } else {
                    $this->message .= "$currentPath should be define as string. ";
                }
            } else if ($endPoint === 'scalar') {
                if (is_scalar($items)) {
                    $judge = true;
                } else {
                    $this->message .= "$currentPath should be define as string. ";
                }
            } else if ($endPoint === 'boolean') {
                if (is_bool($items)) {
                    $judge = true;
                } else {
                    $this->message .= "$currentPath should be define as boolean. ";
                }
            } else if ($endPoint === 'integer') {
                if (is_integer($items)) {
                    $judge = true;
                } else {
                    $this->message .= "$currentPath should be define as integer. ";
                }
            } else if ($endPoint === 'array') {
                if (is_array($items)) {
                    $judge = true;
                } else {
                    $this->message .= "$currentPath should be define as array. ";
                }
            } else if (strpos('string', $endPoint) === 0) {
                $openParen = strpos('(', $endPoint);
                $closeParen = strpos(')', $endPoint);
                $possibleString = substr($endPoint, $openParen + 1, $closeParen - $openParen - 1);
                $possibleValues = explode("|", $possibleString);
                if (in_array($items, $possibleValues)) {
                    $judge = true;
                } else {
                    $this->message = "$currentPath should be define as string within [$possibleString]. ";
                }
            }
            if ($judge) {

            }
        }
    }


    var
        $message;
    var
        $path;
    var
        $currentProhibit;
    var
        $prohibitKeywordsForDBSpec = array(
        'db-class' => 'string',
        'dsn' => 'string',
        'option' => 'string',
        'database' => 'string',
        'user' => 'string',
        'password' => 'string',
        'server' => 'string',
        'port' => 'string',
        'protocol' => 'string',
        'datatype' => 'string',
    );
    var
        $prohibitKeywordsForOption = array(
        'separator' => 'string',
        'formatter' => array(
            '*' => array('field' => 'string',
                'converter-class' => 'string',
                'parameter' => 'string',
            ),
        ),
        'aliases' => array(
            '#' => 'string',
        ),
        'browser-compatibility' => array(
            '#' => 'string',
        ),
        'transaction' => 'string(none|automatic)',
        'authentication' => array(
            'user' => 'array',
            'group' => 'array',
            'user-table' => 'string',
            'group-table' => 'string',
            'corresponding-table' => 'string',
            'challenge-table' => 'string',
            'authexpired' => 'string',
            'storing' => 'string',
        ),
        'media-root-dir'=> 'string',
    );
    var
        $prohibitKeywordsForDataSource = array(
        '*' => array(
            'name' => 'string',
            'table' => 'string',
            'view' => 'string',
            'records' => 'integer',
            'paging' => 'boolean',
            'key' => 'string',
            'sequence' => 'string',
            'relation' => array(
                '*' => array(
                    'foreign-key' => 'string',
                    'join-field' => 'string',
                    'operator' => 'string'
                )
            ),
            'query' => array(
                '*' => array(
                    'field' => 'string',
                    'value' => 'scalar',
                    'operator' => 'string'
                )
            ),
            'sort' => array(
                '*' => array(
                    'field' => 'string',
                    'direction' => 'string'
                )
            ),
            'default-values' => array(
                '*' => array(
                    'field' => 'string',
                    'value' => 'scalar'
                )
            ),
            'repeat-control' => 'string(insert|delete|confirm-insert|confirm-delete)',
            'validation' => array(
                '*' => array(
                    'field' => 'string',
                    'rule' => 'string',
                    'message' => 'string',
                )
            ),
            'post-repeater' => 'string',
            'post-enclosure' => 'string',
            'script' => array(
                '*' => array(
                    'db-operation' => 'string(load|update|new|delete)',
                    'situation' => 'string(pre|presort|post)',
                    'definition' => 'string'
                )
            ),
            'global' => array(
                '*' => array(
                    'db-operation' => 'string(load|update|new|delete)',
                    'field' => 'string',
                    'value' => 'scalar'
                )
            ),
            'authentication' => array(
                'media-handling' => 'boolean',
                'all' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                ),
                'load' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                ),
                'update' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                ),
                'new' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                ),
                'delete' => array(
                    'user' => 'array',
                    'group' => 'array',
                    'target' => 'string(table|field-user|field-group)',
                    'field' => 'string'
                )
            ),
            'extending-class' => 'string',
            'protect-writing' => 'array',
            'protect-reading' => 'array',
            'db-class' => 'string',
            'dsn' => 'string',
            'option' => 'string',
            'database' => 'string',
            'user' => 'string',
            'password' => 'string',
            'server' => 'string',
            'port' => 'string',
            'protocol' => 'string',
            'datatype' => 'string',
        ),
    );

}
