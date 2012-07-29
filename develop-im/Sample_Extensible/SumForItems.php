<?php
/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 12/05/20
 * Time: 13:12
 * To change this template use File | Settings | File Templates.
 */
class SumForItems implements Extending_Interface_AfterGet
{

    function doAfterGetFromDB($dataSourceName, $result)
    {
        $sum = array();
        foreach ($result as $record) {
            $sum[$record["item"]] += $record["total"];
        }
        arsort($sum);
        $result = array();
        $counter = 10;
        foreach ( $sum as $product => $totalprice )  {
            $result[] = array(
                "itemname"=>$product,
                "totalprice"=>number_format($totalprice)
                );
            $counter--;
            if ( $counter <= 0 )    {
                break;
            }
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
