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

use msyk\DropboxAPIShortLivedToken\DropboxClientModified;
use Spatie\Dropbox\Client;
use msyk\DropboxAPIShortLivedToken\AutoRefreshingDropBoxTokenService;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;
use INTERMediator\Params;

/**
 *
 */
class Dropbox implements UploadingSupport, DownloadingSupport
{
    /**
     * @var array|mixed
     */
    private $appKey = null;
    /**
     * @var array|mixed
     */
    private $appSecret = null;
    /**
     * @var array|mixed
     */
    private $refreshToken = null;
    /**
     * @var array|mixed
     */
    private $accessTokenPath = null;
    /**
     * @var array|mixed
     */
    private $rootInDropbox = null;
    /**
     * @var null
     */
    private $fileName = null;

    /**
     *
     */
    public function __construct()
    {
        $this->appKey = Params::getParameterValue('dropboxAppKey', '');
        $this->appSecret = Params::getParameterValue('dropboxAppSecret', '');
        $this->refreshToken = Params::getParameterValue('dropboxRefreshToken', '');
        $this->accessTokenPath = Params::getParameterValue('dropboxAccessTokenPath', '');
        $this->rootInDropbox = Params::getParameterValue('rootInDropbox', '/');
    }

    /**
     * @param $mediaAccess
     * @param $file
     * @param $target
     * @param $dbProxyInstance
     * @param $content
     * @return string
     * @throws \Exception
     */
    public function getMedia($file, $target, $dbProxyInstance)
    {
        $startOfPath = strpos($target, "/", 5);
        $urlPath = substr($target, $startOfPath + 2);
        $this->fileName = str_replace("+", "%20", urlencode(basename($urlPath)) ?? "");
        try {
            $tokenProvider = new AutoRefreshingDropBoxTokenService(
                $this->refreshToken, $this->appKey, $this->appSecret, $this->accessTokenPath);
            $client = new DropboxClientModified($tokenProvider);
            $content = $client->download($urlPath);
        } catch (\Exception $ex) {
            throw $ex;
        }
        return $content;
    }

    /**
     * @param $file
     * @return null|string
     */
    public function getFileName($file)
    {
        return $this->fileName;
    }

    /**
     * @param $db
     * @param $url
     * @param $options
     * @param $files
     * @param $noOutput
     * @param $field
     * @param $contextname
     * @param $keyfield
     * @param $keyvalue
     * @param $datasource
     * @param $dbspec
     * @param $debug
     * @return void
     */
    public function processing($db, $url, $options, $files, $noOutput, $field, $contextname, $keyfield, $keyvalue, $datasource, $dbspec, $debug)
    {
        $dbAlt = new Proxy();
        $counter = -1;
        foreach ($files as $fn => $fileInfo) { // Single file only
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
            } catch (\Exception $ex) {
                $rand4Digits = rand(1000, 9999);
            }

            $objectPath = $this->rootInDropbox . '/' . $dirPath
                . '/' . $filePathInfo['filename'] . '_' . $rand4Digits . '.' . $filePathInfo['extension'];
            $storedURL = "dropbox://$objectPath";
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
                        $dbAlt->initialize($datasource, $options, $dbspec, $debug, isset($item['context']) ? $item['context'] : null);
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
            $db->addOutputData('dbresult', $storedURL);

            try {
                $tokenProvider = new AutoRefreshingDropBoxTokenService(
                    $this->refreshToken, $this->appKey, $this->appSecret, $this->accessTokenPath);
                $client = new Client($tokenProvider);
                $client->upload($objectPath, file_get_contents($fileInfoTemp), 'add');
            } catch (\Exception $ex) {
                if (!is_null($url)) {
                    header('Location: ' . $url);
                } else {
                    $db->logger->setErrorMessage($ex->getMessage());
                    $db->processingRequest("noop");
                    if (!$noOutput) {
                        $db->finishCommunication();
                        $db->exportOutputDataAsJSON();
                    }
                }
                return;
            }
            unlink($fileInfoTemp); // Remove upload file
        }
    }
}
