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
abstract class MessagingProvider
{
    /**
     * @param $dbProxy The DB\Proxy class's instance.
     * @param $contextDef The context definition array of current context.
     * @param $result The result of query or other db operations.
     * @return mixed (No return)
     */
    public abstract function processing($dbProxy, $contextDef, $result);

    protected function modernTemplating($record, $tempStr, $ignoreField = false)
    {
        $bodyStr = $tempStr;
        if (!$ignoreField && isset($record[$tempStr])) {
            $bodyStr = $record[$tempStr];
        }
        if (strlen($tempStr) > 5) {
            $startPos = strpos($bodyStr, '@@', 0);
            $endPos = strpos($bodyStr, '@@', $startPos + 2);
            while ($startPos !== false && $endPos !== false) {
                $fieldName = trim(substr($bodyStr, $startPos + 2, $endPos - $startPos - 2));
                $bodyStr = substr($bodyStr, 0, $startPos)
                    . ($record[$fieldName] ?? '') . substr($bodyStr, $endPos + 2);
                $startPos = strpos($bodyStr, '@@');
                $endPos = strpos($bodyStr, '@@', $startPos + 2);
            }
        }
        return $bodyStr;
    }
}
