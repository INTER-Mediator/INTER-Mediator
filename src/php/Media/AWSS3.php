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

class AWSS3 implements UploadingSupport, DownloadingSupport
{
    /**
     * @var ?string
     */
    private ?string $accessRegion;
    /**
     * @var ?string
     */
    private ?string $rootBucket;
    /**
     * @var ?string
     */
    private ?string $applyingACL;
    /**
     * @var bool
     */
    private bool $isSuppliedSecret;
    /**
     * @var ?string
     */
    private ?string $s3AccessKey;
    /**
     * @var ?string
     */
    private ?string $s3AccessSecret;
    /**
     * @var ?string
     */
    private ?string $s3AccessProfile;
    /**
     * @var bool
     */
    private bool $s3urlCustomize;
    /**
     * @var ?string
     */
    private ?string $fileName = null;

    /**
     *
     */
    public function __construct()
    {
        $this->accessRegion = Params::getParameterValue("accessRegion", null);
        $this->rootBucket = Params::getParameterValue("rootBucket", null);
        $this->applyingACL = Params::getParameterValue("applyingACL", null);
        $this->s3AccessProfile = Params::getParameterValue("s3AccessProfile", null);
        $this->s3AccessKey = Params::getParameterValue("s3AccessKey", null);
        $this->s3AccessSecret = Params::getParameterValue("s3AccessSecret", null);
        $this->s3urlCustomize = Params::getParameterValue("s3urlCustomize", true);
        $this->isSuppliedSecret = $this->s3AccessKey && $this->s3AccessSecret;
    }

    /**
     * @param string $file
     * @param string $target
     * @param Proxy $dbProxyInstance
     * @return string
     */
    public function getMedia(string $file, string $target, Proxy $dbProxyInstance): string
    {
        $startOfPath = strpos($target, "/", 5);
        $urlPath = substr($target, $startOfPath + 1);
        $this->fileName = str_replace("+", "%20", urlencode(basename($urlPath)) ?? "");
        $clientArgs = ['version' => 'latest', 'region' => $this->accessRegion];
        if ($this->s3AccessProfile) {
            $clientArgs['profile'] = $this->s3AccessProfile;
        } else if ($this->s3AccessKey && $this->s3AccessSecret) {
            $clientArgs['credentials'] = new Credentials($this->s3AccessKey, $this->s3AccessSecret);
        }
        $s3 = new S3Client($clientArgs);
        $objectSpec = ['Bucket' => $this->rootBucket, 'Key' => $urlPath,];
        try {
            $result = $s3->getObject($objectSpec);
        } catch (S3Exception $e) {
            throw $e;
        }
        if (interface_exists($result['Body'], 'Psr\Http\Message\StreamInterface')) {
            $content = $result['Body']->getContents();
        } else {
            $content = $result['Body'];
        }
        return $content;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getFileName(string $file): string
    {
        return $this->fileName;
    }

    /**
     * @param Proxy $db
     * @param ?string $url
     * @param array|null $options
     * @param array $files
     * @param bool $noOutput
     * @param array $field
     * @param string $contextname
     * @param ?string $keyfield
     * @param ?string $keyvalue
     * @param array|null $datasource
     * @param array|null $dbspec
     * @param int $debug
     * @throws
     */
    public function processing(Proxy $db, ?string $url, ?array $options, array $files, bool $noOutput, array $field,
                               string  $contextname, ?string $keyfield, ?string $keyvalue,
                               ?array  $datasource, ?array $dbspec, int $debug):void    {
        $dbAlt = new Proxy();

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
            $dirPath = $contextname . DIRECTORY_SEPARATOR
                . $keyfield . "=" . $keyvalue . DIRECTORY_SEPARATOR . $targetFieldName;
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
                    $db->processingRequest("noop");
                    if (!$noOutput) {
                        $db->finishCommunication();
                        $db->exportOutputDataAsJSON();
                    }
                }
                return;
            }
            unlink($fileInfoTemp); // Remove upload file

            $schemaInUrl = "https://";
            if ($this->s3urlCustomize && strpos($result['ObjectURL'], $schemaInUrl) === 0) {
                $storedURL = str_replace($schemaInUrl, "s3://", $result['ObjectURL'] ?? "");
            } else {
                $storedURL = $result['ObjectURL'];
            }

            $dbProxyContext = $db->dbSettings->getDataSourceTargetArray();
            if (isset($dbProxyContext['file-upload'])) {
                foreach ($dbProxyContext['file-upload'] as $item) {
                    if (isset($item['field']) && !isset($item['context'])) {
                        $targetFieldName = $item['field'];
                    }
                }
            }

            $dbAlt->initialize($datasource, $options, $dbspec, $debug, $contextname);
            $dbAlt->dbSettings->addExtraCriteria($keyfield, "=", $keyvalue);
            $dbAlt->dbSettings->setFieldsRequired(array($targetFieldName));
            $dbAlt->dbSettings->setValue(array($storedURL));
            $dbAlt->processingRequest("update", true);
            $dbProxyRecord = $dbAlt->getDatabaseResult();

            if (isset($dbProxyContext['file-upload'])) {
                foreach ($dbProxyContext['file-upload'] as $item) {
                    if (isset($item['field']) && $item['field'] == $targetFieldName) {
                        $dbAlt->initialize($datasource, $options, $dbspec, $debug, $item['context'] ?? null);
                        $relatedContextInfo = $dbAlt->dbSettings->getDataSourceTargetArray();
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
                        $values[] = $storedURL;
                        $dbAlt->dbSettings->setFieldsRequired($fields);
                        $dbAlt->dbSettings->setValue($values);
                        $dbAlt->processingRequest("create", true, true);
                    }
                }
            }
            $dbAlt->addOutputData('dbresult', $storedURL);
        }
    }
}

/*
 * result example:
 * Aws\Result::__set_state(array(
'data' =>
array (
'Expiration' => '',
'ETag' => '"128beb264cd57658400d28f829924e27"',
'ServerSideEncryption' => '',
'VersionId' => '',
'SSECustomerAlgorithm' => '',
'SSECustomerKeyMD5' => '',
'SSEKMSKeyId' => '',
'SSEKMSEncryptionContext' => '',
'RequestCharged' => '',
'@metadata' =>
array (
'statusCode' => 200,
'effectiveUri' => 'https://inter-mediator-developping.s3.ap-northeast-1.amazonaws.com/testtable/id%3D56/text1/%E6%A5%B5%E7%AB%AF%E3%81%AA%E6%A8%AA%E9%95%B7%E7%94%BB%E5%83%8F_8931.jpg',
'headers' =>
array (
'x-amz-id-2' => 'gdMCHVSJ8CH7FXXG6X8hJ63c8Aa1h6OL6WPteCfuTYF6o453j6CeVR1JIqRKzu1/cyN+oLETRDg=',
'x-amz-request-id' => '3380586D80672D33',
'date' => 'Thu, 01 Oct 2020 01:53:14 GMT',
'etag' => '"128beb264cd57658400d28f829924e27"',
'content-length' => '0',
'server' => 'AmazonS3',
),
'transferStats' =>
array (
'http' =>
array (
0 =>
array (
),
),
),
),
'ObjectURL' => 'https://inter-mediator-developping.s3.ap-northeast-1.amazonaws.com/testtable/id%3D56/text1/%E6%A5%B5%E7%AB%AF%E3%81%AA%E6%A8%AA%E9%95%B7%E7%94%BB%E5%83%8F_8931.jpg',
),
'monitoringEvents' =>
array (
),
))
 */
