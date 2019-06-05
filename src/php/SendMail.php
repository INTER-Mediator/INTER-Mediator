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

use INTERMediator\DB\Proxy;

class SendMail
{
    private $isCompatible = true;

    public function __construct()
    {
        $params = IMUtil::getFromParamsPHPFile(["sendMailCompatibilityMode"], true);
        $this->isCompatible = $params['sendMailCompatibilityMode'] ? $params['sendMailCompatibilityMode'] : true;
    }

    public function processing($dbProxy, $sendMailParam, $result, $smtpConfig)
    {
        if (isset($sendMailParam['template-context'])) {
            $this->isCompatible = false;
        }
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

            if ($this->isCompatible) {// ================================== Old send main archtecture
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
                    $ome->setBody($this->modernTemplating($result[$i], $sendMailParam['body-constant']));
                } else if (isset($result[$i]) && $sendMailParam['body'] && isset($result[$i][$sendMailParam['body']])) {
                    $ome->setBody($result[$i][$sendMailParam['body']]);
                }
            } else { // ==================================================== New send main archtecture
                $labels = ['to', 'cc', 'bcc', 'from', 'subject', 'body'];
                $mailSeed = [];
                foreach ($labels as $label) {
                    $mailSeed[$label] = $sendMailParam[$label] ? $sendMailParam[$label] : '';
                }
                if (isset($sendMailParam['template-context'])) {
                    $cParam = explode('@', $sendMailParam['template-context']);
                    if (count($cParam) == 2) {  // Specify a context and target record criteria
                        $idParam = explode('=', $cParam[1]);
                        if (count($idParam) == 2) {
                            $storeContext = new DB\Proxy();
                            $storeContext->ignoringPost();
                            $storeContext->dbSettings->setCurrentUser($dbProxy->dbSettings->getCurrentUser());
                            $storeContext->initialize(
                                $dbProxy->dbSettings->getDataSource(),
                                $dbProxy->dbSettings->getOptions(),
                                $dbProxy->dbSettings->getDbSpec(),
                                2, $cParam[0], null);
                            $storeContext->logger->setDebugMessage("Proxy with the {$cParam[0]} context.", 2);
                            $storeContext->dbSettings->addExtraCriteria($idParam[0], "=", $idParam[1]);
                            $storeContext->processingRequest("read");
                            $templateRecords = $storeContext->getDatabaseResult();
                            $mailSeed = [
                                'to' => $templateRecords[0]['to_field'],
                                'cc' => $templateRecords[0]['cc_field'],
                                'bcc' => $templateRecords[0]['bcc_field'],
                                'from' => $templateRecords[0]['from_field'],
                                'subject' => $templateRecords[0]['subject'],
                                'body' => $templateRecords[0]['body'],
                            ];
                        }
                    } else { // Specify a file name.
                        $fpath = dirname($_SERVER["SCRIPT_FILENAME"]) . '/' . $sendMailParam['template-context'];
                        $mailSeed['body'] = file_get_contents($fpath);
                    }
                }
                $items = explode(",", $this->modernTemplating($result[$i], $mailSeed['to']));
                foreach ($items as $item) {
                    $ome->appendToField(trim($item));
                }
                $items = explode(",", $this->modernTemplating($result[$i], $mailSeed['cc']));
                foreach ($items as $item) {
                    $ome->appendCcField(trim($item));
                }
                $items = explode(",", $this->modernTemplating($result[$i], $mailSeed['bcc']));
                foreach ($items as $item) {
                    $ome->appendBccField(trim($item));
                }
                $ome->appendFromField(trim($this->modernTemplating($result[$i], $mailSeed['from'])));
                $ome->setSubject($this->modernTemplating($result[$i], $mailSeed['subject']));
                $bodyString = $this->modernTemplating($result[$i], $mailSeed['body']);
                $type = strpos($bodyString, '<html>') === 0 ? 'text/html' : false;
                $ome->setBody($bodyString, $type);
            }
            // ====================================================

            if (isset($sendMailParam['attachment']) && $dbProxy->dbSettings->getMediaRoot()) {
                $fpath = "{$dbProxy->dbSettings->getMediaRoot()}/";
                if (substr($sendMailParam['attachment'], 0, 1) === '@') {
                    $fpath .= $result[$i][substr($sendMailParam['attachment'], 1)];
                } else {
                    $fpath .= $sendMailParam['attachment'];
                }
                $ome->addAttachment($fpath);
                $dbProxy->logger->setDebugMessage("Attachment: {$fpath}", 2);
            }
            if ($ome->send()) {
                if (isset($sendMailParam['store'])) {
                    $storeContext = new DB\Proxy();
                    $storeContext->ignoringPost();
                    $storeContext->initialize(
                        $dbProxy->dbSettings->getDataSource(),
                        $dbProxy->dbSettings->getOptions(),
                        $dbProxy->dbSettings->getDbSpec(),
                        2, $sendMailParam['store'], null);
                    $storeContext->logger->setDebugMessage("Proxy with the {$sendMailParam['store']} context.", 2);
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
                            }
                        }
                    }
                    $storeContext->processingRequest("create", true);
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

    private function modernTemplating($record, $tempStr)
    {
        $bodyStr = $tempStr;
        $startPos = strpos($bodyStr, '@@');
        $endPos = strpos($bodyStr, '@@', $startPos + 2);
        while ($startPos !== false && $endPos !== false) {
            $fieldName = trim(substr($bodyStr, $startPos + 2, $endPos - $startPos - 2));
            $bodyStr = substr($bodyStr, 0, $startPos) .
                (isset($record[$fieldName]) ? $record[$fieldName] : '=field not exist=') .
                substr($bodyStr, $endPos + 2);
            $startPos = strpos($bodyStr, '@@');
            $endPos = strpos($bodyStr, '@@', $startPos + 2);
        }
        return $bodyStr;
    }
}