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

use DateTime;
use Exception;
use INTERMediator\IMUtil;
use INTERMediator\DB\Proxy;
use INTERMediator\Locale\IMLocaleFormatTable;
use INTERMediator\Params;

/**
 * FileSystem class handles file uploads, downloads, and CSV import operations for files stored on the server filesystem.
 * Implements UploadingSupport and DownloadingSupport interfaces for media handling.
 */
class FileSystem extends UploadingSupport implements DownloadingSupport
{
    /**
     * Handles file upload processing, including CSV import if specified.
     *
     * @param Proxy $db The database proxy instance.
     * @param string|null $url The redirect URL on error.
     * @param array|null $options Additional options for processing.
     * @param array $files Uploaded files array.
     * @param bool $noOutput Whether to suppress output.
     * @param array $field Array of target field names.
     * @param string $contextName The context name for processing.
     * @param string|null $keyField The key field for database update.
     * @param string|null $keyValue The key value for database update.
     * @param array|null $dataSource Data source definition.
     * @param array|null $dbSpec Database specification.
     * @param int $debug Debug level.
     * @throws Exception If an error occurs during processing.
     * @return void
     */
    public function processing(Proxy  $db, ?string $url, ?array $options, array $files, bool $noOutput, array $field,
                               string $contextName, ?string $keyField, ?string $keyValue,
                               ?array $dataSource, ?array $dbSpec, int $debug): void
    {
        $counter = -1;
        foreach ($files as $fileInfo) {
            $counter += 1;
            list($fileInfoName, $fileInfoTemp) = $this->getFileNames($fileInfo);
            $filePathInfo = pathinfo(IMUtil::removeNull(basename($fileInfoName)));
            $targetFieldName = $field[$counter];

            if ($targetFieldName == "_im_csv_upload") {    // CSV File uploading
                $this->csvImportOperation($db, $dataSource, $options, $dbSpec, $debug, $contextName, $fileInfoTemp);
            } else {  // Any kind of files uploaded.
                list($result, $filePath, $filePartialPath) = $this->decideFilePath($db, $noOutput, $options,
                    $contextName, $keyField, $keyValue, $targetFieldName, $filePathInfo);
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
                $this->processingFile($db, $options, $filePath, $filePartialPath, $targetFieldName,
                    $keyField, $keyValue, $dataSource, $dbSpec, $debug);
            }
            return; // Stop this loop just once.
        }
    }

    /**
     * Retrieves the contents of a file from a given target path.
     *
     * @param string $file The file name (unused).
     * @param string $target The file path to read.
     * @param Proxy $dbProxyInstance The database proxy instance.
     * @return string The file contents.
     * @throws Exception If the file does not exist.
     */
    public function getMedia(string $file, string $target, Proxy $dbProxyInstance): string
    {
        if (!empty($file) && !file_exists($target)) {
            throw new Exception("[INTER-Mediator] The file does't exist: {$target}.");
        }
        return file_get_contents($target);
    }

    /**
     * Returns the base file name from a given file path, removing query parameters if present.
     *
     * @param string $file The file path.
     * @return string|null The base file name.
     */
    public function getFileName(string $file): ?string
    {
        $fileName = basename($file);
        $qPos = strpos($fileName, "?");
        if ($qPos !== false) {
            $fileName = substr($fileName, 0, $qPos);
        }
        return $fileName;
    }

    /**
     * Extracts file name and temporary name from file info array.
     *
     * @param array $info The file info array.
     * @return array Array containing the file name and temporary file name.
     */
    private function getFileNames(array $info): array
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

    /**
     * Outputs an error message and stops further processing.
     *
     * @param Proxy $db The database proxy instance.
     * @param bool $noOutput Whether to suppress output.
     * @param string $errorMsg The error message to output.
     * @return void
     * @throws Exception
     */
    private function prepareErrorOut(Proxy $db, bool $noOutput, string $errorMsg): void
    {
        $db->logger->setErrorMessage($errorMsg);
        $db->processingRequest("nothing");
        if (!$noOutput) {
            $db->finishCommunication();
            $db->exportOutputDataAsJSON();
        }
    }

    /**
     * Determines the file path and partial path for storing an uploaded file.
     *
     * @param Proxy $db The database proxy instance.
     * @param bool $noOutput Whether to suppress output.
     * @param array|null $options Additional options for processing.
     * @param string $contextName The context name.
     * @param string $keyField The key field name.
     * @param string $keyValue The key value.
     * @param string $targetFieldName The target field name.
     * @param array $filePathInfo Information about the file path.
     * @return array Array containing result status, full file path, and partial file path.
     * @throws Exception If the path is invalid or directory creation fails.
     */
    private function decideFilePath(Proxy  $db, bool $noOutput, ?array $options, string $contextName,
                                    string $keyField, string $keyValue, string $targetFieldName, array $filePathInfo): array
    {
        $result = true;
        $fileRoot = $options['media-root-dir'] ?? Params::getParameterValue('mediaRootDir', null) ?? null;
        if (substr($fileRoot, strlen($fileRoot) - 1, 1) != '/') {
            $fileRoot .= '/';
        }
        $uploadFilePathMode = Params::getParameterValue("uploadFilePathMode", null);

        $dirPath = $this->justifyPathComponent($contextName, $uploadFilePathMode) . DIRECTORY_SEPARATOR
            . $this->justifyPathComponent($keyField, $uploadFilePathMode) . "="
            . $this->justifyPathComponent($keyValue, $uploadFilePathMode) . DIRECTORY_SEPARATOR
            . $this->justifyPathComponent($targetFieldName, $uploadFilePathMode);
        try {
            $rand4Digits = random_int(1000, 9999);
        } catch (Exception $ex) {
            $rand4Digits = rand(1000, 9999);
        }
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
        return [$result, $filePath, $filePartialPath];
    }

    /**
     * Justifies and encodes a path component based on the specified mode.
     *
     * @param string $str The string to justify.
     * @param string|null $mode The encoding mode (default, assjis, asucs4).
     * @return string The justified path component.
     */
    private function justifyPathComponent(string $str, ?string $mode = "default"): string
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

    /**
     * Handles CSV import operation from an uploaded file.
     *
     * @param Proxy $db The database proxy instance.
     * @param array|null $dataSource Data source definition.
     * @param array|null $options Additional options for processing.
     * @param array|null $dbSpec Database specification.
     * @param int $debug Debug level.
     * @param string $contextName The context name.
     * @param string $fileInfoTemp The temporary file name of the uploaded CSV.
     * @return void
     * @throws Exception If an error occurs during CSV import.
     */
    private function csvImportOperation($db, $dataSource, $options, $dbSpec, $debug, $contextName, $fileInfoTemp)
    {
        $dbContext = $db->dbSettings->getDataSourceTargetArray();
        [$import1stLine, $importSkipLines, $importFormat, $useReplace,
            $convert2Number, $convert2Date, $convert2DateTime, $encoding]
            = Params::getParameterValue(["import1stLine", "importSkipLines", "importFormat", "useReplace",
            "convert2Number", "convert2Date", "convert2DateTime", "encoding"], [true, 0, 'CSV', false, [], [], [], null,]);
        $import1stLine = (isset($dbContext['import']['1st-line']))
            ? $dbContext['import']['1st-line']
            : ((isset($options['import']['1st-line']))
                ? $options['import']['1st-line'] : $import1stLine);
        $importSkipLines = (isset($dbContext['import']['skip-lines']))
            ? $dbContext['import']['skip-lines']
            : (intval((isset($options['import']['skip-lines']))
                ? $options['import']['skip-lines'] : $importSkipLines));
        $importFormat = (isset($dbContext['import']['format']))
            ? $dbContext['import']['format']
            : ((isset($options['import']['format']))
                ? $options['import']['format'] : $importFormat);
        $separator = (strtolower($importFormat) == 'tsv') ? "\t" : ",";
        $useReplace = boolval((isset($dbContext['import']['use-replace']))
            ? $dbContext['import']['use-replace']
            : ((isset($options['import']['use-replace']))
                ? $options['import']['use-replace'] : $useReplace));
        $convert2Number = (isset($dbContext['import']['convert-number']))
            ? $dbContext['import']['convert-number']
            : ((isset($options['import']['convert-number']))
                ? $options['import']['convert-number'] : $convert2Number);
        $convert2Number = is_array($convert2Number) ? $convert2Number : [];
        $convert2Date = (isset($dbContext['import']['convert-date']))
            ? $dbContext['import']['convert-date']
            : ((isset($options['import']['convert-date']))
                ? $options['import']['convert-date'] : $convert2Date);
        $convert2Date = is_array($convert2Date) ? $convert2Date : [];
        $convert2DateTime = (isset($dbContext['import']['convert-datetime']))
            ? $dbContext['import']['convert-datetime']
            : ((isset($options['import']['convert-datetime']))
                ? $options['import']['convert-datetime'] : $convert2DateTime);
        $convert2DateTime = is_array($convert2DateTime) ? $convert2DateTime : [];
        $encoding = (isset($dbContext['import']['encoding']))
            ? $dbContext['import']['encoding']
            : ((isset($options['import']['encoding']))
                ? $options['import']['encoding'] : $encoding);
        if ($encoding) {
            $db->logger->setDebugMessage("[FileSystem::csvImportOperation] Encoding {$encoding} is selected.", 2);
        }

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
        $db->initialize($dataSource, $options, $dbSpec, $debug, $contextName);

        $importingFields = [];
        if (is_string($import1stLine)) {
            foreach (new FieldDivider($import1stLine, $separator) as $field) {
                $importingFields[] = trim($field);
            }
        }
        $is1stLine = true;
        $createdKeys = [];
        $fileContent = file_get_contents(IMUtil::removeNull($fileInfoTemp));
        if ($encoding) {
            $fileContent = mb_convert_encoding($fileContent, "UTF-8", $encoding);
        }
        foreach (new LineDivider($fileContent) as $line) {
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
                            if ($field !== '_') { // The '_' field is going to ignore.
                                if (in_array($field, $convert2Number)) {
                                    $original = $value;
                                    $value = '';
                                    for ($i = 0; $i < strlen($original); $i++) {
                                        $c = ord(substr($original, $i, 1));
                                        if (($c >= $zeroCode and $c <= $nineCode) || $c == $decimalPoint) {
                                            $value .= chr($c);
                                        }
                                    }
                                }
                                if (in_array($field, $convert2Date)) {
                                    try {
                                        $dt = new DateTime($value);
                                    } catch (Exception $ex) {
                                        $dt = new DateTime("0001-01-01 00:00:00");
                                    }
                                    $value = $dt->format('Y-m-d');
                                }
                                if (in_array($field, $convert2DateTime)) {
                                    try {
                                        $dt = new DateTime($value);
                                    } catch (Exception $ex) {
                                        $dt = new DateTime("0001-01-01 00:00:00");
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