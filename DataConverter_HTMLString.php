<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2009.
 *   INTER-Mediator is supplied under MIT License.
 */

class DataConverter_HTMLString
{
    private $linking;
    private $escape;

    public function __construct($uselink = false, $escape = true)
    {
        $this->linking = $uselink;
        $this->escape = $escape;
    }

    public function converterFromUserToDB($str)
    {
        return $str;
    }

    public function converterFromDBtoUser($str)
    {
        if ($this->escape) {
            $str = str_replace(">", "&gt;",
                str_replace("<", "&lt;",
                    str_replace("'", "&#39;",
                        str_replace('"', "&quot;",
                            str_replace("&", "&amp;", $str)))));
        }
        $str = str_replace("\n", "<br />",
            str_replace("\r", "<br />",
                str_replace("\r\n", "<br />", $str)));
        if ($this->linking) {
            $str = mb_ereg_replace("(https?|ftp)(:\\/\\/[-_.!~*\\'()a-zA-Z0-9;\\/?:\\@&=+\\$,%#]+)",
                "<a href=\"\\0\" target=\"_blank\">\\0</a>", $str, "i");
        }
        return $str;
    }
}
