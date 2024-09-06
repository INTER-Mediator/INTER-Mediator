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

/**
 *
 */
interface Auth_Interface_CommonDB
{
    /**
     * @param string $operation
     * @return string|null
     */
    public function getFieldForAuthorization(string $operation): ?string;

    /**
     * @param string $operation
     * @return string|null
     */
    public function getTargetForAuthorization(string $operation): ?string;

    /**
     * @param string $operation
     * @return string|null
     */
    public function getNoSetForAuthorization(string $operation): ?string;

    /**
     * @param string|null $operation
     * @return array
     */
    public function getAuthorizedUsers(?string $operation = null): array;

    /**
     * @param string|null $operation
     * @return array
     */
    public function getAuthorizedGroups(?string $operation = null): array;

    /**
     * handling auth table: issuedhash
     * @param string|null $uid
     * @param string $challenge
     * @param string $clientId
     * @param string $prefix
     * @return void
     */
    public function authSupportStoreChallenge(?string $uid, string $challenge, string $clientId, string $prefix = ""): void;

    /**
     * handling auth table: issuedhash
     * @return bool
     */
    public function authSupportRemoveOutdatedChallenges(): bool;

    /**
     * handling auth table: issuedhash
     * @param string $uid
     * @param string $clientId
     * @param bool $isDelete
     * @param string $prefix
     * @return string|null
     */
    public function authSupportRetrieveChallenge(string $uid, string $clientId, bool $isDelete = true, string $prefix = ""): ?string;

    /**
     * handling auth table: issuedhash
     * @param string $uid
     * @return string|null
     */
    public function authSupportCheckMediaToken(string $uid): ?string;

    /**
     * handling auth table: authuser
     * @param string $username
     * @return string|null
     */
    public function authSupportRetrieveHashedPassword(string $username): ?string;

    /**
     * handling auth table: authuser
     * @param string $username
     * @param string $hashedpassword
     * @param bool $isSAML
     * @param string|null $ldapPassword
     * @param array|null $attrs
     * @return bool
     */
    public function authSupportCreateUser(string $username, string $hashedpassword, bool $isSAML = false,
                                          string $ldapPassword = null, ?array $attrs = null): bool;

    /**
     * handling auth table: authuser
     * @param string $username
     * @param string $hashednewpassword
     * @return bool
     */
    public function authSupportChangePassword(string $username, string $hashednewpassword): bool;

    /**
     * handling auth table: (any tables)
     * @param string $tableName
     * @param string $targeting
     * @param string $userField
     * @param string $user
     * @param string $keyField
     * @param string $keyValue
     * @return array|null
     */
    public function authSupportCheckMediaPrivilege(string $tableName, string $targeting, string $userField,
                                                   string $user, string $keyField, string $keyValue): ?array;

    /**
     * handling auth table: authuser
     * @param string $email
     * @return string|null
     */
    public function authSupportGetUserIdFromEmail(string $email): ?string;

    /**
     * handling auth table: authuser
     * @param string|null $username
     * @return string|null
     */
    public function authSupportGetUserIdFromUsername(?string $username): ?string;

    /**
     * handling auth table: authuser
     * @param string $userid
     * @return string|null
     */
    public function authSupportGetUsernameFromUserId(string $userid): ?string;

    /**
     * handling auth table: authgroup
     * @param string $groupid
     * @return string|null
     */
    public function authSupportGetGroupNameFromGroupId(string $groupid): ?string;

    /**
     * handling auth table: authuser, authcor, authgroup
     * @param string|null $user
     * @return array
     */
    public function authSupportGetGroupsOfUser(?string $user): array;

    /**
     * handling auth table: authuser
     * @param string|null $username
     * @return string|null
     */
    public function authSupportUnifyUsernameAndEmail(?string $username): ?string;

    /**
     * handling auth table: authuser
     * @param string|null $username
     * @return string|null
     */
    public function authSupportEmailFromUnifiedUsername(?string $username): ?string;

    /**
     * handling auth table: issuedhash
     * @param string $userid
     * @param string $clienthost
     * @param string $hash
     * @return bool
     */
    public function authSupportStoreIssuedHashForResetPassword(
        string $userid, string $clienthost, string $hash): bool;

    /**
     * handling auth table: issuedhash
     * @param string $userid
     * @param string $randdata
     * @param string $hash
     * @return bool
     */
    public function authSupportCheckIssuedHashForResetPassword(
        string $userid, string $randdata, string $hash): bool;

    /**
     * handling auth table: issuedhash
     * @param string $userid
     * @param string $hash
     * @return bool
     */
    public function authSupportUserEnrollmentStart(string $userid, string $hash): bool;

    /**
     * handling auth table: issuedhash
     * @param string $hash
     * @return string|null
     */
    public function authSupportUserEnrollmentEnrollingUser(string $hash): ?string;

    /**
     * handling auth table: authuser
     * @param string $userID
     * @param string|null $password
     * @param string|null $rawPWField
     * @param string|null $rawPW
     * @return string|null
     */
    public function authSupportUserEnrollmentActivateUser(
        string $userID, ?string $password, ?string $rawPWField, ?string $rawPW): ?string;

    /**
     * handling auth table: authuser
     * @param string $userID
     * @return bool
     */
    public function authSupportIsWithinSAMLLimit(string $userID): bool;

    /**
     * handling auth table: authuser, issuedhash
     * @return bool
     */
    public function authSupportCanMigrateSHA256Hash(): bool;

    /**
     * @param array $keyValues
     * @return bool(true: create user, false: reuse user)|null in error
     */
    public function authSupportOAuthUserHandling(array $keyValues): bool;

    /** This method merged following methods authSupportUnifyUsernameAndEmail,
     * authSupportRetrieveHashedPassword and authSupportGetUserIdFromUsername
     * @param string $userID
     * @return array
     */
    public function authSupportUnifyUsernameAndEmailAndGetInfo(string $userID): array;

}
