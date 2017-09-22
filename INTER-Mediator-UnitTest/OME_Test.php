<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2014/02/10
 * Time: 15:40
 */

require_once(dirname(__FILE__) . '/../lib/mailsend/OME.php');
require_once(dirname(__FILE__) . '/../lib/mailsend/qdsmtp/qdsmtp.php');

class OME_Test extends PHPUnit_Framework_TestCase
{

    var $mailAddress = "msyk@msyk.net";
    var $smtpSettings = array(
        'host' => 's98.coreserver.jp',
        'port' => 587,
        'protocol' => 'SMTP_AUTH',
        'user' => 'msyktest@msyk.net',
        'pass' => 'msyk27745test',
    );
    /*
      * This SMTP account won't access any time. Masayuki Nii has this account, and he will be activate it
      * just on his testing only. Usually this password might be wrong.
      */

    public function testAddressDividing()
    {
        $addrString = "Masayuki Nii <msyk@msyk.net>";
        $ome = new OME();
        $ome->setToField($addrString);
        $this->assertEquals($ome->getToField() === $addrString, true, "[ERROR] in parse mail address string.");
    }

    public function testSendSimpleMail()
    {
        $ome = new OME();
        $ome->setToField($this->mailAddress, "Masayuki Nii");
        $ome->setFromField($this->mailAddress, "新居雅行");
        $ome->setSubject("INTER-Mediator ユニットテスト: testSendSimpleMail");
        $ome->setBody("INTER-Mediator Uni Test: testSendSimpleMail");
        $ome->appendBody("\nINTER-Mediator Uni Test: testSendSimpleMail");
        $ome->appendBody("\nINTER-Mediator ユニットテスト: testSendSimpleMail");
        $ome->appendBody("\nINTER-Mediator Uni Test: testSendSimpleMail");
        $result = $ome->send();
        $this->assertEquals($result, true, "[ERROR] in sending mail");
    }

    public function testSendMailSMTP()
    {
        date_default_timezone_set("Asia/Tokyo");

        $ome = new OME();
        $ome->setSmtpInfo($this->smtpSettings);

        $ome->setToField($this->mailAddress, "Masayuki Nii");
        $ome->setFromField($this->mailAddress, "新居雅行");
        $ome->setCurrentDateToHead();
        $ome->setSubject("INTER-Mediator ユニットテスト: testSendMailSMTP");
        $ome->setBody("INTER-Mediator Uni Test: testSendMailSMTP");
        $ome->appendBody("\nINTER-Mediator Uni Test: testSendMailSMTP");
        $ome->appendBody("\nINTER-Mediator ユニットテスト: testSendMailSMTP");
        $ome->appendBody("\nINTER-Mediator Uni Test: testSendMailSMTP");
        for ($i=0;$i<100;$i++)  {
            $ome->appendBody("日本語の「複雑な」構造を、持った文章(sentence)を、書いてみたら、こうなったですぞ。");
        }
        $ome->appendBody("\nこれが最後です。");
        $result = $ome->send();
        $this->assertEquals($result, true, "[ERROR] in sending mail");
    }
}
 