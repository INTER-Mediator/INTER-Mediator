<?php

namespace INTERMediator\DB\Extending;

interface AfterCreate
{
    public function doAfterCreateToDB($result);

}