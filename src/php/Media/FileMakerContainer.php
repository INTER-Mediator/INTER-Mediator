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

use Exception;
use INTERMediator\IMUtil;
use INTERMediator\DB\Proxy;
use INTERMediator\Params;

/**
 * FileMakerContainer handles file upload and download for FileMaker container fields.
 * Implements UploadingSupport and DownloadingSupport interfaces for FileMaker integration.
 */
class FileMakerContainer extends UploadingSupport implements DownloadingSupport
{
    /** Retrieves the contents of a file from a FileMaker container field or direct file path.
     * @param string $file The file name (unused).
     * @param string $target The file path or FileMaker container URL.
     * @param Proxy $dbProxyInstance The database proxy instance.
     * @return string The file contents.
     * @throws Exception If the file cannot be retrieved.
     */
    public function getMedia(string $file, string $target, Proxy $dbProxyInstance): string
    {
        $parsedUrl = parse_url($target);
        if (get_class($dbProxyInstance->dbClass) === 'INTERMediator\DB\FileMaker_DataAPI') { // for FileMaker Data API
            if (isset($parsedUrl['host']) && $parsedUrl['host'] === 'localserver') { // Set As 'localserver'
                $target = 'http://' . $parsedUrl['user'] . ':' . $parsedUrl['pass'] . '@127.0.0.1:1895' . $parsedUrl['path'] . '?' . $parsedUrl['query'];
                if (function_exists('curl_init')) {
                    $session = curl_init($target);
                    curl_setopt($session, CURLOPT_HEADER, true);
                    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                    $content = curl_exec($session);
                    $headerSize = curl_getinfo($session, CURLINFO_HEADER_SIZE);
                    $headers = substr($content, 0, $headerSize);
                    $sessionKey = '';
                    $header = explode("\r\n", $headers);
                    foreach ($header as $line) {
                        if ($line) {
                            $h = explode(': ', $line);
                            if (isset($h[1]) && $h[0] == 'Set-Cookie') {
                                $sessionKey = str_replace(
                                    '; HttpOnly', '', str_replace('X-FMS-Session-Key=', '', $h[1])
                                );
                            }
                        }
                    }
                    $target = 'http://127.0.0.1:1895' . $parsedUrl['path'] . '?' . $parsedUrl['query'];
                    $headers = array('X-FMS-Session-Key: ' . $sessionKey);
                    $session = curl_init($target);
                    curl_setopt($session, CURLOPT_HEADER, false);
                    curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                    $content = curl_exec($session);
                } else {
                    throw new Exception("CURL doesn't installed here.");
                }
            } else { // Other settings
                $dbProxyInstance->dbClass->setupFMDataAPIforDB(NULL, 1);
                $content = base64_decode($dbProxyInstance->dbClass->getFMDataInstance()->getContainerData($target));
            }
        } else if (intval(get_cfg_var('allow_url_fopen')) === 1) {
            $content = file_get_contents($target);
        } else {
            if (function_exists('curl_init')) {
                $session = curl_init($target);
                curl_setopt($session, CURLOPT_HEADER, false);
                curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                $content = curl_exec($session);
            } else {
                throw new Exception("CURL doesn't installed here.");
            }
        }
        return $content;
    }

    /** Returns the base file name from a given file path, removing query parameters if present and decoding spaces.
     * @param string $file The file path.
     * @return string|null The base file name.
     */
    public function getFileName(string $file): ?string
    {
        $fileName = basename($file);
        $qPos = strpos($fileName, "?");
        if ($qPos !== false) {
            $fileName = str_replace("%20", " ", substr($fileName, 0, $qPos));
        }
        return $fileName;
    }

    /** Handles file upload processing for FileMaker container fields.
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
        $mediaRootDir = $options['media-root-dir'] ?? Params::getParameterValue('mediaRootDir', null) ?? null;
        if (!$mediaRootDir) {
            if (!is_null($url)) {
                header('Location: ' . $url);
            } else {
                $db->logger->setErrorMessage("'media-root-dir' isn't specified");
                $db->processingRequest("nothing");
                if (!$noOutput) {
                    $db->finishCommunication();
                    $db->exportOutputDataAsJSON();
                }
            }
            return;
        }

        $counter = -1;
        foreach ($files as $fileInfo) {
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
                    $db->processingRequest("nothing");
                    if (!$noOutput) {
                        $db->finishCommunication();
                        $db->exportOutputDataAsJSON();
                    }
                }
                return;
            }

            $db = new Proxy();
            $db->initialize($dataSource, $options, $dbSpec, $debug, $contextName);
            $db->dbSettings->addExtraCriteria($keyField, "=", $keyValue);
            $db->dbSettings->setFieldsRequired(array($targetFieldName));

            // If the file content is a base64 encoded url starting with 'data:,', decode it and store a file.
            $fileContent = file_get_contents($filePath, false, null, 0, 30);
            $headerTop = strpos($fileContent, "data:");
            $endOfHeader = strpos($fileContent, ",");
            if ($headerTop === 0 && $endOfHeader > 0) {
                $tempFilePath = $filePath . ".temp";
                rename($filePath, $tempFilePath);
                $step = 1024;
                if (str_contains($fileContent, ";base64")) {
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
            if ($dbSpec['db-class'] === 'FileMaker_FX') {
                $db->addOutputData('dbresult',
                    '/fmi/xml/cnt/' . $fileName .
                    '?-db=' . urlencode($db->dbSettings->getDbSpecDatabase()) .
                    '&-lay=' . urlencode($dataSource[0]['name']) .
                    '&-recid=' . intval($keyValue) .
                    '&-field=' . urlencode($targetFieldName));
            } else if ($dbSpec['db-class'] === 'FileMaker_DataAPI') {
                $layout = $dataSource[0]['name'];
                $db->dbClass->setupFMDataAPIforDB($layout, urlencode($targetFieldName));
                $result = $db->dbClass->getFMDataInstance()->{$layout}->query(NULL, NULL, 1, 1);
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