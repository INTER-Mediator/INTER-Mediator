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
class Theme
{
    private $altThemePath;
    private $themeName;

    function processing()
    {
        $params = IMUtil::getFromParamsPHPFile(array("altThemePath", "themeName",), true);
        $this->altThemePath = $params["altThemePath"];
        $this->themeName = $params["themeName"];

        $tType = str_replace('..', '', $_GET['type']);
        if (strtolower($tType) == "css" && !isset($_GET['name'])) {
            $fpath = $this->pathToTheme($_GET['theme']) . "/{$tType}/";
            $cssFiles = glob("{$fpath}*.css");
            $fContent = '';
            foreach ($cssFiles as $aFile) {
                $fContent .= file_get_contents($aFile);
            }
            $fpath = "something.css";
        } else {
            $fName = str_replace('..', '', $_GET['name']);
            $fpath = $this->pathToTheme($_GET['theme']) . "/{$tType}/{$fName}";
            $fContent = file_get_contents($fpath);
        }
        header("Content-Type: " . IMUtil::getMIMEType($fpath));
        header("Content-Length: " . strlen($fContent));
        $util = new IMUtil();
        $util->outputSecurityHeaders();
        echo $fContent;
    }

    private function pathToTheme($themeName)
    {
        $themeName = str_replace('..', '', $themeName);
        $candidateDirs = is_null($this->altThemePath) ? array() : array($this->altThemePath) . "/{$themeName}";
        $candidateDirs[] = dirname(__FILE__) . "/themes/{$themeName}";
        foreach ($candidateDirs as $item) {
            if (file_exists($item)) {
                return $item;
            }
        }
        return null;
    }
}