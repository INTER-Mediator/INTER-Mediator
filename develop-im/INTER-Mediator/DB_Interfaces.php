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
 * Time: 14:21
 * To change this template use File | Settings | File Templates.
 */

interface DB_Interface
{
    function getFromDB($dataSourceName);
    function countQueryResult($dataSourceName);
    function setToDB($dataSourceName);
    function newToDB($dataSourceName, $bypassAuth);
    function deleteFromDB($dataSourceName);
}

interface Auth_Interface_DB
{
    function authSupportStoreChallenge($username, $challenge, $clientId);
    function authSupportRemoveOutdatedChallenges();
    function authSupportRetrieveChallenge($username, $clientId, $isDelete = true);
    function authSupportRetrieveHashedPassword($username);
    function authSupportCreateUser($username, $hashedpassword);
    function authSupportChangePassword($username, $hashednewpassword);
    function authSupportCheckMediaToken($user);
    function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue);
    function authSupportGetUserIdFromEmail($email);
    function authSupportGetUserIdFromUsername($username);
    function authSupportGetUsernameFromUserId($userid);
    function authSupportGetGroupNameFromGroupId($groupid);
    function authSupportGetGroupsOfUser($user);
    function authSupportUnifyUsernameAndEmail($username);
    function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash);
    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash);
}

interface Auth_Interface_Communication
{
    // The followings are used in DB_Proxy::processingRequest.
    function generateClientId($prefix);
    function generateChallenge();
    function saveChallenge($username, $challenge, $clientId);
    function checkAuthorization($username, $hashedvalue, $clientId);
    function checkChallenge($challenge, $clientId);
    function checkMediaToken($user, $token);
    function addUser($username, $password);
    function authSupportGetSalt($username);
    function generateSalt();    // Use inside addUser
    function changePassword($username, $newpassword);
}

interface Auth_Interface_CommonDB
{
    function getFieldForAuthorization($operation);
    function getTargetForAuthorization($operation);
    function getAuthorizedUsers($operation = null);
    function getAuthorizedGroups($operation = null);
}

/**
 * Interface for DB_Proxy
 */
interface DB_Proxy_Interface extends DB_Interface, Auth_Interface_Communication {
    function initialize($datasource, $options, $dbspec, $debug, $target = null);
    function processingRequest($options, $access = null);
    function finishCommunication();
}

/**
 * Interface for DB_PDO, DB_FileMaker_FX
 */
interface DB_Access_Interface extends DB_Interface, Auth_Interface_DB {}

interface Extending_Interface_BeforeGet
{
    function doBeforeGetFromDB($dataSourceName);
}
interface Extending_Interface_AfterGet
{
    function doAfterGetFromDB($dataSourceName, $result);
}
interface Extending_Interface_AfterGet_WithNavigation
{
    function doAfterGetFromDB($dataSourceName, $result);
    function countQueryResult($dataSourceName);
}
interface Extending_Interface_BeforeSet
{
    function doBeforeSetToDB($dataSourceName);
}
interface Extending_Interface_AfterSet
{
    function doAfterSetToDB($dataSourceName, $result);
}
interface Extending_Interface_BeforeNew
{
    function doBeforeNewToDB($dataSourceName);
}
interface Extending_Interface_AfterNew
{
    function doAfterNewToDB($dataSourceName, $result);
}
interface Extending_Interface_BeforeDelete
{
    function doBeforeDeleteFromDB($dataSourceName);
}
interface Extending_Interface_AfterDelete
{
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
