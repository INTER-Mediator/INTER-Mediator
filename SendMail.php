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

require_once('lib/mailsend/OME.php');
require_once('lib/mailsend/qdsmtp/qdsmtp.php');

class SendMail
{

    public function processing($sendMailParam, $result, $smtpConfig)
    {
        $isError = false;
        $errorMsg = "";
        for($i = 0 ; $i < count($result) ; $i++) {
            $ome = new OME();

            if (isset($sendMailParam['f-option']) && $sendMailParam['f-option'] === true) {
                $ome->useSendMailParam();
            }
            if (isset($sendMailParam['body-wrap']) && $sendMailParam['body-wrap'] > 1) {
                $ome->setBodyWidth($sendMailParam['body-wrap']);
            }

            $altSMTPConfig = $this->getSmtpConfigFromParams();
            if ($altSMTPConfig !== false && is_array($altSMTPConfig)) {
                $smtpConfig = $altSMTPConfig;
            }
            if (isset($smtpConfig) && is_array($smtpConfig)) {
                if (isset($smtpConfig['password'])) {
                    $ome->setSmtpInfo(array(
                        'host' => $smtpConfig['server'],
                        'port' => $smtpConfig['port'],
                        'protocol' => 'SMTP_AUTH',
                        'user' => $smtpConfig['username'],
                        'pass' => $smtpConfig['password'],
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
                $ome->setSubject($sendMailParam['subject-constant']);
            } else if (isset($result[$i]) && isset($sendMailParam['subject']) && isset($result[$i][$sendMailParam['subject']])) {
                $ome->setSubject($result[$i][$sendMailParam['subject']]);
            }

            if (isset($sendMailParam['body-template'])) {
                $ome->setTemplateAsFile(dirname($_SERVER["SCRIPT_FILENAME"]) . '/' . $sendMailParam['body-template']);
                $dataArray = array();
                if (isset($sendMailParam['body-fields'])) {
                    foreach (explode(',', $sendMailParam['body-fields']) as $fieldName) {
                        $fieldName = trim($fieldName);
                        if (substr($fieldName, 0, 1) == '@')    {
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
                $ome->setBody($sendMailParam['body-constant']);
            } else if (isset($result[$i]) && $sendMailParam['body'] && isset($result[$i][$sendMailParam['body']])) {
                $ome->setBody($result[$i][$sendMailParam['body']]);
            }
            if (!$ome->send()) {
                $isError = true;
                $errorMsg .= strlen($errorMsg) > 0 ; " / ";
                $errorMsg .= $ome->getErrorMessage();
            }
        }
        if($isError)    {
            return $errorMsg;
        }
        return true;
    }

    private function getSmtpConfigFromParams()
    {
        $currentDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $currentDirParam = $currentDir . 'params.php';
        $parentDirParam = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'params.php';
        if (file_exists($parentDirParam)) {
            include($parentDirParam);
        } else if (file_exists($currentDirParam)) {
            include($currentDirParam);
        }
        if (isset($sendMailSMTP)) {
            return $sendMailSMTP;
        }
        return false;
    }
}