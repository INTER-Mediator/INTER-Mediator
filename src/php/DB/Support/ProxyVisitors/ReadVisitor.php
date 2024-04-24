<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Generator;
use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\DB\Logger;

/**
 *
 */
class ReadVisitor extends OperationVisitor
{
    /**
     * @param CheckAuthenticationElement $e
     * @return void
     */
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
    {
        $e->resultOfCheckAuthentication
            = $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }


    /**
     * @param DataOperationElement $e
     * @return void
     */
    public function visitDataOperation(DataOperationElement $e): void
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
     * @param HandleChallengeElement $e
     * @return void
     */
    public function visitHandleChallenge(HandleChallengeElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}