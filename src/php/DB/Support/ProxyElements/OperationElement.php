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
     * @return void
     */
    public function acceptCheckAuthentication(OperationVisitor $v): void
    {
        $v->visitCheckAuthentication($this);
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