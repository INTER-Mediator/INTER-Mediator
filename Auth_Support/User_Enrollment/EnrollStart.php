<?php

/**
 * Created by JetBrains PhpStorm.
 * User: msyk
 * Date: 2013/08/19
 * Time: 0:06
 * To change this template use File | Settings | File Templates.
 */
class EnrollStart extends DB_UseSharedObjects
    implements Extending_Interface_BeforeNew, Extending_Interface_AfterNew
{
    function doBeforeNewToDB($dataSourceName)
    {
        $currentDT = time() + 3600;
        $currentDTFormat = date('YmdHis', $currentDT);

        $dataFromClient = $this->dbSettings->getValuesWithFields();
        $this->dbSettings->addValueWithField(
            "username", $currentDTFormat . $dataFromClient['email']);
        $this->dbSettings->addValueWithField("hashedpasswd", "dummydummydummy");

    }

    function doAfterNewToDB($dataSourceName, $result)
    {
        $dataFromClient = $this->dbSettings->getValuesWithFields();
        $hash = $this->proxyObject->userEnrollmentStart($result);

        require_once("../../lib/mailsend/OME.php");

        $ome = new OME();
        $ome->setSendMailParam('-f info@msyk.net');
        $ome->setFromField('info@msyk.net', 'Masayuki Nii');
        $ome->setToField($dataFromClient['email']);
        $ome->setBccField('info@msyk.net');
        $ome->setSubject('ユーザ登録を受け付けました');
        $ome->setTemplateAsString(<<<EOL
@@2@@ 様（@@1@@）

ユーザ登録を受け付けました。1時間以内に、以下のリンクのサイトに接続してください。接続後にアカウントを発行してご指定のメールアドレスに送付します。

<< URL to Auth_Support folder>>/confirm.php?c=@@3@@

___________________________________
info@msyk.net - Masayuki Nii
EOL
        );
        $ome->insertToTemplate(array(
            $dataFromClient['email'],
            $dataFromClient['realname'],
            $hash,
        ));
        return $ome->send();

    }
}