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

namespace INTERMediator\DB\Extending;

/**
 * Interface AfterRead_WithNavigation
 * @package INTERMediator\DB\Extending
 */
interface AfterRead_WithNavigation
{
    /**
     * Do after read from DB.
     * @param array<array<string, number|string|bool|null>> $result The result of read operation.
     * @return array<array<string, number|string|bool|null>>|null The result of after read operation.
     */
    public function doAfterReadFromDB($result): ?array;

    /**
     * @return int
     */
    public function countQueryResult();

    /**
     * @return int
     */
    public function getTotalCount();
}
