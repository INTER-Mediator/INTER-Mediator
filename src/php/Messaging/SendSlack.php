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

use INTERMediator\IMUtil;

class SendSlack extends MessagingProvider
{
    private $token;
    private $channel;

    public function __construct()
    {
        $params = IMUtil::getFromParamsPHPFile(["slackParameters"], true);
        $this->token = $params['slackParameters'] ? $params['slackParameters']['token'] : null;
        $this->channle = $params['slackParameters'] ? $params['slackParameters']['channel'] : null;
    }

    /**
     * @param $dbProxy The DB\Proxy class's instance.
     * @param $contextDef The context definition array of current context.
     * @param $result The result of query or other db operations.
     * @return mixed (No return)
     */
    public function processing($dbProxy, $contextDef, $result)
    {
        $options = $dbProxy->dbSettings->getOptions();
        if (isset($options['slack'])) {
            $this->token = isset($options['slack']['token']) ? $options['slack']['token'] : $this->token;
            $this->channel = isset($options['slack']['channel']) ? $options['slack']['channel'] : $this->channel;
        }
        $this->channel = isset($contextDef['subject-constant']) ? $contextDef['subject-constant'] : $this->channel;

        $isError = false;
        $errorMsg = "";
        for ($i = 0; $i < count($result); $i++) {
            $channel = $this->channel;
            if (isset($result[$i]) && isset($contextDef['subject']) && isset($result[$i][$contextDef['subject']])) {
                $channel = $result[$i][$contextDef['subject']];
            }
            $channel = $this->modernTemplating($result[$i], $channel);
            $message = '=Nothing specifies for message=';
            if (isset($contextDef['body-constant'])) {
                $message = $this->modernTemplating($result[$i], $contextDef['body-constant']);
            }
            if (isset($contextDef['body'])) {
                $message = $this->modernTemplating($result[$i], $result[$i][$contextDef['body']]);
            }
            $msgURL = "https://slack.com/api/chat.postMessage";
            $header = ["Content-Type: application/json; charset=utf-8", "Authorization: Bearer {$this->token}"];
            $body = json_encode(['channel' => $channel, 'text' => $message]);
            $info = '';
            $error = '';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $msgURL);
            curl_setopt($ch, CURLOPT_PORT, 443);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $response = curl_exec($ch);
            $errorNumber = curl_errno($ch);
            $dbProxy->logger->setDebugMessage("[SendSlack]Error={$errorNumber}, Response={$response}");
            if (!$errorNumber) {
                $info = var_export(curl_getinfo($ch), true);
                $dbProxy->logger->setDebugMessage("[SendSlack]Info={$info}", 2);
            } else {
                $isError = true;
                $errorMsg .= "//{$error}";
                $error = curl_error($ch);
                $dbProxy->logger->setErrorMessage("[SendSlack]Info={$error}");
            }
            curl_close($ch);
        }
        if ($isError) {
            return $errorMsg;
        }
        return true;
    }
}
