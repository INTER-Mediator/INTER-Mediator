<?php

namespace INTERMediator\DB\Support\ProxyElements;

use INTERMediator\DB\Support\ProxyVisitors\OperationVisitor;

/**
 *
 */
abstract class OperationElement
{
    public bool $resultOfCheckAuthentication = false;

    /**
     * @param OperationVisitor $v
     * @return bool
     */
    public function acceptIsAuthAccessing(OperationVisitor $v): bool
    {
        return $v->visitIsAuthAccessing($this);
    }

    /**
     * @param OperationVisitor $v
     * @return bool
     */
    public function acceptCheckAuthentication(OperationVisitor $v): bool
    {
        return $v->visitCheckAuthentication($this);
    }

    /**
     * @param OperationVisitor $v
     * @return bool
     */
    public function acceptCheckAuthorization(OperationVisitor $v): bool
    {
        return $v->visitCheckAuthorization($this);
    }

    /**
     * @param OperationVisitor $v
     * @return void
     */
    public function acceptDataOperation(OperationVisitor $v): void
    {
        $v->visitDataOperation($this);
    }

    /**
     * @param OperationVisitor $v
     * @return void
     */
    public function acceptHandleChallenge(OperationVisitor $v): void
    {
        $v->visitHandleChallenge($this);
    }

}