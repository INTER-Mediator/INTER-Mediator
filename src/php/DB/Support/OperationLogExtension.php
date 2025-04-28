<?php

namespace INTERMediator\DB\Support;

use INTERMediator\DB\Proxy;

/**
 * Abstract base class for extending operation log functionality.
 * Provides methods to define additional fields and their values for operation logs.
 */
abstract class OperationLogExtension
{
    /**
     * Reference to the Proxy object for database operations.
     *
     * @var Proxy
     */
    protected Proxy $proxy;

    /**
     * Result array containing operation results or null if not set.
     *
     * @var array|null
     */
    protected ?array $result;

    /**
     * Constructor for OperationLogExtension.
     *
     * @param Proxy $proxy Proxy object for database operations.
     * @param array|null $result Result array for operation results (optional).
     */
    public function __construct(Proxy $proxy, ?array $result = null)
    {
        $this->proxy = $proxy;
        $this->result = $result;
    }

    /**
     * Returns an array of additional field names to be included in the operation log.
     *
     * @return array List of field names to be added to the log.
     */
    public abstract function extendingFields(): array;

    /**
     * Returns the value for a given field name to be included in the operation log.
     *
     * @param string $field Name of the field for which to retrieve the value.
     * @return string Value to be logged for the specified field.
     */
    public abstract function valueForField(string $field): string;
}