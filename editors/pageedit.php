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

require_once(dirname(__FILE__) . '/../INTER-Mediator.php');

$defContexts = array(
    array(
        'name' => 'pagefile',
        'records' => 1,
        'key' => 'id',
//            'post-enclosure' => 'pageContentGenerated',
    ),
);

if (php_uname('n') === 'inter-mediator-server' && $_SERVER['SERVER_ADDR'] === '192.168.56.101') {
    // for the INTER-Mediator-Server virtual machine
    IM_Entry($defContexts, array('theme'=>'thosedays'), array('db-class' => 'PageEditor'), false);
}

/**
 * Don't remove comment slashes below on any 'release.'
 */
IM_Entry($defContexts, array('theme'=>'thosedays'), array('db-class' => 'PageEditor'), false);
