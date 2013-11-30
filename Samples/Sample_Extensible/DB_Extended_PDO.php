<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/18
 * Time: 14:36
 * To change this template use File | Settings | File Templates.
 */

require_once('../../INTER-Mediator.php');

class DB_Extended_PDO extends DB_PDO
{
    function getFromDB($dataSourceName)
    {
        $result = parent::getFromDB($dataSourceName);

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
        }
        else if ($dataSourceName == "summary1") {
            $sum = array();
            foreach ($result as $record) {
                $sum[$record["item"]] += $record["total"];
            }
            arsort($sum);
            $result = array();
            $counter = 10;
            foreach ( $sum as $product => $totalprice )  {
                $result[] = array("itemname"=>$product, "totalprice"=>$totalprice);
                $counter--;
                if ( $counter <= 0 )    {
                    break;
                }
            }
        }
        else if ($dataSourceName == "summary2") {
            $sum = array();
            foreach ($result as $record) {
                if (! isset($record["customer"]))   {
                    $sum[$record["customer"]] = $record["total"];
                } else {
                    $sum[$record["customer"]] += $record["total"];
                }
            }
            arsort($sum);
            $result = array();
            $counter = 10;
            foreach ( $sum as $customer => $totalprice )  {
                $result[] = array("customername"=>$customer, "totalprice"=>$totalprice);
                $counter--;
                if ( $counter <= 0 )    {
                    break;
                }
            }
        }
        return $result;
    }
}