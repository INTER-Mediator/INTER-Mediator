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
class DataConverter_MarkdownString extends DataConverter_HTMLString
{

    public function converterFromDBtoUser($str)
    {
        $str = $this->replaceTags($str);
        $str = $this->taggingAsMarkdown($str);
        $str = $this->replaceLinkToATag($str);
        return $str;
    }

    public function taggingAsMarkdown($str)
    {
        $result = ["<div class='_im_markdown'>"];
        $unifyCRLF = str_replace("\r", "\n", str_replace("\r\n", "\n", $str));
        $eachLine = explode("\n", $unifyCRLF);
        $prevDepth = -1;
        $inTable = false;
        foreach ($eachLine as $aLine) {
            $minusCount = 0;
            $paraCount = 0;
            for ($i = 0; $i < mb_strlen($aLine); $i++) {
                $c = substr($aLine, $i, 1);
                if ($c == "-") {
                    $minusCount++;
                } else if ($c == "*") {
                    $paraCount++;
                } else {
                    break;
                }
            }
            if ($prevDepth > 0) {
                if ($minusCount == $prevDepth) {
                    $result[] = "</li>";
                } else if ($minusCount < $prevDepth) {
                    for ($i = 0; $i < ($prevDepth - $minusCount); $i++) {
                        $result[] = "</li></ul></li>";
                    }
                }
            }
            if ($inTable && substr($aLine, 0, 1) != "|") {
                $result[] = "</table>";
                $inTable = false;
            }
            if ($minusCount > 0) {
                if ($minusCount > $prevDepth) {
                    $result[] = "<ul class='_im_markdown_ul'>";
                }
                $result[] = "<li class='_im_markdown_li'>" . substr($aLine, $minusCount);
            } else if ($paraCount > 0) {
                $tag = "h{$paraCount}";
                $result[] = "<{$tag} class='_im_markdown_{$tag}'>" . substr($aLine, $paraCount) . "</{$tag}>";
            } else if (substr($aLine, 0, 3) == "###") {
                $result[] = "<p class='_im_markdown_p3'>". substr($aLine, 3) . "</p>";
            } else if (substr($aLine, 0, 2) == "##") {
                $result[] = "<p class='_im_markdown_p2'>". substr($aLine, 2) . "</p>";
            } else if (substr($aLine, 0, 1) == "#") {
                $result[] = "<p class='_im_markdown_p1'>". substr($aLine, 1) . "</p>";
            } else if (substr($aLine, 0, 6) == "@@IMG[") {
                $endPos = mb_strpos($aLine, ']', 6);
                if ($endPos === FALSE) {
                    $endPos = mb_strlen($aLine) - 6;
                } else {
                    $endPos = $endPos - 6;
                }
                $uri = mb_substr($aLine, 6, $endPos);
                $result[] = "<p class='_im_markdown_para_img'><img src='{$uri}'</p>";
            } else if (substr($aLine, 0, 1) == "|") {
                $sLen = mb_strlen($aLine) - ((substr($aLine, -1, 1) == "|") ? 2 : 1);
                if (!$inTable) {
                    $result[] = "<table class='_im_markdown_table'>";
                }
                $result[] = "<tr class='_im_markdown_tr'>";
                foreach (explode("|", mb_substr($aLine, 1, $sLen)) as $aCell) {
                    $result[] = "<td class='_im_markdown_td'>" . $aCell . "</td>";
                }
                $result[] = "</tr>";
                $inTable = true;
            } else {
                $result[] = "<p class='_im_markdown_para'>$aLine</p>";
            }
            $prevDepth = $minusCount;
        }
        if ($prevDepth > 0) {
            for ($i = 0; $i < $prevDepth; $i++) {
                $result[] = "</li></ul>";
            }
        }
        if ($inTable) {
            $result[] = "</table>";
            $inTable = false;
        }
        $result[] = "</div>";
        return implode('', $result);
    }
}
