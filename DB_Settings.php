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
    private $options = null;
    private $dbSpec = null;
//    private $targetDataSource = null;
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

    private $smtpConfiguration = null;
    private $associated = null;
    /**
     * @var
     */
    public $notifyServer = null;
    public $clientNotificationId = null;
    public $registerTableName = "registeredcontext";
    public $registerPKTableName = "registeredpks";

    public $pusherAppId = null;
    public $pusherKey = null;
    public $pusherSecret = null;
    public $pusherChannel = "_im_pusher_default_channel";

    private $params_ldapServer;
    private $params_ldapPort;
    private $params_ldapBase;
    private $params_ldapContainer;
    private $params_ldapAccountKey;
    private $params_ldapExpiringSeconds;

    private $aggregation_select = null;
    private $aggregation_from = null;
    private $aggregation_group_by = null;

    function __construct()
    {
        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }
        $this->params_ldapServer = isset($ldapServer) ? $ldapServer : null;
        $this->params_ldapPort = isset($ldapPort) ? $ldapPort : null;
        $this->params_ldapBase = isset($ldapBase) ? $ldapBase : null;
        $this->params_ldapContainer = isset($ldapContainer) ? $ldapContainer : null;
        $this->params_ldapAccountKey = isset($ldapAccountKey) ? $ldapAccountKey : null;
        $this->params_ldapExpiringSeconds = isset($ldapExpiringSeconds) ? $ldapExpiringSeconds : 600;
    }

    public function getAggregationSelect()
    {
        return $this->aggregation_select;
    }

    public function setAggregationSelect($value)
    {
        $this->aggregation_select = $value;
    }

    public function getAggregationFrom()
    {
        return $this->aggregation_from;
    }

    public function setAggregationFrom($value)
    {
        $this->aggregation_from = $value;
    }

    public function getAggregationGroupBy()
    {
        return $this->aggregation_group_by;
    }

    public function setAggregationGroupBy($value)
    {
        $this->aggregation_group_by = $value;
    }

    public function getLDAPSettings()
    {
        return array(
            $this->params_ldapServer,
            $this->params_ldapPort,
            $this->params_ldapBase,
            $this->params_ldapContainer,
            $this->params_ldapAccountKey,
        );
    }

    public function addAssociated($name, $field, $value)
    {
        if (!$this->associated) {
            $this->associated = array();
        }
        $this->associated[] = array("name" => $name, "field" => $field, "value" => $value);
    }

    public function getAssociated()
    {
        return $this->associated;
    }

    /**
     * @param string $dataSourceName
     */
    public function setSmtpConfiguration($config)
    {
        $this->smtpConfiguration = $config;
    }

    /**
     * @return string
     */
    public function getSmtpConfiguration()
    {
        return $this->smtpConfiguration;
    }

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

    public function getValuesWithFields()
    {
        $result = array();
        $requiredFields = $this->getFieldsRequired();
        $countFields = count($requiredFields);
        $fieldValues = $this->getValue();
        for ($i = 0; $i < $countFields; $i++) {
            $field = $requiredFields[$i];
            $value = $fieldValues[$i];
            $result[$field] = $value;
        }
        return $result;
    }

    public function addValueWithField($field, $value)
    {
        $this->fieldsValues[] = $value;
        $this->fieldsRequired[] = $field;
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
//    public function setTargetDataSource($targetDataSource)
//    {
//        $this->targetDataSource = $targetDataSource;
//    }

    /**
     * @return null
     */
//    public function getTargetDataSource()
//    {
//        return $this->targetDataSource;
//    }


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

    // This is private (closed) API
    public function getCurrentDataAccess()
    {
        return $this->currentDataAccess;
    }

    // This is private (closed) API
    public function setCurrentDataAccess($dbaccess)
    {
        $this->currentDataAccess = $dbaccess;
    }

    /* Database connection paramters */
    public function setDbSpecServer($str)
    {
        $this->dbSpecServer = $str;
    }

    public function getDbSpecServer()
    {
        return $this->dbSpecServer;
    }

    public function setDbSpecPort($str)
    {
        $this->dbSpecPort = $str;
    }

    public function getDbSpecPort()
    {
        return $this->dbSpecPort;
    }

    public function setDbSpecUser($str)
    {
        $this->dbSpecUser = $str;
    }

    public function getDbSpecUser()
    {
        return $this->dbSpecUser;
    }

    public function setDbSpecPassword($str)
    {
        $this->dbSpecPassword = $str;
    }

    public function getDbSpecPassword()
    {
        return $this->dbSpecPassword;
    }

    public function setDbSpecDataType($str)
    {
        $this->dbSpecDataType = $str;
    }

    public function getDbSpecDataType()
    {
        return $this->dbSpecDataType;
    }

    public function setDbSpecDatabase($str)
    {
        $this->dbSpecDatabase = $str;
    }

    public function getDbSpecDatabase()
    {
        return $this->dbSpecDatabase;
    }

    public function setDbSpecProtocol($str)
    {
        $this->dbSpecProtocol = $str;
    }

    public function getDbSpecProtocol()
    {
        return $this->dbSpecProtocol;
    }

    public function setDbSpecDSN($str)
    {
        $this->dbSpecDSN = $str;
    }

    public function getDbSpecDSN()
    {
        return $this->dbSpecDSN;
    }

    public function setDbSpecOption($str)
    {
        $this->dbSpecOption = $str;
    }

    public function getDbSpecOption()
    {
        return $this->dbSpecOption;
    }

    public function getAccessUser()
    {
        return $this->accessUser != null ? $this->accessUser : $this->dbSpecUser;
    }

    public function getAccessPassword()
    {
        return $this->accessPassword != null ? $this->accessPassword : $this->dbSpecPassword;
    }

    public function setUserAndPasswordForAccess($user, $pass)
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
        if (isset($authentication['authexpired']) && $authentication['authexpired'] == 0) {
            $authentication['authexpired'] = $this->getAuthenticationItem('authexpired');
        }
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
        switch ($key) {
            case 'user-table':
                return 'authuser';
                break;
            case 'group-table':
                return 'authgroup';
                break;
            case 'corresponding-table':
                return 'authcor';
                break;
            case 'challenge-table':
                return 'issuedhash';
                break;
            case 'authexpired':
                return 3600 * 8;
                break;
        }
        return null;
    }

    public function getUserTable()
    {
        return $this->getAuthenticationItem('user-table');
    }

    public function getGroupTable()
    {
        return $this->getAuthenticationItem('group-table');
    }

    public function getCorrTable()
    {
        return $this->getAuthenticationItem('corresponding-table');
    }

    public function getHashTable()
    {
        return $this->getAuthenticationItem('challenge-table');
    }

    public function getExpiringSeconds()
    {
        return $this->getAuthenticationItem('authexpired');
    }

    public function setLDAPExpiringSeconds($sec)
    {
        $this->params_ldapExpiringSeconds = (int)$sec;
    }

    public function getLDAPExpiringSeconds()
    {
        return $this->params_ldapExpiringSeconds;
    }

    public function setCurrentUser($str)
    {
        $this->currentUser = $str;
    }

    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    public function setDataSource($src)
    {
        $this->dataSource = $src;
    }

    public function getDataSource()
    {
        return $this->dataSource;
    }

    public function getDataSourceDefinition($dataSourceName)
    {
        foreach ($this->dataSource as $index => $value) {
            if ($value['name'] == $dataSourceName) {
                return $value;
            }
        }
        return null;
    }

    public function setOptions($src)
    {
        $this->options = $src;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setDbSpec($src)
    {
        $this->dbSpec = $src;
    }

    public function getDbSpec()
    {
        return $this->dbSpec;
    }


//    function getIndexOfDataSource($dataSourceName)
//    {
//        foreach ($this->dataSource as $index => $value) {
//            if ($value['name'] == $dataSourceName) {
//                return $index;
//            }
//        }
//        return null;
//    }

    public function setSeparator($sep)
    {
        $this->separator = $sep;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

//    function setTargetName($val)
//    {
//        $this->dataSourceName = $val;
//    }
//
//    function getTargetName()
//    {
//        return $this->dataSourceName;
//    }

    public function addTargetField($field)
    {
        $this->fieldsRequired[] = $field;
    }

//    function setTargetFields($fields)
//    {
//        $this->fieldsRequired = $fields;
//    }

    public function getFieldOfIndex($ix)
    {
        return $this->fieldsRequired[$ix];
    }

    public function addValue($value)
    {
        $this->fieldsValues[] = $value;
    }

    public function setValue($values)
    {
        $this->fieldsValues = $values;
    }

    public function getValueOfField($targetField)
    {
        $counter = 0;
        foreach ($this->fieldsRequired as $field) {
            if ($targetField == $field) {
                return $this->fieldsValues[$counter];
            }
            $counter++;
        }
        return null;
    }

    public function setStart($st)
    {
        $this->start = intval(mb_ereg_replace('[^0-9]', '', $st));
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getRecordCount()
    {
        return $this->recordCount;
    }

    public function setRecordCount($sk)
    {
        $this->recordCount = intval(mb_ereg_replace('[^0-9]', '', $sk));
    }

    public function getExtraCriteria()
    {
        return $this->extraCriteria;
    }

    public function unsetExtraCriteria($index)
    {
        unset($this->extraCriteria[$index]);
    }

    public function addExtraCriteria($field, $operator, $value)
    {
        $this->extraCriteria[] = array('field' => $field, 'operator' => $operator, 'value' => $value);
    }

    public function getCriteriaValue($targetField)
    {
        foreach ($this->getExtraCriteria() as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["value"];
            }
        }
        return null;
    }

    public function getCriteriaOperator($targetField)
    {
        foreach ($this->getExtraCriteria() as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["operator"];
            }
        }
        return null;
    }

    public function addExtraSortKey($field, $direction)
    {
        $this->extraSortKey[] = array('field' => $field, 'direction' => $direction);
    }

    public function getExtraSortKey()
    {
        return $this->extraSortKey;
    }

    public function addForeignValue($field, $value)
    {
        $this->foreignFieldAndValue[] = array('field' => $field, 'value' => $value);
    }

    public function getForeignKeysValue($targetField)
    {
        foreach ($this->foreignFieldAndValue as $ar) {
            if ($targetField == $ar["field"]) {
                return $ar["value"];
            }
        }
        return null;
    }

    public function setGlobalInContext($contextName, $operation, $field, $value)
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
    public function getDataSourceTargetArray()
    {
        if ($this->dataSource == null) {
            return null;
        }
            foreach ($this->dataSource as $record) {
                if ($record['name'] == $this->dataSourceName) {
                    return $record;
                }
            }
        return null;
    }

    public function getEntityForRetrieve()
    {
        $dsrc = $this->getDataSourceTargetArray();
        if (isset($dsrc['view'])) {
            return $dsrc['view'];
        }
        return $dsrc['name'];
    }

    public function getEntityForUpdate()
    {
        $dsrc = $this->getDataSourceTargetArray();
        if (isset($dsrc['table'])) {
            return $dsrc['table'];
        }
        return $dsrc['name'];
    }

}
