<?php

namespace INTERMediator\DB\Support\ActionHandlers;

use INTERMediator\IMUtil;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling challenge-based authentication operations in the Proxy pattern.
 * Implements methods for authentication, authorization, and challenge handling for challenge access.
 */
class ChallengeHandler extends ActionHandler
{
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
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[handleChallenge] access={$proxy->access}, succeed={$proxy->authSucceed}", 2);

        $proxy->generatedClientID = IMUtil::generateClientId('', $proxy->passwordHash);
        $userSalt = $proxy->authSupportGetSalt($proxy->paramAuthUser);

        $challenge = $this->generateAndSaveChallenge($proxy->paramAuthUser ?? "", $proxy->generatedClientID, "#");
        $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
    }

}