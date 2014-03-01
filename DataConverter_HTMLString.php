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
    private $autolink = false;
    private $noescape = false;

    public function __construct($option = false)
    {
        if ($option) {
            if (in_array(strtolower($option), array('true', 'autolink')) || $option === true) {
                $this->autolink = true;
            }
            if (strtolower($option) === 'noescape') {
                $this->noescape = true;
            }
        }
    }

    public function converterFromUserToDB($str)
    {
        return $str;
    }

    public function converterFromDBtoUser($str)
    {
        if (!$this->noescape) {
            $str = str_replace(">", "&gt;",
                str_replace("<", "&lt;",
                    str_replace("'", "&#39;",
                        str_replace('"', "&quot;",
                            str_replace("&", "&amp;", $str)))));
        }
        $str = str_replace("\n", "<br />",
            str_replace("\r", "<br />",
                str_replace("\r\n", "<br />", $str)));
        if ($this->autolink) {
            $str = mb_ereg_replace("(https?|ftp)(:\\/\\/[-_.!~*\\'()a-zA-Z0-9;\\/?:\\@&=+\\$,%#]+)",
                "<a href=\"\\0\" target=\"_blank\">\\0</a>", $str, "i");
        }
        return $str;
    }
}
