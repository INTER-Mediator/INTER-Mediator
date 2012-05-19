<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/18
 * Time: 14:36
 * To change this template use File | Settings | File Templates.
 */

require_once ('../INTER-Mediator/DB_PDO.php');

class DB_Extended_PDO extends DB_PDO
{
    var $queryCount;

    function getFromDB($dataSourceName)
    {
        $result = parent::getFromDB($dataSourceName);
        $this->queryCount = parent::countQueryResult($dataSourceName);

        if ($dataSourceName == "everymonth") {
            $result = array();
            $year = 2010;
            for ($month = 1; $month < 13; $month++) {
                $startDate = new DateTime("{$year}-{$month}-1 0:0:0");
                $endDate = $startDate->modify("next month");
                $result[] = array(
                    "year" => $year,
                    "month" => $month,
                    "startdt" => "{$year}-{$month}-1 0:0:0",
                    "enddt" => $endDate->format("Y-m-d H:i:s"),
                );
            }
            $this->queryCount = count($result);
        }

        return $result;
    }

    function countQueryResult($dataSourceName)
    {
        return $this->queryCount;
    }
}