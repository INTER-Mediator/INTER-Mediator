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

namespace INTERMediator;

class Theme
{
    private $altThemePath;
    private $themeName;
    private $accessLogLevel;
    private $outputMessage = [];

    public function __construct()
    {
        // Read from params.php
        $paramKeys = ["accessLogLevel", "altThemePath", "themeName"];
        $params = IMUtil::getFromParamsPHPFile($paramKeys, true);
        $this->accessLogLevel = intval($params['accessLogLevel']);    // false: No logging, 1: without data, 2: with data
        $this->altThemePath = $params["altThemePath"];
        $this->themeName = $params["themeName"];
    }

    public function getResultForLog(){
        if($this->accessLogLevel < 1) {
            return [];
        }
        return $this->outputMessage;
    }

    public function processing()
    {
        $themeNameInRequest = $_GET['theme'];
        $selfInRequest = $_SERVER["SCRIPT_NAME"];

        $tType = str_replace('..', '', $_GET['type']);
        if (strtolower($tType) == "css" && !isset($_GET['name'])) {
            $fpath = $this->pathToTheme($_GET['theme']) . "/{$tType}/";
            $cssFiles = glob("{$fpath}*.css");
            $fContent = '';
            foreach ($cssFiles as $aFile) {
                $fContent .= file_get_contents($aFile);
            }
            $fContent = preg_replace("/url\(([^)]+)\)/",
                "url({$selfInRequest}?theme={$themeNameInRequest}" . '&type=images&name=$1)', $fContent);
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
        $imPath = IMUtil::pathToINTERMediator();
        $themeName = str_replace('..', '', $themeName);
        $candidateDirs = is_null($this->altThemePath) ? array() : array($this->altThemePath . "/{$themeName}");
        $candidateDirs[] = $imPath . "/themes/{$themeName}";
        if (is_null($this->altThemePath)) {
            $candidateDirs[] = $imPath . "/themes/{$themeName}";
        }
        foreach ($candidateDirs as $item) {
            if (file_exists($item)) {
                return $item;
            }
        }
        $this->outputMessage['pathToTheme'] = "The theme file for '{$themeName}' doesn't exist.";
        return null;
    }
}