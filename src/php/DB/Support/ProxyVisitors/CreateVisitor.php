<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use Exception;
use INTERMediator\DB\Support\ProxyElements\OperationElement;

/**
 *
 */
class CreateVisitor extends OperationVisitor
{
    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        if ($this->proxy->bypassAuth) {
            return true;
        }
        return $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }

    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        if ($proxy->bypassAuth) {
            return true;
        }
        return $proxy->authSucceed && $this->checkAuthorization();
    }

    /**
     * @param OperationElement $e
     * @return void
     * @throws Exception
     */
    public function visitDataOperation(OperationElement $e): void
    {
        $this->CreateReplaceImpl("create");
    }


    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        if ($this->proxy->bypassAuth) {
            return;
        }
        $this->defaultHandleChallenge();
    }

}