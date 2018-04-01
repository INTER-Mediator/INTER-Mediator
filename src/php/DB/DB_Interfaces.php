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

namespace INTERMediator;

interface Extending_Interface_BeforeDelete
{
    public function doBeforeDeleteFromDB();
}

interface Extending_Interface_AfterDelete
{
    public function doAfterDeleteFromDB($result);
}

interface Extending_Interface_BeforeRead
{
    public function doBeforeReadFromDB();
}

interface Extending_Interface_AfterRead
{
    public function doAfterReadFromDB($result);
}

interface Extending_Interface_AfterRead_WithNavigation
{
    public function doAfterReadFromDB( $result);
    public function countQueryResult();
    public function getTotalCount();
}

interface Extending_Interface_BeforeUpdate
{
    public function doBeforeUpdateDB();
}

interface Extending_Interface_AfterUpdate
{
    public function doAfterUpdateToDB($result);
}

interface Extending_Interface_BeforeCreate
{
    public function doBeforeCreateToDB();
}

interface Extending_Interface_AfterCreate
{
    public function doAfterCreateToDB($result);
}

interface Extending_Interface_BeforeCopy
{
    public function doBeforeCopyInDB();
}

interface Extending_Interface_AfterCopy
{
    public function doAfterCopyInDB($result);
}
