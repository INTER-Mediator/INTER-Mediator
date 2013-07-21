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
 * Date: 12/05/05
 * Time: 20:24
 * To change this template use File | Settings | File Templates.
 */

class DB_Proxy extends DB_UseSharedObjects implements DB_Proxy_Interface
{
    var $dbClass = null; // for Default context
    var $userExpanded = null;
    var $dbClassForContext = array();
    var $outputOfPrcessing = '';
//    var $requireAuthorization;
//    var $requireAuthentication;
//    var $isDBNative;
    var $paramAuthUser;

    var $previousChallenge;
    var $previousClientid;


    function __construct($testmode = false)
    {
        if (!$testmode) {
            header('Content-Type: text/javascript;charset="UTF-8"');
            header('Cache-Control: no-store,no-cache,must-revalidate,post-check=0,pre-check=0');
            header('Expires: 0');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
        }
    }

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
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doAfterGetFromDB")) {
            $this->logger->setDebugMessage("The method 'doAfterSetToDB' of the class '{$className}' is calling.", 2);
            $result = $this->userExpanded->doAfterGetFromDB($dataSourceName, $result);
        }
        return $result;
    }

    function countQueryResult($dataSourceName)
    {
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "countQueryResult")) {
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

    function newToDB($dataSourceName, $bypassAuth)
    {
        if ($this->userExpanded !== null && method_exists($this->userExpanded, "doBeforeNewToDB")) {
            $this->userExpanded->doBeforeNewToDB($dataSourceName);
        }
        if ($this->dbClass !== null) {
            $result = $this->dbClass->newToDB($dataSourceName, $bypassAuth);
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

    function getFieldInfo($dataSourceName)
    {
        if ($this->dbClass !== null) {
            $result = $this->dbClass->getFieldInfo($dataSourceName);
        }
        return $result;
    }

    function initialize($datasource, $options, $dbspec, $debug, $target = null)
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
        $this->dbSettings->setTargetName(!is_null($target) ? $target : (isset($_POST['name']) ? $_POST['name'] : "_im_auth"));
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
        $this->dbClass->setUpSharedObjects($this);
        if ((!isset($prohibitDebugMode) || !$prohibitDebugMode) && $debug) {
            $this->logger->setDebugMode($debug);
        }
        $this->dbSettings->currentProxy = $this;
        $this->dbSettings->currentDataAccess = $this->dbClass;

        $this->logger->setDebugMessage("The class '{$dbClassName}' was instanciated.", 2);

        if (isset($context['extending-class'])) {
            $className = $context['extending-class'];
            $this->userExpanded = new $className();
            if ($this->userExpanded === null) {
                $this->logger->setErrorMessage("The class '{$className}' wasn't instanciated.");
            } else {
                $this->logger->setDebugMessage("The class '{$className}' was instanciated.", 2);
            }
            if (is_subclass_of($this->userExpanded, 'DB_UseSharedObjects')) {
                $this->userExpanded->setUpSharedObjects($this);
            }
        }

        $this->dbSettings->primaryKeyOnly = isset($_POST['pkeyonly']) &&
            !(isset($prohibitIgnoreCondition) ? $prohibitIgnoreCondition : false);

        $this->dbSettings->setCurrentUser(isset($_POST['authuser']) ? $_POST['authuser'] : null);
        $this->dbSettings->authentication = isset($options['authentication']) ? $options['authentication'] : null;

        $this->dbSettings->setStart(isset($_POST['start']) ? $_POST['start'] : 0);
        $this->dbSettings->setRecordCount(isset($_POST['records']) ? $_POST['records'] : 10000000);

        for ($count = 0; $count < 10000; $count++) {
            if (isset($_POST["condition{$count}field"])) {
                $this->dbSettings->setExtraCriteria(
                    $_POST["condition{$count}field"],
                    isset($_POST["condition{$count}operator"]) ? $_POST["condition{$count}operator"] : '=',
                    isset($_POST["condition{$count}value"]) ? $_POST["condition{$count}value"] : null);
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
            $this->dbSettings->setTargetField($_POST["field_{$i}"]);
        }
        for ($i = 0; $i < 1000; $i++) {
            if (!isset($_POST["value_{$i}"])) {
                break;
            }
            $this->dbSettings->setValue(get_magic_quotes_gpc() ? stripslashes($_POST["value_{$i}"]) : $_POST["value_{$i}"]);
        }
        if (isset($options['authentication']) && isset($options['authentication']['email-as-username'])) {
            $this->dbSettings->emailAsAccount = $options['authentication']['email-as-username'];
        }
    }

    /*
    * POST
    * ?access=select
    * &name=<table name>
    * &start=<record number to start>
    * &records=<how many records should it return>
    * &field_<N>=<field name>
    * &value_<N>=<value of the field>
    * &condition<N>field=<Extra criteria's field name>
    * &condition<N>operator=<Extra criteria's operator>
    * &condition<N>value=<Extra criteria's value>
    * &parent_keyval=<value of the foreign key field>
    */

    function processingRequest($options, $access = null, $bypassAuth = false)
    {
        $this->outputOfPrcessing = '';
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

        $messageClass = null;
        $clientLangArray = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        foreach ($clientLangArray as $oneLanguage) {
            $langCountry = explode(';', $oneLanguage);
            if (strlen($langCountry[0]) > 0) {
                $clientLang = explode('-', $langCountry[0]);
                $messageClass = "MessageStrings_$clientLang[0]";
                if (file_exists("{$currentDir}{$messageClass}.php")) {
                    $messageClass = new $messageClass();
                    break;
                }
            }
            $messageClass = null;
        }

        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        $access = is_null($access) ? $_POST['access'] : $access;
        $clientId = isset($_POST['clientid']) ? $_POST['clientid'] : $_SERVER['REMOTE_ADDR'];
        $this->paramAuthUser = isset($_POST['authuser']) ? $_POST['authuser'] : "";
        $paramResponse = isset($_POST['response']) ? $_POST['response'] : "";

        $this->dbSettings->requireAuthentication = false;
        $this->dbSettings->requireAuthorization = false;
        $this->dbSettings->isDBNative = false;
        if (isset($options['authentication'])
            || $access == 'challenge' || $access == 'changepassword'
            || (isset($tableInfo['authentication'])
                && (isset($tableInfo['authentication']['all']) || isset($tableInfo['authentication'][$access])))
        ) {
            $this->dbSettings->requireAuthorization = true;
            $this->dbSettings->isDBNative = false;
            if (isset($options['authentication']['user'])
                && $options['authentication']['user'][0] == 'database_native'
            ) {
                $this->dbSettings->isDBNative = true;
            }
        }

        if (!$bypassAuth && $this->dbSettings->requireAuthorization) { // Authentication required
            if (strlen($this->paramAuthUser) == 0 || strlen($paramResponse) == 0) {
                // No username or password
                $access = "do nothing";
                $this->dbSettings->requireAuthentication = true;
            }
            // User and Password are suppried but...
            if ($access != 'challenge') { // Not accessing getting a challenge.
                if ($this->dbSettings->isDBNative) {
                    $keyArray = openssl_pkey_get_details(openssl_pkey_get_private($generatedPrivateKey, $passPhrase));
                    require_once('bi2php/biRSA.php');
                    $keyDecrypt = new biRSAKeyPair('0', bin2hex($keyArray['rsa']['d']), bin2hex($keyArray['rsa']['n']));
                    $decrypted = $keyDecrypt->biDecryptedString($paramResponse);
//                    $this->logger->setDebugMessage("#### {$decrypted} ###");
                    if ($decrypted !== false) {
                        $nlPos = strpos($decrypted, "\n");
                        $nlPos = ($nlPos === false) ? strlen($decrypted) : $nlPos;
                        $password = substr($decrypted, 0, $nlPos);
                        $password = (strlen($password) == 0) ? "f32b309d4759446fc81de858322ed391a0c167a0" : $password;
                        $challenge = substr($decrypted, $nlPos + 1);
                        if (!$this->checkChallenge($challenge, $clientId)) {
                            $access = "do nothing";
                            $this->dbSettings->requireAuthentication = true;
                        } else {
                            $this->dbSettings->setUserAndPaswordForAccess($this->paramAuthUser, $password);
                        }
                    } else {
                        $this->logger->setDebugMessage("Can't decrypt.");
                        $access = "do nothing";
                        $this->dbSettings->requireAuthentication = true;
                    }
                } else {
                    $noAuthorization = true;
                    $authorizedGroups = $this->dbClass->getAuthorizedGroups($access);
                    $authorizedUsers = $this->dbClass->getAuthorizedUsers($access);
                    $this->logger->setDebugMessage(
                        "authorizedUsers=" . var_export($authorizedUsers, true)
                        . "/authorizedGroups=" . var_export($authorizedGroups, true)
                        , 2);
                    if ((count($authorizedUsers) == 0 && count($authorizedGroups) == 0)) {
                        $noAuthorization = false;
                    } else {
                        $signedUser = $this->dbClass->authSupportUnifyUsernameAndEmail($this->dbSettings->currentUser);
                        if (in_array($signedUser, $authorizedUsers)) {
                            $noAuthorization = false;
                        } else {
                            if (count($authorizedGroups) > 0) {
                                $belongGroups = $this->dbClass->authSupportGetGroupsOfUser($signedUser);
                                if (count(array_intersect($belongGroups, $authorizedGroups)) != 0) {
                                    $noAuthorization = false;
                                }
                            }
                        }
                    }
                    if ($noAuthorization) {
                        $this->logger->setDebugMessage("Authorization doesn't meet the settings.");
                        $access = "do nothing";
                        $this->dbSettings->requireAuthentication = true;
                    }
                    if (!$this->checkAuthorization($this->paramAuthUser, $paramResponse, $clientId)) {
                        $this->logger->setDebugMessage(
                            "Authentication doesn't meet valid.{$this->paramAuthUser}/{$paramResponse}/{$clientId}");
                        // Not Authenticated!
                        $access = "do nothing";
                        $this->dbSettings->requireAuthentication = true;
                    }
                }
            }
        }
        // Come here access=challenge or authenticated access
        switch ($access) {
            case 'select':
                $result = $this->getFromDB($this->dbSettings->getTargetName());
                if (isset($tableInfo['protect-reading']) && is_array($tableInfo['protect-reading'])) {
                    $recordCount = count($result);
                    for ($index = 0; $index < $recordCount; $index++) {
                        foreach ($result[$index] as $field => $value) {
                            if (in_array($field, $tableInfo['protect-reading'])) {
                                $result[$index][$field] = "[protected]";
                            }
                        }
                    }
                }
                $this->outputOfPrcessing = 'dbresult=' . arrayToJS($result, '') . ';'
                    . "resultCount='{$this->countQueryResult($this->dbSettings->getTargetName())}';";
                break;
            case 'update':
                if (isset($tableInfo['protect-writing']) && is_array($tableInfo['protect-writing'])) {
                    $fieldArray = array();
                    $valueArray = array();
                    $counter = 0;
                    foreach ($this->dbSettings->fieldsRequired as $field) {
                        if (!in_array($field, $tableInfo['protect-writing'])) {
                            $fieldArray[] = $field;
                            $valueArray[] = $this->dbSettings->fieldsValues[$counter];
                        }
                        $counter++;
                    }
                    $this->dbSettings->setTargetFields($fieldArray);
                    $this->dbSettings->setValues($valueArray);
                }
                $this->setToDB($this->dbSettings->getTargetName());
                break;
            case 'new':
                $result = $this->newToDB($this->dbSettings->getTargetName(), $bypassAuth);
                $this->outputOfPrcessing = "newRecordKeyValue='{$result}';";
                break;
            case 'delete':
                $this->deleteFromDB($this->dbSettings->getTargetName());
                break;
            case 'challenge':
                break;
            case 'changepassword':
                if (isset($_POST['newpass'])) {
                    $changeResult = $this->changePassword($this->paramAuthUser, $_POST['newpass']);
                    $this->outputOfPrcessing = "changePasswordResult=" . ($changeResult ? "true;" : "false;");
                } else {
                    $this->outputOfPrcessing = "changePasswordResult=false;";
                }
                break;
        }

        if ($this->logger->debugLevel !== false) {
            $fInfo = $this->getFieldInfo($this->dbSettings->getTargetName());
            if ($fInfo != null) {
                foreach ($this->dbSettings->fieldsRequired as $fieldName) {
                    if (!in_array($fieldName, $fInfo)) {
                        $this->logger->setErrorMessage($messageClass->getMessageAs(1033, array($fieldName)));
                    }
                }
            }
        }
    }

    function finishCommunication($notFinish = false)
    {
        echo $this->outputOfPrcessing;
        echo implode('', $this->logger->getMessagesForJS());
        if ($notFinish) {
            return;
        }
        if (!$this->dbSettings->requireAuthorization) {
            return;
        }
        $generatedChallenge = $this->generateChallenge();
        $generatedUID = $this->generateClientId('');
        $userSalt = $this->saveChallenge(
            $this->dbSettings->isDBNative ? 0 : $this->paramAuthUser, $generatedChallenge, $generatedUID);
        $this->previousChallenge = "{$generatedChallenge}{$userSalt}";
        $this->previousClientid = "{$generatedUID}";
        echo "challenge='{$generatedChallenge}{$userSalt}';";
        echo "clientid='{$generatedUID}';";
        if ($this->dbSettings->requireAuthentication) {
            echo "requireAuth=true;"; // Force authentication to client
        }
        $tableInfo = $this->dbSettings->getDataSourceTargetArray();
        if (isset($tableInfo['authentication'])
            && isset($tableInfo['authentication']['media-handling'])
            && $tableInfo['authentication']['media-handling'] === true
        ) {
            $generatedChallenge = $this->generateChallenge();
            $this->saveChallenge($this->paramAuthUser, $generatedChallenge, "_im_media");
            echo "mediatoken='{$generatedChallenge}';";
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

    function authSupportGetSalt($username)
    {
        $hashedpw = $this->dbClass->authSupportRetrieveHashedPassword($username);
        return substr($hashedpw, -8);
    }

    /* returns user's hash salt.

    */
    function saveChallenge($username, $challenge, $clientId)
    {
        $this->dbClass->authSupportStoreChallenge($username, $challenge, $clientId);
        return $username === 0 ? "" : $this->authSupportGetSalt($username);
    }

    function checkAuthorization($username, $hashedvalue, $clientId)
    {
        $this->logger->setDebugMessage("[checkAuthorization]paramResponse={$hashedvalue}", 2);
        $returnValue = false;

        $this->dbClass->authSupportRemoveOutdatedChallenges();

        $storedChalenge = $this->dbClass->authSupportRetrieveChallenge($username, $clientId);
        $this->logger->setDebugMessage("[checkAuthorization]storedChalenge={$storedChalenge}", 2);

        if (strlen($storedChalenge) == 24) { // ex.fc0d54312ce33c2fac19d758
            $hashedPassword = $this->dbClass->authSupportRetrieveHashedPassword($username);
            $this->logger->setDebugMessage("[checkAuthorization]hashedPassword={$hashedPassword}", 2);
            if (strlen($hashedPassword) > 0) {
                if ($hashedvalue == hash_hmac('sha256', $hashedPassword, $storedChalenge)) {
//                    if ($hashedvalue == sha1($storedChalenge . $hashedPassword)) {
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
        $this->dbClass->authSupportRemoveOutdatedChallenges();
        // Database user mode is user_id=0
        $storedChallenge = $this->dbClass->authSupportRetrieveChallenge(0, $clientId);
        if (strlen($storedChallenge) == 24 && $storedChallenge == $challenge) { // ex.fc0d54312ce33c2fac19d758
            $returnValue = true;
        }
        return $returnValue;
    }

    function checkMediaToken($user, $token)
    {
        $returnValue = false;
        $this->dbClass->authSupportRemoveOutdatedChallenges();
        // Database user mode is user_id=0
        $storedChallenge = $this->dbClass->authSupportCheckMediaToken($user);

        if (strlen($storedChallenge) == 24 && $storedChallenge == $token) { // ex.fc0d54312ce33c2fac19d758
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

    function resetPasswordSequenceStart($email)
    {
        if ($email === false || $email == '') {
            return false;
        }
        $userid = $this->dbClass->authSupportGetUserIdFromEmail($email);
        $username = $this->dbClass->authSupportGetUsernameFromUserId($userid);
        if ($username === false || $username == '') {
            return false;
        }
        $clienthost = $this->generateChallenge();
        $hash = sha1($clienthost . $email . $username);
//        var_export($clienthost);
//        var_export($email);
//        var_export($userid);
//        var_export($username);
//        var_export($hash);
        if ($this->dbClass->authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)) {
            return array('randdata' => $clienthost, 'username' => $username);
        }
        return false;
    }

    function resetPasswordSequenceReturnBack($username, $email, $randdata, $newpassword)
    {
        if ($email === false || $email == '' || $username === false || $username == '') {
            return false;
        }
        $userid = $this->dbClass->authSupportGetUserIdFromUsername($username);
        $hash = sha1($randdata . $email . $username);
        if ($this->dbClass->authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)) {
            if ($this->changePassword($username, $newpassword)) {
                return true;
            }
        }
        return false;
    }
}