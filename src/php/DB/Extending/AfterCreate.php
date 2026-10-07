<?php

namespace INTERMediator\DB\Extending;

/**
 * Interface AfterCreate
 * @package INTERMediator\DB\Extending
 */
interface AfterCreate
{
    /**
     * Do after create to DB.
     * @param array<array<string, number|string|bool|null>> $result The result of create operation.
     * @return array<array<string, number|string|bool|null>>|null The result of after create operation.
     */
    public function doAfterCreateToDB($result): ?array;

}