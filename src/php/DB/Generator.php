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

use INTERMediator\IMUtil;
use INTERMediator\Params;
use PDOException;
use Exception;

/**
 * Handles database schema and code generation for INTER-Mediator, including DSN parsing, schema acquisition, and SQL execution.
 */
class Generator
{
    /**
     * The generator username for database access.
     * @var string
     */
    private string $generatorUser;
    /**
     * The generator password for database access.
     * @var string
     */
    private string $generatorPassword;
    /**
     * Logger instance for logging operations.
     * @var Logger
     */
    private Logger $logger;
    /**
     * PDO link for database connection.
     * @var \PDO
     */
    private \PDO $link;
    /**
     * Proxy instance for database settings and operations.
     * @var Proxy
     */
    private Proxy $proxy;
    /**
     * DSN elements parsed from the connection string.
     * @var array
     */
    private array $dsnElements;
    /**
     * DSN prefix (e.g., 'mysql').
     * @var string
     */
    private string $dsnPrefix;
    /**
     * Context definition array.
     * @var array|null
     */
    private ?array $contextDef;
    /**
     * Schema information array.
     * @var array
     */
    private array $schemaInfo;
    /**
     * Options for generator behavior.
     * @var array|null
     */
    private ?array $options;
    /**
     * Supported database engines.
     * @var array
     */
    private array $supportDB = ["mysql",/* "pgsql" */];

    /**
     * Constructor for the Generator class.
     *
     * @param Proxy $proxy Proxy instance for DB settings.
     */
    public function __construct(Proxy $proxy)
    {
        $this->generatorUser = Params::getParameterValue('generatorUser', '');
        $this->generatorPassword = Params::getParameterValue('generatorPassword', '');
        $this->options = Params::getParameterValue('generatorOptions', null);
        $this->logger = Logger::getInstance();
        $this->proxy = $proxy;
        $this->parseDSN($this->proxy->dbSettings->getDbSpecDSN());
        $this->contextDef = $this->proxy->dbSettings->getDataSourceTargetArray();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['generator_info'])) {
            $this->schemaInfo = [];
        } else {
            $this->schemaInfo = $_SESSION['generator_info'];
        }
    }

    /**
     * Acquire schema information from the database.
     *
     * @return array[] The schema information array.
     */
    public function acquire(): array
    {
        $this->schemaInfo['dbName'] = $this->dsnElements['dbname'];
        if (!isset($this->schemaInfo['tables'])) {
            $this->schemaInfo['tables'] = [];
        }

        $contextDef = $this->proxy->dbSettings->getDataSourceTargetArray();
        $fieldList = $this->getFieldList();
        if (!isset($contextDef['aggregation-select'])) {
            $targetTable = $this->proxy->dbSettings->getEntityForUpdate();
            if (!isset($this->schemaInfo['tables'][$targetTable])) {
                $this->schemaInfo['tables'][$targetTable] = [];
            }
            $this->logger->setDebugMessage("[Schema Generator] targetTable = " . $targetTable, 2);

            $this->schemaInfo['tables'][$targetTable]['contextDef-name'] = $contextDef['name'] ?? null;
            $this->schemaInfo['tables'][$targetTable]['contextDef-key'] = $contextDef['key'] ?? null;
            $this->schemaInfo['tables'][$targetTable]['contextDef-view'] = $contextDef['view'] ?? null;
            $this->schemaInfo['tables'][$targetTable]['contextDef-table'] = $contextDef['table'] ?? null;
            $this->schemaInfo['tables'][$targetTable]['contextDef-source'] = $contextDef['source'] ?? null;
            $this->schemaInfo['tables'][$targetTable]['contextDef-relation'] = $contextDef['relation'] ?? null;
            $parentContextName = $this->proxy->dbSettings->getParentOfTarget();
            $parentContextDef = $this->proxy->dbSettings->getDataSourceDefinition($parentContextName);
            $this->schemaInfo['tables'][$targetTable]['contextDef-parent-name'] = $parentContextName;
            $this->schemaInfo['tables'][$targetTable]['contextDef-parent-key'] = $parentContextDef['key'] ?? null;
            if (isset($this->schemaInfo['tables'][$targetTable]['fieldList'])) {
                $this->schemaInfo['tables'][$targetTable]['fieldList']
                    = array_merge($this->schemaInfo['tables'][$targetTable]['fieldList'], $fieldList);
            } else {
                $this->schemaInfo['tables'][$targetTable]['fieldList'] = $fieldList;
            }
            $_SESSION['generator_info'] = $this->schemaInfo;
            session_write_close();
        }
        return $this->generateDummyData($fieldList);
    }

    /**
     * Generate the database schema based on the acquired schema information.
     *
     * @return void
     * @throws Exception
     */
    public function generate(): void
    {
        $_SESSION['generator_info'] = [];
        session_write_close();

        try {  // Establishing the connection with database
            $this->link = new \PDO($this->generateDSN(), $this->generatorUser, $this->generatorPassword, []);
            $sql = $this->proxy->dbClass->handler->sqlSELECTDATABASECommand($this->schemaInfo['dbName']);
            $this->logger->setDebugMessage("[Schema Generator] {$sql}");
            $this->link->query($sql);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('[Schema Generator] ' . $ex->getMessage());
        }

        // Detect foreign key from rom relationship
        foreach ($this->schemaInfo['tables'] as $info) {
            if (isset($info['contextDef-relation'])) {
                $parentContextName = $info['contextDef-parent-name'];
                foreach ($info['contextDef-relation'] as $item) {
                    if (($item['foreign-key'] == ($info['contextDef-key'] ?? '_____'))
                        && (isset($this->schemaInfo['tables'][$parentContextName]))) {
                        $this->schemaInfo['tables'][$parentContextName]['fieldList'][$item['join-field']]
                            = $this->options['fk-type'] ?? 'INT';
                    }
                }
            }
        }

        // Merge infomation of the 'dummy' table
        $detectedTables = array_keys($this->schemaInfo['tables']);
        foreach ($this->schemaInfo['tables'] as $table => $info) {
            if ($table != ($this->options['dummy-table'] ?? 'dummy')) {
                $tableName = '__';
                if (in_array(($info['contextDef-source'] ?? '__'), $detectedTables)) {
                    $tableName = $info['contextDef-source'];
                } else if (in_array(($info['contextDef-view'] ?? '__'), $detectedTables)) {
                    $tableName = $info['contextDef-view'];
                } else if (in_array(($info['contextDef-name'] ?? '__'), $detectedTables)) {
                    $tableName = $info['contextDef-name'];
                }
                if ($tableName != '__') {
                    $this->schemaInfo['tables'][$tableName]['fieldList']
                        = array_merge($this->schemaInfo['tables'][$tableName]['fieldList'], $info['fieldList']);
                }
            }
        }

        $existingTables = $this->getTables();
        $sql = "";
        foreach ($this->schemaInfo['tables'] as $table => $info) {
            if ($table != ($this->options['dummy-table'] ?? 'dummy')) { // Name is not "dummy".
                if (in_array($table, $existingTables)) { // The table is already defined.
                    $definedFields = $this->getTableInfo($table);
                    $this->logger->setDebugMessage("[Schema Generator] definedFields" . var_export($definedFields, true), 2);
                    foreach ($info['fieldList'] as $field => $type) {
                        if (!in_array($field, $definedFields)) {
                            $sql .= $this->proxy->dbClass->handler->sqlADDCOLUMNCommand($table, $field, $type);
                            $sql .= $this->proxy->dbClass->handler->sqlCREATEINDEXCommand(
                                $table, $field, $type === 'TEXT' ? 200 : 0);
                        }
                        // Don't touch the filed which already exists.
                    }
                } else { // The table is not defined.
                    $sql .= $this->proxy->dbClass->handler->sqlCREATETABLECommandStart($table);
                    $postAdding = '';
                    foreach ($info['fieldList'] as $field => $type) {
                        $sql .= $this->proxy->dbClass->handler->sqlFieldDefinitionCommand($field, $type);
                        if ($field != $info['contextDef-key']) {
                            $postAdding .= $this->proxy->dbClass->handler->sqlCREATEINDEXCommand(
                                $table, $field, $type === 'TEXT' ? 200 : 0);
                        }
                    }
                    $sql .= $this->proxy->dbClass->handler->sqlCREATETABLECommandEnd() . $postAdding;
                }
            }
        }
        if (!in_array('operationlog', $existingTables)) {
            $sql .= $this->systemPreparedSchema();
        }

        $this->logger->setDebugMessage("[Schema Generator] SchemaInfo" . var_export($this->schemaInfo, true), 2);
        if (strlen($sql) > 0) {
            try {
                $this->logger->setDebugMessage("[Schema Generator] Execute SQL:\n{$sql}");
                $result = $this->link->query($sql);            // Send schema commands
                if (!$result) {
                    throw (new Exception("Failed in schema operations."));
                }
                $this->logger->setWarningMessage("[Schema Operations are executed]\n"
                    . "The SQL command just executed are here but they are stored in the console of your browser for now. \n\n{$sql}");
            } catch (PDOException $ex) {
                $this->logger->setErrorMessage('[Schema Generator] ' . $ex->getMessage());
            }
        } else {
            $this->logger->setWarningMessage("There is no operation which has to execute..\n{$sql}");
        }
    }

    /**
     * Prepare the database for schema generation.
     *
     * @return void
     */
    public function prepareDatabase(): void
    {
        try {        // Establishing the connection with database
            $this->link = new \PDO($this->generateDSN(true), $this->generatorUser, $this->generatorPassword, []);
            if (!in_array($this->dsnElements['dbname'], $this->getDatabases())) {
                $dbName = $this->dsnElements['dbname'];
                $userName = "webuser";
                $userEntity = "{$userName}@localhost";
                $password = str_replace("'", "z", IMUtil::randomString(20));
                $sql = $this->proxy->dbClass->handler->sqlCREATEDATABASECommand($dbName);
                $sql .= $this->proxy->dbClass->handler->sqlCREATEUSERCommand($dbName, $userEntity, $password);
                $this->logger->setDebugMessage("[Schema Generator] Execute SQL:\n{$sql}", 2);
                $result = $this->link->exec($sql);
                if (!$result) {
                    throw (new Exception("Failed in creating database."));
                }
                $this->logger->setWarningMessage("[Database {$dbName} is created]\n"
                    . "The granted user is generated. You can set it on params.php as:\n\n"
                    . "\$dbUser = '{$userName}';\n\$dbPassword = '{$password}';\n\n"
                    . "You can copy this code on your browser's console.\n{$sql}");
            }
        } catch (Exception $ex) {
            $this->logger->setErrorMessage('[Schema Generator] ' . $ex->getMessage());
        }
    }

    /**
     * Get the list of databases.
     *
     * @return array
     */
    private function getDatabases(): array
    {
        $dbs = [];
        try {
            $sql = $this->proxy->dbClass->handler->sqlLISTDATABASECommand();
            $field = $this->proxy->dbClass->handler->sqlLISTDATABASEColumn();
            $this->logger->setDebugMessage("[Schema Generator] {$sql}");
            $result = $this->link->query($sql);
            if ($result) {
                foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $dbs[] = $row[$field];
                }
            }
            $this->logger->setDebugMessage("[Schema Generator] " . var_export($dbs, true), 2);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('[Schema Generator] Connection Error: ' . $ex->getMessage());
        }
        return $dbs;
    }

    /**
     * Parse the DSN string into elements.
     *
     * @param $dsn
     * @return void
     */
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
            if ($eqPos !== false) {
                $this->dsnElements[trim(substr($item, 0, $eqPos))] = trim(substr($item, $eqPos + 1));
            }
        }
//        $this->logger->setDebugMessage("[Schema Generator] {$this->dsnPrefix}/" . var_export($this->dsnElements, true), 2);
    }

    /**
     * Generate the DSN string for database connection.
     *
     * @param bool $withInitDB
     * @return string
     * @throws Exception
     */
    public function generateDSN(bool $withInitDB = false): string
    {
        if (!in_array($this->dsnPrefix, $this->supportDB)) {
            $msg = "The database '{$this->dsnPrefix}' is NOT supported for Automatic Schema Generation.";
            $this->logger->setWarningMessage($msg);
            throw new Exception($msg);
        }
        $dsn = '';
        if (isset($this->dsnElements['unix_socket'])) {
            $dsn = "{$this->dsnPrefix}:unix_socket={$this->dsnElements['unix_socket']}";
        } else if (isset($this->dsnElements['host'])) {
            $dsn = "{$this->dsnPrefix}:host={$this->dsnElements['host']}";
            if (isset($this->dsnElements['port'])) {
                $dsn .= ";port={$this->dsnElements['port']}";
            }
        }
        if ($withInitDB && $this->dsnPrefix == 'pgsql') {
            $initDB = Params::getParameterValue('dbInitalDBName', null);
            if ($initDB) {
                $dsn .= ";dbname={$initDB}";
            }
        }
        return $dsn;
    }

    /**
     * Get the list of tables in the database.
     *
     * @return array
     */
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
            $this->logger->setDebugMessage("[Schema Generator] getTables:" . var_export($tables, true), 2);
        } catch (PDOException $ex) {
            $this->logger->setErrorMessage('[Schema Generator] Connection Error: ' . $ex->getMessage());
        }
        return $tables;
    }

    /**
     * Get the information of a table.
     *
     * @param string $tableName
     * @return array
     */
    private function getTableInfo(string $tableName): array
    {
        $sql = $this->proxy->dbClass->handler->getTableInfoSQL($tableName); // Returns SQL as like 'SHOW COLUMNS FROM $tableName'.
        $result = null;
        $this->logger->setDebugMessage($sql);
        try {
            $result = $this->link->query($sql);
        } catch (Exception $ex) { // In the case of aggregation-select and aggregation-from keyword appear in context definition.
            //return []; // do nothing
        }
        $infoResult = [];
        if ($result) {
            foreach ($result->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $infoResult[] = $row;
            }
        }
        return array_map(function (array $element): string {
            return $element[$this->proxy->dbClass->handler->fieldNameForField];
        }, $infoResult);
    }

    /**
     * Get the list of fields for a table.
     *
     * @return array
     */
    private function getFieldList(): array
    {
        $fields = [];
        $contextDef = $this->proxy->dbSettings->getDataSourceTargetArray();
        $excludeFields = [];
        if (isset($contextDef['calculation'])) {
            foreach ($contextDef['calculation'] as $item) {
                $excludeFields[] = $item['field'];
            }
        }
        $keys = ['query', 'sort', 'default-values'];
        foreach ($keys as $key) {
            if (isset($contextDef[$key])) {
                foreach ($contextDef[$key] as $entry) {
                    $fields[$entry['field']] = $this->decidedFieldType($entry['field']);
                }
            }
        }
        foreach ($this->proxy->dbSettings->getFieldsRequired() as $field) {
            if (!in_array($field, $excludeFields)) {
                $fields[$field] = $this->decidedFieldType($field);
            }
        }
        if (isset($this->contextDef['key'])) {
            $fields[$this->contextDef['key']] = $this->options['pk-type'] ?? 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY';
        }
        if (isset($this->contextDef['relation'])) {
            foreach ($this->contextDef['relation'] as $relationDef) {
                if ($relationDef['foreign-key'] != ($this->contextDef['key'] ?? '_______')) {
                    $fields[$relationDef['foreign-key']] = $this->options['fk-type'] ?? 'INT';
                }
            }
        }
        return $fields;
    }

    /**
     * Generate dummy data for a table.
     *
     * @param array $fieldList
     * @return array[]
     */
    private function generateDummyData(array $fieldList): array
    {
        $result = [];
        foreach ($fieldList as $field => $type) {
            $result[$field] = ($type === 'INT' || $type === 'DOUBLE') ? 1 : 'abcd';
        }
        return [$result];
    }

    /**
     * Decide the field type based on the field name.
     *
     * @param string $field
     * @return string
     */
    private function decidedFieldType(string $field): string
    {
        if (preg_match("/{$this->options['datetime-suffix']}$/", $field)) {
            return "DATETIME";
        }
        if (preg_match("/{$this->options['date-suffix']}$/", $field)) {
            return "DATE";
        }
        if (preg_match("/{$this->options['time-suffix']}$/", $field)) {
            return "TIME";
        }
        if (preg_match("/{$this->options['int-suffix']}$/", $field)) {
            return "INT";
        }
        if (preg_match("/{$this->options['double-suffix']}$/", $field)) {
            return "DOUBLE";
        }
        if (preg_match("/{$this->options['text-suffix']}$/", $field)) {
            return "TEXT";
        }
        if (preg_match("/^{$this->options['datetime-prefix']}/", $field)) {
            return "DATETIME";
        }
        if (preg_match("/^{$this->options['date-prefix']}/", $field)) {
            return "DATE";
        }
        if (preg_match("/^{$this->options['time-prefix']}/", $field)) {
            return "TIME";
        }
        if (preg_match("/^{$this->options['int-prefix']}/", $field)) {
            return "INT";
        }
        if (preg_match("/^{$this->options['double-prefix']}/", $field)) {
            return "DOUBLE";
        }
        if (preg_match("/^{$this->options['text-prefix']}/", $field)) {
            return "TEXT";
        }
        return $this->options['default-type'];
    }

    /**
     * Get the system prepared schema.
     *
     * @return string
     */
    private function systemPreparedSchema(): string
    {
        return <<<EOL
CREATE TABLE registeredcontext
(
    id           INT AUTO_INCREMENT,
    clientid     TEXT,
    entity       TEXT,
    conditions   TEXT,
    registereddt DATETIME,
    PRIMARY KEY (id)
) CHARACTER SET utf8mb4,
  COLLATE utf8mb4_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE registeredpks
(
    context_id INT,
    pk         INT,
    PRIMARY KEY (context_id, pk),
    FOREIGN KEY (context_id) REFERENCES registeredcontext (id) ON DELETE CASCADE
) CHARACTER SET utf8mb4,
  COLLATE utf8mb4_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE authuser
(
    id           INT AUTO_INCREMENT,
    username     VARCHAR(64),
    hashedpasswd VARCHAR(72),
    realname     VARCHAR(20),
    email        VARCHAR(100),
    limitdt      DATETIME,
    PRIMARY KEY (id)
) CHARACTER SET utf8mb4,
  COLLATE utf8mb4_unicode_ci
  ENGINE = InnoDB;

CREATE INDEX authuser_username
    ON authuser (username);
CREATE INDEX authuser_email
    ON authuser (email);
CREATE INDEX authuser_limitdt
    ON authuser (limitdt);

CREATE TABLE authgroup
(
    id        INT AUTO_INCREMENT,
    groupname VARCHAR(48),
    PRIMARY KEY (id)
) CHARACTER SET utf8mb4,
  COLLATE utf8mb4_unicode_ci
  ENGINE = InnoDB;

CREATE TABLE authcor
(
    id            INT AUTO_INCREMENT,
    user_id       INT,
    group_id      INT,
    dest_group_id INT,
    privname      VARCHAR(48),
    PRIMARY KEY (id)
) CHARACTER SET utf8mb4,
  COLLATE utf8mb4_unicode_ci
  ENGINE = InnoDB;

CREATE INDEX authcor_user_id
    ON authcor (user_id);
CREATE INDEX authcor_group_id
    ON authcor (group_id);
CREATE INDEX authcor_dest_group_id
    ON authcor (dest_group_id);

CREATE TABLE issuedhash
(
    id         INT AUTO_INCREMENT,
    user_id    INT,
    clienthost VARCHAR(64),
    hash       VARCHAR(64),
    expired    DateTime,
    PRIMARY KEY (id)
) CHARACTER SET utf8mb4,
  COLLATE utf8mb4_unicode_ci
  ENGINE = InnoDB;

CREATE INDEX issuedhash_user_id
    ON issuedhash (user_id);
CREATE INDEX issuedhash_expired
    ON issuedhash (expired);
CREATE INDEX issuedhash_clienthost
    ON issuedhash (clienthost);
CREATE INDEX issuedhash_user_id_clienthost
    ON issuedhash (user_id, clienthost);

# Operation Log Store
CREATE TABLE operationlog
(
    id            INT AUTO_INCREMENT,
    dt            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user          VARCHAR(48),
    client_id_in  VARCHAR(48),
    client_id_out VARCHAR(48),
    require_auth  BIT(1),
    set_auth      BIT(1),
    client_ip     VARCHAR(60),
    path          VARCHAR(256),
    access        VARCHAR(20),
    context       VARCHAR(50),
    get_data      TEXT,
    post_data     TEXT,
    result        TEXT,
    error         TEXT,
    condition0    VARCHAR(50),
    condition1    VARCHAR(50),
    condition2    VARCHAR(50),
    condition3    VARCHAR(50),
    condition4    VARCHAR(50),
    field0        TEXT,
    field1        TEXT,
    field2        TEXT,
    field3        TEXT,
    field4        TEXT,
    field5        TEXT,
    field6        TEXT,
    field7        TEXT,
    field8        TEXT,
    field9        TEXT,
    PRIMARY KEY (id)
) CHARACTER SET utf8mb4,
  COLLATE utf8mb4_unicode_ci
  ENGINE = InnoDB;
# In case of real deployment, some indices are required for quick operations.

EOL;

    }
}