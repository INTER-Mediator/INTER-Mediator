<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\IMUtil;
use INTERMediator\DB\Logger;

class ChallengeVisitor extends OperationVisitor
{
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
    {
        $this->proxy->dbSettings->setRequireAuthorization(true);
        // It just returns 'false' to the property $resultOfCheckAuthentication in CheckAuthenticationElement
        // DO NOT CALL the prepareCheckAuthentication method for the challenge accessing.
    }


    public function visitDataOperation(DataOperationElement $e): void
    {
    }


    public function visitHandleChallenge(HandleChallengeElement $e): void
    {
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[handleChallenge] access={$proxy->access}, succeed={$proxy->authSucceed}", 2);

        $proxy->generatedClientID = IMUtil::generateClientId('', $proxy->passwordHash);
        $userSalt = $proxy->authSupportGetSalt($proxy->paramAuthUser);

        $challenge = $this->generateAndSaveChallenge($proxy->paramAuthUser, $proxy->generatedClientID, "#");
        $proxy->outputOfProcessing['challenge'] = "{$challenge}{$userSalt}";
    }

}