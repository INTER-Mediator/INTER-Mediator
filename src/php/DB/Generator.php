<?php
/**
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 *
 * @copyright     Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * @link          https://inter-mediator.com/
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace INTERMediator\DB;

use INTERMediator\Params;
use PDOException;

/**
 *
 */
class Generator
{
    private string $generatorUser;
    private string $generatorPassword;
    private Logger $logger;
    private \PDO $link;
    private Proxy $proxy;
    private array $dsnElements;
    private string $dsnPrefix;
    private array $contextDef;
    private ?string $parentKey;

    public function __construct(Proxy $proxy)
    {
        $this->generatorUser = Params::getParameterValue('generatorUser', '');
        $this->generatorPassword = Params::getParameterValue('generatorPassword', '');
        $this->logger = Logger::getInstance();
        $this->proxy = $proxy;
        $this->parseDSN($this->proxy->dbSettings->getDbSpecDSN());
        $this->contextDef = $this->proxy->dbSettings->getDataSourceTargetArray();
    }

    public function generate(): void
    {
        $this->createDBLink();
        $dbName = $this->dsnElements['dbname'];
        if (!in_array($dbName, $this->getDatabases())) {
            $this->createDatabase($dbName);
        }
        $this->useDatabase($dbName);
        $targetTable = $this->proxy->dbSettings->getEntityForUpdate();
        $parentContextDef = $this->proxy->dbSettings->getDataSourceDefinition($this->proxy->dbSettings->getParentOfTarget());
        $this->parentKey = $parentContextDef['key'] ?? null;
        if (!in_array($targetTable, $this->getTables())) {
            $this->createTable($targetTable);
        } else {
            $this->updateTable($targetTable);
        }
    }

    private function createDBLink(): void
    {
        try {
            $this->link = new \PDO($this->generateDSN(), $this->generatorUser, $this->generatorPassword, []);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('[Schema Generator] Connection Error: ' . $ex->getMessage());
        }
    }

    private function getDatabases(): array
    {
        $dbs = [];
        try {
            $sql = "SHOW DATABASES;";
            $this->logger->setDebugMessage("[Schema Generator] {$sql}");
            $result = $this->link->query($sql);
            if ($result) {
                foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $dbs[] = $row['Database'];
                }
            }
            $this->logger->setDebugMessage("[Schema Generator] " . var_export($dbs, true), 2);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('[Schema Generator] Connection Error: ' . $ex->getMessage());
        }
        return $dbs;
    }

    private function parseDSN($dsn): void
    {
        $colonPos = strpos($dsn, ":");
        if ($colonPos === false) {
            return;
        }
        $this->dsnElements = [];
        $this->dsnPrefix = trim(substr($dsn, 0, $colonPos));
        foreach (explode(';', substr($dsn, $colonPos + 1)) as $item) {
            $eqPos = strpos($item, "=");
            if ($colonPos !== false) {
                $this->dsnElements[trim(substr($item, 0, $eqPos))] = trim(substr($item, $eqPos + 1));
            }
        }
        $this->logger->setDebugMessage("[Schema Generator] {$this->dsnPrefix}/" . var_export($this->dsnElements, true), 2);
    }

    private function generateDSN(): string
    {
        $dsn = '';
        if (isset($this->dsnElements['unix_socket'])) {
            $dsn = "{$this->dsnPrefix}:unix_socket={$this->dsnElements['unix_socket']}";
        } else if (isset($this->dsnElements['host'])) {
            $dsn = "{$this->dsnPrefix}:host={$this->dsnElements['host']}";
            if (isset($this->dsnElements['port'])) {
                $dsn .= ";port={$this->dsnElements['port']}";
            }
        }
        return $dsn;
    }

    private function createDatabase(string $dbName): void
    {
        try {
            $sql = "CREATE DATABASE {$dbName};";
            $this->logger->setDebugMessage("[Schema Generator] {$sql}");
            $result = $this->link->query($sql);
            if (!$result) {
                throw (new \Exception("[Schema Generator] Failed in creating database."));
            }
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('[Schema Generator] Error: ' . $ex->getMessage());
        }

    }

    private function useDatabase(string $dbName): void
    {
        try {
            $sql = "USE {$dbName}";
            $this->logger->setDebugMessage("[Schema Generator] {$sql}");
            $result = $this->link->query($sql);
            if (!$result) {
                throw (new \Exception("[Schema Generator] Failed in creating database."));
            }
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('[Schema Generator] Error: ' . $ex->getMessage());
        }

    }

    private function getTables(): array
    {
        $tables = [];
        try {
            $sql = "SHOW TABLES;";
            $this->logger->setDebugMessage("[Schema Generator] {$sql}");
            $result = $this->link->query($sql);
            if ($result) {
                foreach ($result->fetchAll(\PDO::FETCH_NUM) as $row) {
                    $tables[] = $row[0];
                }
            }
            $this->logger->setDebugMessage("[Schema Generator] " . var_export($tables, true), 2);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('[Schema Generator] Connection Error: ' . $ex->getMessage());
        }
        return $tables;
    }

    private function createTable(string $targetTable): void
    {
        $fieldList = $this->getFieldList();
        $this->logger->setDebugMessage("[Schema Generator] createTable({$targetTable}){$fieldList}", 2);
    }

    private function updateTable(string $targetTable): void
    {
        $fieldList = $this->getFieldList();
        $this->logger->setDebugMessage("[Schema Generator] updateTable({$targetTable}){$fieldList}", 2);
    }

    private function getFieldList(): string
    {
        $fields = [];
        if (isset($this->contextDef['key'])) {
            $fields[$this->contextDef['key']] = 'INT NOT NULL AUTO_INCREMENT';
        }
        foreach ($this->proxy->dbSettings->getFieldsRequired() as $field) {
            $fields[$field] = 'TEXT';
        }
        if (isset($this->contextDef['relation'])) {
            foreach ($this->contextDef['relation'] as $relationDef) {
                if($relationDef['join-field']==$this->parentKey){
                    $fields[$relationDef['foreign-key']] = 'INT';
                } else {
                    $fields[$relationDef['foreign-key']] = 'TEXT';
                }
            }
        }
        return implode(", ",
            array_map(fn($key, $value): string => "$key $value", array_keys($fields), array_values($fields)));
        return $fields;
    }
}