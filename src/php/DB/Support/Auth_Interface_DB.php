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

namespace INTERMediator\DB\Support;

interface Auth_Interface_DB					// with using table for authentication/authorization
{
    public function authSupportStoreChallenge($uid, $challenge, $clientId);	// issuedhash
    public function authSupportRemoveOutdatedChallenges();							// issuedhash
    public function authSupportRetrieveChallenge($uid, $clientId, $isDelete = true);	// issuedhash
    public function authSupportCheckMediaToken($uid);								// issuedhash
    public function authSupportRetrieveHashedPassword($username);					// authuser
    public function authSupportCreateUser($username, $hashedpassword, $isSAML = false, $ldapPassword = null, $attrs=null);	// authuser
    public function authSupportChangePassword($username, $hashednewpassword);		// authuser
    public function authSupportCheckMediaPrivilege($tableName, $targeting, $userField, $user, $keyField, $keyValue);	// (any table)
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
    public function authSupportIsWithinSAMLLimit($userID);  // authuser
    public function authSupportCanMigrateSHA256Hash();  // authuser, issuedhash
}
