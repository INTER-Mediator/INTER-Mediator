<?php
/**
 * INTER-Mediator Ver.@@@@2@@@@ Released @@@@1@@@@
 *
 *   Copyright (c) 2010-2015 INTER-Mediator Directive Committee, All rights reserved.
 *
 *   This project started at the end of 2009 by Masayuki Nii  msyk@msyk.net.
 *   INTER-Mediator is supplied under MIT License.
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

class IMUtil
{
    public function removeNull($str)
    {
        return str_replace("\x00", '', $str);
    }
}
