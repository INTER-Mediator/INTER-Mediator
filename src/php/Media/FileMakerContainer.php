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

class FileMakerContainer implements UploadingSupport
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

            $db->dbSettings->setValue(array($fileName . "\n" .
                base64_encode(file_get_contents($filePath))));

            $db->processingRequest("update", true);
            $dbProxyRecord = $db->getDatabaseResult();

            $relatedContext = null;

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