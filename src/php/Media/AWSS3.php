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


namespace INTERMediator\Media;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;

class AWSS3 implements UploadingSupport
{
    public function processing($db, $url, $options, $files, $noOutput, $field, $contextname, $keyfield, $keyvalue, $datasource, $dbspec, $debug)
    {
        if ($useFileSystem) { // requires media-root-dir specification.
            $fileRoot = $options['media-root-dir'];
            if (substr($fileRoot, strlen($fileRoot) - 1, 1) !== '/') {
                $fileRoot .= '/';
            }
        }

        if (count($files) < 1) {
            if (!is_null($url)) {
                header('Location: ' . $url);
            } else {
                $messages = IMUtil::getMessageClassInstance();
                $db->logger->setErrorMessage($messages->getMessageAs(3202));
                $db->processingRequest("noop");
                if (!$noOutput) {
                    $db->finishCommunication();
                    $db->exportOutputDataAsJSON();
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
                if ($useFMContainer) {
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
                } else if ($useS3) {


                } else if ($useFileSystem) { // for normal file uploading
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
                        $db->logger->setErrorMessage("Invalid Path Error.");
                        $db->processingRequest("noop");
                        if (!$noOutput) {
                            $db->finishCommunication();
                            $db->exportOutputDataAsJSON();
                        }
                        return;
                    }

                    if (!file_exists($fileRoot . $dirPath)) {
                        $result = mkdir($fileRoot . $dirPath, 0755, true);
                        if (!$result) {
                            $db->logger->setErrorMessage("Can't make directory. [{$dirPath}]");
                            $db->processingRequest("noop");
                            if (!$noOutput) {
                                $db->finishCommunication();
                                $db->exportOutputDataAsJSON();
                            }
                            return;
                        }
                    }
                    //exec("chmod -R o+x " . escapeshellcmd($fileRoot));
                }
                $result = move_uploaded_file(IMUtil::removeNull($fileInfoTemp), $filePath);
                if (!$result) {
                    if (!is_null($url)) {
                        header('Location: ' . $url);
                    } else {
                        $db->logger->setErrorMessage("Fail to move the uploaded file in the media folder.");
                        $db->processingRequest("noop");
                        if (!$noOutput) {
                            $db->finishCommunication();
                            $db->exportOutputDataAsJSON();
                        }
                    }
                    return;
                }

                if ($useFileSystem) {
                    $dbProxyContext = $db->dbSettings->getDataSourceTargetArray();
                    if (isset($dbProxyContext['file-upload'])) {
                        foreach ($dbProxyContext['file-upload'] as $item) {
                            if (isset($item['field']) && !isset($item['context'])) {
                                $targetFieldName = $item['field'];
                            }
                        }
                    }
                }

                $db = new Proxy();
                $db->initialize($datasource, $options, $dbspec, $debug, $contextname);
                $db->dbSettings->addExtraCriteria($keyfield, "=", $keyvalue);
                $db->dbSettings->setFieldsRequired(array($targetFieldName));

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

                if ($useFileSystem) {
                    $db->dbSettings->setValue(array($filePartialPath));
                } else if ($useS3) {


                } else if ($useFMContainer) {
                    $db->dbSettings->setValue(array($fileName . "\n" .
                        base64_encode(file_get_contents($filePath))));
                }

                $db->processingRequest("update", true);
                $dbProxyRecord = $db->getDatabaseResult();

                $relatedContext = null;
                if ($useFileSystem) {
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

            if ($useFileSystem) {
                $db->addOutputData('dbresult', $filePath);
            } else if ($useS3) {

            } else if ($useFMContainer) {
                if ($dbspec['db-class'] === 'FileMaker_FX') {
                    $db->addOutputData('dbresult',
                        '/fmi/xml/cnt/' . $fileName .
                        '?-db=' . urlencode($db->dbSettings->getDbSpecDatabase()) .
                        '&-lay=' . urlencode($datasource[0]['name']) .
                        '&-recid=' . intval($keyvalue) .
                        '&-field=' . urlencode($targetFieldName));
                } else if ($dbspec['db-class'] === 'FileMaker_DataAPI') {
                    $layout = $datasource[0]['name'];
                    $db->dbClass->setupFMDataAPIforDB($layout, urlencode($targetFieldName));
                    $result = $db->dbClass->fmData->{$layout}->query(NULL, NULL, 1, 1);
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
                    $db->addOutputData('dbresult', $path);
                }
            }
        }
    }

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
}