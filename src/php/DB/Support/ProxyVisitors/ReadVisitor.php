<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Generator;
use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 *
 */
class ReadVisitor extends OperationVisitor
{
    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        return $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }

    /**
     * @param OperationElement $e
     * @return bool
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorization();
    }

    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitDataOperation(OperationElement $e): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start read processing", 2);
        $tableInfo = $this->proxy->dbSettings->getDataSourceTargetArray();
        if ($this->proxy->activateGenerator) { // Schema auto generating mode
            $this->proxy->outputOfProcessing['dbresult'] = (new Generator($this->proxy))->acquire();
            $this->proxy->outputOfProcessing['resultCount'] = 1;
            $this->proxy->outputOfProcessing['totalCount'] = 1;
        } else { // normal access
            $result = $this->proxy->readFromDB();
            if (isset($tableInfo['protect-reading']) && is_array($tableInfo['protect-reading'])) {
                $recordCount = count($result);
                for ($index = 0; $index < $recordCount; $index++) {
                    foreach ($result[$index] as $field => $value) {
                        if (in_array($field, $tableInfo['protect-reading'])) {
                            $result[$index][$field] = "[protected]";
                        }
                    }
                }
            }
            $this->proxy->outputOfProcessing['dbresult'] = $result;
            $this->proxy->outputOfProcessing['resultCount'] = $this->proxy->countQueryResult();
            $this->proxy->outputOfProcessing['totalCount'] = $this->proxy->getTotalCount();
            $this->proxy->suppressMediaToken = false;
        }
    }


    /**
     * @param OperationElement $e
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}