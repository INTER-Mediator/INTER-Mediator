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
    public function saveChallenge($username, $challenge, $clientId);

    public function checkAuthorization($username, $isLDAP = false);

    public function checkChallenge($challenge, $clientId);

    public function checkMediaToken($user, $token);

    public function addUser($username, $password, $isLDAP = false, $attrs = null);

    public function authSupportGetSalt($username);

    public function changePassword($username, $newpassword);
}
