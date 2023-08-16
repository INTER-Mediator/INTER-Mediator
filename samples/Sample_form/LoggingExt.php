<?php

use INTERMediator\DB\Support\OperationLogExtension;

class LoggingExt extends OperationLogExtension
{
    public function extendingFields(): array
    {
        return ['field1'];
    }

    public function valueForField($field): string
    {
        return "value of {$field}";
    }
}