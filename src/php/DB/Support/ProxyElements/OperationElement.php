<?php

namespace INTERMediator\DB\Support\ProxyElements;

use INTERMediator\DB\Support\ProxyVisitors\OperationVisitor;

abstract class OperationElement
{
    public function acceptCheckAuthentication(OperationVisitor $v): void
    {
        $v->visitCheckAuthentication($this);
    }

    public function acceptDataOperation(OperationVisitor $v): void
    {
        $v->visitDataOperation($this);
    }

    public function acceptHandleChallenge(OperationVisitor $v): void
    {
        $v->visitHandleChallenge($this);
    }

}