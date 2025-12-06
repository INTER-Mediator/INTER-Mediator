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
use INTERMediator\Params;
use INTERMediator\DB\Proxy;

/**
 * Class SendSlack
 * Messaging provider for sending messages to Slack channels via Slack API.
 *
 * @package INTERMediator\Messaging
 */
class SendSlack extends MessagingProvider
{
    /** @var string|null Slack API token for authentication.
     */
    private ?string $token = null;

    /** @var string|null Slack channel ID or name to send messages to.
     */
    private ?string $channel = null;

    /** SendSlack constructor.
     * Initializes Slack token and channel from parameters if available.
     */
    public function __construct()
    {
        $slackParameters = Params::getParameterValue("slackParameters", null);
        if (is_array($slackParameters)) {
            $this->token = IMUtil::getFromProfileIfAvailable($slackParameters['token']);
            $this->channel = $slackParameters['channel'];
        }
    }

    /** Sends messages to Slack based on the given DB context and result.
     * @param Proxy $dbProxy Proxy class's instance.
     * @param array $contextDef Context definition array of the current context.
     * @param array $result Result of query or other db operations.
     * @return bool True if all messages sent successfully, false if any error occurred.
     */
    public function processing(Proxy $dbProxy, array $contextDef, array $result): bool
    {
        $options = $dbProxy->dbSettings->getOptions();
        if (isset($options['slack'])) {
            $this->token = $options['slack']['token'] ?? $this->token;
            $this->channel = $options['slack']['channel'] ?? $this->channel;
        }
        $this->channel = $contextDef['subject-constant'] ?? $this->channel;

        $returnValue = true;
        for ($i = 0; $i < count($result); $i++) {
            $channel = $this->channel;
            if (isset($result[$i][$contextDef['subject']]) && isset($contextDef['subject'])) {
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
                $error = curl_error($ch);
                $info = var_export(curl_getinfo($ch), true);
                $dbProxy->logger->setDebugMessage("[SendSlack]Info={$info}, Error={$error}", 2);
                $this->setWarningMessage(1055, curl_error($ch));
                $returnValue = false;
            }
        }
        return $returnValue;
    }
}
