<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use Exception;
use INTERMediator\DB\Support\ProxyElements\OperationElement;
use INTERMediator\DB\Logger;

/**
 * Visitor class for handling describe operations in the Proxy pattern.
 * Implements methods for authentication, authorization, schema description, and challenge handling.
 */
class DescribeVisitor extends OperationVisitor
{
    /**
     * Visits the IsAuthAccessing operation.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool Always returns false for describe operations (no auth access required).
     */
    public function visitIsAuthAccessing(OperationElement $e): bool
    {
        return false;
    }

    /**
     * Visits the CheckAuthentication operation for describe operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authentication succeeds, false otherwise.
     */
    public function visitCheckAuthentication(OperationElement $e): bool
    {
        return $this->prepareCheckAuthentication($e) && $this->checkAuthenticationCommon($e);
    }

    /**
     * Visits the CheckAuthorization operation for describe operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return bool True if authorization succeeds, false otherwise.
     */
    public function visitCheckAuthorization(OperationElement $e): bool
    {
        $proxy = $this->proxy;
        return $proxy->authSucceed && $this->checkAuthorization();
    }

    /**
     * Visits the DataOperation operation to perform schema description.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     * @throws Exception If the schema description fails.
     */
    public function visitDataOperation(OperationElement $e): void
    {
        Logger::getInstance()->setDebugMessage("[processingRequest] start describe processing", 2);
        $result = $this->proxy->dbClass->getSchema($this->proxy->dbSettings->getDataSourceName());
        $this->proxy->outputOfProcessing['dbresult'] = $result;
        $this->proxy->outputOfProcessing['resultCount'] = 0;
        $this->proxy->outputOfProcessing['totalCount'] = 0;
    }

    /**
     * Visits the HandleChallenge operation for describe operations.
     *
     * @param OperationElement $e The operation element being visited.
     * @return void
     */
    public function visitHandleChallenge(OperationElement $e): void
    {
        $this->defaultHandleChallenge();
    }

}