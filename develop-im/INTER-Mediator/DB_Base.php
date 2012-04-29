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
    function authSupportStoreChallenge($username, $challenge, $clientId);

    /**
     * @abstract
     * @param $username
     */
    function authSupportGetSalt($username);

    /**
     * @abstract
     */
    function removeOutdatedChallenges();
    /**
     * @param $username
     */
    function authSupportRetrieveChallenge($username, $clientId);

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

    var $accessUser = null;
    var $accessPassword = null;

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
    var $debugLevel = false;
    var $dataSourceName = '';
    var $foreignFieldAndValue = array();

    var $currentUser = null;
//    var $currentChallenge = null;
    var $authentication = null;

    function __construct()
    {
    }

    function initialize( $datasrc, $options, $dbspec )   {
        $currentDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname( dirname( __FILE__ )  ). DIRECTORY_SEPARATOR . 'params.php';
        if ( file_exists( $parentDirParam )) {
            include( $parentDirParam );
        } else if ( file_exists( $currentDirParam )) {
            include( $currentDirParam );
        }

        $this->setDataSource( $datasrc );

        $this->setSeparator( isset($options['separator']) ? $options['separator'] : '@');
        $this->setFormatter( isset($options['formatter']) ? $options['formatter'] : null);
        $this->setTargetName( isset($_POST['name']) ? $_POST['name'] : null );

        $context = $this->getDataSourceTargetArray();

        $this->setDbSpecServer(
            isset($context['server']) ? $context['server'] :
                (isset($dbspec['server']) ? $dbspec['server'] : (isset ($dbServer) ? $dbServer : '')));
        $this->setDbSpecPort(
            isset($context['port']) ? $context['port'] :
                (isset($dbspec['port']) ? $dbspec['port'] : (isset ($dbPort) ? $dbPort : '')));
        $this->setDbSpecUser(
            isset($context['user']) ? $context['user'] :
                (isset($dbspec['user']) ? $dbspec['user'] : (isset ($dbUser) ? $dbUser : '')));
        $this->setDbSpecPassword(
            isset($context['password']) ? $context['password'] :
                (isset($dbspec['password']) ? $dbspec['password'] : (isset ($dbPassword) ? $dbPassword : '')));
        $this->setDbSpecDataType(
            isset($context['datatype']) ? $context['datatype'] :
                (isset($dbspec['datatype']) ? $dbspec['datatype'] : (isset ($dbDataType) ? $dbDataType : '')));
        $this->setDbSpecDatabase(
            isset($context['database']) ? $context['database'] :
                (isset($dbspec['database']) ? $dbspec['database'] : (isset ($dbDatabase) ? $dbDatabase : '')));
        $this->setDbSpecProtocol(
            isset($context['protocol']) ? $context['protocol'] :
                (isset($dbspec['protocol']) ? $dbspec['protocol'] : (isset ($dbProtocol) ? $dbProtocol : '')));
        $this->setDbSpecOption(
            isset($context['option']) ? $context['option'] :
                (isset($dbspec['option']) ? $dbspec['option'] : (isset ($dbOption) ? $dbOption : '')));
        $this->setDbSpecDSN(
            isset($context['dsn']) ? $context['dsn'] :
                (isset($dbspec['dsn']) ? $dbspec['dsn'] : (isset ($dbDSN) ? $dbDSN : '')));

        $this->setCurrentUser( isset($_POST['authuser']) ? $_POST['authuser'] : null );
    //    $this->setCurrentChallenge( isset($_POST['challenge']) ? $_POST['challenge'] : null );
        $this->authentication = isset($options['authentication']) ? $options['authentication'] : null;

        $this->setStart( isset($_POST['start']) ? $_POST['start'] : 0 );
        $this->setRecordCount( isset($_POST['records']) ? $_POST['records'] : 10000000 );

        for ($count = 0; $count < 10000; $count++) {
            if (isset($_POST["condition{$count}field"])) {
                $this->setExtraCriteria(
                    $_POST["condition{$count}field"],
                    isset($_POST["condition{$count}operator"]) ? $_POST["condition{$count}operator"] : '=',
                    isset($_POST["condition{$count}value"]) ? $_POST["condition{$count}value"] : '');
            } else {
                break;
            }
        }
        for ($count = 0; $count < 10000; $count++) {
            if (isset($_POST["sortkey{$count}field"])) {
                $this->setExtraSortKey($_POST["sortkey{$count}field"], $_POST["sortkey{$count}direction"]);
            } else {
                break;
            }
        }
        for ($count = 0; $count < 10000; $count++) {
            if (!isset($_POST["foreign{$count}field"])) {
                break;
            }
            $this->setForeignValue($_POST["foreign{$count}field"], $_POST["foreign{$count}value"]);
        }

        for ($i = 0; $i < 1000; $i++) {
            if (!isset($_POST["field_{$i}"])) {
                break;
            }
            $this->setTargetFields($_POST["field_{$i}"]);
        }
        for ($i = 0; $i < 1000; $i++) {
            if (!isset($_POST["value_{$i}"])) {
                break;
            }
            $this->setValues(get_magic_quotes_gpc() ? stripslashes($_POST["value_{$i}"]) : $_POST["value_{$i}"]);
        }
        //		if ( isset( $_POST['parent_keyval'] ))	{
        //			$dbInstance->setParentKeyValue( $_POST['parent_keyval'] );
        //		}

    }

    function getAccessUser()    {
        return $this->accessUser != null ? $this->accessUser : $this->dbSpecUser;
    }
    function getAccessPassword()    {
        return $this->accessPassword != null ? $this->accessPassword : $this->dbSpecPassword;
    }
    function setUserAndPaswordForAccess( $user, $pass )   {
        $this->accessUser = $user;
        $this->accessPassword = $pass;
    }
    /* Call on INTER-Mediator.php */

    function getUserTable() {
        return isset($this->authentication['user-table'])
            ? $this->authentication['user-table'] : 'authuser';
    }

    function getGroupTable() {
        return isset($this->authentication['group-table'])
            ? $this->authentication['group-table'] : 'authgroup';
    }
/*
    function getPrivTable() {
        return isset($this->authentication['privilege-table'])
            ? $this->authentication['privilege-table'] : 'authpriv';
    }
*/
    function getCorrTable() {
        return isset($this->authentication['corresponding-table'])
            ? $this->authentication['corresponding-table'] : 'authcor';
    }

    function getHashTable() {
        return isset($this->authentication['challenge-table'])
            ? $this->authentication['challenge-table'] : 'issuedhash';
    }

    function getExpiringSeconds() {
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

    function getIndexOfDataSource( $dataSourceName )    {
        foreach( $this->dataSource as $index => $value )    {
            if ( $value['name'] == $dataSourceName) {
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
    function setDebugMessage($str, $level = 1 )
    {
        if ($this->debugLevel !== false && $this->debugLevel >= $level ) {
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

    function getMessagesForJS() {
        $q = '"';
        $returnData = array();
        foreach ($this->getErrorMessages() as $oneError) {
            $returnData[] = "INTERMediator.errorMessages.push({$q}"
                . str_replace( "\n", " ", addslashes($oneError)) . "{$q});";
        }
        foreach ($this->getDebugMessages() as $oneError) {
            $returnData[] = "INTERMediator.debugMessages.push({$q}"
                . str_replace( "\n", " ", addslashes($oneError)) . "{$q});";
        }
        return $returnData;
    }

    function setDebugMode( $val )
    {
        if ( $val === true )    {
            $this->debugLevel = 1;
        } else {
            $this->debugLevel = $val;
        }

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
    function generateClientId( $prefix )
    {
        return sha1( uniqid( $prefix, true ));
    }

    function generateChallenge()
    {
        $str = '';
        for ( $i = 0 ; $i < 12 ; $i++ )  {
            $n = rand( 1, 255 );
            $str .= ($n < 16 ? '0' : '' ) . dechex($n);
        }
        return $str;
    }

    function generateSalt()
    {
        $str = '';
        for ( $i = 0 ; $i < 4 ; $i++ )  {
            $n = rand( 1, 255 );
            $str .= chr($n);
        }
        return $str;
    }

    function getFieldForAuthorization( $operation )
    {
        $tableInfo = $this->getDataSourceTargetArray();
        $authInfoField = null;
        if ( isset( $tableInfo['authentication']['all']['field'] )) {
            $authInfoField = $tableInfo['authentication']['all']['field'];
        }
        if ( isset( $tableInfo['authentication'][$operation]['field'] )) {
            $authInfoField = $tableInfo['authentication'][ $operation ]['field'];
        }
        return $authInfoField;
    }

    function getTargetForAuthorization( $operation )
    {
        $tableInfo = $this->getDataSourceTargetArray();
        $authInfoTarget = null;
        if ( isset( $tableInfo['authentication']['all']['target'] )) {
            $authInfoTarget = $tableInfo['authentication']['all']['target'];
        }
        if ( isset( $tableInfo['authentication'][$operation]['target'] )) {
            $authInfoTarget = $tableInfo['authentication'][ $operation ]['target'];
        }
        return $authInfoTarget;
    }

    function getAuthorizedUsers( $operation = null )
    {
        $tableInfo = $this->getDataSourceTargetArray( );
        $usersArray = array();
        if ( isset( $this->authentication['user'] )) {
            $usersArray = array_merge( $usersArray, $this->authentication['user'] );
        }
        if ( isset( $tableInfo['authentication']['all']['user'] )) {
            $usersArray = array_merge( $usersArray, $tableInfo['authentication']['all']['user'] );
        }
        if ( isset( $tableInfo['authentication'][ $operation ]['user'] )) {
            $usersArray = array_merge( $usersArray, $tableInfo['authentication'][ $operation ]['user'] );
        }
        return $usersArray;
    }

    function getAuthorizedGroups( $operation = null )
    {
        $tableInfo = $this->getDataSourceTargetArray( );
        $groupsArray = array();
        if ( isset( $this->authentication['group'] )) {
            $groupsArray = array_merge( $groupsArray, $this->authentication['group'] );
        }
        if ( isset( $tableInfo['authentication']['all']['group'] )) {
            $groupsArray = array_merge( $groupsArray, $tableInfo['authentication']['all']['group'] );
        }
        if ( isset( $tableInfo['authentication'][ $operation ]['group'] )) {
            $groupsArray = array_merge( $groupsArray, $tableInfo['authentication'][ $operation ]['group'] );
        }
        return $groupsArray;
    }
    /* returns user's hash salt.

    */
    function saveChallenge( $username, $challenge, $clientId )
    {
        $this->authSupportStoreChallenge($username, $challenge, $clientId);
        return $username === 0 ? "" : $this->authSupportGetSalt($username);
    }

    function checkAuthorization( $username, $hashedvalue, $clientId )
    {
        $returnValue = false;

        $this->removeOutdatedChallenges();

        $storedChalenge = $this->authSupportRetrieveChallenge($username, $clientId);
        $this->setDebugMessage("[checkAuthorization]storedChalenge={$storedChalenge}", 2);

        if ( strlen($storedChalenge) == 24 ) {   // ex.fc0d54312ce33c2fac19d758
            $hashedPassword = $this->authSupportRetrieveHashedPassword($username);
            $this->setDebugMessage("[checkAuthorization]hashedPassword={$hashedPassword}", 2);
            if ( strlen($hashedPassword) > 0 ) {
                if ( $hashedvalue == sha1($storedChalenge . $hashedPassword) ) {
                    $returnValue = true;
                }
            }
        }
        return $returnValue;
    }

    // This method is just used to authenticate with database user
    function checkChallenge( $challenge, $clientId )
    {
        $returnValue = false;
        $this->removeOutdatedChallenges();
        // Database user mode is user_id=0
        $storedChallenge = $this->authSupportRetrieveChallenge( 0, $clientId );
        if ( strlen($storedChallenge) == 24 && $storedChallenge == $challenge ) {   // ex.fc0d54312ce33c2fac19d758
            $returnValue = true;
        }
        return $returnValue;
    }

    function addUser( $username, $password )
    {
        $salt = $this->generateSalt();
        $hexSalt = bin2hex( $salt );
        $returnValue = $this->authSupportCreateUser($username, sha1($password . $salt) . $hexSalt);
        return $returnValue;
    }

    function changePassword( $username, $oldpassword, $newpassword )
    {
        $returnValue = $this->authSupportChangePassword($username, sha1($oldpassword),sha1($newpassword));
        return $returnValue;
    }

}

?>