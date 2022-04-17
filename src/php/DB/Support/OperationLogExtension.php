<?php

namespace INTERMediator\DB\Support;

abstract class OperationLogExtension
{
    private $proxy;
    private $result;

    public function __construct($proxy, $result = null)
    {
        $this->proxy = $proxy;
        $this->result = $result;
    }

    public abstract function extendingFields();

    public abstract function valueForField($field);
}