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

/**
 * Class MessagingProxy
 * Acts as a proxy for messaging providers, delegating message processing to the appropriate provider based on the driver.
 *
 * @package INTERMediator\Messaging
 */
class MessagingProxy extends MessagingProvider
{
    /**
     * @var MessagingProvider The actual messaging provider instance used for processing.
     */
    private MessagingProvider $msgProvider;

    /**
     * MessagingProxy constructor.
     * Initializes the messaging provider based on the specified driver.
     *
     * @param string $driver The driver name (e.g., 'Mail', 'Slack').
     */
    public function __construct(string $driver)
    {
        $className = ucfirst(strtolower(mb_ereg_replace('([a-zA-Z]+)', '\\1', $driver)));
        $className = "INTERMediator\\Messaging\\Send{$className}";
        $this->msgProvider = new $className;
    }

    /**
     * Processes the messaging request by delegating to the actual provider.
     *
     * @param Proxy $dbProxy Proxy class's instance for logging and settings.
     * @param array $contextDef Context definition array of current context.
     * @param array $result Result set from database operations.
     * @return bool True if processing succeeds, false otherwise.
     */
    public function processing(Proxy $dbProxy, array $contextDef, array $result): bool
    {
        $className = get_class($this->msgProvider);
        $dbProxy->logger->setDebugMessage("[Messaging\\MessagingProxy] Processing with {$className} class.", 1);
        $dbProxy->logger->setDebugMessage("[Messaging\\MessagingProxy] context definition: "
            . str_replace("\n", "", substr(var_export($contextDef, true), 0, 5000)), 2);
        $dbProxy->logger->setDebugMessage("[Messaging\\MessagingProxy] processing with: "
            . str_replace("\n", "", substr(var_export($result, true), 0, 5000)), 2);
        return $this->msgProvider->processing($dbProxy, $contextDef, $result);
    }
}
