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
 *
 */
interface UploadingSupport
{
    /**
     * @param Proxy $db
     * @param ?string $url
     * @param array|null $options
     * @param array $files
     * @param bool $noOutput
     * @param array $field
     * @param string $contextname
     * @param ?string $keyfield
     * @param ?string $keyvalue
     * @param array|null $datasource
     * @param array|null $dbspec
     * @param int $debug
     */
    public function processing(Proxy $db, ?string $url, ?array $options, array $files, bool $noOutput, array $field,
                               string  $contextname, ?string $keyfield, ?string $keyvalue,
                               ?array  $datasource, ?array $dbspec, int $debug):void;
}
