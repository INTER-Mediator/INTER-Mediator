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

// error_reporting(E_ALL); // For debugging

require_once('src/php/INTER-Mediator.php');

function IM_Entry($dataSource, $options, $dbSpecification, $debug = false)
{
    INTERMediator\IM_Entry($dataSource, $options, $dbSpecification, $debug, $_SERVER['SCRIPT_FILENAME']);
}

function IM_Entry_YAML($yaml, $defFile = null) {
    $yamlContent = INTERMediator\IMUtil::getDefinitionFromYAML($yaml);
    IM_Entry(
        $yamlContent['contexts'] ?? null, $yamlContent['options'] ?? null,
        $yamlContent['connection'] ?? null, $yamlContent['debug'] ?? 2, $defFile
    );
}