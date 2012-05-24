<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/20
 * Time: 14:21
 * To change this template use File | Settings | File Templates.
 */

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

    function initialize($datasource, $options, $dbspec, $debug);
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
    function generateClientId($prefix);

    function generateChallenge();

    function generateSalt();

    function saveChallenge($username, $challenge, $clientId);

    function checkAuthorization($username, $hashedvalue, $clientId);

    function checkChallenge($challenge, $clientId);

    function addUser($username, $password);
}

/**
 * Interface for DB_PDO, DB_FileMaker_FX
 */
interface Auth_Interface_CommonDB
{
    function getFieldForAuthorization($operation);

    function getTargetForAuthorization($operation);

    function getAuthorizedUsers($operation = null);

    function getAuthorizedGroups($operation = null);

    function changePassword($username, $oldpassword, $newpassword);

}

interface DB_Proxy_Interface extends DB_Interface
{

}

interface DB_Access_Interface extends DB_Interface, Auth_Interface_DB
{

}

interface Extending_Interface_BeforeGet {
    function doBeforeGetFromDB($dataSourceName);
}
interface Extending_Interface_AfterGet {
    function doAfterGetFromDB($dataSourceName, $result);
//    function countQueryResult($dataSourceName);
}
interface Extending_Interface_BeforeSet {
    function doBeforeSetToDB($dataSourceName);
}
interface Extending_Interface_AfterSet {
    function doAfterSetToDB($dataSourceName, $result);
}
interface Extending_Interface_BeforeNew {
    function doBeforeNewToDB($dataSourceName);
}
interface Extending_Interface_AfterNew {
    function doAfterNewToDB($dataSourceName, $result);
}
interface Extending_Interface_BeforeDelete {
    function doBeforeDeleteFromDB($dataSourceName);
}
interface Extending_Interface_AfterDelete {
    function doAfterDeleteFromDB($dataSourceName, $result);
}

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
