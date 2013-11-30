<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/20
 * Time: 13:11
 * To change this template use File | Settings | File Templates.
 */
class YearMonthGen implements Extending_Interface_AfterGet
{

    function doAfterGetFromDB($dataSourceName, $result)
    {
        $result = array();
        $year = 2010;
        for ($month = 1; $month < 13; $month++) {
            $startDate = new DateTime("{$year}-{$month}-1 00:00:00");
            $endDate = $startDate->modify("next month");
            $result[] = array(
                "year" => $year,
                "month" => $month,
                "startdt" => "{$year}-{$month}-1 00:00:00",
                "enddt" => $endDate->format("Y-m-d H:i:s"),
            );
        }
//        $this->resultCount = count($result);
        return $result;
    }

//    var $resultCount;
//
//    function countQueryResult($dataSourceName)
//    {
//        return $this->resultCount;
//    }
}
