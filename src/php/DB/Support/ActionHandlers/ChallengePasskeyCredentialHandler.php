<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\DB\Logger;
use INTERMediator\IMUtil;


/**
 * Visitor class for handling challenge-based authentication operations in the Proxy pattern.
 * Implements methods for authentication, authorization, and challenge handling for challenge access.
 */
class ChallengePasskeyCredentialHandler extends ActionHandler
{
    use PasskeySupport;

    /** Visits the IsAuthAccessing operation.
     *
     * @return bool Always returns true for challenge access.
     */
    public function isAuthAccessing(): bool
    {
        return true;
    }

    /** Visits the CheckAuthentication operation for challenge access.
     *
     * @return bool Always returns false for challenge access (no authentication is performed).
     */
    public function checkAuthentication(): bool
    {
        $this->proxy->dbSettings->setRequireAuthorization(true);
        return false;
        // DO NOT CALL the prepareCheckAuthentication method for the challenge accessing.
    }

    /** Visits the CheckAuthorization operation for challenge access.
     *
     * @return bool Always returns true for challenge access.
     */
    public function checkAuthorization(): bool
    {
        return true;
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
        $this->proxy->generatedClientID = IMUtil::generateClientID();
        $this->proxy->outputOfProcessing['passkeyOption']
            = $this->passKeySeriarize($this->createPublicKeyCredentialRequestOptions("", $this->proxy->generatedClientID));
    }
}