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
class DB_PageEditor extends DB_AuthCommon implements DB_Access_Interface
{
    private $recordCount;
    private $isRequiredUpdated = false;
    private $updatedRecord = null;

    function readFromDB()
    {
        $dataSourceName = $this->dbSettings->getDataSourceName();
        $filePath = $this->dbSettings->getCriteriaValue('target');
        if (substr_count($filePath, '../') > 2) {
            $this->logger->setErrorMessage("You can't access files in inhibit area: {$dataSourceName}.");
            return null;
        }
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage("The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            $this->recordCount = 0;
            return null;
        }
        $this->recordCount = 1;
        return array(array('id' => 1, 'content' => $fileContent));
    }

    function countQueryResult()
    {
        return $this->recordCount;
    }

    function getTotalCount()
    {
        return $this->recordCount;
    }

    function updateDB()
    {
        $dataSourceName = $this->dbSettings->getDataSourceName();
        $filePath = $this->dbSettings->getValueOfField('target');
        if (substr_count($filePath, '../') > 2) {
            $this->logger->setErrorMessage("You can't access files in inhibit area: {$dataSourceName}.");
            return null;
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            $this->logger->setErrorMessage("The 'target' parameter doesn't point the valid file path in context: {$dataSourceName}.");
            return null;
        }
        $fileWriteResult = file_put_contents($filePath, $this->dbSettings->getValueOfField('content'));
        if ($fileWriteResult === false) {
            $this->logger->setErrorMessage("The file {$filePath} doesn't have the permission to write.");
            return null;
        }
        $result = array(array('id' => 1, 'content' => $this->dbSettings->getValueOfField('content')));
        $this->updatedRecord = $result;
        return $result;
    }

    function createInDB($bypassAuth)
    {
    }

    function deleteFromDB()
    {
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

    function authSupportCreateUser($username, $hashedpassword, $isLDAP = false, $ldapPassword = null)
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

    public function setupConnection()
    {
        return true;
    }

    public static function defaultKey()
    {
        // TODO: Implement defaultKey() method.
    }

    public function getDefaultKey()
    {
        // TODO: Implement getDefaultKey() method.
    }

    public function isPossibleOperator($operator)
    {
        // TODO: Implement isPossibleOperator() method.
    }

    public function isPossibleOrderSpecifier($specifier)
    {
        // TODO: Implement isPossibleOrderSpecifier() method.
    }

    public
    function requireUpdatedRecord($value)
    {
        $this->isRequiredUpdated = $value;
    }

    public
    function updatedRecord()
    {
        return $this->updatedRecord;
    }

    public function isContainingFieldName($fname, $fieldnames)
    {
        // TODO: Implement isContainingFieldName() method.
    }

    public function isNullAcceptable()
    {
        // TODO: Implement isNullAcceptable() method.
    }

    public function softDeleteActivate($field, $value)
    {
        // TODO: Implement softDeleteActivate() method.
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
        // TODO: Implement authSupportUserEnrollmentStart() method.
    }

    public function authSupportUserEnrollmentActivateUser($userID, $password, $rawPWField, $rawPW)
    {
        // TODO: Implement authSupportUserEnrollmentActivateUser() method.
    }

    public function authSupportUserEnrollmentEnrollingUser($hash)
    {
        // TODO: Implement authSupportUserEnrollmentEnrollingUser() method.
    }
}
