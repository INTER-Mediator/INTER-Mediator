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

class YearMonthGen implements Extending_Interface_AfterRead
{
    public function doAfterReadFromDB($result)
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
        return $result;
    }
}
