<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\IMUtil;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling challenge-based authentication operations in the Proxy pattern.
 * Implements methods for authentication, authorization, and challenge handling for challenge access.
 */
class ChallengeVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns true for challenge access.
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return true;
    }

    /**
     * Visits the CheckAuthentication operation for challenge access.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns false for challenge access (no authentication is performed).
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        $this->proxy->dbSettings->setRequireAuthorization(true);
        return false;
        // DO NOT CALL the prepareCheckAuthentication method for the challenge accessing.
    }

    /**
     * Visits the CheckAuthorization operation for challenge access.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns true for challenge access.
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        return true;
    }

    /**
     * Visits the DataOperation operation. No operation for challenge visitor.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitDataOperation(OperationElement $e): void
    {
    }

    /**
     * Visits the HandleChallenge operation to process challenge/response for challenge access.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[handleChallenge] access={$proxy->access}, succeed={$proxy->authSucceed}", 2);

        $proxy->generatedClientID = IMUtil::generateClientId('', $proxy->passwordHash);
        $userSalt = $proxy->authSupportGetSalt($proxy->paramAuthUser);

        $challenge = $this->generateAndSaveChallenge($proxy->paramAuthUser ?? "", $proxy->generatedClientID, "#");
        $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
    }

}