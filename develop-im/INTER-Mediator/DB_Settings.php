<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/20
 * Time: 14:22
 * To change this template use File | Settings | File Templates.
 */
class DB_Settings
{
    var $dbSpecServer = null;
    var $dbSpecPort = null;
    var $dbSpecUser = null;
    var $dbSpecPassword = null;
    var $dbSpecDatabase = null;
    var $dbSpecDataType = null;
    var $dbSpecProtocol = null;
    var $dbSpecDSN = null;
    var $dbSpecOption = null;

    var $dataSource = null;
    var $targetDataSource = null;
    var $extraCriteria = array();
    var $extraSortKey = array();
    var $fieldsRequired = array();
    var $fieldsValues = array();
    var $separator = null;
    var $start = 0;
    var $dataSourceName = '';
    var $foreignFieldAndValue = array();
    var $recordCount = 0;

    var $currentUser = null;
    var $authentication = null;
    var $accessUser = null;
    var $accessPassword = null;

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

    function setUserAndPaswordForAccess($user, $pass)
    {
        $this->accessUser = $user;
        $this->accessPassword = $pass;
    }

    /* Call on INTER-Mediator.php */

    function getUserTable()
    {
        return isset($this->authentication['user-table'])
            ? $this->authentication['user-table'] : 'authuser';
    }

    function getGroupTable()
    {
        return isset($this->authentication['group-table'])
            ? $this->authentication['group-table'] : 'authgroup';
    }

    function getCorrTable()
    {
        return isset($this->authentication['corresponding-table'])
            ? $this->authentication['corresponding-table'] : 'authcor';
    }

    function getHashTable()
    {
        return isset($this->authentication['challenge-table'])
            ? $this->authentication['challenge-table'] : 'issuedhash';
    }

    function getExpiringSeconds()
    {
        return isset($this->authentication['authexpired'])
            ? $this->authentication['authexpired'] : 3600 * 8;
    }

    function setCurrentUser($str)
    {
        $this->currentUser = $str;
    }

    /*
        function setCurrentChallenge($str)
        {
            $this->currentChallenge = $str;
        }
    */
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

    function setTargetName($val)
    {
        $this->dataSourceName = $val;
    }

    function getTargetName()
    {
        return $this->dataSourceName;
    }

    function setTargetFields($field)
    {
        $this->fieldsRequired[] = $field;
    }

    function setValues($value)
    {
        $this->fieldsValues[] = $value;
    }

    function setStart($st)
    {
        $this->start = $st;
    }

    function setRecordCount($sk)
    {
        $this->recordCount = $sk;
    }

    function setExtraCriteria($field, $operator, $value)
    {
        $this->extraCriteria[] = array('field' => $field, 'operator' => $operator, 'value' => $value);
    }

    function setExtraSortKey($field, $direction)
    {
        $this->extraSortKey[] = array('field' => $field, 'direction' => $direction);
    }

    function setForeignValue($field, $value)
    {
        $this->foreignFieldAndValue[] = array('field' => $field, 'value' => $value);
    }

    /* get the information for the 'name'. */
    function getDataSourceTargetArray()
    {
        if ($this->targetDataSource == null) {
            foreach ($this->dataSource as $record) {
                if ($record['name'] == $this->dataSourceName) {
                    $this->targetDataSource = $record;
                    return $record;
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
