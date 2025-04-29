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
 * Abstract class for supporting file upload processing in INTER-Mediator.
 * Provides a method to handle file upload and database update operations.
 */
abstract class UploadingSupport
{
    /**
     * Processes an uploaded file and updates the database record accordingly.
     *
     * @param Proxy $db The database proxy instance for performing operations.
     * @param array|null $options Additional options for processing.
     * @param string $filePath The full path to the uploaded file.
     * @param string $filePartialPath The relative path to the uploaded file to be stored in the database.
     * @param string $targetFieldName The name of the database field to update with the file path.
     * @param string|null $keyField The key field name for identifying the record to update.
     * @param string|null $keyValue The key value for identifying the record to update.
     * @param array|null $dataSource The data source definition for related context.
     * @param array|null $dbSpec The database specification array.
     * @param int $debug Debug level.
     * @throws Exception If an error occurs during processing.
     * @return void
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
                if (isset($item['field']) && $item['field'] == $targetFieldName) {
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
                                if (isset($dbProxyRecord[0][$cItem['join-field']])) {
                                    $values[] = $dbProxyRecord[0][$cItem['join-field']];
                                } else {
                                    $values[] = null;
                                }
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
