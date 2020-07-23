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

class FileUploader
{
    private $db;
    private $url = NULL;
    private $accessLogLevel = 0;
    private $outputMessage = ['apology'=>'Logging messages are not implemented so far.'];

    public function getResultForLog(){
        if($this->accessLogLevel < 1) {
            return [];
        }
        return $this->outputMessage;
    }

    public function finishCommunication()
    {
        $this->db->finishCommunication();
    }

    /*
            array(6) { ["_im_redirect"]=> string(54) "http://localhost/im/Sample_webpage/fileupload_MySQL.html" ["_im_contextname"]=> string(4) "chat" ["_im_field"]=> string(7) "message" ["_im_keyfield"]=> string(2) "id" ["_im_keyvalue"]=> string(2) "38" ["access"]=> string(10) "uploadfile" } array(1) { ["_im_uploadfile"]=> array(5) { ["name"]=> string(16) "ac0600_aoiro.pdf" ["type"]=> string(15) "application/pdf" ["tmp_name"]=> string(26) "/private/var/tmp/phpkk9RXn" ["error"]=> int(0) ["size"]=> int(77732) } }

    */

    private function justfyPathComponent($str, $mode = "default")
    {
        $jStr = $str;
        switch ($mode) {
            case "assjis":
                $jStr = mb_convert_encoding($jStr, "SJIS", "UTF-8");
                $jStr = mb_convert_encoding($jStr, "UTF-8", "SJIS");
                $jStr = str_replace(DIRECTORY_SEPARATOR, '_', str_replace('.', '_', $jStr));
                break;
            case "asucs4":
                $jStr = mb_convert_encoding($jStr, "UCS-4", "UTF-8");
                $jStr = mb_convert_encoding($jStr, "UTF-8", "UCS-4");
                $jStr = str_replace(DIRECTORY_SEPARATOR, '_', str_replace('.', '_', $jStr));
                break;
            default:
                $jStr = str_replace('.', '_', urlencode($jStr));
                break;
        }
        return $jStr;
    }

    public function processingAsError($datasource, $options, $dbspec, $debug, $contextname)
    {
        $this->db = new DB\Proxy();
        $this->db->initialize($datasource, $options, $dbspec, $debug, $contextname);

        $messages = IMUtil::getMessageClassInstance();
        if (count($_FILES) === 0) {
            $this->db->logger->setErrorMessage($messages->getMessageAs(3201));
        } else {
            foreach ($_FILES as $fn => $fileInfo) {
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
        $this->db->processingRequest("noop");
        $this->db->finishCommunication();
        $this->db->exportOutputDataAsJSON();
        return;
    }

    public function processing($datasource, $options, $dbspec, $debug)
    {
        $contextname = $_POST["_im_contextname"];
        $keyfield = $_POST["_im_keyfield"];
        $keyvalue = $_POST["_im_keyvalue"];
        $field = [$_POST["_im_field"]];
        $files = $_FILES;

        $this->processingWithParameters($datasource, $options, $dbspec, $debug,
            $contextname, $keyfield, $keyvalue, $field, $files, false);
        $this->db->finishCommunication();
        if (!is_null($this->url)) {
            header('Location: ' . $this->url);
        }
        $this->db->exportOutputDataAsJSON();
    }

    public function processingWithParameters($datasource, $options, $dbspec, $debug,
                                             $contextname, $keyfield, $keyvalue, $field, $files, $noOutput)
    {
        $this->db = new DB\Proxy();
        $this->db->initialize($datasource, $options, $dbspec, $debug, $contextname);

        $this->db->logger->setDebugMessage("FileUploader class's processing starts");

        $useContainer = FALSE;
        $dbProxyContext = $this->db->dbSettings->getDataSourceTargetArray();
        if (($dbspec['db-class'] === 'FileMaker_FX' || $dbspec['db-class'] === 'FileMaker_DataAPI') &&
            isset($dbProxyContext['file-upload'])) {
            foreach ($dbProxyContext['file-upload'] as $item) {
                if (isset($item['container']) && (boolean)$item['container'] === TRUE) {
                    $useContainer = TRUE;
                }
            }
        }

        if (isset($_POST['_im_redirect'])) {
            $this->url = $this->getRedirectUrl($_POST['_im_redirect']);
            if (is_null($this->url)) {
                header("HTTP/1.1 500 Internal Server Error");
                $this->db->logger->setErrorMessage('Header may not contain more than a single header, new line detected.');
                $this->db->processingRequest('noop');
                if(!$noOutput) {
                    $this->db->finishCommunication();
                    $this->db->exportOutputDataAsJSON();
                }
                return;
            }
        }

        if (!isset($options['media-root-dir']) && $useContainer === FALSE) {
            if (!is_null($this->url)) {
                header('Location: ' . $this->url);
            } else {
                $this->db->logger->setErrorMessage("'media-root-dir' isn't specified");
                $this->db->processingRequest("noop");
                if(!$noOutput) {
                    $this->db->finishCommunication();
                    $this->db->exportOutputDataAsJSON();
                }
            }
            return;
        }
        if ($useContainer === FALSE) {
            // requires media-root-dir specification.
            $fileRoot = $options['media-root-dir'];
            if (substr($fileRoot, strlen($fileRoot) - 1, 1) !== '/') {
                $fileRoot .= '/';
            }
        }

        if (count($files) < 1) {
            if (!is_null($this->url)) {
                header('Location: ' . $this->url);
            } else {
                $messages = IMUtil::getMessageClassInstance();
                $this->db->logger->setErrorMessage($messages->getMessageAs(3202));
                $this->db->processingRequest("noop");
                if(!$noOutput) {
                    $this->db->finishCommunication();
                    $this->db->exportOutputDataAsJSON();
                }
            }
            return;
        }

        $counter = -1;
        foreach ($files as $fn => $fileInfo) {
            $counter += 1;
            if (is_array($fileInfo['name'])) {   // JQuery File Upload Style
                $fileInfoName = $fileInfo['name'][0];
                $fileInfoTemp = $fileInfo['tmp_name'][0];
            } else {
                $fileInfoName = $fileInfo['name'];
                $fileInfoTemp = $fileInfo['tmp_name'];
            }
            $filePathInfo = pathinfo(IMUtil::removeNull(basename($fileInfoName)));

            $targetFieldName = $field[$counter];
            if ($targetFieldName != "_im_csv_upload") {
                // file uploading or FM's container
                if ($useContainer) {
                    // for uploading to FileMaker's container field
                    $fileName = $filePathInfo['filename'] . '.' . $filePathInfo['extension'];
                    $tmpDir = ini_get('upload_tmp_dir');
                    if ($tmpDir === '') {
                        $tmpDir = sys_get_temp_dir();
                    }
                    if (mb_substr($tmpDir, 1) === DIRECTORY_SEPARATOR) {
                        $filePath = $tmpDir . $fileName;
                    } else {
                        $filePath = $tmpDir . DIRECTORY_SEPARATOR . $fileName;
                    }
                } else { // for normal file uploading
                    $fileRoot = $options['media-root-dir'];
                    if (substr($fileRoot, strlen($fileRoot) - 1, 1) != '/') {
                        $fileRoot .= '/';
                    }

                    $uploadFilePathMode = null;
                    $params = IMUtil::getFromParamsPHPFile(array("uploadFilePathMode",), true);
                    $uploadFilePathMode = $params["uploadFilePathMode"];


                    $dirPath =
                        $this->justfyPathComponent($contextname, $uploadFilePathMode) . DIRECTORY_SEPARATOR
                        . $this->justfyPathComponent($keyfield, $uploadFilePathMode) . "="
                        . $this->justfyPathComponent($keyvalue, $uploadFilePathMode) . DIRECTORY_SEPARATOR
                        . $this->justfyPathComponent($targetFieldName, $uploadFilePathMode);
                    $rand4Digits = rand(1000, 9999);
                    $filePartialPath = $dirPath . '/' . $filePathInfo['filename'] . '_'
                        . $rand4Digits . '.' . $filePathInfo['extension'];
                    $filePath = $fileRoot . $filePartialPath;
                    if (strpos($filePath, $fileRoot) !== 0) {
                        $this->db->logger->setErrorMessage("Invalid Path Error.");
                        $this->db->processingRequest("noop");
                        if(!$noOutput) {
                            $this->db->finishCommunication();
                            $this->db->exportOutputDataAsJSON();
                        }
                        return;
                    }

                    if (!file_exists($fileRoot . $dirPath)) {
                        $result = mkdir($fileRoot . $dirPath, 0755, true);
                        if (!$result) {
                            $this->db->logger->setErrorMessage("Can't make directory. [{$dirPath}]");
                            $this->db->processingRequest("noop");
                            if(!$noOutput) {
                                $this->db->finishCommunication();
                                $this->db->exportOutputDataAsJSON();
                            }
                            return;
                        }
                    }
                    //exec("chmod -R o+x " . escapeshellcmd($fileRoot));
                }
                $result = move_uploaded_file(IMUtil::removeNull($fileInfoTemp), $filePath);
                if (!$result) {
                    if (!is_null($this->url)) {
                        header('Location: ' . $this->url);
                    } else {
                        $this->db->logger->setErrorMessage("Fail to move the uploaded file in the media folder.");
                        $this->db->processingRequest("noop");
                        if(!$noOutput) {
                            $this->db->finishCommunication();
                            $this->db->exportOutputDataAsJSON();
                        }
                    }
                    return;
                }

                if ($useContainer === FALSE) {
                    $dbProxyContext = $this->db->dbSettings->getDataSourceTargetArray();
                    if (isset($dbProxyContext['file-upload'])) {
                        foreach ($dbProxyContext['file-upload'] as $item) {
                            if (isset($item['field']) && !isset($item['context'])) {
                                $targetFieldName = $item['field'];
                            }
                        }
                    }
                }

                $this->db = new DB\Proxy();
                $this->db->initialize($datasource, $options, $dbspec, $debug, $contextname);
                $this->db->dbSettings->addExtraCriteria($keyfield, "=", $keyvalue);
                $this->db->dbSettings->setFieldsRequired(array($targetFieldName));

                // If the file content is base64 encoded url starting with 'data:,', decode it and store a file.
                $fileContent = file_get_contents($filePath, false, null, 0, 30);
                $headerTop = strpos($fileContent, "data:");
                $endOfHeader = strpos($fileContent, ",");
                if ($headerTop === 0 && $endOfHeader > 0) {
                    $tempFilePath = $filePath . ".temp";
                    rename($filePath, $tempFilePath);
                    $step = 1024;
                    if (strpos($fileContent, ";base64") !== false) {
                        $fw = fopen($filePath, "w");
                        $fp = fopen($tempFilePath, "r");
                        fread($fp, $endOfHeader + 1);
                        while ($str = fread($fp, $step)) {
                            fwrite($fw, base64_decode($str));
                        }
                        fclose($fp);
                        fclose($fw);
                        unlink($tempFilePath);
                    }
                }

                if ($useContainer === FALSE) {
                    $this->db->dbSettings->setValue(array($filePartialPath));
                } else {
                    $this->db->dbSettings->setValue(array($fileName . "\n" . base64_encode(file_get_contents($filePath))));
                }

                $this->db->processingRequest("update", true);
                $dbProxyRecord = $this->db->getDatabaseResult();

                $relatedContext = null;
                if ($useContainer === FALSE) {
                    if (isset($dbProxyContext['file-upload'])) {
                        foreach ($dbProxyContext['file-upload'] as $item) {
                            if ($item['field'] == $targetFieldName) {
                                $relatedContext = new DB\Proxy();
                                $relatedContext->initialize($datasource, $options, $dbspec, $debug, isset($item['context']) ? $item['context'] : null);
                                $relatedContextInfo = $relatedContext->dbSettings->getDataSourceTargetArray();
                                $fields = array();
                                $values = array();
                                if (isset($relatedContextInfo["query"])) {
                                    foreach ($relatedContextInfo["query"] as $cItem) {
                                        if ($cItem['operator'] == "=" || $cItem['operator'] == "eq") {
                                            $fields[] = $cItem['field'];
                                            $values[] = $cItem['value'];
                                        }
                                    }
                                }
                                if (isset($relatedContextInfo["relation"])) {
                                    foreach ($relatedContextInfo["relation"] as $cItem) {
                                        if ($cItem['operator'] == "=" || $cItem['operator'] == "eq") {
                                            $fields[] = $cItem['foreign-key'];
                                            $values[] = $dbProxyRecord[0][$cItem['join-field']];
                                        }
                                    }
                                }
                                $fields[] = "path";
                                $values[] = $filePartialPath;
                                $relatedContext->dbSettings->setFieldsRequired($fields);
                                $relatedContext->dbSettings->setValue($values);
                                $relatedContext->processingRequest("create", true, true);
                                /* 2019-03-13 msyk
                                Why can the authentication bypass here? This db access is followed by another db processing,
                                and if the authentication is not valid, previous processing is going to arise any errors.
                                */
                                //    $relatedContext->finishCommunication(true);
                                //    $relatedContext->exportOutputDataAsJSON();
                            }
                        }
                    }
                }
            } else {    // CSV File uploading

            }

            if ($useContainer === FALSE) {
                $this->db->addOutputData('dbresult', $filePath);
            } else {
                if ($dbspec['db-class'] === 'FileMaker_FX') {
                    $this->db->addOutputData('dbresult',
                        '/fmi/xml/cnt/' . $fileName .
                        '?-db=' . urlencode($this->db->dbSettings->getDbSpecDatabase()) .
                        '&-lay=' . urlencode($datasource[0]['name']) .
                        '&-recid=' . intval($keyvalue) .
                        '&-field=' . urlencode($targetFieldName));
                } else if ($dbspec['db-class'] === 'FileMaker_DataAPI') {
                    $layout = $datasource[0]['name'];
                    $this->db->dbClass->setupFMDataAPIforDB($layout, urlencode($targetFieldName));
                    $result = $this->db->dbClass->fmData->{$layout}->query(NULL, NULL, 1, 1);
                    $path = '';
                    $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL);
                    if ($host === NULL || $host === FALSE) {
                        $host = 'localhost';
                    }
                    foreach ($result as $record) {
                        $path = str_replace('https://' . $host, '', $record->{$targetFieldName});
                        break;
                    }
                    $path = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL) . '?media=' . urlencode($path);
                    $this->db->addOutputData('dbresult', $path);
                }
            }
        }
    }

    //
    public function processInfo()
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

    protected function getRedirectUrl($url)
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

        $params = IMUtil::getFromParamsPHPFile(array('webServerName'), true);
        $webServerName = $params['webServerName'];
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

    protected function checkRedirectUrl($url, $webServerName)
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
}
