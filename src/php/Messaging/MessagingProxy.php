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

class MessagingProxy extends MessagingProvider
{
    private $msgProvider;

    public function __construct($driver)
    {
        $className = ucfirst(strtolower(mb_ereg_replace('([a-zA-Z]+)', '\1', $driver)));
        $className = "INTERMediator\\Messaging\\Send{$className}";
        $this->msgProvider = new $className;
    }

    public function processing($dbProxy, $contextDef, $result)
    {
        $className = get_class($this->msgProvider);
        $dbProxy->logger->setDebugMessage("[Messaging\MessagingProxy] Processing with {$className} class.", 1);
        $dbProxy->logger->setDebugMessage("[Messaging\MessagingProxy] context definition: "
            . str_replace("\n", "",substr(var_export($contextDef, true),0,5000)), 2);
        $dbProxy->logger->setDebugMessage("[Messaging\MessagingProxy] processing with: "
            . str_replace("\n", "",substr(var_export($result, true),0,5000)), 2);
        return $this->msgProvider->processing($dbProxy, $contextDef, $result);
    }
}
