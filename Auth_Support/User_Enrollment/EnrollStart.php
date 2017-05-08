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

class EnrollStart extends DB_UseSharedObjects
    implements Extending_Interface_BeforeCreate, Extending_Interface_AfterCreate
{
     public function doBeforeCreateToDB()
    {
        $currentDT = time() + 3600;
        $currentDTFormat = date('YmdHis', $currentDT);

        $dataFromClient = $this->dbSettings->getValuesWithFields();
        $this->dbSettings->addValueWithField(
            "username", $currentDTFormat . $dataFromClient['email']);
        $this->dbSettings->addValueWithField("hashedpasswd", "dummydummydummy");
    }

    public function doAfterCreateToDB($result)
    {
        $createdRecord = $this->dbClass->updatedRecord();
        $hash = $this->proxyObject->userEnrollmentStart($createdRecord[0]["id"]);
        $this->dbClass->setUpdatedRecord("hash", $hash);
        return $result;
    }
}