<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/05
 * Time: 20:24
 * To change this template use File | Settings | File Templates.
 */
interface DB_Interface_Previous
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
}

interface DB_Interface
{
    // Data Access Object pattern.
    /**
     * @param $dataSourceName
     * @return
     */
    function getFromDB($dataSourceName);
    function countQueryResult($dataSourceName);

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
    function initialize( $datasource, $options, $dbspec, $debug );
}

interface Auth_Interface_DB
{
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

/**
 * Interface for DB_Proxy
 */
interface Auth_Interface_Communication
{
    function generateClientId( $prefix );
    function generateChallenge();
    function generateSalt();
    function saveChallenge( $username, $challenge, $clientId );
    function checkAuthorization( $username, $hashedvalue, $clientId );
    function checkChallenge( $challenge, $clientId );
    function addUser( $username, $password );
}

/**
 * Interface for DB_PDO, DB_FileMaker_FX
 */
interface Auth_Interface_CommonDB
{
    function getFieldForAuthorization( $operation );
    function getTargetForAuthorization( $operation );
    function getAuthorizedUsers( $operation = null );
    function getAuthorizedGroups( $operation = null );
    function changePassword( $username, $oldpassword, $newpassword );

}

interface DB_Proxy_Interface extends DB_Interface, Auth_Interface_Communication  {

}

interface DB_Access_Interface extends DB_Interface, Auth_Interface_DB  {

}

interface Expanding_Interface   {
    function doBeforeGetFromDB($dataSourceName);
    function doAfterGetFromDB($dataSourceName, $result);
    function doBeforeSetToDB($dataSourceName);
    function doAfterSetToDB($dataSourceName, $result);
    function doBeforeNewToDB($dataSourceName);
    function doAfterNewToDB($dataSourceName, $result);
    function doBeforeDeleteFromDB($dataSourceName);
    function doAfterDeleteFromDB($dataSourceName, $result);
}


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
    var $formatter = null;
    var $separator = null;
    var $start = 0;
    var $dataSourceName = '';
    var $foreignFieldAndValue = array();

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


    /* Formatter processing */
    function setFormatter($fmt)
    {
        if (is_array($fmt)) {
            $this->formatter = array();
            foreach ($fmt as $oneItem) {
                if (!isset($this->formatter[$oneItem['field']])) {
                    $cvClassName = "DataConverter_{$oneItem['converter-class']}";
                //    require_once("{$cvClassName}.php");
                    $parameter = isset($oneItem['parameter']) ? $oneItem['parameter'] : '';
                    $cvInstance = new $cvClassName($parameter);
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

}

class DB_Logger
{
    /* Debug and Messages */
    var $debugLevel = false;
    var $errorMessage = array();
    var $debugMessage = array();

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

//    function getDebugMessages()
//    {
//        return $this->debugMessage;
//    }
//
//    function getErrorMessages()
//    {
//        return $this->errorMessage;
//    }

    function getMessagesForJS() {
        $q = '"';
        $returnData = array();
        foreach ($this->errorMessage as $oneError) {
            $returnData[] = "INTERMediator.errorMessages.push({$q}"
                . str_replace( "\n", " ", addslashes($oneError)) . "{$q});";
        }
        foreach ($this->debugMessage as $oneError) {
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
}

abstract class UseSharedObjects implements Auth_Interface_CommonDB {
    var $dbSettings = null;
    var $logger = null;

    function setSettings($dbSettings) {
        $this->dbSettings = $dbSettings;
    }
    function setLogger($logger)   {
        $this->logger = $logger;
    }

    function getFieldForAuthorization( $operation )
    {
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
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
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
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
        $tableInfo = $this->dbSettings->getDataSourceTargetArray( );
        $usersArray = array();
        if ( isset( $this->dbSettings->authentication['user'] )) {
            $usersArray = array_merge( $usersArray, $this->dbSettings->authentication['user'] );
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
        $tableInfo = $this->dbSettings->getDataSourceTargetArray( );
        $groupsArray = array();
        if ( isset( $this->dbSettings->authentication['group'] )) {
            $groupsArray = array_merge( $groupsArray, $this->dbSettings->authentication['group'] );
        }
        if ( isset( $tableInfo['authentication']['all']['group'] )) {
            $groupsArray = array_merge( $groupsArray, $tableInfo['authentication']['all']['group'] );
        }
        if ( isset( $tableInfo['authentication'][ $operation ]['group'] )) {
            $groupsArray = array_merge( $groupsArray, $tableInfo['authentication'][ $operation ]['group'] );
        }
        return $groupsArray;
    }

    function changePassword( $username, $oldpassword, $newpassword )
    {
        $returnValue = $this->dbClass->authSupportChangePassword($username, sha1($oldpassword),sha1($newpassword));
        return $returnValue;
    }

}

class DB_Proxy implements DB_Proxy_Interface  {

    var $dbClass = null;
    var $dbSettings = null;
    var $userExpanded = null;
    var $logger = null;

    function getFromDB($dataSourceName) {
        if ( $this->userExpanded !== null && method_exists( $this->userExpanded, "doBeforeGetFromDB" ))  {
            $this->userExpanded->doBeforeGetFromDB($dataSourceName);
        }
        if ( $this->dbClass !== null )  {
            $result = $this->dbClass->getFromDB($dataSourceName);
        }
        if ( $this->userExpanded !== null && method_exists( $this->userExpanded, "doAfterGetFromDB" ))  {
            $result = $this->userExpanded->doAfterGetFromDB($dataSourceName, $result);
        }
        return $result;
    }

    function countQueryResult($dataSourceName)  {
        return $result = $this->dbClass->countQueryResult($dataSourceName);
    }

    function setToDB($dataSourceName)   {
        if ( $this->userExpanded !== null && method_exists( $this->userExpanded, "doBeforeSetToDB" ))  {
            $this->userExpanded->doBeforeSetToDB($dataSourceName);
        }
        if ( $this->dbClass !== null )  {
            $result = $this->dbClass->setToDB($dataSourceName);
        }
        if ( $this->userExpanded !== null && method_exists( $this->userExpanded, "doAfterSetToDB" ))  {
            $result = $this->userExpanded->doAfterSetToDB($dataSourceName, $result);
        }
        return $result;
    }

    function newToDB($dataSourceName)   {
        if ( $this->userExpanded !== null && method_exists( $this->userExpanded, "doBeforeNewToDB" ))  {
            $this->userExpanded->doBeforeNewToDB($dataSourceName);
        }
        if ( $this->dbClass !== null )  {
            $result = $this->dbClass->newToDB($dataSourceName);
        }
        if ( $this->userExpanded !== null && method_exists( $this->userExpanded, "doAfterNewToDB" ))  {
            $result = $this->userExpanded->doAfterNewToDB($dataSourceName, $result);
        }
        return $result;
    }

    function deleteFromDB($dataSourceName)  {
        if ( $this->userExpanded !== null && method_exists( $this->userExpanded, "doBeforeDeleteFromDB" ))  {
            $this->userExpanded->doBeforeDeleteFromDB($dataSourceName);
        }
        if ( $this->dbClass !== null )  {
            $result = $this->dbClass->deleteFromDB($dataSourceName);
        }
        if ( $this->userExpanded !== null && method_exists( $this->userExpanded, "doAfterDeleteFromDB" ))  {
            $result = $this->userExpanded->doAfterDeleteFromDB($dataSourceName, $result);
        }
        return $result;
    }

    function initialize( $datasource, $options, $dbspec, $debug )   {
        $this->dbSettings = new DB_Settings();
        $this->logger = new DB_Logger();

        $currentDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname( dirname( __FILE__ )  ). DIRECTORY_SEPARATOR . 'params.php';
        if ( file_exists( $parentDirParam )) {
            include( $parentDirParam );
        } else if ( file_exists( $currentDirParam )) {
            include( $currentDirParam );
        }

        $this->dbSettings->setDataSource( $datasource );

        $this->dbSettings->setSeparator( isset($options['separator']) ? $options['separator'] : '@' );
        $this->dbSettings->setFormatter( isset($options['formatter']) ? $options['formatter'] : null);
        $this->dbSettings->setTargetName( isset($_POST['name']) ? $_POST['name'] : null );
        $context = $this->dbSettings->getDataSourceTargetArray();

        $dbClassName = 'DB_' .
            (isset($context['db-class']) ? $context['db-class'] :
                (isset($dbspec['db-class']) ? $dbspec['db-class'] :
                    (isset ($dbClass) ? $dbClass : '')));
        $this->dbSettings->setDbSpecServer(
            isset($context['server']) ? $context['server'] :
                (isset($dbspec['server']) ? $dbspec['server'] :
                    (isset ($dbServer) ? $dbServer : '')));
        $this->dbSettings->setDbSpecPort(
            isset($context['port']) ? $context['port'] :
                (isset($dbspec['port']) ? $dbspec['port'] :
                    (isset ($dbPort) ? $dbPort : '')));
        $this->dbSettings->setDbSpecUser(
            isset($context['user']) ? $context['user'] :
                (isset($dbspec['user']) ? $dbspec['user'] :
                    (isset ($dbUser) ? $dbUser : '')));
        $this->dbSettings->setDbSpecPassword(
            isset($context['password']) ? $context['password'] :
                (isset($dbspec['password']) ? $dbspec['password'] :
                    (isset ($dbPassword) ? $dbPassword : '')));
        $this->dbSettings->setDbSpecDataType(
            isset($context['datatype']) ? $context['datatype'] :
                (isset($dbspec['datatype']) ? $dbspec['datatype'] :
                    (isset ($dbDataType) ? $dbDataType : '')));
        $this->dbSettings->setDbSpecDatabase(
            isset($context['database']) ? $context['database'] :
                (isset($dbspec['database']) ? $dbspec['database'] :
                    (isset ($dbDatabase) ? $dbDatabase : '')));
        $this->dbSettings->setDbSpecProtocol(
            isset($context['protocol']) ? $context['protocol'] :
                (isset($dbspec['protocol']) ? $dbspec['protocol'] :
                    (isset ($dbProtocol) ? $dbProtocol : '')));
        $this->dbSettings->setDbSpecOption(
            isset($context['option']) ? $context['option'] :
                (isset($dbspec['option']) ? $dbspec['option'] :
                    (isset ($dbOption) ? $dbOption : '')));
        $this->dbSettings->setDbSpecDSN(
            isset($context['dsn']) ? $context['dsn'] :
                (isset($dbspec['dsn']) ? $dbspec['dsn'] :
                    (isset ($dbDSN) ? $dbDSN : '')));

        require_once("{$dbClassName}.php");
        $this->dbClass = new $dbClassName();
        if ($this->dbClass == null) {
            $this->logger->setErrorMessage( "The database class [{$dbClassName}] that you specify is not valid." );
            echo implode('', $this->logger->getMessagesForJS());
            return false;
        }
        $this->dbClass->setSettings($this->dbSettings);
        $this->dbClass->setLogger($this->logger);

        if (isset($context['expanded']))    {
            $this->userExpanded = new $context['expanded']();
            $this->userExpanded->setSettings($this->dbSettings);
            $this->userExpanded->setLogger($this->logger);
        };

        if ((!isset($prohibitDebugMode) || !$prohibitDebugMode) && $debug) {
            $this->logger->setDebugMode($debug);
        }


        $this->dbSettings->setCurrentUser( isset($_POST['authuser']) ? $_POST['authuser'] : null );
        $this->dbSettings->authentication = isset($options['authentication']) ? $options['authentication'] : null;

        $this->dbSettings->setStart( isset($_POST['start']) ? $_POST['start'] : 0 );
        $this->dbSettings->setRecordCount( isset($_POST['records']) ? $_POST['records'] : 10000000 );

        for ($count = 0; $count < 10000; $count++) {
            if (isset($_POST["condition{$count}field"])) {
                $this->dbSettings->setExtraCriteria(
                    $_POST["condition{$count}field"],
                    isset($_POST["condition{$count}operator"]) ? $_POST["condition{$count}operator"] : '=',
                    isset($_POST["condition{$count}value"]) ? $_POST["condition{$count}value"] : '');
            } else {
                break;
            }
        }
        for ($count = 0; $count < 10000; $count++) {
            if (isset($_POST["sortkey{$count}field"])) {
                $this->dbSettings->setExtraSortKey($_POST["sortkey{$count}field"], $_POST["sortkey{$count}direction"]);
            } else {
                break;
            }
        }
        for ($count = 0; $count < 10000; $count++) {
            if (!isset($_POST["foreign{$count}field"])) {
                break;
            }
            $this->dbSettings->setForeignValue($_POST["foreign{$count}field"], $_POST["foreign{$count}value"]);
        }

        for ($i = 0; $i < 1000; $i++) {
            if (!isset($_POST["field_{$i}"])) {
                break;
            }
            $this->dbSettings->setTargetFields($_POST["field_{$i}"]);
        }
        for ($i = 0; $i < 1000; $i++) {
            if (!isset($_POST["value_{$i}"])) {
                break;
            }
            $this->dbSettings->setValues(get_magic_quotes_gpc() ? stripslashes($_POST["value_{$i}"]) : $_POST["value_{$i}"]);
        }

    }

    function processingRequest($options)    {
        $generatedPrivateKey = '';
        $passPhrase = '';

        $currentDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname( dirname( __FILE__ )  ). DIRECTORY_SEPARATOR . 'params.php';
        if ( file_exists( $parentDirParam )) {
            include( $parentDirParam );
        } else if ( file_exists( $currentDirParam )) {
            include( $currentDirParam );
        }

        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $access = $_POST['access'];
        $clientId = isset($_POST['clientid']) ? $_POST['clientid'] : $_SERVER['REMOTE_ADDR'];
        $paramAuthUser = isset($_POST['authuser']) ? $_POST['authuser'] : "";
        $paramResponse = isset($_POST['response']) ? $_POST['response'] : "";

        $requireAuthentication = false;
        $requireAuthorization = false;
        $isDBNative = false;
        if (   isset($options['authentication'] )
            && (  isset($options['authentication']['user'])
                || isset($options['authentication']['group']) )
            || $access == 'challenge'
            || (isset($tableInfo['authentication'])
                && ( isset($tableInfo['authentication']['all'])
                    || isset($tableInfo['authentication'][$access])))
        ) {
            $requireAuthorization = true;
            $isDBNative = ($options['authentication']['user'] == 'database_native');
        }

        if ($requireAuthorization) { // Authentication required
            if ( strlen($paramAuthUser) == 0  || strlen($paramResponse) == 0 ) {
                // No username or password
                $access = "do nothing";
                $requireAuthentication = true;
            }
            // User and Password are suppried but...
            if ( $access != 'challenge') { // Not accessing getting a challenge.
                if ( $isDBNative ) {
                    $keyArray = openssl_pkey_get_details( openssl_pkey_get_private( $generatedPrivateKey, $passPhrase ));
                    require_once( 'bi2php/biRSA.php' );
                    $keyDecrypt = new biRSAKeyPair( '0', bin2hex( $keyArray['rsa']['d']), bin2hex( $keyArray['rsa']['n']));
                    $decrypted = $keyDecrypt->biDecryptedString( $paramResponse );
                    if ( $decrypted !== false ) {
                        $nlPos = strpos( $decrypted, "\n" );
                        $nlPos = ($nlPos === false) ? strlen($decrypted) : $nlPos;
                        $password = substr( $decrypted, 0, $nlPos );
                        $challenge = substr( $decrypted, $nlPos + 1 );
                        if ( ! $this->checkChallenge( $challenge, $clientId ) ) {
                            $access = "do nothing";
                            $requireAuthentication = true;
                        } else {
                            $this->dbSettings->setUserAndPaswordForAccess( $paramAuthUser, $password );
                        }
                    } else {
                        $this->logger->setDebugMessage("Can't decrypt.");
                        $access = "do nothing";
                        $requireAuthentication = true;
                    }
                } else {
                    $noAuthorization = true;
                    $authorizedUsers = $this->dbClass->getAuthorizedUsers($access);
                    $authorizedGroups = $this->dbClass->getAuthorizedGroups($access);
                    $this->logger->setDebugMessage(
                        "authorizedUsers=" . var_export($authorizedUsers, true)
                            . "/authorizedGroups=" . var_export($authorizedGroups, true)
                        ,2);
                    if ( (count($authorizedUsers) == 0 && count($authorizedGroups) == 0 )) {
                        $noAuthorization = false;
                    } else {
                        if (in_array($this->dbSettings->currentUser, $authorizedUsers)) {
                            $noAuthorization = false;
                        } else {
                            if (count($authorizedGroups) > 0) {
                                $belongGroups = $this->dbClass->getGroupsOfUser($this->dbSettings->currentUser);
                                if (count(array_intersect($belongGroups, $authorizedGroups)) != 0) {
                                    $noAuthorization = false;
                                }
                            }
                        }
                    }
                    if ($noAuthorization) {
                        $this->logger->setDebugMessage("Authorization doesn't meet the settings.");
                        $access = "do nothing";
                        $requireAuthentication = true;
                    }
                    if (!$this->checkAuthorization($paramAuthUser, $paramResponse, $clientId)) {
                        $this->logger->setDebugMessage("Authentication doesn't meet valid.{$paramAuthUser}/{$paramResponse}/{$clientId}");
                        // Not Authenticated!
                        $access = "do nothing";
                        $requireAuthentication = true;
                    }
                }
            }
        }
        // Come here access=challenge or authenticated access
        switch ($access) {
            case 'select':
                $result = $this->dbClass->getFromDB($this->dbSettings->getTargetName());
                echo 'dbresult=' . arrayToJS($result, ''), ';',
                    "resultCount='{$this->dbClass->countQueryResult($this->dbSettings->getTargetName())}';";
                break;
            case 'update':
                $this->dbClass->setToDB($this->dbSettings->getTargetName());
                break;
            case 'new':
                $result = $this->dbClass->newToDB($this->dbSettings->getTargetName());
                echo "newRecordKeyValue='{$result}';";
                break;
            case 'delete':
                $this->dbClass->deleteFromDB($this->dbSettings->getTargetName());
                break;
            case 'challenge':
                break;
        }
        echo implode('', $this->logger->getMessagesForJS());
        if ($requireAuthorization) {
            $generatedChallenge = $this->generateChallenge();
            $generatedUID = $this->generateClientId('');
            $userSalt = $this->saveChallenge(
                $isDBNative ? 0 : $paramAuthUser, $generatedChallenge, $generatedUID);
            echo "challenge='{$generatedChallenge}{$userSalt}';";
            echo "clientid='{$generatedUID}';";
            if ($requireAuthentication) {
                echo "requireAuth=true;"; // Force authentication to client
            }
        }
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

    /* returns user's hash salt.

    */
    function saveChallenge( $username, $challenge, $clientId )
    {
        $this->dbClass->authSupportStoreChallenge($username, $challenge, $clientId);
        return $username === 0 ? "" : $this->dbClass->authSupportGetSalt($username);
    }

    function checkAuthorization( $username, $hashedvalue, $clientId )
    {
        $returnValue = false;

        $this->dbClass->removeOutdatedChallenges();

        $storedChalenge = $this->dbClass->authSupportRetrieveChallenge($username, $clientId);
        $this->logger->setDebugMessage("[checkAuthorization]storedChalenge={$storedChalenge}", 2);

        if ( strlen($storedChalenge) == 24 ) {   // ex.fc0d54312ce33c2fac19d758
            $hashedPassword = $this->dbClass->authSupportRetrieveHashedPassword($username);
            $this->logger->setDebugMessage("[checkAuthorization]hashedPassword={$hashedPassword}", 2);
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
        $this->dbClass->removeOutdatedChallenges();
        // Database user mode is user_id=0
        $storedChallenge = $this->dbClass->authSupportRetrieveChallenge( 0, $clientId );
        if ( strlen($storedChallenge) == 24 && $storedChallenge == $challenge ) {   // ex.fc0d54312ce33c2fac19d758
            $returnValue = true;
        }
        return $returnValue;
    }

    function addUser( $username, $password )
    {
        $salt = $this->generateSalt();
        $hexSalt = bin2hex( $salt );
        $returnValue = $this->dbClass->authSupportCreateUser($username, sha1($password . $salt) . $hexSalt);
        return $returnValue;
    }


}