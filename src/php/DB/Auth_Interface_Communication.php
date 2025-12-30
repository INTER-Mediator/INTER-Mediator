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

namespace INTERMediator\DB;

/**
 * Interface for authentication-related communication methods in INTER-Mediator DB classes.
 */
interface Auth_Interface_Communication
{
    /** Save a challenge for a user session.
     * @param string|null $username The username, or null.
     * @param string $challenge The challenge string.
     * @param string $clientId The client identifier.
     * @param string $prefix Optional prefix for the challenge.
     * @return void
     */
    public function saveChallenge(?string $username, string $challenge, string $clientId, string $prefix = ""): void;

    // public function checkAuthorization(string $username, bool $isSAML = false): bool;
    // public function checkChallenge(string $challenge, string $clientId): bool;

    /** Check if the given media token is valid for the user.
     * @param string $user The username.
     * @param string $token The media token.
     * @return bool True if the token is valid, false otherwise.
     */
    public function checkMediaToken(string $user, string $token): bool;

    /** Add a user with optional SAML and attributes.
     * @param string $username The username.
     * @param string $password The password.
     * @param bool $isSAML Whether the user is SAML-based.
     * @param array|null $attrs Optional additional attributes.
     * @return array Result of the add operation.
     */
    public function addUser(string $username, string $password, bool $isSAML = false, ?array $attrs = null): array;

    /** Get the salt value for authentication for a given username.
     * @param string|null $username The username, or null.
     * @return string|null The salt value or null if not found.
     */
    public function authSupportGetSalt(?string $username): ?string;

    /** Change the password for a given username.
     * @param string $username The username.
     * @param string $newpassword The new password.
     * @return bool True on success, false otherwise.
     */
    public function changePassword(string $username, string $newpassword): bool;
}
