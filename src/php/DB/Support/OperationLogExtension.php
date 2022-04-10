<?php

namespace INTERMediator\DB\Support;

abstract class OperationLogExtension
{
    private $proxy;

    public function __construct($proxy){
        $this->proxy = $proxy;
    }

    public abstract function extendingFields();

    public abstract function valueForField($field);
}