<?php

namespace INTERMediator\DB;

use Exception;

/**
 *
 */
abstract class DBClass extends UseSharedObjects implements DBClass_Interface
{
    /**
     * @param array $condition
     * @return mixed
     * @throws Exception
     */
    public function normalizedCondition(array $condition)
    {
        throw new Exception("Don't use normalizedCondition method on DBClass instance without FileMaker ones.");
    }

}