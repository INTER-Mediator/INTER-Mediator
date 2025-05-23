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

/**
 * Theme management class for INTER-Mediator.
 * Handles theme file resolution and processing for static resources.
 */
class Theme
{
    /**
     * Alternate theme path if specified in configuration.
     *
     * @var string|null
     */
    private ?string $altThemePath;
    /**
     * Access log level setting from configuration.
     *
     * @var bool
     */
    private bool $accessLogLevel;
    /**
     * Stores output messages for logging.
     *
     * @var array
     */
    private array $outputMessage = [];

    /**
     * Theme constructor.
     * Initializes configuration from params.php.
     */
    public function __construct()
    {
        // Read from params.php
        $this->accessLogLevel = Params::getParameterValue("accessLogLevel", false);
        $this->altThemePath = Params::getParameterValue("altThemePath", null);
    }

    /**
     * Gets the result message array for access logging.
     *
     * @return array Output message array for logging.
     */
    public function getResultForLog(): array
    {
        if ($this->accessLogLevel < 1) {
            return [];
        }
        return $this->outputMessage;
    }

    /**
     * Processes the theme resource request and outputs the appropriate file content with headers.
     * Handles CSS aggregation and image resource resolution.
     *
     * @return void
     */
    public function processing(?string $deffile): void
    {
        $docRootPath = $_SERVER['DOCUMENT_ROOT'];
        $deffilePath = null;
        if (!is_null($deffile) && str_starts_with($deffile, $docRootPath)) {
            $deffilePath = substr($deffile, strlen($docRootPath));
        }
        $themeNameInRequest = $_GET['theme'];
        $selfInRequest = $_SERVER["SCRIPT_NAME"];

        $tType = str_replace('..', '', $_GET['type'] ?? "");
        if (strtolower($tType) == "css" && !isset($_GET['name'])) {
            $fpath = $this->pathToTheme($_GET['theme']) . "/{$tType}/";
            $cssFiles = glob("{$fpath}*.css");
            $fContent = '';
            foreach ($cssFiles as $aFile) {
                $fContent .= file_get_contents($aFile);
            }
            $replacingURL = "{$selfInRequest}?theme={$themeNameInRequest}&type=images&name=$1";
            if (!is_null($deffilePath)) {
                $replacingURL .= "&deffile={$deffilePath}";
            }
            $fContent = preg_replace("/url\(([^)]+)\)/", "url({$replacingURL})", $fContent);
            $fpath = "something.css";
        } else {
            $fName = str_replace('..', '', $_GET['name'] ?? "");
            $fpath = $this->pathToTheme($_GET['theme']) . "/{$tType}/{$fName}";
            $fContent = file_get_contents($fpath);
        }
        header("Content-Type: " . IMUtil::getMIMEType($fpath));
        header("Content-Length: " . strlen($fContent));
        $util = new IMUtil();
        $util->outputSecurityHeaders();
        echo $fContent;
    }

    /**
     * Resolves the theme directory path for the given theme name.
     * Returns null if the theme directory does not exist.
     *
     * @param string|null $themeName Name of the theme.
     * @return string|null Path to the theme directory or null if not found.
     */
    private function pathToTheme(?string $themeName): ?string
    {
        $imPath = IMUtil::pathToINTERMediator();
        $themeName = str_replace('..', '', $themeName ?? "");
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