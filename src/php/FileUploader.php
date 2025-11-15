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

use Exception;
use INTERMediator\DB\Proxy;

/**
 * Class FileUploader
 * Handles file upload processing, error handling, and integration with INTER-Mediator database proxies.
 *
 * @package INTERMediator
 */
class FileUploader
{
    /**
     * @var Proxy Database proxy instance for communication with the backend.
     */
    private Proxy $db;

    /**
     * @var int Access log level for logging purposes.
     */
    private int $accessLogLevel;

    /**
     * @var array Output messages for logging or response.
     */
    private array $outputMessage = [];

    /**
     * @var array|null Database result after processing (e.g., for CSV uploads).
     */
    public ?array $dbresult = null;

    /**
     * FileUploader constructor.
     * Initializes access log level from parameters.
     */
    public function __construct()
    {
        $this->accessLogLevel = Params::getParameterValue("accessLogLevel", false);
    }

    /**
     * Gets the log result for the current upload process.
     *
     * @return array Output message array if access log level is enough, otherwise empty array.
     */
    public function getResultForLog(): array
    {
        if ($this->accessLogLevel < 1) {
            return [];
        }

        $this->outputMessage['name'] = $_POST["_im_contextname"];
        return $this->outputMessage;
    }

    /**
     * Finalizes communication with the database proxy.
     *
     * @return void
     */
    public function finishCommunication(): void
    {
        $this->db->finishCommunication();
    }

    /**
     * Handles file upload errors and outputs error messages as JSON if needed.
     *
     * @param array|null $dataSource Data source definitions.
     * @param array|null $options Options for INTER-Mediator.
     * @param array|null $dbSpec Database specification.
     * @param int $debug Debug mode level.
     * @param string|null $contextName Context name for the upload.
     * @param bool $noOutput If true, suppresses output.
     * @return void
     * @throws Exception
     */
    public function processingAsError(?array $dataSource, ?array $options, ?array $dbSpec, int $debug, ?string $contextName, bool $noOutput): void
    {
        $this->db = new Proxy();
        $this->db->initialize($dataSource, $options, $dbSpec, $debug, $contextName);

        $messages = IMUtil::getMessageClassInstance();
        if (count($_FILES) === 0) {
            $this->db->logger->setErrorMessage($messages->getMessageAs(3201));
        } else {
            foreach ($_FILES as $fileInfo) {
                if (isset($fileInfo["error"])) {
                    switch (is_array($fileInfo["error"]) ? $fileInfo["error"][0] : $fileInfo["error"]) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $this->db->logger->setErrorMessage($messages->getMessageAs(3202));
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $this->db->logger->setErrorMessage($messages->getMessageAs(3203));
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $this->db->logger->setErrorMessage($messages->getMessageAs(3204));
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $this->db->logger->setErrorMessage($messages->getMessageAs(3205));
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $this->db->logger->setErrorMessage($messages->getMessageAs(3206));
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $this->db->logger->setErrorMessage($messages->getMessageAs(3207));
                            break;
                        default:
                            $this->db->logger->setErrorMessage($messages->getMessageAs(3208));
                    }
                }
            }
        }
        if (!$noOutput) {
            $this->db->processingRequest("nothing");
            $this->db->finishCommunication();
            $this->db->exportOutputDataAsJSON();
        }
    }

    /**
     * Main entry point for handling a file upload request from POST/FILES.
     *
     * @param array|null $dataSource Data source definitions.
     * @param array|null $options Options for INTER-Mediator.
     * @param array|null $dbSpec Database specification.
     * @param int $debug Debug mode level.
     * @return void
     * @throws Exception
     */
    public function processing(?array $dataSource, ?array $options, ?array $dbSpec, int $debug): void
    {
        $contextName = $_POST["_im_contextname"];
        $keyField = $_POST["_im_keyfield"];
        $keyValue = $_POST["_im_keyvalue"];
        $field = [$_POST["_im_field"]];
        $files = $_FILES;

        $this->processingWithParameters($dataSource, $options, $dbSpec, $debug,
            $contextName, $keyField, $keyValue, $field, $files, false);
        $this->db->finishCommunication();
        $this->db->exportOutputDataAsJSON();
    }

    /**
     * Handles file upload processing with explicit parameters and file data.
     *
     * @param array|null $dataSource Data source definitions.
     * @param array|null $options Options for INTER-Mediator.
     * @param array|null $dbSpec Database specification.
     * @param int $debug Debug mode level.
     * @param string|null $contextName Context name for the upload.
     * @param string|null $keyField Key field name for the record.
     * @param string|null $keyValue Key value for the record.
     * @param array|null $field Field(s) for the upload.
     * @param array|null $files Uploaded file(s) data.
     * @param bool $noOutput If true, suppresses output.
     * @return void
     * @throws Exception
     */
    public function processingWithParameters(?array  $dataSource, ?array $options, ?array $dbSpec, int $debug,
                                             ?string $contextName, ?string $keyField, ?string $keyValue, ?array $field,
                                             ?array  $files, bool $noOutput): void
    {
        $this->db = new DB\Proxy();
        $this->db->initialize($dataSource, $options, $dbSpec, $debug, $contextName);

        $this->db->logger->setDebugMessage("[FileUploader] FileUploader class's processing starts: files="
            . str_replace(["\n", " "], ["", ""], var_export($files, true)), 2);

        $contextDef = $this->db->dbSettings->getDataSourceTargetArray();
        $dbClass = ($contextDef['db-class'] ?? ($dbSpec['db-class'] ?? Params::getParameterValue('dbClass', '')));
        $className = $this->getClassNameForMedia($dbClass); // Decided media class name

        if (count($files) < 1) { // If no file is uploaded.
            $messages = IMUtil::getMessageClassInstance();
            $this->db->logger->setErrorMessage($messages->getMessageAs(3202));
            $this->db->processingRequest("nothing");
            if (!$noOutput) {
                $this->db->finishCommunication();
                $this->db->exportOutputDataAsJSON();
            }
            return;
        }

        $className = "INTERMediator\\Media\\{$className}"; // Instantiated media class object.
        $this->db->logger->setDebugMessage("Instantiate the class '{$className}'", 2);
        $mediaClassObj = new $className();
        $mediaClassObj->processing($this->db, null, $options, $files, $noOutput, $field,
            $contextName, $keyField, $keyValue, $dataSource, $dbSpec, $debug);
        if ($field[0] == "_im_csv_upload") {    // CSV File uploading
            if (isset($this->db->outputOfProcessing['dbresult'])) { // For CSV importing
                $this->dbresult = $this->db->outputOfProcessing['dbresult'];
            }
        }
    }

    /**
     * Outputs the upload progress for APC-enabled servers as an HTML page.
     *
     * @return void
     */
    public function processInfo(): void
    {
        if (function_exists('apc_fetch')) {
            $onloadScript = "window.onload=function(){setInterval(\"location.reload()\",500);};";
            echo "<html lang='en'><head><script>{$onloadScript}</script></head><body style='margin:0;padding:0'>";
            echo "<div style='width:160px;border:1px solid #555555;padding:1px;background-color:white;'>";
            $status = apc_fetch('upload_' . $_GET['uploadprocess']);
            if ($status === false) {
                $progress = 0;
            } else {
                $progress = round($status['current'] / $status['total'], 2) * 100;
            }
            echo "<div style='width:{$progress}%;height:20px;background-color: #ffb52d;'>";
            echo "<div style='position:absolute;left:0;top:0;padding-left:8px;'>";
            echo $progress . " %";
            echo "</div></div></div></body></html>";
        }
    }

    /**
     * Validates and returns a redirect URL if it is safe, otherwise returns NULL.
     *
     * @param string|null $url The URL to validate.
     * @return string|null The validated URL or NULL if invalid.
     */
    protected function getRedirectUrl(?string $url): ?string
    {
        if (str_contains(strtolower($url), '%0a') || str_contains(strtolower($url), '%0d')) {
            return NULL;
        }

        if (str_starts_with($url, 'http://' . php_uname('n') . '/') ||
            str_starts_with($url, 'https://' . php_uname('n') . '/')
        ) {
            return $url;
        }

        if (isset($_SERVER['SERVER_ADDR']) &&
            str_starts_with($url, 'http://' . $_SERVER['SERVER_ADDR'] . '/')
        ) {
            return $url;
        }

        $webServerName = Params::getParameterValue('webServerName', null);
        if (!is_null($webServerName)) {
            if (is_array($webServerName)) {
                foreach ($webServerName as $name) {
                    if ($this->checkRedirectUrl($url, $name) === TRUE) {
                        return $url;
                    }
                }
            } else {
                if ($this->checkRedirectUrl($url, $webServerName) === TRUE) {
                    return $url;
                }
            }
        }
        return NULL;
    }

    /**
     * Checks if the given URL matches the allowed web server name.
     *
     * @param string|null $url The URL to check.
     * @param string|null $webServerName The allowed web server name.
     * @return bool True if the URL is allowed, false otherwise.
     */
    protected function checkRedirectUrl(?string $url, ?string $webServerName): bool
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $parsedUrl = parse_url($url);

            $util = new IMUtil();
            if ($util->checkHost($parsedUrl['host'], $webServerName)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Determines the media handler class name based on the database class and context definition.
     *
     * @param string $dbclass The database class name.
     * @return string The media handler class name.
     */
    private function getClassNameForMedia(string $dbclass): string
    {
        $className = "FileSystem";
        $contextDef = $this->db->dbSettings->getDataSourceTargetArray();

        if (($dbclass === 'FileMaker_FX' || $dbclass === 'FileMaker_DataAPI') &&
            isset($contextDef['file-upload'])) {
            foreach ($contextDef['file-upload'] as $item) {
                if (isset($item['container'])
                    && (($item['container'] === TRUE) || ($item['container'] === 'FileMaker'))) {
                    $className = "FileMakerContainer";
                    break;
                }
            }
        }
        if (isset($contextDef['file-upload'])) {
            foreach ($contextDef['file-upload'] as $item) {
                if (isset($item['container']) && (strtolower($item['container']) === 's3')) {
                    $className = "AWSS3";
                    break;
                } else if (isset($item['container']) && (strtolower($item['container']) === 'dropbox')) {
                    $className = "Dropbox";
                    break;
                } else if (isset($item['container']) && (strtolower($item['container']) === 'fileurl')) {
                    $className = "FileURL";
                    break;
                }
            }
        }
        return $className;
    }
}
