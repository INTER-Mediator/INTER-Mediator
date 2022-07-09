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

use Spatie\Dropbox\Client;
use msyk\DropboxAPIShortLivedToken\AutoRefreshingDropBoxTokenService;
use INTERMediator\DB\Proxy;
use INTERMediator\IMUtil;
use INTERMediator\Params;

class Dropbox implements UploadingSupport
{
    private $appKey = null;
    private $appSecret = null;
    private $refreshToken = null;
    private $accessTokenPath = null;
    private $rootInDropbox = null;

    public function __construct()
    {
        $this->appKey = Params::getParameterValue('dropboxAppKey', '');
        $this->appSecret = Params::getParameterValue('dropboxAppSecret', '');
        $this->refreshToken = Params::getParameterValue('dropboxRefreshToken', '');
        $this->accessTokenPath = Params::getParameterValue('dropboxAccessTokenPath', '');
        $this->rootInDropbox = Params::getParameterValue('rootInDropbox', '/');
    }

    public function processing($db, $url, $options, $files, $noOutput, $field, $contextname, $keyfield, $keyvalue, $datasource, $dbspec, $debug)
    {
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

            $db = new Proxy();
            $db->initialize($datasource, $options, $dbspec, $debug, $contextname);
            $db->dbSettings->addExtraCriteria($keyfield, "=", $keyvalue);
            $db->dbSettings->setFieldsRequired(array($targetFieldName));
            $db->dbSettings->setValue(array($storedURL));
            $db->processingRequest("update", true);
            $dbProxyRecord = $db->getDatabaseResult();

            $relatedContext = null;
            if (isset($dbProxyContext['file-upload'])) {
                foreach ($dbProxyContext['file-upload'] as $item) {
                    if (isset($item['field']) && $item['field'] == $targetFieldName) {
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
                        $values[] = $storedURL;
                        $relatedContext->dbSettings->setFieldsRequired($fields);
                        $relatedContext->dbSettings->setValue($values);
                        $relatedContext->processingRequest("create", true, true);
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
                var_export($ex->getMessage());
            }
        }
    }
}
