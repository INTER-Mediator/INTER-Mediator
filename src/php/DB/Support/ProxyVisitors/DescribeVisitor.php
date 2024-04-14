<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\DB\Logger;

class DescribeVisitor extends OperationVisitor
{
    public function visitCheckAuthentication(CheckAuthenticationElement $e): void
    {
        $e->resultOfCheckAuthentication
            = $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }


    public function visitDataOperation(DataOperationElement $e): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start describe processing", 2);
        $result = $this->proxy->dbClass->getSchema($this->proxy->dbSettings->getDataSourceName());
        $this->proxy->outputOfProcessing['dbresult'] = $result;
        $this->proxy->outputOfProcessing['resultCount'] = 0;
        $this->proxy->outputOfProcessing['totalCount'] = 0;
    }


    public function visitHandleChallenge(HandleChallengeElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}