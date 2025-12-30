<?php

namespace INTERMediator\DB;

/* Easy DB Programming Support */

use Exception;

/**
 * Trait Proxy_ExtSupport provides utility methods and properties for easy DB programming support in INTER-Mediator.
 * Includes proxy/data source management and CRUD operation helpers.
 */
trait Proxy_ExtSupport
{
    /** Proxy instance for extended operations.
     * @var Proxy|null
     */
    private ?Proxy $extProxy = null;
    /** Data source for extended operations.
     * @var array|null
     */
    private ?array $extDataSource = null;
    /** Options for extended operations.
     * @var array|null
     */
    private ?array $extOptions = null;
    /** DB spec for extended operations.
     * @var array|null
     */
    private ?array $extDBSpec = null;
    /** Debug level for extended operations.
     * @var int|null
     */
    private ?int $extDebug = 0;
    /** Fixed key for operations.
     * @var string|null
     */
    private ?string $fixedKey = null;
    /** Test mode flag.
     * @var bool
     */
    private bool $testMode = false;

    /** Get the Proxy instance for extended operations.
     * @return Proxy|null
     * @throws Exception
     */
    public function getExtProxy(): ?Proxy
    {
        if(!$this->extProxy){
            $this->dbInit();
            $this->initializeSpec("", null);
        }
        return $this->extProxy;
    }

    /** Set the fixed key for operations.
     * @param string|null $key
     * @return void
     */
    public function setFixedKey(?string $key = null): void
    {
        $this->fixedKey = $key;
    }

    /** Enable test mode.
     * @return void
     */
    public function setTestMode(): void
    {
        $this->testMode = true;
    }

    /** Initialize the proxy and operation settings.
     * @param array|null $dataSource
     * @param array|null $options
     * @param array|null $dbSpec
     * @param int|null $debug
     * @return void
     */
    public function dbInit(?array $dataSource = null, ?array $options = null, ?array $dbSpec = null, ?int $debug = null): void
    {
        if (!$this->extProxy) {
            $this->extProxy = new Proxy($this->testMode);
            $this->extProxy->ignorePost();
        }
        $this->extDataSource = $dataSource;
        $this->extOptions = $options;
        $this->extDBSpec = $dbSpec;
        $this->extDebug = $debug;
    }

    /** Read records from the database.
     * @param string $target
     * @param array|null $query
     * @param array|null $sort
     * @param array|null $spec
     * @return array|null
     * @throws Exception
     */
    public function dbRead(string $target, ?array $query = null, ?array $sort = null, ?array $spec = null): ?array
    {
        if (!$this->extProxy) {
            $this->dbInit();
        }
        $this->initializeSpec($target, $spec);
        $this->setupQuery($query);
        $this->setupSort($sort);
        $this->extProxy->processingRequest('read', true);
        return $this->extProxy->getDatabaseResult();
    }

    /** Update records in the database.
     * @param string $target
     * @param array|null $query
     * @param array|null $data
     * @param array|null $spec
     * @return array|null
     * @throws Exception
     */
    public function dbUpdate(string $target, ?array $query = null, ?array $data = null, ?array $spec = null): ?array
    {
        if (!$this->extProxy) {
            $this->dbInit();
        }
        $this->initializeSpec($target, $spec);
        $this->setupQuery($query);
        $this->setupData($data);
        $this->extProxy->processingRequest('update', true);
        return $this->extProxy->getDatabaseResult();
    }

    /** Create new records in the database.
     * @param string $target
     * @param array|null $data
     * @param array|null $spec
     * @return array|null
     * @throws Exception
     */
    public function dbCreate(string $target, ?array $data = null, ?array $spec = null): ?array
    {
        if (!$this->extProxy) {
            $this->dbInit();
        }
        $this->initializeSpec($target, $spec);
        $this->setupData($data);
        $this->extProxy->processingRequest('create', true);
        return $this->extProxy->getDatabaseResult();
    }

    /** Delete records from the database.
     * @param string $target
     * @param array|null $query
     * @param array|null $spec
     * @return array|null
     * @throws Exception
     */
    public function dbDelete(string $target, ?array $query = null, ?array $spec = null): ?array
    {
        if (!$this->extProxy) {
            $this->dbInit();
        }
        $this->initializeSpec($target, $spec);
        $this->setupQuery($query);
        $this->extProxy->processingRequest('delete', true);
        return $this->extProxy->getDatabaseResult();
    }

    /** Copy records from the database.
     * @param string $target
     * @param array|null $query
     * @param array|null $sort
     * @param array|null $spec
     * @return array|null
     */
    public function dbCopy(string $target, ?array $query = null, ?array $sort = null, ?array $spec = null): ?array
    {
        // To be implemented.
        return null;
    }

    /** Check if the target exists in the data source.
     * @param string $target
     * @param array|null $spec
     * @return bool
     */
    private function hasTarget(string $target, ?array $spec = null): bool
    {
        $result = false;
        $targetSpec = $spec ?? $this->extDataSource;
        if ($targetSpec) {
            foreach ($targetSpec as $item) {
                if (isset($item['name']) && ($item['name'] == $target)) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    /** Initialize the proxy with the target and spec.
     * @param string $target
     * @param array|null $spec
     * @return void
     * @throws Exception
     */
    private function initializeSpec(string $target, ?array $spec): void
    {
        if ($spec && $this->hasTarget($target, $spec)) {
            $this->extProxy->initialize($spec, $this->extOptions, $this->extDBSpec, $this->extDebug, $target);
        } elseif ($this->extDataSource && $this->hasTarget($target)) {
            $this->extProxy->initialize(
                $this->extDataSource, $this->extOptions, $this->extDBSpec, $this->extDebug, $target);
        } else {
            $this->extProxy->initialize(
                [['name' => $target, 'key' => $this->fixedKey ?? "{$target}_id"]],
                $this->extOptions, $this->extDBSpec, $this->extDebug, $target);
        }
    }

    /** Set up the query for the operation.
     * @param array|null $query
     * @return void
     */
    private function setupQuery(?array $query): void
    {
        if (!$query) {
            return;
        }
        if (isset($query[0]) && is_array($query[0])) {
//            Logger::getInstance()->setDebugMessage("###1#");
            foreach ($query as $item) {
                $this->extProxy->dbSettings->addExtraCriteria($item['field'], $item['operator'], $item['value'] ?? null);
            }
        } else {
            foreach ($query as $field => $value) {
                $this->extProxy->dbSettings->addExtraCriteria($field, '=', $value);
            }
        }
    }

    /** Set up the sort for the operation.
     * @param array|null $sort
     * @return void
     */
    private function setupSort(?array $sort): void
    {
        if (!$sort) {
            return;
        }
        if (isset($sort[0]) && is_array($sort[0])) {
            foreach ($sort as $item) {
                $this->extProxy->dbSettings->addExtraSortKey($item['field'], $item['direction']);
            }
        } else {
            foreach ($sort as $field => $value) {
                $this->extProxy->dbSettings->addExtraSortKey($field, $value);
            }
        }
    }

    /** Set up the data for the operation.
     * @param array|null $data
     * @return void
     */
    private function setupData(?array $data): void
    {
        if (!$data) {
            return;
        }
        if (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $item) {
                $this->extProxy->dbSettings->addValueWithField($item['field'], $item['value']);
            }
        } else {
            foreach ($data as $field => $value) {
                $this->extProxy->dbSettings->addValueWithField($field, $value);
            }
        }
    }
}