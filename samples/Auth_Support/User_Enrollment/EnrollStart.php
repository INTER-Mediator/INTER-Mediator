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
use INTERMediator\DB\Proxy_ExtSupport;

class EnrollStart extends UseSharedObjects implements BeforeCreate, AfterCreate
{
    use Proxy_ExtSupport;

    public function doBeforeCreateToDB()
    {
        $currentDT = time() + 3600;
        $currentDTFormat = date('YmdHis', $currentDT);
        $dataFromClient = $this->dbSettings->getValuesWithFields();
        $email = $dataFromClient['email'];
        $result = $this->dbRead('authuser', ['email' => $email]);
        if (count($result) > 0) {
            return 'メールアドレスはすでに登録されています。';
        }
        $generatedUsername = $currentDTFormat . $email; // This username format is supposed to the email address can be as a username.
        $this->dbSettings->addValueWithField("username", $generatedUsername);
        $this->dbSettings->addValueWithField("hashedpasswd", "dummydummydummy");
    }

    public function doAfterCreateToDB($result): ?array
    {
        if (isset($result[0])) {
            $hash = $this->proxyObject->userEnrollmentStart($result[0]["id"]);
            $result[0]['hash'] = $hash;
        }
        return $result;
    }
}