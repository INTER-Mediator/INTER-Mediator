<?php

namespace INTERMediator\DB\Support;

use INTERMediator\DB\Proxy;

abstract class OperationLogExtension
{
    protected Proxy $proxy;
    protected ?array $result;

    public function __construct(Proxy $proxy, ?array $result = null)
    {
        $this->proxy = $proxy;
        $this->result = $result;
    }

    public abstract function extendingFields(): array;

    public abstract function valueForField(string $field): string;
}