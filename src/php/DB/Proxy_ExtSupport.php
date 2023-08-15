<?php

namespace INTERMediator\DB;

/* Easy DB Programming Support */

/**
 *
 */
trait Proxy_ExtSupport
{
    /**
     * @var null
     */
    private $extProxy = null;
    /**
     * @var null
     */
    private $extDataSource = null;
    /**
     * @var null
     */
    private $extOptions = null;
    /**
     * @var null
     */
    private $extDBSpec = null;
    /**
     * @var null
     */
    private $extDebug = null;
    /**
     * @var null
     */
    private $fixedKey = null;
    /**
     * @var bool
     */
    private $testMode = false;

    /**
     * @return null
     */
    public function getExtProxy()
    {
        return $this->extProxy;
    }

    /**
     * @param $key
     * @return void
     */
    public function setFixedKey($key = null)
    {
        $this->fixedKey = $key;
    }

    /**
     * @return void
     */
    public function setTestMode()
    {
        $this->testMode = true;
    }

    /**
     * @param $datasource
     * @param $options
     * @param $dbspec
     * @param $debug
     * @return void
     */
    public function dbInit($datasource = null, $options = null, $dbspec = null, $debug = null)
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
     * @param $target
     * @param $query
     * @param $sort
     * @param $spec
     * @return mixed
     */
    public function dbRead($target, $query = null, $sort = null, $spec = null)
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
     * @param $target
     * @param $query
     * @param $data
     * @param $spec
     * @return mixed
     */
    public function dbUpdate($target, $query = null, $data = null, $spec = null)
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
     * @param $target
     * @param $data
     * @param $spec
     * @return mixed
     */
    public function dbCreate($target, $data = null, $spec = null)
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
     * @param $target
     * @param $query
     * @param $spec
     * @return mixed
     */
    public function dbDelete($target, $query = null, $spec = null)
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
     * @param $target
     * @param $query
     * @param $sort
     * @param $spec
     * @return void
     */
    public function dbCopy($target, $query = null, $sort = null, $spec = null)
    {

    }

    /**
     * @param $target
     * @param $spec
     * @return bool
     */
    private function hasTarget($target, $spec = null)
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
     * @param $target
     * @param $spec
     * @return void
     */
    private function initializeSpec($target, $spec)
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
     * @param $query
     * @return void
     */
    private function setupQuery($query)
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
     * @param $sort
     * @return void
     */
    private function setupSort($sort)
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
     * @param $data
     * @return void
     */
    private function setupData($data)
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