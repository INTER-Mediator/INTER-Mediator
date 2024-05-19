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

use INTERMediator\DB\Extending\BeforeRead;
use INTERMediator\DB\UseSharedObjects;

class ResetFinish extends UseSharedObjects implements BeforeRead
{
    public function doBeforeReadFromDB()
    {
        $dataFromClient = $this->dbSettings->getExtraCriteria();
        $email = $dataFromClient[0]['value'];
        $resetcode = $dataFromClient[1]['value'];
        $hashedpw = $dataFromClient[2]['value'];
        $result = $this->proxyObject->resetPasswordSequenceReturnBack(null, $email, $resetcode, $hashedpw);
        if (!$result) {
            $message = 'パスワードのリセット処理に問題が発生しました。'
                . 'URLに含まれるコードの間違い、メールアドレスの間違い、要求してから1時間以上経過した、などの原因が考えられます。';
            return $message;
        }
        $this->dbSettings->unsetExtraCriteria(1);
        $this->dbSettings->unsetExtraCriteria(2);
    }
}