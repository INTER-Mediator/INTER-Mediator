<?php

use PHPUnit\Framework\TestCase;
use INTERMediator\DB\Support\DB_PDO_MySQL_Handler;
use INTERMediator\DB\Support\DB_PDO_PostgreSQL_Handler;
use INTERMediator\DB\Support\DB_PDO_SQLite_Handler;
use INTERMediator\DB\Support\DB_PDO_SQLServer_Handler;

class DB_PDO_QuotedEntityName_Test extends TestCase
{
    public function testMySQL(): void
    {
        $h = new DB_PDO_MySQL_Handler();
        $this->assertEquals('`person`', $h->quotedEntityName('person'));
        $this->assertEquals('`db`.`person`', $h->quotedEntityName('db.person'));
        $this->assertEquals('`id`` = 1 OR 1=1 -- `', $h->quotedEntityName('id` = 1 OR 1=1 -- '));
    }

    public function testSQLServer(): void
    {
        $h = new DB_PDO_SQLServer_Handler();
        $this->assertEquals('[person]', $h->quotedEntityName('person'));
        $this->assertEquals('[dbo].[person]', $h->quotedEntityName('dbo.person'));
        $this->assertEquals('[id]] = 1 OR 1=1 -- ]', $h->quotedEntityName('id] = 1 OR 1=1 -- '));
        $this->assertNull($h->quotedEntityName(''));
    }

    public function testPostgreSQLAndSQLite(): void
    {
        foreach ([new DB_PDO_PostgreSQL_Handler(), new DB_PDO_SQLite_Handler()] as $h) {
            $this->assertEquals('"id"" = 1 --"', $h->quotedEntityName('id" = 1 --'));
        }
    }
}
