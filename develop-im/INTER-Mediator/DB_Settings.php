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
 * Date: 12/05/20
 * Time: 14:22
 * To change this template use File | Settings | File Templates.
 */
class DB_Settings
{
    private $dbSpecServer = null;
    private $dbSpecPort = null;
    private $dbSpecUser = null;
    private $dbSpecPassword = null;
    private $dbSpecDatabase = null;
    private $dbSpecDataType = null;
    private $dbSpecProtocol = null;
    private $dbSpecDSN = null;
    private $dbSpecOption = null;

    private $dataSource = null;
    private $targetDataSource = null;
    private $dataSourceName = '';
    private $recordCount = 0;
    private $start = 0;
    private $separator = null;

    private $extraCriteria = array();
    private $extraSortKey = array();
    private $fieldsRequired = array();
    private $fieldsValues = array();
    private $foreignFieldAndValue = array();
    private $currentDataAccess = null;

    private $currentUser = null;
    private $authentication = null;
    private $accessUser = null;
    private $accessPassword = null;
    private $primaryKeyOnly = false;
    private $isDBNative = false;
    private $requireAuthorization = false;
    private $requireAuthentication = false;


    /**
     * @param string $dataSourceName
     */
    public function setDataSourceName($dataSourceName)
    {
        $this->dataSourceName = $dataSourceName;
    }

    /**
     * @return string
     */
    public function getDataSourceName()
    {
        return $this->dataSourceName;
    }

    /**
     * @param array $fieldsRequired
     */
    public function setFieldsRequired($fieldsRequired)
    {
        $this->fieldsRequired = $fieldsRequired;
    }

    /**
     * @return array
     */
    public function getFieldsRequired()
    {
        return $this->fieldsRequired;
    }

    /**
     * @return array
     */
    public function getValue()
    {
        return $this->fieldsValues;
    }

    /**
     * @param array $foreignFieldAndValue
     */
    public function setForeignFieldAndValue($foreignFieldAndValue)
    {
        $this->foreignFieldAndValue = $foreignFieldAndValue;
    }

    /**
     * @return array
     */
    public function getForeignFieldAndValue()
    {
        return $this->foreignFieldAndValue;
    }

    /**
     * @param boolean $isDBNative
     */
    public function setDBNative($isDBNative)
    {
        $this->isDBNative = $isDBNative;
    }

    /**
     * @return boolean
     */
    public function isDBNative()
    {
        return $this->isDBNative;
    }

    /**
     * @param boolean $requireAuthentication
     */
    public function setRequireAuthentication($requireAuthentication)
    {
        $this->requireAuthentication = $requireAuthentication;
    }

    /**
     * @return boolean
     */
    public function getRequireAuthentication()
    {
        return $this->requireAuthentication;
    }

    /**
     * @param boolean $requireAuthorization
     */
    public function setRequireAuthorization($requireAuthorization)
    {
        $this->requireAuthorization = $requireAuthorization;
    }

    /**
     * @return boolean
     */
    public function getRequireAuthorization()
    {
        return $this->requireAuthorization;
    }

    /**
     * @param null $targetDataSource
     */
    public function setTargetDataSource($targetDataSource)
    {
        $this->targetDataSource = $targetDataSource;
    }

    /**
     * @return null
     */
    public function getTargetDataSource()
    {
        return $this->targetDataSource;
    }


     /**
     * @param boolean $primaryKeyOnly
     */
    public function setPrimaryKeyOnly($primaryKeyOnly)
    {
        $this->primaryKeyOnly = $primaryKeyOnly;
    }

    /**
     * @return boolean
     */
    public function getPrimaryKeyOnly()
    {
        return $this->primaryKeyOnly;
    }

    private $emailAsAccount = false;

    /**
     * @param boolean $emailAsAccount
     */
    public function setEmailAsAccount($emailAsAccount)
    {
        $this->emailAsAccount = $emailAsAccount;
    }

    /**
     * @return boolean
     */
    public function getEmailAsAccount()
    {
        return $this->emailAsAccount;
    }

    function getCurrentDataAccess()
    {
        return $this->currentDataAccess;
    }

    function setCurrentDataAccess($dbaccess)
    {
        $this->currentDataAccess = $dbaccess;
    }

    /* Database connection paramters */
    function setDbSpecServer($str)
    {
        $this->dbSpecServer = $str;
    }

    function getDbSpecServer()
    {
        return $this->dbSpecServer;
    }

    function setDbSpecPort($str)
    {
        $this->dbSpecPort = $str;
    }

    function getDbSpecPort()
    {
        return $this->dbSpecPort;
    }

    function setDbSpecUser($str)
    {
        $this->dbSpecUser = $str;
    }

    function getDbSpecUser()
    {
        return $this->dbSpecUser;
    }

    function setDbSpecPassword($str)
    {
        $this->dbSpecPassword = $str;
    }

    function getDbSpecPassword()
    {
        return $this->dbSpecPassword;
    }

    function setDbSpecDataType($str)
    {
        $this->dbSpecDataType = $str;
    }

    function getDbSpecDataType()
    {
        return $this->dbSpecDataType;
    }

    function setDbSpecDatabase($str)
    {
        $this->dbSpecDatabase = $str;
    }

    function getDbSpecDatabase()
    {
        return $this->dbSpecDatabase;
    }

    function setDbSpecProtocol($str)
    {
        $this->dbSpecProtocol = $str;
    }

    function getDbSpecProtocol()
    {
        return $this->dbSpecProtocol;
    }

    function setDbSpecDSN($str)
    {
        $this->dbSpecDSN = $str;
    }

    function getDbSpecDSN()
    {
        return $this->dbSpecDSN;
    }

    function setDbSpecOption($str)
    {
        $this->dbSpecOption = $str;
    }

    function getDbSpecOption()
    {
        return $this->dbSpecOption;
    }

    function getAccessUser()
    {
        return $this->accessUser != null ? $this->accessUser : $this->dbSpecUser;
    }

    function getAccessPassword()
    {
        return $this->accessPassword != null ? $this->accessPassword : $this->dbSpecPassword;
    }

    function setUserAndPasswordForAccess($user, $pass)
    {
        $this->accessUser = $user;
        $this->accessPassword = $pass;
    }

    /* Call on INTER-Mediator.php */

    /**
     * @param array $authentication
     */
    public function setAuthentication($authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * @return array
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    public function getAuthenticationItem($key)
    {
        if (isset($this->authentication[$key])) {
            return $this->authentication[$key];
        }
        switch($key)    {
            case 'user-table':          return 'authuser';      break;
            case 'group-table':         return 'authgroup';     break;
            case 'corresponding-table': return 'authcor';       break;
            case 'challenge-table':     return 'issuedhash';    break;
            case 'authexpired':         return 3600 * 8;        break;
        }
        return null;
    }

    function getUserTable()
    {
        return $this->getAuthenticationItem('user-table');
    }

    function getGroupTable()
    {
        return $this->getAuthenticationItem('group-table');
    }

    function getCorrTable()
    {
        return $this->getAuthenticationItem('corresponding-table');
    }

    function getHashTable()
    {
        return $this->getAuthenticationItem('challenge-table');
    }

    function getExpiringSeconds()
    {
        return $this->getAuthenticationItem('authexpired');
    }

    function setCurrentUser($str)
    {
        $this->currentUser = $str;
    }

    function getCurrentUser()
    {
        return $this->currentUser;
    }

    function setDataSource($src)
    {
        $this->dataSource = $src;
    }

    function getIndexOfDataSource($dataSourceName)
    {
        foreach ($this->dataSource as $index => $value) {
            if ($value['name'] == $dataSourceName) {
                return $index;
            }
        }
        return null;
    }

    function setSeparator($sep)
    {
        $this->separator = $sep;
    }

    function getSeparator()
    {
        return $this->separator;
    }

    function setTargetName($val)
    {
        $this->dataSourceName = $val;
    }

    function getTargetName()
    {
        return $this->dataSourceName;
    }

    function addTargetField($field)
    {
        $this->fieldsRequired[] = $field;
    }

    function setTargetFields($fields)
    {
        $this->fieldsRequired = $fields;
    }

    function addValue($value)
    {
        $this->fieldsValues[] = $value;
    }

    function setValue($values)
    {
        $this->fieldsValues = $values;
    }

    function setStart($st)
    {
        $this->start = $st;
    }

    function getStart()
    {
        return $this->start;
    }

    function getRecordCount()
    {
        return $this->recordCount;
    }

    function setRecordCount($sk)
    {
        $this->recordCount = $sk;
    }

    function getExtraCriteria()
    {
        return $this->extraCriteria;
    }

    function addExtraCriteria($field, $operator, $value)
    {
        $this->extraCriteria[] = array('field' => $field, 'operator' => $operator, 'value' => $value);
    }

    function getCriteriaValue($targetField)
    {
        foreach ($this->getExtraCriteria() as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["value"];
            }
        }
        return null;
    }

    function getCriteriaOperator($targetField)
    {
        foreach ($this->getExtraCriteria() as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["operator"];
            }
        }
        return null;
    }

    function addExtraSortKey($field, $direction)
    {
        $this->extraSortKey[] = array('field' => $field, 'direction' => $direction);
    }

    function getExtraSortKey()
    {
        return $this->extraSortKey;
    }

    function addForeignValue($field, $value)
    {
        $this->foreignFieldAndValue[] = array('field' => $field, 'value' => $value);
    }

    function getForeignKeysValue($targetField)
    {
        foreach ($this->foreignFieldAndValue as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["value"];
            }
        }
        return null;
    }

    function setGlobalInContext($contextName, $operation, $field, $value)
    {
        foreach ($this->dataSource as $index => $record) {
            if ($record['name'] == $contextName) {
                if (!isset($this->dataSource[$index]['global'])) {
                    $this->dataSource[$index]['global'] = array();
                }
                $this->dataSource[$index]['global'][] = array(
                    'db-operation' => $operation,
                    'field' => $field,
                    'value' => $value);
                return;
            }
        }
    }

    /* get the information for the 'name'. */
    function getDataSourceTargetArray($isAssociative = false)
    {
        if ($this->targetDataSource == null) {
            foreach ($this->dataSource as $record) {
                if ($record['name'] == $this->dataSourceName) {
                    //    $this->targetDataSource = $record;
                    if ($isAssociative) {
                        $resultArray = array();
                        foreach ($record as $key => $value) {
                            $resultArray[$key] = $value;
                        }
                        return $resultArray;
                    } else {
                        return $record;
                    }
                }
            }
        } else {
            return $this->targetDataSource;
        }
        return null;
    }

    function getEntityForRetrieve()
    {
        $dsrc = $this->getDataSourceTargetArray();
        if (isset($dsrc['view'])) {
            return $dsrc['view'];
        }
        return $dsrc['name'];
    }

    function getEntityForUpdate()
    {
        $dsrc = $this->getDataSourceTargetArray();
        if (isset($dsrc['table'])) {
            return $dsrc['table'];
        }
        return $dsrc['name'];
    }

}
