<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/05
 * Time: 20:24
 * To change this template use File | Settings | File Templates.
 */

class DB_Proxy extends DB_UseSharedObjects implements DB_Proxy_Interface
{
    var $dbClass = null;
    var $userExpanded = null;
//    var $dbSettings = null;
//    var $logger = null;

    function getFromDB($dataSourceName)
    {
        $className = get_class($this->userExpanded);
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeGetFromDB")) {
            $this->logger->setDebugMessage("The method 'doBeforeSetToDB' of the class '{$className}' is calling.", 2);
            $this->userExpanded->doBeforeGetFromDB($dataSourceName);
        }
        if ($this->dbClass !== null) {
            $this->logger->setDebugMessage("The method 'getFromDB' of the class '{$className}' is calling.", 2);
            $result = $this->dbClass->getFromDB($dataSourceName);
        }
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterGetFromDB" )) {
            $this->logger->setDebugMessage("The method 'doAfterSetToDB' of the class '{$className}' is calling.", 2);
            $result = $this->userExpanded->doAfterGetFromDB($dataSourceName, $result);
        }
        return $result;
    }

    function countQueryResult($dataSourceName)
    {
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "countQueryResult" )) {
            return $result = $this->userExpanded->countQueryResult($dataSourceName);
        }
        if ($this->dbClass !== null) {
            return $result = $this->dbClass->countQueryResult($dataSourceName);
        }
    }

    function setToDB($dataSourceName)
    {
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeSetToDB")) {
            $this->userExpanded->doBeforeSetToDB($dataSourceName);
        }
        if ($this->dbClass !== null) {
             $result = $this->dbClass->setToDB($dataSourceName);
        }
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterSetToDB")) {
            $result = $this->userExpanded->doAfterSetToDB($dataSourceName, $result);
        }
        return $result;
    }

    function newToDB($dataSourceName)
    {
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeNewToDB")) {
            $this->userExpanded->doBeforeNewToDB($dataSourceName);
        }
        if ($this->dbClass !== null) {
            $result = $this->dbClass->newToDB($dataSourceName);
        }
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterNewToDB")) {
            $result = $this->userExpanded->doAfterNewToDB($dataSourceName, $result);
        }
        return $result;
    }

    function deleteFromDB($dataSourceName)
    {
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeDeleteFromDB")) {
            $this->userExpanded->doBeforeDeleteFromDB($dataSourceName);
        }
        if ($this->dbClass !== null) {
            $result = $this->dbClass->deleteFromDB($dataSourceName);
        }
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterDeleteFromDB")) {
            $result = $this->userExpanded->doAfterDeleteFromDB($dataSourceName, $result);
        }
        return $result;
    }

    function initialize($datasource, $options, $dbspec, $debug)
    {
        $this->setUpSharedObjects();

        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }

        $this->dbSettings->setDataSource($datasource);

        $this->dbSettings->setSeparator(isset($options['separator']) ? $options['separator'] : '@');
        $this->formatter->setFormatter(isset($options['formatter']) ? $options['formatter'] : null);
        $this->dbSettings->setTargetName(isset($_POST['name']) ? $_POST['name'] : null);
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
            $this->logger->setErrorMessage("The database class [{$dbClassName}] that you specify is not valid.");
            echo implode('', $this->logger->getMessagesForJS());
            return false;
        }
        $this->dbClass->setUpSharedObjects( $this );
        if ((!isset($prohibitDebugMode) || !$prohibitDebugMode) && $debug) {
            $this->logger->setDebugMode($debug);
        }
        $this->dbSettings->currentProxy = $this;
        $this->dbSettings->currentDataAccess = $this->dbClass;

        $this->logger->setDebugMessage("The class '{$dbClassName}' was instanciated.", 2);

        if (isset($context['extending-class'])) {
            $className = $context['extending-class'];
            $this->userExpanded = new $className();
            if ( $this->userExpanded === null ) {
                $this->logger->setErrorMessage("The class '{$className}' wasn't instanciated.");
            } else {
                $this->logger->setDebugMessage("The class '{$className}' was instanciated.", 2);
            }
            if ( is_subclass_of( $this->userExpanded, 'DB_UseSharedObjects' ))   {
                $this->userExpanded->setUpSharedObjects( $this );
            }
        }



        $this->dbSettings->setCurrentUser(isset($_POST['authuser']) ? $_POST['authuser'] : null);
        $this->dbSettings->authentication = isset($options['authentication']) ? $options['authentication'] : null;

        $this->dbSettings->setStart(isset($_POST['start']) ? $_POST['start'] : 0);
        $this->dbSettings->setRecordCount(isset($_POST['records']) ? $_POST['records'] : 10000000);

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
            $this->dbSettings->setForeignValue(
                $_POST["foreign{$count}field"],
                $_POST["foreign{$count}value"]);
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

    function processingRequest($options)
    {
        $generatedPrivateKey = '';
        $passPhrase = '';

        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }

        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $access = $_POST['access'];
        $clientId = isset($_POST['clientid']) ? $_POST['clientid'] : $_SERVER['REMOTE_ADDR'];
        $paramAuthUser = isset($_POST['authuser']) ? $_POST['authuser'] : "";
        $paramResponse = isset($_POST['response']) ? $_POST['response'] : "";

        $requireAuthentication = false;
        $requireAuthorization = false;
        $isDBNative = false;
        if (isset($options['authentication'])
//            && (isset($options['authentication']['user'])
//                || isset($options['authentication']['group']))
            || $access == 'challenge'
            || (isset($tableInfo['authentication'])
                && (isset($tableInfo['authentication']['all'])
                    || isset($tableInfo['authentication'][$access])))
        ) {
            $requireAuthorization = true;
            $isDBNative = ($options['authentication']['user'] == 'database_native');
        }

        if ($requireAuthorization) { // Authentication required
            if (strlen($paramAuthUser) == 0 || strlen($paramResponse) == 0) {
                // No username or password
                $access = "do nothing";
                $requireAuthentication = true;
            }
            // User and Password are suppried but...
            if ($access != 'challenge') { // Not accessing getting a challenge.
                if ($isDBNative) {
                    $keyArray = openssl_pkey_get_details(openssl_pkey_get_private($generatedPrivateKey, $passPhrase));
                    require_once('bi2php/biRSA.php');
                    $keyDecrypt = new biRSAKeyPair('0', bin2hex($keyArray['rsa']['d']), bin2hex($keyArray['rsa']['n']));
                    $decrypted = $keyDecrypt->biDecryptedString($paramResponse);
                    if ($decrypted !== false) {
                        $nlPos = strpos($decrypted, "\n");
                        $nlPos = ($nlPos === false) ? strlen($decrypted) : $nlPos;
                        $password = substr($decrypted, 0, $nlPos);
                        $challenge = substr($decrypted, $nlPos + 1);
                        if (!$this->dbClass->checkChallenge($challenge, $clientId)) {
                            $access = "do nothing";
                            $requireAuthentication = true;
                        } else {
                            $this->dbSettings->setUserAndPaswordForAccess($paramAuthUser, $password);
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
                        , 2);
                    if ((count($authorizedUsers) == 0 && count($authorizedGroups) == 0)) {
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
                $result = $this->getFromDB($this->dbSettings->getTargetName());
                echo 'dbresult=' . arrayToJS($result, ''), ';',
                "resultCount='{$this->countQueryResult($this->dbSettings->getTargetName())}';";
                break;
            case 'update':
                $this->setToDB($this->dbSettings->getTargetName());
                break;
            case 'new':
                $result = $this->newToDB($this->dbSettings->getTargetName());
                echo "newRecordKeyValue='{$result}';";
                break;
            case 'delete':
                $this->deleteFromDB($this->dbSettings->getTargetName());
                break;
            case 'challenge':
                break;
            case 'changepassword':
                if( isset($_POST['newpass'])) {
                    $changeResult = $this->changePassword($paramAuthUser, $_POST['newpass']);
                    echo "changePasswordResult=", $changeResult?"true":"false", ";";
                } else {
                    echo "changePasswordResult=false;";
                }
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
    function generateClientId($prefix)
    {
        return sha1(uniqid($prefix, true));
    }

    function generateChallenge()
    {
        $str = '';
        for ($i = 0; $i < 12; $i++) {
            $n = rand(1, 255);
            $str .= ($n < 16 ? '0' : '') . dechex($n);
        }
        return $str;
    }

    function generateSalt()
    {
        $str = '';
        for ($i = 0; $i < 4; $i++) {
            $n = rand(1, 255);
            $str .= chr($n);
        }
        return $str;
    }

    /* returns user's hash salt.

    */
    function saveChallenge($username, $challenge, $clientId)
    {
        $this->dbClass->authSupportStoreChallenge($username, $challenge, $clientId);
        return $username === 0 ? "" : $this->dbClass->authSupportGetSalt($username);
    }

    function checkAuthorization($username, $hashedvalue, $clientId)
    {
        $returnValue = false;

        $this->dbClass->removeOutdatedChallenges();

        $storedChalenge = $this->dbClass->authSupportRetrieveChallenge($username, $clientId);
        $this->logger->setDebugMessage("[checkAuthorization]storedChalenge={$storedChalenge}", 2);

        if (strlen($storedChalenge) == 24) { // ex.fc0d54312ce33c2fac19d758
            $hashedPassword = $this->dbClass->authSupportRetrieveHashedPassword($username);
            $this->logger->setDebugMessage("[checkAuthorization]hashedPassword={$hashedPassword}", 2);
            if (strlen($hashedPassword) > 0) {
                if ($hashedvalue == sha1($storedChalenge . $hashedPassword)) {
                    $returnValue = true;
                }
            }
        }
        return $returnValue;
    }

    // This method is just used to authenticate with database user
    function checkChallenge($challenge, $clientId)
    {
        $returnValue = false;
        $this->dbClass->removeOutdatedChallenges();
        // Database user mode is user_id=0
        $storedChallenge = $this->dbClass->authSupportRetrieveChallenge(0, $clientId);
        if (strlen($storedChallenge) == 24 && $storedChallenge == $challenge) { // ex.fc0d54312ce33c2fac19d758
            $returnValue = true;
        }
        return $returnValue;
    }

    function addUser($username, $password)
    {
        $salt = $this->generateSalt();
        $hexSalt = bin2hex($salt);
        $returnValue = $this->dbClass->authSupportCreateUser($username, sha1($password . $salt) . $hexSalt);
        return $returnValue;
    }

    function changePassword($username, $newpassword)
    {
        $returnValue = $this->dbClass->authSupportChangePassword($username, $newpassword);
        return $returnValue;
    }


}