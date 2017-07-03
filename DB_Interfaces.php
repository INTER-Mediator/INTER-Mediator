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

interface DB_Interface extends DB_Spec_Behavior
{
    public function readFromDB();         // former getFromDB
    public function countQueryResult();
    public function getTotalCount();
    public function updateDB();           // former setToDB
    public function createInDB($bypassAuth);  // former newToDB
    public function deleteFromDB();
    public function copyInDB();
}

interface DB_Spec_Behavior
{
    public function getFieldInfo($dataSourceName);
    public function setupConnection();
    public static function defaultKey();   // For PHP 5.3 or above
    public function getDefaultKey();   // For PHP 5.2
    public function requireUpdatedRecord($value);
    public function updatedRecord();
    public function isContainingFieldName($fname, $fieldnames);
    public function isNullAcceptable();
    public function softDeleteActivate($field, $value);
    public function isSupportAggregation();
}

interface DB_Interface_Registering
{
    public function isExistRequiredTable();
    public function queriedEntity();
    public function queriedCondition();
    public function queriedPrimaryKeys();
    public function register($clientId, $entity, $condition, $pkArray);
    public function unregister($clientId, $tableKeys);
    public function matchInRegisterd($clientId, $entity, $pkArray);
    public function appendIntoRegisterd($clientId, $entity, $pkArray);
    public function removeFromRegisterd($clientId, $entity, $pkArray);
}

interface Auth_Interface_DB					// with using table for authentication/authorization
{
    public function authSupportStoreChallenge($uid, $challenge, $clientId);	// issuedhash
    public function authSupportRemoveOutdatedChallenges();							// issuedhash
    public function authSupportRetrieveChallenge($uid, $clientId, $isDelete = true);	// issuedhash
    public function authSupportCheckMediaToken($uid);								// issuedhash
    public function authSupportRetrieveHashedPassword($username);					// authuser
    public function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null);	// authuser
    public function authSupportChangePassword($username, $hashednewpassword);		// authuser
    public function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue);	// (any table)
    public function authSupportGetUserIdFromEmail($email);							// authuser
    public function authSupportGetUserIdFromUsername($username);					// authuser
    public function authSupportGetUsernameFromUserId($userid);						// authuser
    public function authSupportGetGroupNameFromGroupId($groupid);					// authgroup
    public function authSupportGetGroupsOfUser($user);								// authcor
    public function authSupportUnifyUsernameAndEmail($username);					// authuser
    public function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash);	// issuedhash
    public function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash);		// issuedhash
    public function authSupportUserEnrollmentStart($userid, $hash);             // issuedhash
    public function authSupportUserEnrollmentEnrollingUser($hash);                     // issuedhash
    public function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW);  // authuser
}

interface Auth_Interface_Communication
{
    // The followings are used in DB_Proxy::processingRequest.
    public function generateClientId($prefix);
    public function generateChallenge();
    public function saveChallenge($username, $challenge, $clientId);
    public function checkAuthorization($username, $hashedvalue, $clientId);
    public function checkChallenge($challenge, $clientId);
    public function checkMediaToken($user, $token);
    public function addUser($username, $password);
    public function authSupportGetSalt($username);
    public function generateSalt();    // Use inside addUser
    public function changePassword($username, $newpassword);
}

interface Auth_Interface_CommonDB
{
    public function getFieldForAuthorization($operation);
    public function getTargetForAuthorization($operation);
    public function getAuthorizedUsers($operation = null);
    public function getAuthorizedGroups($operation = null);
}

/**
 * Interface for DB_Proxy
 */
interface DB_Proxy_Interface extends DB_Interface, Auth_Interface_Communication {
    public function initialize($datasource, $options, $dbspec, $debug, $target = null);
    public function processingRequest($access = null, $bypassAuth = false);
    public function finishCommunication();
}

/**
 * Interface for DB_PDO, DB_FileMaker_FX
 */
interface DB_Access_Interface extends DB_Interface, Auth_Interface_DB {}

interface Extending_Interface_BeforeDelete
{
    public function doBeforeDeleteFromDB();
}

interface Extending_Interface_AfterDelete
{
    public function doAfterDeleteFromDB($result);
}


interface Extending_Interface_BeforeRead
{
    public function doBeforeReadFromDB();
}

interface Extending_Interface_AfterRead
{
    public function doAfterReadFromDB($result);
}

interface Extending_Interface_AfterRead_WithNavigation
{
    public function doAfterReadFromDB( $result);
    public function countQueryResult();
    public function getTotalCount();
}

interface Extending_Interface_BeforeUpdate
{
    public function doBeforeUpdateDB();
}

interface Extending_Interface_AfterUpdate
{
    public function doAfterUpdateToDB($result);
}

interface Extending_Interface_BeforeCreate
{
    public function doBeforeCreateToDB();
}

interface Extending_Interface_AfterCreate
{
    public function doAfterCreateToDB($result);
}

interface Extending_Interface_BeforeCopy
{
    public function doBeforeCopyInDB();
}

interface Extending_Interface_AfterCopy
{
    public function doAfterCopyInDB($result);
}
