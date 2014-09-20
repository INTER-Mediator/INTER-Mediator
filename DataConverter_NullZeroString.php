<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

class DataConverter_NullZeroString
{
    public function __construct()
    {
    }

    public function converterFromUserToDB($str)
    {
        return ($str == '') ? null : $str;
    }

    public function converterFromDBtoUser($str)
    {
        return is_null($str) ? '' : $str;
    }
}
