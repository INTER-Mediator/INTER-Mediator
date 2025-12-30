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

namespace INTERMediator\Media;

use INTERMediator\DB\Proxy;

/**
 * Interface for classes that provide support for downloading media files.
 * Implementing classes should provide methods to retrieve media content and file names.
 */
interface DownloadingSupport
{
    /** @param string $file
     * @param string $target
     * @param Proxy $dbProxyInstance
     * @return string
     */
    public function getMedia(string $file, string $target, Proxy $dbProxyInstance): string;

    /** @param string $file
     * @return string|null
     */
    public function getFileName(string $file): ?string;
}