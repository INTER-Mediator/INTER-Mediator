<?php

namespace INTERMediator\DB\Support;

use INTERMediator\DB\Proxy;

/**
 *
 */
abstract class OperationLogExtension
{
    /**
     * @var Proxy
     */
    protected Proxy $proxy;
    /**
     * @var array|null
     */
    protected ?array $result;

    /**
     * @param Proxy $proxy
     * @param array|null $result
     */
    public function __construct(Proxy $proxy, ?array $result = null)
    {
        $this->proxy = $proxy;
        $this->result = $result;
    }

    /**
     * @return array
     */
    public abstract function extendingFields(): array;

    /**
     * @param string $field
     * @return string
     */
    public abstract function valueForField(string $field): string;
}