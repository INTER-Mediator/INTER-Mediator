<?php

use PHPUnit\Framework\TestCase;

class DB_ExtSupport_Test extends TestCase
{
    use INTERMediator\DB\Proxy_ExtSupport;

    protected $dbSpec;

    function setUp(): void
    {
        $this->dbSpec = [
            'db-class' => 'PDO',
            'dsn' => 'mysql:dbname=test_db;host=127.0.0.1;charset=utf8',
            'user' => 'web',
            'password' => 'password',
        ];
    }

    function testExtSupport1()
    {
        $this->setTestMode();
        $this->setFixedKey('id');
        $this->dbInit([['name' => 'person', 'key' => 'id',],], null, $this->dbSpec, 2);
        $result = $this->dbRead('person');
        $this->assertTrue(is_array($result) and count($result) > 0, 'Read from db and got any data.');

        $result = $this->dbRead('person', [['field' => 'id', 'operator' => '=', 'value' => '2',]]);
        $this->assertCount(1, $result, 'Read from one record from the person table.');
        $this->assertEquals(2, $result[0]['id'], 'The id field of the record is same as query condition.');

        $result = $this->dbRead('person', [['field' => 'id', 'operator' => '=', 'value' => '3',]]);
        $this->assertCount(1, $result, 'Read from one record from the person table.');
        $this->assertEquals(3, $result[0]['id'], 'The id field of the record is same as query condition.');

        $randStr = random_int(10000000, 99999999);
        $result = $this->dbCreate('person', [['field' => 'name', 'value' => $randStr,],]);
        $createdId = $result[0]['id'];
        $result = $this->dbRead('person', [['field' => 'id', 'operator' => '=', 'value' => $createdId,]]);
        $this->assertCount(1, $result, 'Read from one record from the person table.');
        $this->assertEquals($createdId, $result[0]['id'], 'The id field of the record is same as query condition.');
        $this->assertEquals($randStr, $result[0]['name'], 'The name field of the record is same as data parameter.');

        $randStr = random_int(10000000, 99999999);
        $result = $this->dbUpdate('person',
            [['field' => 'id', 'operator' => '=', 'value' => $createdId,]],
            [['field' => 'name', 'value' => $randStr,],]);
        $result = $this->dbRead('person', [['field' => 'id', 'operator' => '=', 'value' => $createdId,]]);
        $this->assertCount(1, $result, 'Read from one record from the person table.');
        $this->assertEquals($createdId, $result[0]['id'], 'The id field of the record is same as query condition.');
        $this->assertEquals($randStr, $result[0]['name'], 'The name field of the record is same as data parameter.');

        $result = $this->dbDelete('person', [['field' => 'id', 'operator' => '=', 'value' => $createdId,]]);
        $result = $this->dbRead('person', [['field' => 'id', 'operator' => '=', 'value' => $createdId,]]);
        $this->assertCount(0, $result, 'Read from one record from the person table.');

        $result = $this->dbDelete('person', [['field' => 'id', 'operator' => '=', 'value' => -999,]]);
        $this->assertCount(0, $result, 'Read from one record from the person table.');
    }

    function testExtSupport2()
    {
        $this->setTestMode();
        $this->setFixedKey('id');
        $this->dbInit([['name' => 'person', 'key' => 'id',],], null, $this->dbSpec, 2);

        $result = $this->dbRead('person', ['id' => '2',]);
        $this->assertCount(1, $result, 'Read from one record from the person table.');
        $this->assertEquals(2, $result[0]['id'], 'The id field of the record is same as query condition.');

        $result = $this->dbRead('person', ['id' => '3',]);
        $this->assertCount(1, $result, 'Read from one record from the person table.');
        $this->assertEquals(3, $result[0]['id'], 'The id field of the record is same as query condition.');

        $randStr = random_int(10000000, 99999999);
        $result = $this->dbCreate('person', ['name' => $randStr,]);
        $createdId = $result[0]['id'];
        $result = $this->dbRead('person', ['id' => $createdId,]);
        $this->assertCount(1, $result, 'Read from one record from the person table.');
        $this->assertEquals($createdId, $result[0]['id'], 'The id field of the record is same as query condition.');
        $this->assertEquals($randStr, $result[0]['name'], 'The name field of the record is same as data parameter.');

        $randStr = random_int(10000000, 99999999);
        $result = $this->dbUpdate('person', ['id' => $createdId,], ['name' => $randStr,]);
        $result = $this->dbRead('person', ['id' => $createdId,]);
        $this->assertEquals(1, count($result), 'Read from one record from the person table.');
        $this->assertEquals($createdId, $result[0]['id'], 'The id field of the record is same as query condition.');
        $this->assertEquals($randStr, $result[0]['name'], 'The name field of the record is same as data parameter.');

        $result = $this->dbDelete('person', [['field' => 'id', 'operator' => '=', 'value' => $createdId,]]);
        $result = $this->dbRead('person', [['field' => 'id', 'operator' => '=', 'value' => $createdId,]]);
        $this->assertCount(0, $result, 'Read from one record from the person table.');

        $result = $this->dbDelete('person', [['field' => 'id', 'operator' => '=', 'value' => -999,]]);
        $this->assertCount(0, $result, 'Read from one record from the person table.');
    }
}