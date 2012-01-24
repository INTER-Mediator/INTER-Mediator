<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

interface DB_Interface
{
    // Data Access Object pattern.
    /**
     * @param $dataSourceName
     * @return
     */
    function getFromDB($dataSourceName);

    /**
     * @param $dataSourceName
     * @return
     */
    function setToDB($dataSourceName);

    /**
     * @param $dataSourceName
     * @return
     */
    function newToDB($dataSourceName);

    /**
     * @param $dataSourceName
     * @return
     */
    function deleteFromDB($dataSourceName);

    // These method should be be implemented in the inherited class
    /**
     * @param $username
     * @param $challenge
     * @return
     */
    function authSupportStoreChallenge($username, $challenge);

    /**
     * @param $username
     */
    function authSupportRetrieveChallenge($username);

    /**
     * @param $username
     */
    function authSupportRetrieveHashedPassword($username);

    /**
     * @param $username
     * @param $hashedpassword
     */
    function authSupportCreateUser($username, $hashedpassword);

    /**
     * @param $username
     * @param $hashedoldpassword
     * @param $hashednewpassword
     */
    function authSupportChangePassword($username, $hashedoldpassword, $hashednewpassword);
}

abstract class DB_Base
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
    var $mainTableCount = 0;
    var $fieldsRequired = array();
    var $fieldsValues = array();
    var $formatter = null;
    var $separator = null;
    var $start = 0;
    var $recordCount = 0;
    var $errorMessage = array();
    var $debugMessage = array();
    var $isDebug = false;
    var $dataSourceName = '';
    var $foreignFieldAndValue = array();

    function __construct()
    {
    }

    /* Call on INTER-Mediator.php */
    function setDataSource($src)
    {
        $this->dataSource = $src;
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

    /* Debug and Messages */
    function setDebugMessage($str)
    {
        if ($this->isDebug) {
            $this->debugMessage[] = $str;
        }
    }

    function setErrorMessage($str)
    {
        $this->errorMessage[] = $str;
    }

    function getDebugMessages()
    {
        return $this->debugMessage;
    }

    function getErrorMessages()
    {
        return $this->errorMessage;
    }

    function setDebugMode()
    {
        $this->isDebug = true;
    }

    /* Formatter processing */
    function setFormatter($fmt)
    {
        if (is_array($fmt)) {
            $this->formatter = array();
            foreach ($fmt as $oneItem) {
                if (!isset($this->formatter[$oneItem['field']])) {
                    require_once("DataConverter_{$oneItem['converter-class']}.php");
                    $parameter = isset($oneItem['parameter']) ? $oneItem['parameter'] : '';
                    $cvInstance = '';
                    eval("\$cvInstance = new DataConverter_{$oneItem['converter-class']}('{$parameter}');");
                    $this->formatter[$oneItem['field']] = $cvInstance;
                }
            }
        }
    }

    function formatterFromDB($field, $data)
    {
        if (is_array($this->formatter)) {
            if (isset($this->formatter[$field])) {
                return $this->formatter[$field]->converterFromDBtoUser($data);
            }
        }
        return $data;
    }

    function formatterToDB($field, $data)
    {
        if (is_array($this->formatter)) {
            if (isset($this->formatter[$field])) {
                return $this->formatter[$field]->converterFromUserToDB($data);
            }
        }
        return $data;
    }

    /* Authentication support */
    function generateChallenge()
    {
        $str = '';
        for ( $i = 0 ; $i < 8 ; $i++ )  {
            $str .= chr( rand( 1, 255 ));
        }
        return urlencode( $str );
    }

    function saveChallenge( $username, $challenge )
    {
        $this->authSupportStoreChallenge($username, $challenge);
        return false;
    }

    function checkChallenge( $username, $hashedvalue )
    {
        $returnValue = false;

        $storedChalenge = $this->authSupportRetrieveChallenge($username);
        if ( strlen($storedChalenge) == 8 ) {
            $hashedPassword = $this->authSupportRetrieveHashedPassword($username);
            if ( strlen($hashedPassword) > 0 ) {
                $hashSeed = $hashedPassword . $storedChalenge . $username;
                if ( $hashedvalue === sha1($hashSeed) ) {
                    $returnValue = true;
                }
            }
        }
        return $returnValue;
    }

    function addUser( $username, $password )
    {
        $returnValue = $this->authSupportCreateUser($username, sha1($password));
        return $returnValue;
    }

    function changePassword( $username, $oldpassword, $newpassword )
    {
        $returnValue = $this->authSupportChangePassword($username, sha1($oldpassword),sha1($newpassword));
        return $returnValue;
    }

}

?>