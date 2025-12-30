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

namespace INTERMediator\Data_Converter;

/**
 *
 */
class NullZeroString
{
    /**
     */
    public function __construct()
    {
    }

    /** @param string $str
     * @return string|null
     */
    public function converterFromUserToDB(string $str): ?string
    {
        return ($str == '') ? null : $str;
    }

    /** @param string|null $str
     * @return string
     */
    public function converterFromDBtoUser(?string $str): string
    {
        return is_null($str) ? '' : $str;
    }
}
