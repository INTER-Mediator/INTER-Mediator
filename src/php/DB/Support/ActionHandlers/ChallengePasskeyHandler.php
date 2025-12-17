<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\IMUtil;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling challenge-based authentication operations in the Proxy pattern.
 * Implements methods for authentication, authorization, and challenge handling for challenge access.
 */
class ChallengePasskeyHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     *
     * @return bool Always returns true for challenge access.
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation for challenge access.
     *
     * @return bool Always returns false for challenge access (no authentication is performed).
     */
    public function checkAuthentication(): bool
    {
        if ($this->proxy->bypassAuth) {
            return true;
        }
        return $this->prepareCheckAuthentication() && $this->checkAuthenticationCommon();

        // DO NOT CALL the prepareCheckAuthentication method for the challenge accessing.
    }

    /** Visits the CheckAuthorization operation for challenge access.
     *
     * @return bool Always returns true for challenge access.
     */
    public function checkAuthorization(): bool
    {
        $proxy = $this->proxy;
        if ($proxy->bypassAuth) {
            return true;
        }
        return $proxy->authSucceed && $this->checkAuthorizationImpl();
    }

    /** The DataOperation action. No operation to the challenge action.
     *
     * @return void
     */
    public function dataOperation(): void
    {
        $uid = $this->proxy->dbSettings->getCurrentUser();
        [$userId, $realName] = $this->proxy->dbClass->authHandler->getLoginUserInfo($uid);
        $this->proxy->outputOfProcessing['passkeyUserId'] = $userId;
        $this->proxy->outputOfProcessing['passkeyUserName'] = $uid;
        $this->proxy->outputOfProcessing['passkeyUserRealname'] = $realName;
    }

    /** Visits the HandleChallenge operation to process challenge/response for challenge access.
     *
     * @return void
     */
    public function handleChallenge(): void
    {
        if ($this->proxy->bypassAuth) {
            return;
        }
        $this->defaultHandleChallenge();
        $challengePasskey = $this->generateAndSaveChallenge($this->proxy->paramAuthUser ?? "", IMUtil::generateChallenge(), "$");
        $this->proxy->outputOfProcessing['passkeyChallenge'] = $challengePasskey;
    }

}