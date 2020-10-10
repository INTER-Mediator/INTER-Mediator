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

interface UploadingSupport
{
    /**
     * @param bool $useFileSystem
     * @param $options
     * @param $files
     * @param $noOutput
     * @param $field
     * @param bool $useFMContainer
     * @param bool $useS3
     * @param $contextname
     * @param $keyfield
     * @param $keyvalue
     * @param $datasource
     * @param $dbspec
     * @param $debug
     */
    public function processing($db, $url, $options, $files, $noOutput, $field, $contextname, $keyfield, $keyvalue, $datasource, $dbspec, $debug);
}