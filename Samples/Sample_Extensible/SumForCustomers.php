<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 * 
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */
class SumForCustomers implements Extending_Interface_AfterGet
{

    function doAfterGetFromDB($dataSourceName, $result)
    {
        $sum = array();
        foreach ($result as $record) {
            if(! isset($sum[$record["customer"]]))  {
                $sum[$record["customer"]] = $record["total"];
            } else {
                $sum[$record["customer"]] += $record["total"];
            }
        }
        arsort($sum);
        $result = array();
        $counter = 10;
        foreach ( $sum as $customer => $totalprice )  {
            $result[] = array(
                "customername"=>$customer,
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
