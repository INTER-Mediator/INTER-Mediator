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
    var $linking;

    function __construct($uselink = false)
    {
        $this->linking = $uselink;
    }

    function converterFromUserToDB($str)
    {
        return $str;
    }

    function converterFromDBtoUser($str)
    {
        $str = str_replace("\n", "<br/>",
            str_replace("\r", "<br/>",
                str_replace("\r\n", "<br/>",
                    str_replace(">", "&gt;",
                        str_replace("<", "&lt;",
                            str_replace("&", "&amp;", $str))))));
        if ($this->linking) {
            $str = mb_ereg_replace ( "(https?|ftp)(:\\/\\/[-_.!~*\\'()a-zA-Z0-9;\\/?:\\@&=+\\$,%#]+)" ,
                "<a href=\"\\0\" target='_blank'>\\0</a>" , $str ,"i");
        }
        return $str;
    }
}

?>
