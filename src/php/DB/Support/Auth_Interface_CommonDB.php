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
 * Interface for common database authentication support.
 * Provides methods for handling authorization fields, targets, users, groups,
 * and challenge/response mechanisms for authentication tables.
 */
interface Auth_Interface_CommonDB
{
    /** Returns the field name used for authorization for a given operation.
     * @param string $operation The operation type (e.g., 'select', 'update').
     * @return string|null The field name, or null if not set.
     */
    public function getFieldForAuthorization(string $operation): ?string;

    /** Returns the target value used for authorization for a given operation.
     * @param string $operation The operation type.
     * @return string|null The target value, or null if not set.
     */
    public function getTargetForAuthorization(string $operation): ?string;

    /** Returns the value for 'no set' authorization for a given operation.
     * @param string $operation The operation type.
     * @return string|null The value, or null if not set.
     */
    public function getNoSetForAuthorization(string $operation): ?string;

    /** Returns a list of authorized users for a given operation.
     * @param string|null $operation The operation type, or null for all.
     * @return array List of authorized users.
     */
    public function getAuthorizedUsers(?string $operation = null): array;

    /** Returns a list of authorized groups for a given operation.
     * @param string|null $operation The operation type, or null for all.
     * @return array List of authorized groups.
     */
    public function getAuthorizedGroups(?string $operation = null): array;

    /** Stores a challenge in the issuedhash authentication table.
     * @param string|null $uid The user ID.
     * @param string $challenge The challenge string.
     * @param string $clientId The client identifier.
     * @param string $prefix Optional prefix for the challenge.
     * @param bool $alwaysInsert Whether to always insert a new challenge.
     * @return void
     */
    public function authSupportStoreChallenge(?string $uid, string $challenge, string $clientId, string $prefix = "", bool $alwaysInsert = false): void;

    /** Removes outdated challenges from the issuedhash authentication table.
     * @return bool True if successful, false otherwise.
     */
    public function authSupportRemoveOutdatedChallenges(): bool;

    /** Retrieves a challenge from the issuedhash authentication table.
     * @param string $uid The user ID.
     * @param string $clientId The client identifier.
     * @param bool $isDelete Whether to delete the challenge after retrieval.
     * @param string $prefix Optional prefix for the challenge.
     * @param bool $isMulti Whether to support multiple challenges.
     * @return string|null The challenge string, or null if not found.
     */
    public function authSupportRetrieveChallenge(
        string $uid, string $clientId, bool $isDelete = true, string $prefix = "", bool $isMulti = false): ?string;

    /** Checks the media token for a user in the issuedhash authentication table.
     * @param string $uid The user ID.
     * @return string|null The media token, or null if not found.
     */
    public function authSupportCheckMediaToken(string $uid): ?string;

    /** Retrieves the hashed password for a user from the authuser authentication table.
     * @param string $username The username.
     * @return string|null The hashed password, or null if not found.
     */
    public function authSupportRetrieveHashedPassword(string $username): ?string;

    /** Creates a new user in the authuser authentication table.
     * @param string $username The username.
     * @param string $hashedpassword The hashed password.
     * @param bool $isSAML Whether SAML authentication is used.
     * @param string|null $ldapPassword Optional LDAP password.
     * @param array|null $attrs Optional attributes.
     * @return bool True if successful, false otherwise.
     */
    public function authSupportCreateUser(string $username, string $hashedpassword, bool $isSAML = false,
                                          string|null $ldapPassword = null, ?array $attrs = null): bool;

    /** Changes the password for a user in the authuser authentication table.
     * @param string $username The username.
     * @param string $hashednewpassword The new hashed password.
     * @return bool True if successful, false otherwise.
     */
    public function authSupportChangePassword(string $username, string $hashednewpassword): bool;

    /** Checks the media privilege for a user in a given table.
     * @param string $tableName The table name.
     * @param string $targeting The targeting value.
     * @param string $userField The user field name.
     * @param string $user The user value.
     * @param string $keyField The key field name.
     * @param string $keyValue The key value.
     * @return array|null The media privilege data, or null if not found.
     */
    public function authSupportCheckMediaPrivilege(string $tableName, string $targeting, string $userField,
                                                   string $user, string $keyField, string $keyValue): ?array;

    /** Retrieves the user ID from an email address in the authuser authentication table.
     * @param string $email The email address.
     * @return string|null The user ID, or null if not found.
     */
    public function authSupportGetUserIdFromEmail(string $email): ?string;

    /** Retrieves the user ID from a username in the authuser authentication table.
     * @param string|null $username The username.
     * @return string|null The user ID, or null if not found.
     */
    public function authSupportGetUserIdFromUsername(?string $username): ?string;

    /** Retrieves the username from a user ID in the authuser authentication table.
     * @param string $userid The user ID.
     * @return string|null The username, or null if not found.
     */
    public function authSupportGetUsernameFromUserId(string $userid): ?string;

    /** Retrieves the group name from a group ID in the authgroup authentication table.
     * @param string $groupid The group ID.
     * @return string|null The group name, or null if not found.
     */
    public function authSupportGetGroupNameFromGroupId(string $groupid): ?string;

    /** Retrieves the groups for a user in the authuser and authgroup authentication tables.
     * @param string|null $user The user value.
     * @return array The groups for the user.
     */
    public function authSupportGetGroupsOfUser(?string $user): array;

    /** Unifies a username and email address in the authuser authentication table.
     * @param string|null $username The username.
     * @return string|null The unified username, or null if not found.
     */
    public function authSupportUnifyUsernameAndEmail(?string $username): ?string;

    /** Retrieves the email address from a unified username in the authuser authentication table.
     * @param string|null $username The unified username.
     * @return string|null The email address, or null if not found.
     */
    public function authSupportEmailFromUnifiedUsername(?string $username): ?string;

    /** Stores an issued hash for a user in the issuedhash authentication table.
     * @param string $userid The user ID.
     * @param string $clienthost The client host.
     * @param string $hash The hash value.
     * @return bool True if successful, false otherwise.
     */
    public function authSupportStoreIssuedHashForResetPassword(
        string $userid, string $clienthost, string $hash): bool;

    /** Checks the issued hash for a user in the issuedhash authentication table.
     * @param string $userid The user ID.
     * @param string $randdata The random data.
     * @param string $hash The hash value.
     * @return bool True if successful, false otherwise.
     */
    public function authSupportCheckIssuedHashForResetPassword(
        string $userid, string $randdata, string $hash): bool;

    /** Starts the user enrollment process for a user in the issuedhash authentication table.
     * @param string $userid The user ID.
     * @param string $hash The hash value.
     * @return bool True if successful, false otherwise.
     */
    public function authSupportUserEnrollmentStart(string $userid, string $hash): bool;

    /** Retrieves the enrolling user from the issuedhash authentication table.
     * @param string $hash The hash value.
     * @return string|null The enrolling user, or null if not found.
     */
    public function authSupportUserEnrollmentEnrollingUser(string $hash): ?string;

    /** Activates a user in the authuser authentication table.
     * @param string $userID The user ID.
     * @param string|null $password The password.
     * @param string|null $rawPWField The raw password field.
     * @param string|null $rawPW The raw password.
     * @return string|null The activation result, or null if not found.
     */
    public function authSupportUserEnrollmentActivateUser(
        string $userID, ?string $password, ?string $rawPWField, ?string $rawPW): ?string;

    /** Checks if a user is within the SAML limit in the authuser authentication table.
     * @param string $userID The user ID.
     * @return bool True if within the limit, false otherwise.
     */
    public function authSupportIsWithinSAMLLimit(string $userID): bool;

    /** Checks if the SHA256 hash can be migrated in the authuser and issuedhash authentication tables.
     * @return bool True if migratable, false otherwise.
     */
    public function authSupportCanMigrateSHA256Hash(): bool;

    /** Handles OAuth user authentication.
     * @param array $keyValues The key-value pairs.
     * @return bool|null True if create user, false if reuse user, null in error.
     */
    public function authSupportOAuthUserHandling(array $keyValues): ?bool;

    /** Unifies a username and email address, retrieves the hashed password, and gets the user ID.
     * @param string|null $userID The user ID.
     * @return array The unified data.
     */
    public function authSupportUnifyUsernameAndEmailAndGetInfo(?string $userID): array;

    public function getLoginUserInfo(string $userID): array;

}
