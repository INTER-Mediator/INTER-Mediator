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
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Credentials\Credentials;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;
use INTERMediator\Params;

/**
 * AWSS3 class handles file upload and download operations with Amazon S3.
 * Implements UploadingSupport and DownloadingSupport interfaces for integration with INTER-Mediator.
 */
class AWSS3 extends UploadingSupport implements DownloadingSupport
{
    /** AWS region for S3 operations.
     * @var string|null
     */
    private ?string $accessRegion;
    /** Root S3 bucket name.
     * @var string|null
     */
    private ?string $rootBucket;
    /** ACL to apply to uploaded S3 objects.
     * @var string|null
     */
    private ?string $applyingACL;
    /** Whether secret credentials are supplied directly.
     * @var bool
     */
    private bool $isSuppliedSecret;
    /** AWS access key for S3.
     * @var string|null
     */
    private ?string $s3AccessKey;
    /** AWS secret access key for S3.
     * @var string|null
     */
    private ?string $s3AccessSecret;
    /** AWS credentials profile for S3.
     * @var string|null
     */
    private ?string $s3AccessProfile;
    /** Whether to customize S3 URL format.
     * @var bool
     */
    private bool $s3urlCustomize;
    /** The file name of the current file being processed or retrieved.
     * @var string|null
     */
    private ?string $fileName = null;

    /** AWSS3 constructor. Initializes S3 credentials and configuration from parameters.
     */
    public function __construct()
    {
        $this->accessRegion = Params::getParameterValue("accessRegion", null);
        $this->rootBucket = Params::getParameterValue("rootBucket", null);
        $this->applyingACL = Params::getParameterValue("applyingACL", null);
        $this->s3AccessProfile = Params::getParameterValue("s3AccessProfile", null);
        $this->s3AccessKey = IMUtil::getFromProfileIfAvailable(Params::getParameterValue("s3AccessKey", null));
        $this->s3AccessSecret = IMUtil::getFromProfileIfAvailable(Params::getParameterValue("s3AccessSecret", null));
        $this->s3urlCustomize = Params::getParameterValue("s3urlCustomize", true);
        $this->isSuppliedSecret = $this->s3AccessKey && $this->s3AccessSecret;
    }

    /** Retrieves the contents of a file from Amazon S3.
     * @param string $file The file name (unused, for interface compatibility).
     * @param string $target The S3 file path or URL.
     * @param Proxy $dbProxyInstance The database proxy instance.
     * @return string The file contents.
     * @throws Exception If the file cannot be retrieved.
     */
    public function getMedia(string $file, string $target, Proxy $dbProxyInstance): string
    {
        $startOfPath = strpos($target, "/", 5);
        $urlPath = substr($target, $startOfPath + 1);
        $this->fileName = str_replace("+", "%20", urlencode(basename($urlPath)));
        $clientArgs = ['version' => 'latest', 'region' => $this->accessRegion];
        if ($this->s3AccessProfile) {
            $clientArgs['profile'] = $this->s3AccessProfile;
        } else if ($this->s3AccessKey && $this->s3AccessSecret) {
            $clientArgs['credentials'] = new Credentials($this->s3AccessKey, $this->s3AccessSecret);
        }
        $s3 = new S3Client($clientArgs);
        $objectSpec = ['Bucket' => $this->rootBucket, 'Key' => $urlPath,];
        $result = $s3->getObject($objectSpec);
        if (interface_exists($result['Body'], 'Psr\Http\Message\StreamInterface')) {
            $content = $result['Body']->getContents(); // @phpstan-ignore method.nonObject
        } else {
            $content = $result['Body'];
        }
        return $content;
    }

    /** Returns the file name of the last accessed or processed file.
     * @param string $file The file path (unused).
     * @return string|null The file name, or null if not set.
     */
    public function getFileName(string $file): ?string
    {
        return $this->fileName;
    }

    /** Handles file upload processing to Amazon S3.
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
            if (is_array($fileInfo['name'])) {   // JQuery File Upload Style
                $fileInfoName = $fileInfo['name'][0];
                $fileInfoTemp = $fileInfo['tmp_name'][0];
            } else {
                $fileInfoName = $fileInfo['name'];
                $fileInfoTemp = $fileInfo['tmp_name'];
            }
            if (!is_uploaded_file($fileInfoTemp)) { // Security check
                return;
            }
            $filePathInfo = pathinfo(IMUtil::removeNull(basename($fileInfoName)));
            $targetFieldName = $field[$counter];
            $dirPath = $contextName . DIRECTORY_SEPARATOR
                . $keyField . "=" . $keyValue . DIRECTORY_SEPARATOR . $targetFieldName;
            try {
                $rand4Digits = random_int(1000, 9999);
            } catch (Exception $ex) {
                $rand4Digits = rand(1000, 9999);
            }
            $objectKey = $dirPath . '/' . $filePathInfo['filename'] . '_' . $rand4Digits . '.' . $filePathInfo['extension'];

            $clientArgs = ['version' => 'latest', 'region' => $this->accessRegion];
            if ($this->s3AccessProfile) {
                $clientArgs['profile'] = $this->s3AccessProfile;
            } else if ($this->isSuppliedSecret) {
                $clientArgs['credentials'] = new Credentials($this->s3AccessKey, $this->s3AccessSecret);
            }

            $s3 = new S3Client($clientArgs); // Initialize S3 client
            $objectSpec = [
                'Bucket' => $this->rootBucket,
                'Key' => $objectKey,
                'Body' => file_get_contents($fileInfoTemp),
                'ACL' => $this->applyingACL
            ];
            try {
                $result = $s3->putObject($objectSpec); // Store to S3
            } catch (S3Exception $e) {
                if (!is_null($url)) {
                    header('Location: ' . $url);
                } else {
                    $db->logger->setErrorMessage($e->getMessage());
                    $db->processingRequest("nothing");
                    if (!$noOutput) {
                        $db->finishCommunication();
                        $db->exportOutputDataAsJSON();
                    }
                }
                return;
            }
            unlink($fileInfoTemp); // Remove upload file

            $schemaInUrl = "https://";
            if ($this->s3urlCustomize && str_starts_with($result['ObjectURL'], $schemaInUrl)) {
                $storedURL = str_replace($schemaInUrl, "s3://", $result['ObjectURL'] ?? "");
            } else {
                $storedURL = $result['ObjectURL'];
            }
            $this->processingFile($db, $options, $storedURL, $storedURL, $targetFieldName,
                $keyField, $keyValue, $dataSource, $dbSpec, $debug);
        }
    }
}

/*
 * result example:
 * Aws\Result::__set_state(array(
'data' =>
<...>
*/