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

namespace INTERMediator;

class SendMail
{
    public function processing($dbProxy, $sendMailParam, $result, $smtpConfig)
    {
        $isError = false;
        $errorMsg = "";
        for ($i = 0; $i < count($result); $i++) {
            $ome = new OME();

            if (isset($sendMailParam['f-option']) && $sendMailParam['f-option'] === true) {
                $ome->useSendMailParam();
            }
            if (isset($sendMailParam['body-wrap']) && $sendMailParam['body-wrap'] > 1) {
                $ome->setBodyWidth($sendMailParam['body-wrap']);
            }

            if (isset($smtpConfig) && is_array($smtpConfig)) {
                if (isset($smtpConfig['password'])) {
                    $ome->setSmtpInfo(array(
                        'host' => $smtpConfig['server'],
                        'port' => $smtpConfig['port'],
                        'protocol' => 'SMTP_AUTH',
                        'user' => $smtpConfig['username'],
                        'pass' => $smtpConfig['password'],
                        'encryption' => $smtpConfig['encryption'],
                    ));
                } else {
                    $ome->setSmtpInfo(array(
                        'host' => $smtpConfig['server'],
                        'port' => $smtpConfig['port'],
                        'protocol' => 'SMTP',
                    ));
                }
            }

            if (isset($sendMailParam['to-constant'])) {
                $items = explode(",", $sendMailParam['to-constant']);
                foreach ($items as $item) {
                    $ome->appendToField(trim($item));
                }
            } else if (isset($result[$i]) && isset($sendMailParam['to']) && isset($result[$i][$sendMailParam['to']])) {
                $items = explode(",", $result[$i][$sendMailParam['to']]);
                foreach ($items as $item) {
                    $ome->appendToField(trim($item));
                }
            }
            if (isset($sendMailParam['cc-constant'])) {
                $items = explode(",", $sendMailParam['cc-constant']);
                foreach ($items as $item) {
                    $ome->appendCcField(trim($item));
                }
            } else if (isset($result[$i]) && isset($sendMailParam['cc']) && isset($result[$i][$sendMailParam['cc']])) {
                $items = explode(",", $result[$i][$sendMailParam['cc']]);
                foreach ($items as $item) {
                    $ome->appendCcField(trim($item));
                }
            }
            if (isset($sendMailParam['bcc-constant'])) {
                $items = explode(",", $sendMailParam['bcc-constant']);
                foreach ($items as $item) {
                    $ome->appendBccField(trim($item));
                }
            } else if (isset($result[$i]) && isset($sendMailParam['bcc']) && isset($result[$i][$sendMailParam['bcc']])) {
                $items = explode(",", $result[$i][$sendMailParam['bcc']]);
                foreach ($items as $item) {
                    $ome->appendBccField(trim($item));
                }
            }
            if (isset($sendMailParam['from-constant'])) {
                $ome->setFromField($sendMailParam['from-constant']);
            } else if (isset($result[$i]) && isset($sendMailParam['from']) && isset($result[$i][$sendMailParam['from']])) {
                $ome->setFromField($result[$i][$sendMailParam['from']]);
            }
            if (isset($sendMailParam['subject-constant'])) {
                $subjectStr = $sendMailParam['subject-constant'];
                $startPos = strpos($subjectStr, '@@');
                $endPos = strpos($subjectStr, '@@', $startPos + 2);
                while ($startPos !== false && $endPos !== false) {
                    $fieldName = trim(substr($subjectStr, $startPos + 2, $endPos - $startPos - 2));
                    $subjectStr = substr($subjectStr, 0, $startPos) .
                        (isset($result[$i][$fieldName]) ? $result[$i][$fieldName] : '') .
                        substr($subjectStr, $endPos + 2);
                    $startPos = strpos($subjectStr, '@@');
                    $endPos = strpos($subjectStr, '@@', $startPos + 2);
                }
                $ome->setSubject($subjectStr);
            } else if (isset($result[$i]) && isset($sendMailParam['subject']) && isset($result[$i][$sendMailParam['subject']])) {
                $ome->setSubject($result[$i][$sendMailParam['subject']]);
            }

            if (isset($sendMailParam['body-template'])) {
                $ome->setTemplateAsFile(dirname($_SERVER["SCRIPT_FILENAME"]) . '/' . $sendMailParam['body-template']);
                $dataArray = array();
                if (isset($sendMailParam['body-fields'])) {
                    foreach (explode(',', $sendMailParam['body-fields']) as $fieldName) {
                        $fieldName = trim($fieldName);
                        if (substr($fieldName, 0, 1) == '@') {
                            $dataArray[] = substr($fieldName, 1);
                        } else if (isset($result[$i]) && isset($result[$i][$fieldName])) {
                            $dataArray[] = $result[$i][$fieldName];
                        } else {
                            $dataArray[] = '';
                        }
                    }
                }
                $ome->insertToTemplate($dataArray);
            } else if (isset($sendMailParam['body-constant'])) {
                $bodyStr = $sendMailParam['body-constant'];
                $startPos = strpos($bodyStr, '@@');
                $endPos = strpos($bodyStr, '@@', $startPos + 2);
                while ($startPos !== false && $endPos !== false) {
                    $fieldName = trim(substr($bodyStr, $startPos + 2, $endPos - $startPos - 2));
                    $bodyStr = substr($bodyStr, 0, $startPos) .
                        (isset($result[$i][$fieldName]) ? $result[$i][$fieldName] : '') .
                        substr($bodyStr, $endPos + 2);
                    $startPos = strpos($bodyStr, '@@');
                    $endPos = strpos($bodyStr, '@@', $startPos + 2);
                }
                $ome->setBody($bodyStr);
            } else if (isset($result[$i]) && $sendMailParam['body'] && isset($result[$i][$sendMailParam['body']])) {
                $ome->setBody($result[$i][$sendMailParam['body']]);
            }

            if ($ome->send()) {
                if ($sendMailParam['store']) {
                    $storeContext = new DB\Proxy();
                    $storeContext->ignoringPost();
                    $storeContext->initialize(
                        $dbProxy->dbSettings->getDataSource(),
                        $dbProxy->dbSettings->getOptions(),
                        $dbProxy->dbSettings->getDbSpec(),
                        2, $sendMailParam['store'], null);
                    $storeContext->dbSettings->setCurrentUser($dbProxy->dbSettings->getCurrentUser());
                    $storeContextInfo = $storeContext->dbSettings->getDataSourceTargetArray();
                    $storeContext->dbSettings->addValueWithField("errors", $ome->getErrorMessage());
                    $storeContext->dbSettings->addValueWithField("to_field", $ome->getToField());
                    $storeContext->dbSettings->addValueWithField("bcc_field", $ome->getBccField());
                    $storeContext->dbSettings->addValueWithField("cc_field", $ome->getCcField());
                    $storeContext->dbSettings->addValueWithField("from_field", $ome->getFromField());
                    $storeContext->dbSettings->addValueWithField("subject", $ome->getSubject());
                    $storeContext->dbSettings->addValueWithField("body", $ome->getBody());
                    if (isset($storeContextInfo["query"])) {
                        foreach ($storeContextInfo["query"] as $cItem) {
                            if ($cItem['operator'] == "=" || $cItem['operator'] == "eq") {
                                $storeContext->dbSettings->addValueWithField($cItem['field'], $cItem['value']);
                            }
                        }
                    }
                    if (isset($storeContextInfo["relation"])) {
                        foreach ($storeContextInfo["relation"] as $cItem) {
                            if ($cItem['operator'] == "=" || $cItem['operator'] == "eq") {
                                $storeContext->dbSettings->addValueWithField(
                                    $cItem['foreign-key'], $result[0][$cItem['join-field']]);
                                break;
                            }
                        }
                    }
                    $storeContext->processingRequest("create", true);
                    //    $storeContext->finishCommunication(true);
                    //    $storeContext->exportOutputDataAsJSON();
                }
            } else {
                $isError = true;
                $errorMsg .= (strlen($errorMsg) > 0) ? " / {$ome->getErrorMessage()}" : '';
            }
        }
        if ($isError) {
            return $errorMsg;
        }
        return true;
    }
}