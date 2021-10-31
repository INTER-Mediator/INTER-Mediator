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

namespace INTERMediator\DB;

/**
 * Interface for Proxy
 */
interface Proxy_Interface extends DBClass_Interface, Auth_Interface_Communication
{
    public function initialize($datasource, $options, $dbspec, $debug, $target = null);

    public function processingRequest($access = null, $bypassAuth = false, $ignoreFiles = false);

    public function finishCommunication();

}

