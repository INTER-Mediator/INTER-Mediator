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

/**
 * Interface MessagingProvider
 * @package INTERMediator\Messaging
 */
interface MessagingProvider
{
    /**
     * @param $dbProxy The DB\Proxy class's instance.
     * @param $contextDef The context definition array of current context.
     * @param $result The result of query or other db operations.
     * @return mixed (No return)
     */
    public function processing($dbProxy, $contextDef, $result);
}
