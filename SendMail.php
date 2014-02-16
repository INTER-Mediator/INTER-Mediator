<?php
/*
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   by Masayuki Nii  msyk@msyk.net Copyright (c) 2010-2014 Masayuki Nii, All rights reserved.
 *
 *   This project started at the end of 2014.
 *   INTER-Mediator is supplied under MIT License.
 */
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2014/02/14
 * Time: 12:19
 */

require_once('lib/mailsend/OME.php');
require_once('lib/mailsend/qdsmtp/qdsmtp.php');

class SendMail
{

    public function processing($sendMailParam, $result, $smtpConfig)
    {
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
            $ome->setSmtpInfo(array(
                'host' => $smtpConfig['server'],
                'port' => $smtpConfig['port'],
                'protocol' => (isset($smtpConfig['password']) ? 'SMTP_AUTH' : 'SMTP'),
                'user' => $smtpConfig['username'],
                'pass' => $smtpConfig['password'],
            ));
        }

        if (isset($sendMailParam['to-constant'])) {
            $ome->setToField($sendMailParam['to-constant']);
        } else if (isset($result[0]) && isset($sendMailParam['to']) && isset($result[0][$sendMailParam['to']])) {
            $ome->setToField($result[0][$sendMailParam['to']]);
        }
        if (isset($sendMailParam['cc-constant'])) {
            $ome->setToField($sendMailParam['cc-constant']);
        } else if (isset($result[0]) && isset($sendMailParam['cc']) && isset($result[0][$sendMailParam['cc']])) {
            $ome->setCcField($result[0][$sendMailParam['cc']]);
        }
        if (isset($sendMailParam['bcc-constant'])) {
            $ome->setToField($sendMailParam['bcc-constant']);
        } else if (isset($result[0]) && isset($sendMailParam['bcc']) && isset($result[0][$sendMailParam['bcc']])) {
            $ome->setBccField($result[0][$sendMailParam['bcc']]);
        }
        if (isset($sendMailParam['from-constant'])) {
            $ome->setFromField($sendMailParam['from-constant']);
        } else if (isset($result[0]) && isset($sendMailParam['from']) && isset($result[0][$sendMailParam['from']])) {
            $ome->setFromField($result[0][$sendMailParam['from']]);
        }
        if (isset($sendMailParam['subject-constant'])) {
            $ome->setSubject($sendMailParam['subject-constant']);
        } else if (isset($result[0]) && isset($sendMailParam['subject']) && isset($result[0][$sendMailParam['subject']])) {
            $ome->setSubject($result[0][$sendMailParam['subject']]);
        }

        if (isset($sendMailParam['body-template'])) {
            $ome->setTemplateAsFile(dirname($_SERVER["SCRIPT_FILENAME"]) . '/' . $sendMailParam['body-template']);
            $dataArray = array();
            if (isset($sendMailParam['body-fields'])) {
                foreach (explode(',', $sendMailParam['body-fields']) as $fieldName) {
                    if (isset($result[0]) && isset($result[0][$fieldName])) {
                        $dataArray[] = $result[0][$fieldName];
                    } else {
                        $dataArray[] = '';
                    }
                }
            }
            $ome->insertToTemplate($dataArray);
        } else if (isset($sendMailParam['body-constant'])) {
            $ome->setBody($sendMailParam['body-constant']);
        } else if (isset($result[0]) && $sendMailParam['body'] && isset($result[0][$sendMailParam['body']])) {
            $ome->setBody($result[0][$sendMailParam['body']]);
        }

        if (!$ome->send()) {
            return $ome->getErrorMessage();
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