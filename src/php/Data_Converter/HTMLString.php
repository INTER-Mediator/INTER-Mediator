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

namespace INTERMediator\Data_Converter;

/**
 *
 */
class HTMLString
{
    /**
     * @var bool
     */
    protected bool $autolink = false;
    /**
     * @var bool
     */
    protected bool $noescape = false;

    /**
     * @param bool $option
     */
    public function __construct(bool $option = false)
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

    /**
     * @param string $str
     * @return string
     */
    public function converterFromUserToDB(string $str): string
    {
        return $str;
    }

    /**
     * @param ?string $str
     * @return string
     */
    public function converterFromDBtoUser(?string $str): string
    {
        if (!$this->noescape) {
            $str = $this->replaceTags($str);
        }
        $str = $this->replaceCRLF($str);
        if ($this->autolink) {
            $str = $this->replaceLinkToATag($str);
        }
        return $str;
    }

    /**
     * @param string|null $str
     * @return string|null
     */
    protected function replaceTags(?string $str): ?string
    {
        if (is_null($str)) {
            return null;
        }
        return str_replace(">", "&gt;",
            str_replace("<", "&lt;",
                str_replace("'", "&#39;",
                    str_replace('"', "&quot;",
                        str_replace("&", "&amp;", $str)))));

    }

    /**
     * @param string|null $str
     * @return string|null
     */
    protected function replaceCRLF(?string $str): ?string
    {
        if (is_null($str)) {
            return null;
        }
        return str_replace("\n", "<br />",
            str_replace("\r", "<br />",
                str_replace("\r\n", "<br />", $str)));
    }

    /**
     * @param string|null $str
     * @return string|null
     */
    protected function replaceLinkToATag(?string $str): ?string
    {
        if (is_null($str)) {
            return null;
        }
        return mb_ereg_replace("(https?|ftp)(:\\/\\/[-_.!~*\\'()a-zA-Z0-9;\\/?:\\@&=+\\$,%#]+)",
            "<a href=\"\\0\" target=\"_blank\">\\0</a>", $str, "i");
    }
}
