<?php
/*
 * INTER-Mediator Ver.0.7.6 Released 2011-09-18
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

class DataConverter_HTMLString
{

    function converterFromUserToDB($str)
    {
        return $str;
    }

    function converterFromDBtoUser($str)
    {
        return str_replace("\n", "<br/>",
            str_replace("\r", "<br/>",
                str_replace("\r\n", "<br/>",
                    str_replace(">", "&gt;",
                        str_replace("<", "&lt;",
                            str_replace("&", "&amp;", $str))))));
    }
}

?>
