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

use INTERMediator\DB\Proxy;

class FileUploader
{
    private ?Proxy $db;
    private ?string $url = NULL;
    private int $accessLogLevel;
    private array $outputMessage = [];

    public ?array $dbresult = null;

    public function __construct()
    {
        $this->accessLogLevel = Params::getParameterValue("accessLogLevel", false);
    }

    public function getResultForLog(): array
    {
        if ($this->accessLogLevel < 1) {
            return [];
        }

        $this->outputMessage['name'] = $_POST["_im_contextname"];
        return $this->outputMessage;
    }

    public function finishCommunication(): void
    {
        $this->db->finishCommunication();
    }

    /*
            array(6) { ["_im_redirect"]=> string(54) "http://localhost/im/Sample_webpage/fileupload_MySQL.html" ["_im_contextname"]=> string(4) "chat" ["_im_field"]=> string(7) "message" ["_im_keyfield"]=> string(2) "id" ["_im_keyvalue"]=> string(2) "38" ["access"]=> string(10) "uploadfile" } array(1) { ["_im_uploadfile"]=> array(5) { ["name"]=> string(16) "ac0600_aoiro.pdf" ["type"]=> string(15) "application/pdf" ["tmp_name"]=> string(26) "/private/var/tmp/phpkk9RXn" ["error"]=> int(0) ["size"]=> int(77732) } }

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
        if (!is_null($this->url)) {
            header('Location: ' . $this->url);
        }
        $this->db->exportOutputDataAsJSON();
    }

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
            if (!is_null($this->url)) {
                header('Location: ' . $this->url);
            } else {
                $messages = IMUtil::getMessageClassInstance();
                $this->db->logger->setErrorMessage($messages->getMessageAs(3202));
                $this->db->processingRequest("nothing");
                if (!$noOutput) {
                    $this->db->finishCommunication();
                    $this->db->exportOutputDataAsJSON();
                }
            }
            return;
        }

        $className = "INTERMediator\\Media\\{$className}"; // Instantiated media class object.
        $this->db->logger->setDebugMessage("Instantiate the class '{$className}'", 2);
        $mediaClassObj = new $className();
        $mediaClassObj->processing($this->db, $this->url, $options, $files, $noOutput, $field,
            $contextName, $keyField, $keyValue, $dataSource, $dbSpec, $debug);
        if ($field[0] == "_im_csv_upload") {    // CSV File uploading
            if (isset($this->db->outputOfProcessing['dbresult'])) { // For CSV importing
                $this->dbresult = $this->db->outputOfProcessing['dbresult'];
            }
        }
    }

    //
    public function processInfo(): void
    {
        if (function_exists('apc_fetch')) {
            $onloadScript = "window.onload=function(){setInterval(\"location.reload()\",500);};";
            echo "<html><head><script>{$onloadScript}</script></head><body style='margin:0;padding:0'>";
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

    protected function getRedirectUrl(?string $url): ?string
    {
        if (strpos(strtolower($url), '%0a') !== false || strpos(strtolower($url), '%0d') !== false) {
            return NULL;
        }

        if (strpos($url, 'http://' . php_uname('n') . '/') === 0 ||
            strpos($url, 'https://' . php_uname('n') . '/') === 0
        ) {
            return $url;
        }

        if (isset($_SERVER['SERVER_ADDR']) &&
            strpos($url, 'http://' . $_SERVER['SERVER_ADDR'] . '/') === 0
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

    protected function checkRedirectUrl(?string $url, ?string $webServerName): bool
    {
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            $parsedUrl = parse_url($url);

            $util = new IMUtil();
            if ($util->checkHost($parsedUrl['host'], $webServerName)) {
                return TRUE;
            }
        }

        return FALSE;
    }


    /**
     * @param string $dbclass
     * @return string
     */
    private function getClassNameForMedia(string $dbclass): string
    {
        $className = "FileSystem";
        $contextDef = $this->db->dbSettings->getDataSourceTargetArray();

        if (($dbclass === 'FileMaker_FX' || $dbclass === 'FileMaker_DataAPI') &&
            isset($contextDef['file-upload'])) {
            foreach ($contextDef['file-upload'] as $item) {
                if (isset($item['container'])
                    && (((boolean)$item['container'] === TRUE)
                        || ($item['container'] === 'FileMaker'))) {
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
