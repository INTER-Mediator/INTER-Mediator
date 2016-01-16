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
        foreach ($eachLine as $aLine) {
            $minusCount = 0;
            $paraCount = 0;
            for ($i = 0; $i < mb_strlen($aLine); $i++) {
                $c = substr($aLine, $i, 1);
                if ($c == "-") {
                    $minusCount++;
                } else if ($c == "#" || $c == "*") {
                    $paraCount++;
                } else {
                    break;
                }
            }
            if ($minusCount < $prevDepth) {
                $result[] = "</ul>";
            }
            if ($minusCount > 0) {
                if ($minusCount > $prevDepth) {
                    $result[] = "<ul class='_im_markdown_ul'>";
                }
                $result[] = "<li class='_im_markdown_li'>" . substr($aLine, $minusCount) . "</li>";
            } else if ($paraCount > 0) {
                $tag = "h{$paraCount}";
                $result[] = "<{$tag} class='_im_markdown_{$tag}'>" . substr($paraCount, $minusCount) . "</{$tag}>";
            } else if (substr($aLine, 0, 6) == "@@IMG[") {
                $endPos = mb_strpos($aLine, ']', 6);
                if ($endPos === FALSE)  {
                    $endPos = mb_strlen($aLine) - 6;
                }
                $uri = mb_substr($aLine, 6, $endPos);
                $result[] = "<p class='_im_markdown_para_img'><img src='{$uri}'</p>";
            } else {
                $result[] = "<p class='_im_markdown_para'>$aLine</p>";
            }
            $prevDepth = $minusCount;
        }
        $result[] = "</div>";
        return implode('', $result);
    }
}
