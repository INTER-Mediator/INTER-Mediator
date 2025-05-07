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

namespace INTERMediator\Messaging;

use INTERMediator\DB\Proxy;
use INTERMediator\Params;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Class SendMail
 * Messaging provider for sending emails using SMTP or the mail() function.
 * Supports both legacy and modern email sending architectures.
 *
 * @package INTERMediator\Messaging
 */
class SendMail extends MessagingProvider
{
    /**
     * @var bool Determines if compatibility mode (legacy email sending) is enabled.
     */
    private bool $isCompatible;

    /**
     * SendMail constructor.
     * Initializes compatibility mode from parameters.
     */
    public function __construct()
    {
        $this->isCompatible = Params::getParameterValue("sendMailCompatibilityMode", false);
    }

    /**
     * Sends emails based on the provided parameters and result set.
     * Handles both legacy and new email architectures, supports attachments and storing sent mail data.
     *
     * @param Proxy $dbProxy A Proxy class's instance for logging and settings.
     * @param array $sendMailParam Parameters for email sending (recipients, subject, body, etc.).
     * @param array $result Result set from database operations.
     * @return bool True if all emails sent successfully, false if any error occurred.
     * @throws TransportExceptionInterface
     */
    public function processing(Proxy $dbProxy, array $sendMailParam, array $result): bool
    {
        $smtpConfig = $dbProxy->dbSettings->getSmtpConfiguration();
        if (isset($sendMailParam['template-context'])) {
            $this->isCompatible = false;
        }
        $returnValue = true;
        for ($i = 0; $i < count($result); $i++) {
            $isErrorThisRecord = false;
            $ome = new OME();

            if (isset($sendMailParam['f-option']) && $sendMailParam['f-option'] === true) {
                $ome->useSendMailParam();
            }
            if (isset($sendMailParam['body-wrap']) && $sendMailParam['body-wrap'] > 1) {
                $ome->setBodyWidth($sendMailParam['body-wrap']);
            }

            if (isset($smtpConfig)) {
                if (isset($smtpConfig['password'])) {
                    $ome->setSmtpInfo(array(
                        'host' => $smtpConfig['server'] ?? 'default',
                        'port' => $smtpConfig['port'] ?? '',
                        'protocol' => $smtpConfig['protocol'] ?? 'smtp',
                        'user' => $smtpConfig['username'],
                        'pass' => $smtpConfig['password'],
                        'encryption' => $smtpConfig['encryption'] ?? null,
                    ));
                } else {
                    $ome->setSmtpInfo(array(
                        'host' => $smtpConfig['server'] ?? 'default',
                        'port' => $smtpConfig['port'] ?? '',
                        'protocol' => $smtpConfig['protocol'] ?? 'smtp',
                    ));
                }
            }

            if ($this->isCompatible) {// ================================== Old send main architecture
                $dbProxy->logger->setDebugMessage("[Messaging\SendMail] SendMail old architecture", 2);
                if (isset($sendMailParam['to-constant'])) {
                    $items = explode(",", $sendMailParam['to-constant']);
                    foreach ($items as $item) {
                        $ome->appendToField(trim($item));
                        $dbProxy->logger->setDebugMessage("[Messaging\SendMail] set address {$item}", 2);
                    }
                } else if (isset($sendMailParam['to']) && isset($result[$i][$sendMailParam['to']])) {
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
                } else if (isset($sendMailParam['cc']) && isset($result[$i][$sendMailParam['cc']])) {
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
                } else if (isset($sendMailParam['bcc']) && isset($result[$i][$sendMailParam['bcc']])) {
                    $items = explode(",", $result[$i][$sendMailParam['bcc']]);
                    foreach ($items as $item) {
                        $ome->appendBccField(trim($item));
                    }
                }
                if (isset($sendMailParam['from-constant'])) {
                    $ome->setFromField($sendMailParam['from-constant']);
                } else if (isset($sendMailParam['from']) && isset($result[$i][$sendMailParam['from']])) {
                    $ome->setFromField($result[$i][$sendMailParam['from']]);
                }
                if (isset($sendMailParam['subject-constant'])) {
                    $ome->setSubject($this->modernTemplating($result[$i], $sendMailParam['subject-constant']));
                } else if (isset($sendMailParam['subject']) && isset($result[$i][$sendMailParam['subject']])) {
                    $ome->setSubject($result[$i][$sendMailParam['subject']]);
                }

                if (isset($sendMailParam['body-template'])) {
                    $ome->setTemplateAsFile(dirname($_SERVER["SCRIPT_FILENAME"]) . '/' . $sendMailParam['body-template']);
                    $dataArray = array();
                    if (isset($sendMailParam['body-fields'])) {
                        foreach (explode(',', $sendMailParam['body-fields']) as $fieldName) {
                            $fieldName = trim($fieldName);
                            if (str_starts_with($fieldName, '@')) {
                                $dataArray[] = substr($fieldName, 1);
                            } else if (isset($result[$i][$fieldName])) {
                                $dataArray[] = $result[$i][$fieldName];
                            } else {
                                $dataArray[] = '';
                            }
                        }
                    }
                    $ome->insertToTemplate($dataArray);
                } else if (isset($sendMailParam['body-constant'])) {
                    $ome->setBody($this->modernTemplating($result[$i], $sendMailParam['body-constant']), true);
                } else if (isset($result[$i][$sendMailParam['body']]) && $sendMailParam['body']) {
                    $ome->setBody($result[$i][$sendMailParam['body']]);
                }
            } else { // ==================================================== New send main architecture
                $dbProxy->logger->setDebugMessage("[Messaging\SendMail] SendMail new architecture", 2);
                $labels = ['to', 'cc', 'bcc', 'from', 'subject', 'body'];
                $mailSeed = [];
                foreach ($labels as $label) {
                    $mailSeed[$label] = $sendMailParam[$label] ?? '';
                }
                if (isset($sendMailParam['template-context'])) {
                    $cParam = explode('@', $sendMailParam['template-context']);
                    if (count($cParam) == 2) {  // Specify a context and target record criteria
                        $idParam = explode('=', $cParam[1]);
                        if (count($idParam) == 2) {
                            $currentDataSource = $dbProxy->dbSettings->getDataSource();
                            if (!$dbProxy->dbSettings->isExistContext($cParam[0])) {
                                $currentDataSource[] = ['name' => $cParam[0], 'key' => $idParam[0], 'records' => 1];
                            }
                            $storeContext = new Proxy();
                            $storeContext->ignoringPost();
                            $storeContext->initialize(
                                $currentDataSource,
                                $dbProxy->dbSettings->getOptions(),
                                $dbProxy->dbSettings->getDbSpec(),
                                2, $cParam[0]);
                            $storeContext->dbSettings->setCurrentUser($dbProxy->dbSettings->getCurrentUser());
                            $dbProxy->logger->setDebugMessage("Proxy with the {$cParam[0]} context.", 2);
                            $storeContext->dbSettings->addExtraCriteria($idParam[0], "=", $idParam[1]);
                            $storeContext->processingRequest("read", true);
                            $templateRecords = $storeContext->getDatabaseResult();
                            if (count($templateRecords) > 0) {
                                $dbProxy->logger->setDebugMessage("[Messaging\SendMail] Acquired mail template: "
                                    . $sendMailParam['template-context'], 2);
                            }
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

                $dbProxy->logger->setDebugMessage("[Messaging\SendMail] mailSeed = " . var_export($mailSeed, true), 2);

                $unsentAddrs = [];
                $items = explode(",", $this->modernTemplating($result[$i], $mailSeed['to']));
                foreach ($items as $item) {
                    $addr = trim($item);
                    if ($addr != '' && !$ome->appendToField($addr)) {
                        $unsentAddrs[] = $addr;
                    }
                }
                $items = explode(",", $this->modernTemplating($result[$i], $mailSeed['cc']));
                foreach ($items as $item) {
                    $addr = trim($item);
                    if ($addr != '' && !$ome->appendCcField(trim($item))) {
                        $unsentAddrs[] = $addr;
                    }
                }
                $items = explode(",", $this->modernTemplating($result[$i], $mailSeed['bcc']));
                foreach ($items as $item) {
                    $addr = trim($item);
                    if ($addr != '' && !$ome->appendBccField(trim($item))) {
                        $unsentAddrs[] = $addr;
                    }
                }
                if (count($unsentAddrs) > 0) {
                    $isErrorThisRecord = true;
                    $returnValue = false;
                    $dbProxy->logger->setDebugMessage("[Messaging\SendMail] Cancel to send for bad address: " . implode(', ', $unsentAddrs), 2);
                    $this->setWarningMessage(1050, implode(', ', $unsentAddrs));
                }
                $ome->setFromField(trim($this->modernTemplating($result[$i], $mailSeed['from'])));
                $ome->setSubject($this->modernTemplating($result[$i], $mailSeed['subject']));
                $bodyString = $this->modernTemplating($result[$i], $mailSeed['body']);
                $type = (str_starts_with($bodyString, '<html>')) ? 'text/html' : false;

                $ome->setBody($bodyString, $type);
            }
            // ====================================================

            if (isset($sendMailParam['attachment']) && $dbProxy->dbSettings->getMediaRoot()) {
                $fpath = $dbProxy->dbSettings->getMediaRoot() . "/" .
                    $this->modernTemplating($result[$i], $sendMailParam['attachment']);
                $ome->addAttachment($fpath);
                $dbProxy->logger->setDebugMessage("[Messaging\SendMail] Attachment: {$fpath}", 2);
            }
            if (!$isErrorThisRecord) {
                if ($ome->send()) {
                    $dbProxy->logger->setDebugMessage("[Messaging\SendMail] !!! Succeed to send mail.", 2);
                    if (isset($sendMailParam['store'])) {
                        $storeContext = new Proxy();
                        $storeContext->ignoringPost();
                        $storeContext->initialize(
                            $dbProxy->dbSettings->getDataSource(),
                            $dbProxy->dbSettings->getOptions(),
                            $dbProxy->dbSettings->getDbSpec(),
                            2, $sendMailParam['store']);
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
                    $dbProxy->logger->setDebugMessage("[Messaging\SendMail] !!! Fail to send mail."
                        . $ome->getErrorMessage(), 2);
                    $this->setWarningMessage(1051, $ome->getErrorMessage());
                    $returnValue = false;
                }
            }
        }
        return $returnValue;
    }
}
