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

use INTERMediator\IMUtil;
use INTERMediator\DB\Proxy;
use INTERMediator\Locale\IMLocaleFormatTable;

class FileSystem implements UploadingSupport
{
    public function processing($db, $url, $options, $files, $noOutput, $field, $contextname, $keyfield, $keyvalue, $datasource, $dbspec, $debug)
    {
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
                if (!isset($options['media-root-dir'])) {
                    if (!is_null($this->url)) {
                        header('Location: ' . $this->url);
                    } else {
                        $this->db->logger->setErrorMessage("'media-root-dir' isn't specified");
                        $this->db->processingRequest("noop");
                        if (!$noOutput) {
                            $this->db->finishCommunication();
                            $this->db->exportOutputDataAsJSON();
                        }
                    }
                    return;
                }
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

                $dbProxyContext = $db->dbSettings->getDataSourceTargetArray();
                if (isset($dbProxyContext['file-upload'])) {
                    foreach ($dbProxyContext['file-upload'] as $item) {
                        if (isset($item['field']) && !isset($item['context'])) {
                            $targetFieldName = $item['field'];
                        }
                    }
                }

                $db = new Proxy();
                $db->initialize($datasource, $options, $dbspec, $debug, $contextname);
                $db->dbSettings->addExtraCriteria($keyfield, "=", $keyvalue);
                $db->dbSettings->setFieldsRequired(array($targetFieldName));

                $db->dbSettings->setValue(array($filePartialPath));

                $db->processingRequest("update", true);
                $dbProxyRecord = $db->getDatabaseResult();

                $relatedContext = null;
                if (isset($dbProxyContext['file-upload'])) {
                    foreach ($dbProxyContext['file-upload'] as $item) {
                        if ($item['field'] == $targetFieldName) {
                            $relatedContext = new Proxy();
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
                $db->addOutputData('dbresult', $filePath);

            } else {    // CSV File uploading
                $params = IMUtil::getFromParamsPHPFile(
                    ["import1stLine", "importSkipLines", "importFormat", "useReplace",
                        "convert2Number", "convert2Date", "convert2DateTime"], true);
                $import1stLine = (isset($options['import']) && isset($options['import']['1st-line']))
                    ? $options['import']['1st-line']
                    : (isset($params["import1stLine"]) ? $params["import1stLine"] : true);
                $importSkipLines = intval((isset($options['import']) && isset($options['import']['skip-lines']))
                    ? $options['import']['skip-lines']
                    : (isset($params["importSkipLines"]) ? $params["importSkipLines"] : 0));
                $importFormat = (isset($options['import']) && isset($options['import']['format']))
                    ? $options['import']['format']
                    : (isset($params["importFormat"]) ? $params["importFormat"] : "CSV");
                $separator = (strtolower($importFormat) == 'tsv') ? "\t" : ",";
                $useReplace = boolval((isset($options['import']) && isset($options['import']['use-replace']))
                    ? $options['import']['use-replace']
                    : (isset($params["useReplace"]) ? $params["useReplace"] : false));
                $convert2Number = (isset($options['import']) && isset($options['import']['convert-number']))
                    ? $options['import']['convert-number']
                    : (isset($params["convert2Number"]) ? $params["convert2Number"] : []);;
                $convert2Number = is_array($convert2Number) ? $convert2Number : [];
                $convert2Date = (isset($options['import']) && isset($options['import']['convert-date']))
                    ? $options['import']['convert-date']
                    : (isset($params["convert2Date"]) ? $params["convert2Date"] : []);;
                $convert2Date = is_array($convert2Date) ? $convert2Date : [];
                $convert2DateTime = (isset($options['import']) && isset($options['import']['convert-datetime']))
                    ? $options['import']['convert-datetime']
                    : (isset($params["convert2DateTime"]) ? $params["convert2DateTime"] : []);;
                $convert2DateTime = is_array($convert2DateTime) ? $convert2DateTime : [];

                $decimalPoint = ord(IMLocaleFormatTable::getCurrentLocaleFormat()['mon_decimal_point']);
                $zeroCode = ord('0');
                $nineCode = ord('9');

                $db->ignoringPost();
                $db->initialize($datasource, $options, $dbspec, $debug, $contextname);
                $dbContext = $db->dbSettings->getDataSourceTargetArray();

                $importingFields = [];
                if (is_string($import1stLine)) {
                    foreach (new FieldDivider($import1stLine, $separator) as $field) {
                        $importingFields[] = trim($field);
                    }
                }
                $is1stLine = true;
                $createdKeys = [];
                foreach (new LineDivider(file_get_contents(IMUtil::removeNull($fileInfoTemp))) as $line) {
                    if ($importSkipLines > 0) {
                        $importSkipLines -= 1;
                    } else {
                        if ($is1stLine && $import1stLine === true) {
                            foreach (new FieldDivider($line, $separator) as $field) {
                                $importingFields[] = trim($field);
                            }
                        } else {
                            $db->dbSettings->setValue([]);
                            $db->dbSettings->setFieldsRequired([]);
                            foreach (new FieldDivider($line, $separator) as $index => $value) {
                                if ($index < count($importingFields)) {
                                    $field = $importingFields[$index];
                                    if (array_search($field, $convert2Number) !== false) {
                                        $original = $value;
                                        $value = '';
                                        for ($i = 0; $i < strlen($original); $i++) {
                                            $c = ord(substr($original, $i, 1));
                                            if (($c >= $zeroCode and $c <= $nineCode) || $c == $decimalPoint) {
                                                $value .= chr($c);
                                            }
                                        }
                                    }
                                    if (array_search($field, $convert2Date) !== false) {
                                        try {
                                            $dt = new \DateTime($value);
                                        } catch (\Exception $ex) {
                                            $dt = new \DateTime("0001-01-01 00:00:00");
                                        }
                                        $value = $dt->format('Y-m-d');
                                    }
                                    if (array_search($field, $convert2DateTime) !== false) {
                                        try {
                                            $dt = new \DateTime($value);
                                        } catch (\Exception $ex) {
                                            $dt = new \DateTime("0001-01-01 00:00:00");
                                        }
                                        $value = $dt->format('Y-m-d H:i:s');
                                    }
                                    $db->dbSettings->addValueWithField($field, $value);
                                }
                            }
                            $db->processingRequest($useReplace ? "replace" : "create", true, true);
                            //$createdKeys[] = [$dbContext['key'] => ($db->getDatabaseResult()[0])[$dbContext['key']]];
                        }
                        $is1stLine = false;
                    }
                }
                unlink(IMUtil::removeNull($fileInfoTemp));
                $db->outputOfProcessing['dbresult'] = $createdKeys;
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