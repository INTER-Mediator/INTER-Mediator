<?php


namespace INTERMediator\Messaging;


class SendSlack implements MessagingProvider
{

    /**
     * @param $dbProxy The DB\Proxy class's instance.
     * @param $contextDef The context definition array of current context.
     * @param $result The result of query or other db operations.
     * @return mixed (No return)
     */
    public function processing($dbProxy, $contextDef, $result)
    {
        // TODO: Implement processing() method.
    }
}
