<?php
namespace Test;

use INTERMediator\DB\Extending\AfterRead;
use INTERMediator\DB\UseSharedObjects;

class MorePostalCode extends UseSharedObjects implements AfterRead
{
    public function doAfterReadFromDB($result)
    {
        return $result;
    }
}