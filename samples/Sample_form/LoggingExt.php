<?php

class LoggingExt extends INTERMediator\DB\Support\OperationLogExtension
{
    public function extendingFields()
    {
        return ['field1'];
    }

    public function valueForField($field)
    {
        return "value of {$field}";
    }
}