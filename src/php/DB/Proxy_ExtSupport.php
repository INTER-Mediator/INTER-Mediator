<?php

namespace INTERMediator\DB;

/* Easy DB Programming Support */

trait Proxy_ExtSupport
{
    private $extProxy = null;
    private $extDataSource = null;
    private $extOptions = null;
    private $extDBSpec = null;
    private $extDebug = null;
    private $fixedKey = null;
    private $testMode = false;

    public function getExtProxy()
    {
        return $this->extProxy;
    }

    public function setFixedKey($key = null)
    {
        $this->fixedKey = $key;
    }

    public function setTestMode()
    {
        $this->testMode = true;
    }

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

    public function dbCopy($target, $query = null, $sort = null, $spec = null)
    {

    }

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

    private function setupQuery($query)
    {
        if (!$query) {
            return;
        }
        if (isset($query[0]) && is_array($query[0])) {
            foreach ($query as $item) {
                $this->extProxy->dbSettings->addExtraCriteria($item['field'], $item['operator'], $item['value']);
            }
        } else {
            foreach ($query as $field => $value) {
                $this->extProxy->dbSettings->addExtraCriteria($field, '=', $value);
            }
        }
    }

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