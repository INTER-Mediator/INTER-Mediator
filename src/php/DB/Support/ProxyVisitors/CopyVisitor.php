<?php

namespace INTERMediator\DB\Support\ProxyVisitors;

use INTERMediator\DB\Support\ProxyElements\CheckAuthenticationElement;
use INTERMediator\DB\Support\ProxyElements\DataOperationElement;
use INTERMediator\DB\Support\ProxyElements\HandleChallengeElement;
use INTERMediator\DB\Logger;

/**
 *
 */
class CopyVisitor extends OperationVisitor
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
        $proxy = $this->proxy;
        Logger::getInstance()->setDebugMessage("[processingRequest] start copy processing", 2);

        if ($proxy->checkValidation()) {
            $result = $proxy->copyInDB();
            $proxy->outputOfProcessing['newRecordKeyValue'] = $result;
            $proxy->outputOfProcessing['dbresult'] = $proxy->getUpdatedRecord();
        } else {
            $proxy->logger->setErrorMessage("Invalid data. Any validation rule was violated.");
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