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
    /** Alternate theme path if specified in the configuration.
     * @var string|null
     */
    private ?string $altThemePath;
    /** Access log level setting from configuration.
     * @var int
     */
    private int $accessLogLevel;
    /** Stores output messages for logging.
     * @var array<string, string>
     */
    private array $outputMessage = [];

    /** Theme constructor.
     * Initializes configuration from params.php.
     */
    public function __construct()
    {
        // Read from params.php
        $this->accessLogLevel = intval(Params::getParameterValue("accessLogLevel", 0));
        $this->altThemePath = Params::getParameterValue("altThemePath", null);
    }

    /** Gets the result message array for access logging.
     * @return array<string, string> Output message array for logging.
     */
    public function getResultForLog(): array
    {
        if ($this->accessLogLevel < 1) {
            return [];
        }
        return $this->outputMessage;
    }

    /** Processes the theme resource request and outputs the appropriate file content with headers.
     * Handles CSS aggregation and image resource resolution.
     * @param string|null $deffile
     * @return void
     */
    public function processing(?string $deffile): void
    {
        $docRootPath = strval($_SERVER['DOCUMENT_ROOT'] ?? '');
        $deffilePath = null;
        if (!is_null($deffile) && $docRootPath !== '' && str_starts_with($deffile, $docRootPath)) {
            $deffilePath = substr($deffile, strlen($docRootPath));
        }
        $themeName = strval($_GET['theme'] ?? '');
        $tType = strval($_GET['type'] ?? '');

        $themeDir = $this->pathToTheme($themeName);
        if (is_null($themeDir)) {
            $this->outputNotFound();
            return;
        }
        if (strtolower($tType) === "css" && !isset($_GET['name'])) {
            $cssDir = $this->pathInTheme($themeDir, $tType);
            $cssFiles = is_null($cssDir) ? false : glob($cssDir . DIRECTORY_SEPARATOR . "*.css");
            $fContent = '';
            if ($cssFiles) {
                foreach ($cssFiles as $aFile) {
                    $fContent .= strval(file_get_contents($aFile));
                }
            }
            $fContent = $this->replaceURLsInCSS($fContent, strval($_SERVER["SCRIPT_NAME"] ?? ''), $themeName, $deffilePath);
            $mimeType = "text/css";
        } else {
            $fpath = $this->pathInTheme($themeDir, $tType . DIRECTORY_SEPARATOR . strval($_GET['name'] ?? ''));
            $fContent = (is_null($fpath) || !is_file($fpath)) ? false : file_get_contents($fpath);
            if (is_null($fpath) || $fContent === false) {
                $this->outputNotFound();
                return;
            }
            $mimeType = IMUtil::getMIMEType($fpath);
        }
        header("Content-Type: " . $mimeType);
        header("Content-Length: " . strlen($fContent));
        $util = new IMUtil();
        $util->outputSecurityHeaders();
        echo $fContent;
    }

    /** Replaces the relative URLs in the CSS with the URLs to request the theme resources.
     * The absolute URLs, the root-relative URLs, the data URIs and the fragments are kept.
     * @param string $content The CSS content.
     * @param string $self The path of the definition file in the request.
     * @param string $themeName The theme name.
     * @param string|null $deffilePath The path of the definition file from the document root.
     * @return string The replaced CSS content.
     */
    private function replaceURLsInCSS(string $content, string $self, string $themeName, ?string $deffilePath): string
    {
        $result = preg_replace_callback('/url\(\s*([\'"]?)([^\'")]*)\1\s*\)/i',
            function (array $matches) use ($self, $themeName, $deffilePath): string {
                $url = trim($matches[2]);
                if ($url === '' || preg_match('/^([a-z][a-z0-9+.\-]*:|\/|#)/i', $url)) {
                    return $matches[0];
                }
                $replacingURL = "{$self}?theme=" . rawurlencode($themeName)
                    . "&type=images&name=" . rawurlencode($url);
                if (!is_null($deffilePath)) {
                    $replacingURL .= "&deffile=" . rawurlencode($deffilePath);
                }
                return "url(\"{$replacingURL}\")";
            }, $content);
        return $result ?? $content;
    }

    /** Resolves the path in the theme directory. The path outside the theme directory is rejected.
     * @param string $themeDir The theme directory.
     * @param string $relativePath The relative path in the theme directory.
     * @return string|null The resolved real path, or null if it doesn't exist or is outside of the theme directory.
     */
    private function pathInTheme(string $themeDir, string $relativePath): ?string
    {
        $realThemeDir = realpath($themeDir);
        $realPath = realpath($themeDir . DIRECTORY_SEPARATOR . $relativePath);
        if ($realThemeDir === false || $realPath === false
            || !str_starts_with($realPath, rtrim($realThemeDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
            return null;
        }
        return $realPath;
    }

    /** Outputs the 404 Not Found response.
     * @return void
     */
    private function outputNotFound(): void
    {
        http_response_code(404);
        header("Content-Type: text/plain");
        $util = new IMUtil();
        $util->outputSecurityHeaders();
        $this->outputMessage['processing'] = "The requested theme resource doesn't exist.";
    }

    /** Resolves the theme directory path for the given theme name.
     * Returns null if the theme directory does not exist.
     * @param string|null $themeName Name of the theme.
     * @return string|null Path to the theme directory or null if not found.
     */
    private function pathToTheme(?string $themeName): ?string
    {
        $themeName = $themeName ?? '';
        if ($themeName !== '') {
            $baseDirs = is_null($this->altThemePath) ? [] : [$this->altThemePath];
            $baseDirs[] = IMUtil::pathToINTERMediator() . DIRECTORY_SEPARATOR . "themes";
            foreach ($baseDirs as $baseDir) {
                $themeDir = $this->pathInTheme($baseDir, $themeName);
                if (!is_null($themeDir) && is_dir($themeDir)) {
                    return $themeDir;
                }
            }
        }
        $this->outputMessage['pathToTheme'] = "The theme file for '{$themeName}' doesn't exist.";
        return null;
    }
}