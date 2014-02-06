<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2014/01/26
 * Time: 1:06
 */
class SumForCustomers implements Extending_Interface_AfterNew
{
    function doAfterNewToDB($dataSourceName, $result)
    {
        return $result;
    }
}