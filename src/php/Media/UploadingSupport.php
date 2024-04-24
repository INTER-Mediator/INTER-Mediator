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
use INTERMediator\DB\Proxy;

/**
 *
 */
abstract class UploadingSupport
{
    /**
     * @param Proxy $db
     * @param array|null $options
     * @param string $filePath
     * @param string $filePartialPath
     * @param string $targetFieldName
     * @param string|null $keyField
     * @param string|null $keyValue
     * @param array|null $dataSource
     * @param array|null $dbSpec
     * @param int $debug
     * @throws Exception
     */
    public function processingFile(Proxy  $db, ?array $options, string $filePath, string $filePartialPath,
                                   string $targetFieldName, ?string $keyField, ?string $keyValue,
                                   ?array $dataSource, ?array $dbSpec, int $debug): void
    {
        $dbProxyContext = $db->dbSettings->getDataSourceTargetArray();
        if (isset($dbProxyContext['file-upload'])) {
            foreach ($dbProxyContext['file-upload'] as $item) {
                if (isset($item['field']) && !isset($item['context'])) {
                    $targetFieldName = $item['field'];
                }
            }
        }

        $db->dbSettings->addExtraCriteria($keyField, "=", $keyValue);
        $db->dbSettings->setFieldsRequired(array($targetFieldName));
        $db->dbSettings->setValue(array($filePartialPath));
        $db->processingRequest("update"/*,true*/);
        $dbProxyRecord = $db->getDatabaseResult();

        $db->logger->setDebugMessage("[FileSystem::processing] dbProxyRecord=" . var_export($dbProxyRecord, true), 2);
        $db->logger->setDebugMessage("[FileSystem::processing] dbProxyContext=" . var_export($dbProxyContext, true), 2);

        $db->addOutputData('dbresult', $filePath);
        $db->finishCommunication();
        if (isset($dbProxyContext['file-upload'])) {
            foreach ($dbProxyContext['file-upload'] as $item) {
                if ($item['field'] == $targetFieldName) {
                    $relatedContext = new Proxy();
                    $relatedContext->initialize($dataSource, $options, $dbSpec, $debug, $item['context'] ?? null);
                    $relatedContextInfo = $relatedContext->dbSettings->getDataSourceTargetArray();
                    $db->logger->setDebugMessage("[FileSystem::processing] context={$item['context']} relatedContextInfo=" . var_export($relatedContextInfo, true), 2);
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
                }
            }
        }
    }
}
