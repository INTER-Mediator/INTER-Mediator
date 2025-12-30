<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Logger;


/**
 * Visitor class for handling challenge-based authentication operations in the Proxy pattern.
 * Implements methods for authentication, authorization, and challenge handling for challenge access.
 */
class ChallengePasskeyRegistrationHandler extends ActionHandler
{
    use PasskeySupport;

    /** Visits the IsAuthAccessing operation.
     *
     * @return bool Always returns true for challenge access.
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation for copy operations.
     *
     * @return bool True, if authentication succeeds or bypassAuth is enabled, false otherwise.
     */
    public function checkAuthentication(): bool
    {
        if ($this->proxy->bypassAuth) {
            return true;
        }
        return $this->prepareCheckAuthentication() && $this->checkAuthenticationCommon();
    }

    /** Visits the CheckAuthorization operation for copy operations.
     *
     * @return bool True, if authorization succeeds or bypassAuth is enabled, false otherwise.
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
    }

    /** Visits the HandleChallenge operation to process challenge/response for challenge access.
     *
     * @return void
     */
    public function handleChallenge(): void
    {
        Logger::getInstance()->setDebugMessage("[ChallengePasskeyRegistrationHandler] handleChallenge()", 2);
        if ($this->proxy->bypassAuth) {
            return;
        }
        $this->defaultHandleChallenge();
        $this->proxy->outputOfProcessing['passkeyOption']
            = $this->passKeySeriarize($this->publicKeyCredentialCreationOptions($this->proxy->paramAuthUser));
    }
}