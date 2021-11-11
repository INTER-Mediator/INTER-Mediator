<?php
/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

use INTERMediator\DB\Extending\AfterCreate;
use INTERMediator\DB\Extending\BeforeCreate;
use INTERMediator\DB\UseSharedObjects;

class EnrollStart extends UseSharedObjects implements BeforeCreate, AfterCreate
{
    public function doBeforeCreateToDB()
    {
        $currentDT = time() + 3600;
        $currentDTFormat = date('YmdHis', $currentDT);

        $dataFromClient = $this->dbSettings->getValuesWithFields();
        $this->dbSettings->addValueWithField("username", $currentDTFormat . $dataFromClient['email']);
        // This username format is supposed to the email address can be as an username.
        $this->dbSettings->addValueWithField("hashedpasswd", "dummydummydummy");
    }

    public function doAfterCreateToDB($result)
    {
        $createdRecord = $this->dbClass->getUpdatedRecord();
        $hash = $this->proxyObject->userEnrollmentStart($createdRecord[0]["id"]);
        $this->dbClass->setDataToUpdatedRecord("hash", $hash);
        return $this->dbClass->getUpdatedRecord();
    }
}