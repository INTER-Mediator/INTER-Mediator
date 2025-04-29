<?php

namespace INTERMediator\DB\Support;

/**
 * Exception class to signal that an operation should be stopped immediately.
 * Used to interrupt or abort ongoing processes in the database support layer.
 */
class StopOperationException extends \Exception
{
    // No additional properties or methods; serves as a specific exception type.
}