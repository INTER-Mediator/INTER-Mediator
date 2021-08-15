<?php
/**
 * Created by PhpStorm.
 * User: msyk
 * Date: 2014/02/10
 * Time: 15:40
 */

use \PHPUnit\Framework\TestCase;
use INTERMediator\Messaging\OME;
//require_once(dirname(__FILE__) . '/../../src/php/Messaging/OME.php');

class OME_Test extends TestCase
{
    private $mailAddress = "msyk@msyk.net";
    private $smtpSettings = array(
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
    public function testAddressCheck()
    {
        $addrString = "Masayuki Nii <msyk@msyk.net>";
        $ome = new OME();
        $result = $ome->checkEmail($addrString);
        $this->assertFalse($result, "[ERROR] in checking mail address.");

        $addrString = "msyk@msyk.net";
        $ome = new OME();
        $result = $ome->checkEmail($addrString);
        $this->assertTrue($result, "[ERROR] in checking mail address.");

        $addrString = "Masayuki Nii";
        $ome = new OME();
        $result = $ome->checkEmail($addrString);
        $this->assertFalse($result, "[ERROR] in checking mail address.");

        $addrString = "";
        $ome = new OME();
        $result = $ome->checkEmail($addrString);
        $this->assertFalse($result, "[ERROR] in checking mail address.");
    }
    public function testAddressAppend()
    {
        $addrString = "Masayuki Nii <msyk@msyk.net>";
        $ome = new OME();
        $result = $ome->appendToField($addrString);
        $this->assertTrue($result, "[ERROR] in appending mail address.");
        $this->assertTrue($ome->getToField() === $addrString,
            "[ERROR] in appending mail address string. Compare [{$ome->getToField()}] [{$addrString}]");

        $addrString = "";
        $ome = new OME();
        $prevToField = $ome->getToField();
        $result = $ome->appendToField($addrString);
        $this->assertFalse($result, "[ERROR] in appending mail address.");
        $this->assertTrue($ome->getToField() === $prevToField,
            "[ERROR] in appending mail address string. Compare [{$ome->getToField()}] [{$prevToField}]");

        $addrString = "Masayuki Nii <msyk@msyk.net>";
        $ome = new OME();
        $result = $ome->appendCcField($addrString);
        $this->assertTrue($result, "[ERROR] in appending mail address.");
        $this->assertTrue($ome->getCcField() === $addrString,
            "[ERROR] in appending mail address string. Compare [{$ome->getCcField()}] [{$addrString}]");

        $addrString = "";
        $ome = new OME();
        $prevToField = $ome->getCcField();
        $result = $ome->appendCcField($addrString);
        $this->assertFalse($result, "[ERROR] in appending mail address.");
        $this->assertTrue($ome->getCcField() === $prevToField,
            "[ERROR] in appending mail address string. Compare [{$ome->getCcField()}] [{$prevToField}]");

        $addrString = "Masayuki Nii <msyk@msyk.net>";
        $ome = new OME();
        $result = $ome->appendBccField($addrString);
        $this->assertTrue($result, "[ERROR] in appending mail address.");
        $this->assertTrue($ome->getBccField() === $addrString,
            "[ERROR] in appending mail address string. Compare [{$ome->getBccField()}] [{$addrString}]");

        $addrString = "";
        $ome = new OME();
        $prevToField = $ome->getBccField();
        $result = $ome->appendBccField($addrString);
        $this->assertFalse($result, "[ERROR] in appending mail address.");
        $this->assertTrue($ome->getBccField() === $prevToField,
            "[ERROR] in appending mail address string. Compare [{$ome->getBccField()}] [{$prevToField}]");
    }
/*
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
        for ($i = 0; $i < 100; $i++) {
            $ome->appendBody("日本語の「複雑な」構造を、持った文章(sentence)を、書いてみたら、こうなったですぞ。");
        }
        $ome->appendBody("\nこれが最後です。");
        $result = $ome->send();
        $this->assertEquals($result, true, "[ERROR] in sending mail");
    }
*/
}
 