<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;

/**
 *
 */
class ReplaceVisitor extends OperationVisitor
{
    /**
     * @param CheckAuthenticationElement $e
     * @return void
     */
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
    {
        $e->resultOfCheckAuthentication
            = $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }


    /**
     * @param DataOperationElement $e
     * @return void
     */
    public function visitDataOperation(DataOperationElement $e): void
    {
        $this->CreateReplaceImpl("create");
    }


    /**
     * @param HandleChallengeElement $e
     * @return void
     */
    public function visitHandleChallenge(HandleChallengeElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}