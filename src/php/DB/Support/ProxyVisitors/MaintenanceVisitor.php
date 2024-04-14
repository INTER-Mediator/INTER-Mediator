<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Generator;
use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\DB\Logger;

class MaintenanceVisitor extends OperationVisitor
{
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
    {
        $e->resultOfCheckAuthentication
            = $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }


    public function visitDataOperation(DataOperationElement $e): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start maintenance processing", 2);
        if ($this->proxy->activateGenerator) { // Schema auto generating mode
            (new Generator($this->proxy))->generate();
        } else { // normal access
        }
    }


    public function visitHandleChallenge(HandleChallengeElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}