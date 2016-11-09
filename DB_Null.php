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

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'INTER-Mediator.php');

class DB_Null extends DB_UseSharedObjects implements DB_Access_Interface
{

    public function readFromDB()
    {
        return null;
    }

    public function countQueryResult()
    {
        return 0;
    }

    public function getTotalCount()
    {
        return 0;
    }

    public function updateDB()
    {
        return null;
    }

    public function createInDB($bypassAuth)
    {
        return null;
    }

    public function deleteFromDB()
    {
        return null;
    }

    public function getFieldInfo($dataSourceName)
    {
        return null;
    }

    public function setupConnection()
    {
        return true;
    }

    public static function defaultKey()
    {
        return null;
    }

    public function getDefaultKey()
    {
        return null;
    }

    public function isPossibleOperator($operator)
    {
        return null;
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        return null;
    }

    public function requireUpdatedRecord($value)
    {
        return null;
    }

    public function updatedRecord()
    {
        return null;
    }

    public function isContainingFieldName($fname, $fieldnames)
    {
        return null;
    }

    public function isNullAcceptable()
    {
        return null;
    }

    function authSupportStoreChallenge($uid, $challenge, $clientId)
    {
        return null;
    }

    function authSupportRemoveOutdatedChallenges()
    {
        return null;
    }

    function authSupportRetrieveChallenge($uid, $clientId, $isDelete = true)
    {
        return null;
    }

    function authSupportCheckMediaToken($uid)
    {
        return null;
    }

    function authSupportRetrieveHashedPassword($username)
    {
        return null;
    }

    function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null)
    {
        return null;
    }

    function authSupportChangePassword($username, $hashednewpassword)
    {
        return null;
    }

    function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        return null;
    }

    function authSupportGetUserIdFromEmail($email)
    {
        return null;
    }

    function authSupportGetUserIdFromUsername($username)
    {
        return null;
    }

    function authSupportGetUsernameFromUserId($userid)
    {
        return null;
    }

    function authSupportGetGroupNameFromGroupId($groupid)
    {
        return null;
    }

    function authSupportGetGroupsOfUser($user)
    {
        return null;
    }

    function authSupportUnifyUsernameAndEmail($username)
    {
        return null;
    }

    function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        return null;
    }

    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        return null;
    }

    public function softDeleteActivate($field, $value)
    {
        return null;
    }

    public function copyInDB()
    {
        return false;
    }

    public function isSupportAggregation()
    {
        return false;
    }

    public function authSupportUserEnrollmentStart($userid, $hash)
    {
        return false;
    }

    public function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW)
    {
        return false;
    }

    public function authSupportUserEnrollmentEnrollingUser($hash)
    {
        return false;
    }
}
