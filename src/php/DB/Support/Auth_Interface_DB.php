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

interface Auth_Interface_DB                    // with using table for authentication/authorization
{
    public function authSupportStoreChallenge(?string $uid, string $challenge, string $clientId): void;    // issuedhash

    public function authSupportRemoveOutdatedChallenges();                            // issuedhash

    public function authSupportRetrieveChallenge(string $uid, string $clientId, bool $isDelete = true): ?string;    // issuedhash

    public function authSupportCheckMediaToken(string $uid): ?string;                                // issuedhash

    public function authSupportRetrieveHashedPassword(string $username): ?string;                    // authuser

    public function authSupportCreateUser(string $username, string $hashedpassword, bool $isSAML = false,
                                          string $ldapPassword = null, ?array $attrs = null): bool;    // authuser

    public function authSupportChangePassword(string $username, string $hashednewpassword): bool;        // authuser

    public function authSupportCheckMediaPrivilege(string $tableName, string $targeting, string $userField,
                                                   string $user, string $keyField, string $keyValue): ?array;    // (any table)

    public function authSupportGetUserIdFromEmail(string $email): ?string;                            // authuser

    public function authSupportGetUserIdFromUsername(?string $username): ?string;                    // authuser

    public function authSupportGetUsernameFromUserId(string $userid): ?string;                        // authuser

    public function authSupportGetGroupNameFromGroupId(string $groupid): ?string;                    // authgroup

    public function authSupportGetGroupsOfUser(?string $user): array;                                // authcor

    public function authSupportUnifyUsernameAndEmail(?string $username): ?string;                    // authuser

    public function authSupportStoreIssuedHashForResetPassword(
        string $userid, string $clienthost, string $hash): bool;    // issuedhash

    public function authSupportCheckIssuedHashForResetPassword(
        string $userid, string $randdata, string $hash): bool;        // issuedhash

    public function authSupportUserEnrollmentStart(string $userid, string $hash): bool;             // issuedhash

    public function authSupportUserEnrollmentEnrollingUser(string $hash): ?string;                     // issuedhash

    public function authSupportUserEnrollmentActivateUser(
        string $userID, ?string $password, ?string $rawPWField, ?string $rawPW): ?string;  // authuser

    public function authSupportIsWithinSAMLLimit(string $userID): bool;  // authuser

    public function authSupportCanMigrateSHA256Hash(): bool;  // authuser, issuedhash

    /**
     * @param array $keyValues
     * @return bool(true: create user, false: reuse user)|null in error
     */
    public function authSupportOAuthUserHandling(array $keyValues): bool;
}
