<?php
/*
* INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
*
*   by Masayuki Nii  msyk@msyk.net Copyright (c) 2013 Masayuki Nii, All rights reserved.
*
*   This project started at the end of 2009.
*   INTER-Mediator is supplied under MIT License.
*/

class DB_DefEditor extends DB_AuthCommon implements DB_Access_Interface {

    function getFromDB($dataSourceName)
    {
        // TODO: Implement getFromDB() method.
    }

    function countQueryResult($dataSourceName)
    {
        // TODO: Implement countQueryResult() method.
    }

    function setToDB($dataSourceName)
    {
        // TODO: Implement setToDB() method.
    }

    function newToDB($dataSourceName, $bypassAuth)
    {
        // TODO: Implement newToDB() method.
    }

    function deleteFromDB($dataSourceName)
    {
        // TODO: Implement deleteFromDB() method.
    }

    function getFieldInfo($dataSourceName)
    {
        // TODO: Implement getFieldInfo() method.
    }

    function authSupportStoreChallenge($username, $challenge, $clientId)
    {
        // TODO: Implement authSupportStoreChallenge() method.
    }

    function authSupportRemoveOutdatedChallenges()
    {
        // TODO: Implement authSupportRemoveOutdatedChallenges() method.
    }

    function authSupportRetrieveChallenge($username, $clientId, $isDelete = true)
    {
        // TODO: Implement authSupportRetrieveChallenge() method.
    }

    function authSupportRetrieveHashedPassword($username)
    {
        // TODO: Implement authSupportRetrieveHashedPassword() method.
    }

    function authSupportCreateUser($username, $hashedpassword)
    {
        // TODO: Implement authSupportCreateUser() method.
    }

    function authSupportChangePassword($username, $hashednewpassword)
    {
        // TODO: Implement authSupportChangePassword() method.
    }

    function authSupportCheckMediaToken($user)
    {
        // TODO: Implement authSupportCheckMediaToken() method.
    }

    function authSupportCheckMediaPrivilege($tableName, $userField, $user, $keyField, $keyValue)
    {
        // TODO: Implement authSupportCheckMediaPrivilege() method.
    }

    function authSupportGetUserIdFromEmail($email)
    {
        // TODO: Implement authSupportGetUserIdFromEmail() method.
    }

    function authSupportGetUserIdFromUsername($username)
    {
        // TODO: Implement authSupportGetUserIdFromUsername() method.
    }

    function authSupportGetUsernameFromUserId($userid)
    {
        // TODO: Implement authSupportGetUsernameFromUserId() method.
    }

    function authSupportGetGroupNameFromGroupId($groupid)
    {
        // TODO: Implement authSupportGetGroupNameFromGroupId() method.
    }

    function authSupportGetGroupsOfUser($user)
    {
        // TODO: Implement authSupportGetGroupsOfUser() method.
    }

    function authSupportUnifyUsernameAndEmail($username)
    {
        // TODO: Implement authSupportUnifyUsernameAndEmail() method.
    }

    function authSupportStoreIssuedHashForResetPassword($userid, $clienthost, $hash)
    {
        // TODO: Implement authSupportStoreIssuedHashForResetPassword() method.
    }

    function authSupportCheckIssuedHashForResetPassword($userid, $randdata, $hash)
    {
        // TODO: Implement authSupportCheckIssuedHashForResetPassword() method.
    }
}