<?php

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__, 4) . DIRECTORY_SEPARATOR
    . 'src' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'CWPKit' . DIRECTORY_SEPARATOR . 'CWPKit.php');

class CWPKit_Test extends TestCase
{
    public function setUp(): void
    {
        $config = array(
            'urlScheme' => 'http',
            'dataServer' => '10.211.56.2',// '192.168.56.1',
            'dataPort' => '80',
            'DBUser' => 'web',
            'DBPassword' => 'password',
        );
        $this->cwpkit = new CWPKit($config);
    }

    public function test_query()
    {
        $queryString = '-db=TestDB&-lay=person_layout&-findall&-max=1';
        $xml = $this->cwpkit->query($queryString);

        $expected = '1.0';
        $result = (string)$xml->attributes()->version;
        $this->assertEquals($result, $expected);

        $expected = 3;
        $result = (int)$xml->resultset->attributes()->count;
        $this->assertEquals($result, $expected);
    }

    public function test_getServerVersion()
    {
        $expected = '20.1.2.207';
        $result = $this->cwpkit->getServerVersion();
        $this->assertEquals($result, $expected);
    }

    public function test__removeDuplicatedQuery()
    {
        $expected = '-db=TestDB&-lay=person_layout&-find=&name=1&name.op=eq';
        $queryString = '-db=TestDB&-lay=person_layout&-find=&name=1&name.op=eq&name=2&name.op=eq';
        $result = $this->cwpkit->_removeDuplicatedQuery($queryString);
        $this->assertEquals($result, $expected);
    }

    public function test__checkDuplicatedFXCondition()
    {
        $queryString = '-db=TestDB&-lay=person_layout&-find=&name=1&name.op=eq';
        $field = 'name';
        $value = '1';
        $result = $this->cwpkit->_checkDuplicatedFXCondition($queryString, $field, $value);
        $this->assertFalse($result);

        $queryString = '-db=TestDB&-lay=person_layout&-find=&name=1&name.op=eq';
        $field = 'class';
        $value = '1';
        $result = $this->cwpkit->_checkDuplicatedFXCondition($queryString, $field, $value);
        $this->assertTrue($result);
    }

}
