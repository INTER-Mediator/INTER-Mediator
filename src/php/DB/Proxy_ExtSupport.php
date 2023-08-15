<?php

namespace INTERMediator\DB;

/* Easy DB Programming Support */

/**
 *
 */
trait Proxy_ExtSupport
{
    /**
     * @var Proxy|null
     */
    private ?Proxy $extProxy = null;
    /**
     * @var array|null
     */
    private ?array $extDataSource = null;
    /**
     * @var array|null
     */
    private ?array $extOptions = null;
    /**
     * @var array|null
     */
    private ?array $extDBSpec = null;
    /**
     * @var int
     */
    private int $extDebug = 0;
    /**
     * @var string|null
     */
    private ?string $fixedKey = null;
    /**
     * @var bool
     */
    private bool $testMode = false;

    /**
     * @return ?Proxy
     */
    public function getExtProxy(): ?Proxy
    {
        return $this->extProxy;
    }

    /**
     * @param string|null $key
     * @return void
     */
    public function setFixedKey(?string $key = null): void
    {
        $this->fixedKey = $key;
    }

    /**
     * @return void
     */
    public function setTestMode(): void
    {
        $this->testMode = true;
    }

    /**
     * @param array|null $datasource
     * @param array|null $options
     * @param array|null $dbspec
     * @param int|null $debug
     * @return void
     */
    public function dbInit(?array $datasource = null, ?array $options = null, ?array $dbspec = null, int $debug = null): void
    {
        if (!$this->extProxy) {
            $this->extProxy = new Proxy($this->testMode);
            $this->extProxy->ignorePost();
        }
        $this->extDataSource = $datasource;
        $this->extOptions = $options;
        $this->extDBSpec = $dbspec;
        $this->extDebug = $debug;
    }

    /**
     * @param string $target
     * @param array|null $query
     * @param array|null $sort
     * @param array|null $spec
     * @return mixed
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

    /**
     * @param string $target
     * @param array|null $query
     * @param array|null $data
     * @param array|null $spec
     * @return mixed
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

    /**
     * @param string $target
     * @param array|null $data
     * @param array|null $spec
     * @return mixed
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

    /**
     * @param string $target
     * @param array|null $query
     * @param array|null $spec
     * @return mixed
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

    /**
     * @param string $target
     * @param array|null $query
     * @param array|null $sort
     * @param array|null $spec
     * @return void
     */
    public function dbCopy(string $target, ?array $query = null, ?array $sort = null, ?array $spec = null): ?array
    {

    }

    /**
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

    /**
     * @param string $target
     * @param array|null $spec
     * @return void
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

    /**
     * @param array|null $query
     * @return void
     */
    private function setupQuery(?array $query): void
    {
        if (!$query) {
            return;
        }
        if (isset($query[0]) && is_array($query[0])) {
            Logger::getInstance()->setDebugMessage("###1#");
            foreach ($query as $item) {
                $this->extProxy->dbSettings->addExtraCriteria($item['field'], $item['operator'], $item['value'] ?? null);
            }
        } else {
            foreach ($query as $field => $value) {
                $this->extProxy->dbSettings->addExtraCriteria($field, '=', $value);
            }
        }
    }

    /**
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

    /**
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