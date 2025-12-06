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
 * Interface for Proxy implementations in INTER-Mediator.
 * Defines methods for initialization, request processing, and output export.
 */
interface Proxy_Interface extends DBClass_Interface, Auth_Interface_Communication
{
    /** Initialize the proxy with data source, options, DB spec, debug level, and target.
     * @param array|null $dataSource Data source definition.
     * @param array|null $options Options for proxy operation.
     * @param array|null $dbSpec Database specification.
     * @param int|null $debug Debug level.
     * @param string|null $target Target context.
     * @return bool True on success, false otherwise.
     */
    function initialize(?array $dataSource, ?array $options, ?array $dbSpec, ?int $debug, ?string $target = null): bool;

    /** Process an incoming request.
     * @param string|null $access Access type.
     * @param bool $bypassAuth Whether to bypass authentication.
     * @param bool $ignoreFiles Whether to ignore file operations.
     * @return void
     */
    public function processingRequest(?string $access = null, bool $bypassAuth = false, bool $ignoreFiles = false): void;

    /** Finish communication for the current request.
     * @return void
     */
    public function finishCommunication(): void;

    /** Export output data as JSON.
     * @return void
     */
    public function exportOutputDataAsJSON(): void;
}
