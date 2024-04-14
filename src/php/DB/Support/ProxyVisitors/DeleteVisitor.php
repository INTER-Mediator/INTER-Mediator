<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\DB\Logger;

class DeleteVisitor extends OperationVisitor
{
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
    {
        $e->resultOfCheckAuthentication
            = $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }


    public function visitDataOperation(DataOperationElement $e): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start delete processing", 2);
        $this->proxy->deleteFromDB();
    }


    public function visitHandleChallenge(HandleChallengeElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}