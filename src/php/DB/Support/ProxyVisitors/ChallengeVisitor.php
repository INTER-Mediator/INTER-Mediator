<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\IMUtil;
use INTERMediator\DB\Logger;

/**
 *
 */
class ChallengeVisitor extends OperationVisitor
{
    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return true;
    }

    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        $this->proxy->dbSettings->setRequireAuthorization(true);
        return false;
        // DO NOT CALL the prepareCheckAuthentication method for the challenge accessing.
    }

    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        return true;
    }

    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitDataOperation(OperationElement $e): void
    {
    }


    /**
     * @param OperationElement $e
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