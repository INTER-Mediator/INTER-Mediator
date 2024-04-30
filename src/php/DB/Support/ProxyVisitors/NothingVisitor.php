<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\OperationElement;

/**
 *
 */
class NothingVisitor extends OperationVisitor
{
    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitCheckAuthentication(OperationElement $e): void
    {
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
    }

}