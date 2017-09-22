<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

require_once(dirname(__FILE__) . '/../../INTER-Mediator.php');

class DB_Extended_PDO extends DB_PDO
{
    function readFromDB()
    {
        $result = parent::readFromDB();
        $dataSourceName = $this->dbSettings->getDataSourceName();
        if ($dataSourceName === 'everymonth') {
            $result = array();
            $year = 2010;
            for ($month = 1; $month < 13; $month++) {
                $startDate = new DateTime("{$year}-{$month}-1 0:0:0");
                $endDate = $startDate->modify('next month');
                $result[] = array(
                    'year' => $year,
                    'month' => $month,
                    'startdt' => "{$year}-{$month}-1 0:0:0",
                    'enddt' => $endDate->format('Y-m-d H:i:s'),
                );
            }
        }
        else if ($dataSourceName === 'summary1') {
            $sum = array();
            foreach ($result as $record) {
                if (!isset($sum[$record['item']])) {
                    $sum = array_merge($sum, array($record['item'] => $record['total']));
                } else {
                    $sum[$record['item']] += $record['total'];
                }
            }
            arsort($sum);
            $result = array();
            $counter = 10;
            foreach ( $sum as $product => $totalprice ) {
                $result[] = array('itemname'=>$product, 'totalprice'=>$totalprice);
                $counter--;
                if ( $counter <= 0 ) {
                    break;
                }
            }
        }
        else if ($dataSourceName === 'summary2') {
            $sum = array();
            foreach ($result as $record) {
                if (!isset($sum[$record['customer']])) {
                    $sum = array_merge($sum, array($record['customer'] => $record['total']));
                } else {
                    $sum[$record['customer']] += $record['total'];
                }
            }
            arsort($sum);
            $result = array();
            $counter = 10;
            foreach ( $sum as $customer => $totalprice ) {
                $result[] = array('customername'=>$customer, 'totalprice'=>$totalprice);
                $counter--;
                if ( $counter <= 0 ) {
                    break;
                }
            }
        }
        return $result;
    }
}
