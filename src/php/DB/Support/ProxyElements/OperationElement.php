<?php

namespace INTERMediator\DB\Support\ProxyElements;

use INTERMediator\DB\Support\ProxyVisitors\OperationVisitor;

/**
 * Abstract base class for operation elements in the Proxy authentication/authorization system.
 * Provides acceptor methods for visitor pattern operations such as authentication and data handling.
 */
abstract class OperationElement
{
    /**
     * Result of the authentication check for this operation element.
     *
     * @var bool
     */
    public bool $resultOfCheckAuthentication = false;

    /**
     * Accepts a visitor for the IsAuthAccessing operation.
     *
     * @param OperationVisitor $v The visitor instance.
     * @return bool Result of the visitor's visitIsAuthAccessing method.
     */
    public function acceptIsAuthAccessing(OperationVisitor $v): bool
    {
        return $v->visitIsAuthAccessing($this);
    }

    /**
     * Accepts a visitor for the CheckAuthentication operation.
     *
     * @param OperationVisitor $v The visitor instance.
     * @return bool Result of the visitor's visitCheckAuthentication method.
     */
    public function acceptCheckAuthentication(OperationVisitor $v): bool
    {
        return $v->visitCheckAuthentication($this);
    }

    /**
     * Accepts a visitor for the CheckAuthorization operation.
     *
     * @param OperationVisitor $v The visitor instance.
     * @return bool Result of the visitor's visitCheckAuthorization method.
     */
    public function acceptCheckAuthorization(OperationVisitor $v): bool
    {
        return $v->visitCheckAuthorization($this);
    }

    /**
     * Accepts a visitor for the DataOperation operation.
     *
     * @param OperationVisitor $v The visitor instance.
     * @return void
     */
    public function acceptDataOperation(OperationVisitor $v): void
    {
        $v->visitDataOperation($this);
    }

    /**
     * Accepts a visitor for the HandleChallenge operation.
     *
     * @param OperationVisitor $v The visitor instance.
     * @return void
     */
    public function acceptHandleChallenge(OperationVisitor $v): void
    {
        $v->visitHandleChallenge($this);
    }

}