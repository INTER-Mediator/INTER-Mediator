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
     * @return void
     */
    public function visitCheckAuthentication(OperationElement $e): void
    {
        $this->proxy->dbSettings->setRequireAuthorization(true);
        // It just returns 'false' to the property $resultOfCheckAuthentication in CheckAuthenticationElement
        // DO NOT CALL the prepareCheckAuthentication method for the challenge accessing.
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

        $challenge = $this->generateAndSaveChallenge($proxy->paramAuthUser, $proxy->generatedClientID, "#");
        $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
    }

}