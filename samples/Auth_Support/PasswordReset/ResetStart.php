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

use INTERMediator\DB\Extending\AfterRead;
use INTERMediator\DB\UseSharedObjects;

class ResetStart extends UseSharedObjects implements AfterRead
{
    public function doAfterReadFromDB($result): ?array
    {
        $this->logger->setDebugMessage("[ResetStart::doAfterReadFromDB] " . var_export($result, true), 2);
        if (count($result) === 1) {
            $seqResult = $this->proxyObject->resetPasswordSequenceStart($result[0]["email"]);
            if ($seqResult && isset($seqResult['randdata'])) {
                $result[0]["hash"] = $seqResult['randdata'];
            } else {
                $this->logger->setWarningMessage('パスワードのリセット処理に問題が発生しました。システム側の問題である可能性があります。');
            }
        } else {
            $this->logger->setWarningMessage('パスワードのリセット処理に問題が発生しました。登録されたメールアドレスでない可能性があります。');
        }
        return $result;
    }
}