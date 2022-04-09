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
use INTERMediator\Params;

class FileSystem implements UploadingSupport
{
    private function getFileNames($info)
    {
        if (is_array($info['name'])) {   // JQuery File Upload Style
            $fileInfoName = $info['name'][0];
            $fileInfoTemp = $info['tmp_name'][0];
        } else {
            $fileInfoName = $info['name'];
            $fileInfoTemp = $info['tmp_name'];
        }
        return [$fileInfoName, $fileInfoTemp];
    }

    private function prepareErrorOut($db, $noOutput, $errorMsg)
    {
        $db->logger->setErrorMessage($errorMsg);
        $db->processingRequest("noop");
        if (!$noOutput) {
            $db->finishCommunication();
            $db->exportOutputDataAsJSON();
        }
    }

    private function decideFilePath($db, $noOutput, $options,
                                    $contextname, $keyfield, $keyvalue, $targetFieldName, $filePathInfo)
    {
        $result = true;
        $fileRoot = $options['media-root-dir'];
        if (substr($fileRoot, strlen($fileRoot) - 1, 1) != '/') {
            $fileRoot .= '/';
        }
//        $uploadFilePathMode = null;
//        $params = IMUtil::getFromParamsPHPFile(array("uploadFilePathMode",), true);
//        $uploadFilePathMode = $params["uploadFilePathMode"];
        $uploadFilePathMode = Params::getParameterValue("uploadFilePathMode", null);

        $dirPath = $this->justfyPathComponent($contextname, $uploadFilePathMode) . DIRECTORY_SEPARATOR
            . $this->justfyPathComponent($keyfield, $uploadFilePathMode) . "="
            . $this->justfyPathComponent($keyvalue, $uploadFilePathMode) . DIRECTORY_SEPARATOR
            . $this->justfyPathComponent($targetFieldName, $uploadFilePathMode);
        $rand4Digits = random_int(1000, 9999);
        $filePartialPath = $dirPath . '/' . $filePathInfo['filename'] . '_'
            . $rand4Digits . '.' . $filePathInfo['extension'];
        $filePath = $fileRoot . $filePartialPath;
        if (strpos($filePath, $fileRoot) !== 0) {
            $this->prepareErrorOut($db, $noOutput, "Invalid Path Error.");
            $result = false;
        }

        if (!file_exists($fileRoot . $dirPath)) {
            $result = mkdir($fileRoot . $dirPath, 0755, true);
            if (!$result) {
                $this->prepareErrorOut($db, $noOutput, "Can't make directory. [{$dirPath}]");
                $result = false;
            }
        }
        //exec("chmod -R o+x " . escapeshellcmd($fileRoot));
        return [$result, $filePath, $filePartialPath];
    }

    public function processing($db, $url, $options, $files, $noOutput,
                               $field, $contextname, $keyfield, $keyvalue, $datasource, $dbspec, $debug)
    {
        $counter = -1;
        foreach ($files as $fn => $fileInfo) {
            $counter += 1;
            list($fileInfoName, $fileInfoTemp) = $this->getFileNames($fileInfo);
            $filePathInfo = pathinfo(IMUtil::removeNull(basename($fileInfoName)));
            $targetFieldName = $field[$counter];

            if ($targetFieldName == "_im_csv_upload") {    // CSV File uploading
                $this->csvImportOperation($db, $datasource, $options, $dbspec, $debug, $contextname, $fileInfoTemp);
            } else {
                if (!isset($options['media-root-dir'])) { // Check the 'media-root-dir'.
                    if (!is_null($url)) {
                        header('Location: ' . $url);
                    } else {
                        $this->prepareErrorOut($db, $noOutput, "'media-root-dir' isn't specified");
                    }
                    return;
                }
                list($result, $filePath, $filePartialPath) = $this->decideFilePath($db, $noOutput, $options,
                    $contextname, $keyfield, $keyvalue, $targetFieldName, $filePathInfo);
                if ($result === false) {
                    return;
                }
                $result = move_uploaded_file(IMUtil::removeNull($fileInfoTemp), $filePath);
                if (!$result) {
                    if (!is_null($url)) {
                        header('Location: ' . $url);
                    } else {
                        $this->prepareErrorOut($db, $noOutput, "Fail to move the uploaded file in the media folder.");
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
            }
            return; // Stop this loop just once.
        }
    }

    private
    function justfyPathComponent($str, $mode = "default")
    {
        $jStr = $str;
        switch ($mode) {
            case "assjis":
                $jStr = mb_convert_encoding($jStr, "SJIS", "UTF-8");
                $jStr = mb_convert_encoding($jStr, "UTF-8", "SJIS");
                $jStr = str_replace(DIRECTORY_SEPARATOR, '_', str_replace('.', '_', $jStr ?? ""));
                break;
            case "asucs4":
                $jStr = mb_convert_encoding($jStr, "UCS-4", "UTF-8");
                $jStr = mb_convert_encoding($jStr, "UTF-8", "UCS-4");
                $jStr = str_replace(DIRECTORY_SEPARATOR, '_', str_replace('.', '_', $jStr ?? ""));
                break;
            default:
                $jStr = str_replace('.', '_', urlencode($jStr) ?? "");
                break;
        }
        return $jStr;
    }

    /**
     * @param $db
     * @param $datasource
     * @param $options
     * @param $dbspec
     * @param $debug
     * @param $contextname
     * @param $fileInfoTemp
     */
    private
    function csvImportOperation($db, $datasource, $options, $dbspec, $debug, $contextname, $fileInfoTemp)
    {
        $dbContext = $db->dbSettings->getDataSourceTargetArray();
        [$import1stLine, $importSkipLines, $importFormat, $useReplace, $convert2Number, $convert2Date, $convert2DateTime]
            = Params::getParameterValue(["import1stLine", "importSkipLines", "importFormat", "useReplace",
            "convert2Number", "convert2Date", "convert2DateTime"], [true, 0, 'CSV', false, [], [], [],]);
//        $params = IMUtil::getFromParamsPHPFile(
//            ["import1stLine", "importSkipLines", "importFormat", "useReplace",
//                "convert2Number", "convert2Date", "convert2DateTime"], true);
        $import1stLine = (isset($dbContext['import']) && isset($dbContext['import']['1st-line']))
            ? $dbContext['import']['1st-line']
            : ((isset($options['import']) && isset($options['import']['1st-line']))
                ? $options['import']['1st-line'] : $import1stLine);
        $importSkipLines = (isset($dbContext['import']) && isset($dbContext['import']['skip-lines']))
            ? $dbContext['import']['skip-lines']
            : (intval((isset($options['import']) && isset($options['import']['skip-lines']))
                ? $options['import']['skip-lines'] : $importSkipLines));
        $importFormat = (isset($dbContext['import']) && isset($dbContext['import']['format']))
            ? $dbContext['import']['format']
            : ((isset($options['import']) && isset($options['import']['format']))
                ? $options['import']['format'] : $importFormat);
        $separator = (strtolower($importFormat) == 'tsv') ? "\t" : ",";
        $useReplace = boolval((isset($dbContext['import']) && isset($dbContext['import']['use-replace']))
            ? $dbContext['import']['use-replace']
            : ((isset($options['import']) && isset($options['import']['use-replace']))
                ? $options['import']['use-replace'] : $useReplace));
        $convert2Number = (isset($dbContext['import']) && isset($dbContext['import']['convert-number']))
            ? $dbContext['import']['convert-number']
            : ((isset($options['import']) && isset($options['import']['convert-number']))
                ? $options['import']['convert-number'] : $convert2Number);
        $convert2Number = is_array($convert2Number) ? $convert2Number : [];
        $convert2Date = (isset($dbContext['import']) && isset($dbContext['import']['convert-date']))
            ? $dbContext['import']['convert-date']
            : ((isset($options['import']) && isset($options['import']['convert-date']))
                ? $options['import']['convert-date'] : $convert2Date);
        $convert2Date = is_array($convert2Date) ? $convert2Date : [];
        $convert2DateTime = (isset($dbContext['import']) && isset($dbContext['import']['convert-datetime']))
            ? $dbContext['import']['convert-datetime']
            : ((isset($options['import']) && isset($options['import']['convert-datetime']))
                ? $options['import']['convert-datetime'] : $convert2DateTime);
        $convert2DateTime = is_array($convert2DateTime) ? $convert2DateTime : [];

        $decimalPoint = ord(IMLocaleFormatTable::getCurrentLocaleFormat()['mon_decimal_point']);
        if (!$decimalPoint) {
            $decimalPoint = ord(IMLocaleFormatTable::getCurrentLocaleFormat()['decimal_point']);
            if (!$decimalPoint) {
                $decimalPoint = ord('.');
            }
        }
        $zeroCode = ord('0');
        $nineCode = ord('9');

        $db->ignoringPost();
        $db->initialize($datasource, $options, $dbspec, $debug, $contextname);

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
                            if ($field !== '_') { // The '_' field is gonna ignore.
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
                    }
                    $db->processingRequest($useReplace ? "replace" : "create", true, true);
                    //$createdKeys[] = [$dbContext['key'] => ($db->getDatabaseResult()[0])[$dbContext['key']]];
                }
                $is1stLine = false;
            }
            if (count($db->logger->getErrorMessages()) > 0) {
                $db->logger->setWarningMessage(
                    "\nCan't read line: " . substr($line, 0, min(20, strlen($line))) . "...");
                $db->logger->clearErrorLog();
            }
        }
        unlink(IMUtil::removeNull($fileInfoTemp));
        $db->outputOfProcessing['dbresult'] = $createdKeys;
    }
}