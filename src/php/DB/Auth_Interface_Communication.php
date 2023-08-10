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

interface Auth_Interface_Communication
{
    // The followings are used in Proxy::processingRequest.
    public function saveChallenge(string $username, string $challenge, string $clientId): ?string;

    public function checkAuthorization(string $username, bool $isSAML = false): bool;

    public function checkChallenge(string $challenge, string $clientId): bool;

    public function checkMediaToken(string $user, string $token): bool;

    public function addUser(string $username, string $password, bool $isSAML = false, ?array $attrs = null): array;

    public function authSupportGetSalt(string $username): ?string;

    public function changePassword(string $username, string $newpassword): bool;
}
