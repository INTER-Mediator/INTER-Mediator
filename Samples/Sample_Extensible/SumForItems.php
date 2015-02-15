<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 * 
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 * 
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 */
class SumForItems implements Extending_Interface_AfterGet
{

    function doAfterGetFromDB($dataSourceName, $result)
    {
        $sum = array();
        foreach ($result as $record) {
            if(! isset($sum[$record["item"]]))  {
                $sum[$record["item"]] = $record["total"];
            } else {
                $sum[$record["item"]] += $record["total"];
            }
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
