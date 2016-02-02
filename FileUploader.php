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

class FileUploader
{
    private $db;

    public function finishCommunication()
    {
        $this->db->finishCommunication();
    }

    /*
            array(6) { ["_im_redirect"]=> string(54) "http://localhost/im/Sample_webpage/fileupload_MySQL.html" ["_im_contextname"]=> string(4) "chat" ["_im_field"]=> string(7) "message" ["_im_keyfield"]=> string(2) "id" ["_im_keyvalue"]=> string(2) "38" ["access"]=> string(10) "uploadfile" } array(1) { ["_im_uploadfile"]=> array(5) { ["name"]=> string(16) "ac0600_aoiro.pdf" ["type"]=> string(15) "application/pdf" ["tmp_name"]=> string(26) "/private/var/tmp/phpkk9RXn" ["error"]=> int(0) ["size"]=> int(77732) } }

    */

    public function processing($datasource, $options, $dbspec, $debug)
    {
        $dbProxyInstance = new DB_Proxy();
        $this->db = $dbProxyInstance;
        $dbProxyInstance->initialize($datasource, $options, $dbspec, $debug, $_POST["_im_contextname"]);

        $useContainer = FALSE;
        $dbProxyContext = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
        if ($dbspec['db-class'] === 'FileMaker_FX' && isset($dbProxyContext['file-upload'])) {
          foreach ($dbProxyContext['file-upload'] as $item) {
              if (isset($item['container']) && (boolean)$item['container'] === TRUE) {
                  $useContainer = TRUE;
              }
          }
        }

        $url = NULL;
        if (isset($_POST['_im_redirect'])) {
            $url = $this->getRedirectUrl($_POST['_im_redirect']);
            if (is_null($url)) {
                header("HTTP/1.1 500 Internal Server Error");
                $dbProxyInstance->logger->setErrorMessage('Header may not contain more than a single header, new line detected.');
                $dbProxyInstance->processingRequest($options, 'noop');
                $dbProxyInstance->finishCommunication();
                $dbProxyInstance->exportOutputDataAsJSON();
                return;
            }
        }

        if (!isset($options['media-root-dir']) && $useContainer === FALSE) {
            if (!is_null($url)) {
                header('Location: ' . $url);
            } else {
                $dbProxyInstance->logger->setErrorMessage("'media-root-dir' isn't specified");
                $dbProxyInstance->processingRequest($options, "noop");
                $dbProxyInstance->finishCommunication();
                $dbProxyInstance->exportOutputDataAsJSON();
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

        if (count($_FILES) < 1) {
            if (!is_null($url)) {
                header('Location: ' . $url);
            } else {
                $dbProxyInstance->logger->setErrorMessage("No file wasn't uploaded.");
                $dbProxyInstance->processingRequest($options, "noop");
                $dbProxyInstance->finishCommunication();
                $dbProxyInstance->exportOutputDataAsJSON();
            }
            return;
        }
        foreach ($_FILES as $fn => $fileInfo) {
        }

        $util = new IMUtil();
        $filePathInfo = pathinfo($util->removeNull(basename($fileInfo['name'])));

        if ($useContainer === FALSE) {
            $fileRoot = $options['media-root-dir'];
            if (substr($fileRoot, strlen($fileRoot) - 1, 1) != '/') {
                $fileRoot .= '/';
            }

            $dirPath = str_replace('.', '_', urlencode($_POST["_im_contextname"])) . '/'
                . str_replace('.', '_', urlencode($_POST["_im_keyfield"])) . "="
                . str_replace('.', '_', urlencode($_POST["_im_keyvalue"])) . '/'
                . str_replace('.', '_', urlencode($_POST["_im_field"]));
            $rand4Digits = rand(1000, 9999);
            $filePartialPath = $dirPath . '/' . $filePathInfo['filename'] . '_'
                . $rand4Digits . '.' . $filePathInfo['extension'];
            $filePath = $fileRoot . $filePartialPath;
            if (strpos($filePath, $fileRoot) !== 0) {
                $dbProxyInstance->logger->setErrorMessage("Invalid Path Error.");
                $dbProxyInstance->processingRequest($options, "noop");
                $dbProxyInstance->finishCommunication();
                $dbProxyInstance->exportOutputDataAsJSON();
                return;
            }

            if (!file_exists($fileRoot . $dirPath)) {
                $result = mkdir($fileRoot . $dirPath, 0744, true);
                if (!$result) {
                    $dbProxyInstance->logger->setErrorMessage("Can't make directory. [{$dirPath}]");
                    $dbProxyInstance->processingRequest($options, "noop");
                    $dbProxyInstance->finishCommunication();
                    $dbProxyInstance->exportOutputDataAsJSON();
                    return;
                }
            }
        }
        if ($useContainer === TRUE) {
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
        }
        $result = move_uploaded_file($util->removeNull($fileInfo['tmp_name']), $filePath);
        if (!$result) {
            if (!is_null($url)) {
                header('Location: ' . $url);
            } else {
                $dbProxyInstance->logger->setErrorMessage("Fail to move the uploaded file in the media folder.");
                $dbProxyInstance->processingRequest($options, "noop");
                $dbProxyInstance->finishCommunication();
                $dbProxyInstance->exportOutputDataAsJSON();
            }
            return;
        }

        $targetFieldName = $_POST["_im_field"];
        if ($useContainer === FALSE) {
            $dbProxyContext = $dbProxyInstance->dbSettings->getDataSourceTargetArray();
            if (isset($dbProxyContext['file-upload'])) {
                foreach ($dbProxyContext['file-upload'] as $item) {
                    if (isset($item['field']) && !isset($item['context'])) {
                        $targetFieldName = $item['field'];
                    }
                }
            }
        }

        $dbKeyValue = $_POST["_im_keyvalue"];
        $dbProxyInstance = new DB_Proxy();
        $dbProxyInstance->initialize($datasource, $options, $dbspec, $debug, $_POST["_im_contextname"]);
        $dbProxyInstance->dbSettings->addExtraCriteria($_POST["_im_keyfield"], "=", $dbKeyValue);
        $dbProxyInstance->dbSettings->setTargetFields(array($targetFieldName));

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
            $dbProxyInstance->dbSettings->setValue(array($filePath));
        } else {
            $dbProxyInstance->dbSettings->setValue(array($fileName . "\n" . base64_encode(file_get_contents($filePath))));
        }

        $dbProxyInstance->processingRequest($options, "update");

        $relatedContext = null;
        if ($useContainer === FALSE) {
            if (isset($dbProxyContext['file-upload'])) {
                foreach ($dbProxyContext['file-upload'] as $item) {
                    if ($item['field'] == $_POST["_im_field"]) {
                        $relatedContext = new DB_Proxy();
                        $relatedContext->initialize($datasource, $options, $dbspec, $debug, isset($item['context']) ? $item['context'] : null);
                        $relatedContextInfo = $relatedContext->dbSettings->getDataSourceTargetArray();
                        $fields = array();
                        $values = array();
                        if (isset($relatedContextInfo["query"])) {
                            foreach ($relatedContextInfo["query"] as $cItem) {
                                if ($cItem['operator'] == "=" || $cItem['operator'] == "eq" ) {
                                    $fields[] = $cItem['field'];
                                    $values[] = $cItem['value'];
                                }
                            }
                        }
                        if (isset($relatedContextInfo["relation"])) {
                            foreach ($relatedContextInfo["relation"] as $cItem) {
                                if ($cItem['operator'] == "=" || $cItem['operator'] == "eq" ) {
                                    $fields[] = $cItem['foreign-key'];
                                    $values[] = $dbKeyValue;
                                }
                            }
                        }
                        $fields[] = "path";
                        $values[] = $filePartialPath;
                        $relatedContext->dbSettings->setTargetFields($fields);
                        $relatedContext->dbSettings->setValue($values);
                        $relatedContext->processingRequest($options, "create", true);
                    //    $relatedContext->finishCommunication(true);
                    //    $relatedContext->exportOutputDataAsJSON();
                    }
                }
            }
        }

        if ($useContainer === FALSE) {
            $dbProxyInstance->addOutputData('dbresult', $filePath);
        } else {
            $dbProxyInstance->addOutputData('dbresult',
                '/fmi/xml/cnt/' . $fileName .
                '?-db=' . urlencode($dbProxyInstance->dbSettings->getDbSpecDatabase()) .
                '&-lay=' . urlencode($datasource[0]['name']) .
                '&-recid=' . intval($_POST['_im_keyvalue']) .
                '&-field=' . urlencode($targetFieldName));
        }
        $dbProxyInstance->finishCommunication();
        $dbProxyInstance->exportOutputDataAsJSON();
        if (!is_null($url)) {
            header('Location: ' . $url);
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
                $progress = round($status['current'] / $status['total'], 2)*100;
            }
            echo "<div style='width:{$progress}%;height:20px;background-color: #ffb52d;'>";
            echo "<div style='position:absolute;left:0;top:0;padding-left:8px;'>";
            echo $progress  . " %";
            echo "</div></div></div></body></html>";
        }
    }

    protected function getRedirectUrl($url)
    {
        if (strpos(strtolower($url), '%0a') !== false || strpos(strtolower($url), '%0d') !== false) {
            return NULL;
        }

        if (strpos($url, 'http://' . php_uname('n') . '/') === 0 ||
            strpos($url, 'https://' . php_uname('n') . '/') === 0) {
            return $url;
        }

        if (isset($_SERVER['SERVER_ADDR']) &&
            strpos($url, 'http://' . $_SERVER['SERVER_ADDR'] . '/') === 0) {
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
