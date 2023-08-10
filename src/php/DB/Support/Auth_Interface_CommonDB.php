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

namespace INTERMediator\DB\Support;

interface Auth_Interface_CommonDB
{
    public function getFieldForAuthorization(string $operation): ?string;
    public function getTargetForAuthorization(string $operation): ?string;
    public function getNoSetForAuthorization(string $operation): ?string;
    public function getAuthorizedUsers(?string $operation = null): array;
    public function getAuthorizedGroups(?string $operation = null): array;
}
