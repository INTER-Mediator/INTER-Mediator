<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
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
