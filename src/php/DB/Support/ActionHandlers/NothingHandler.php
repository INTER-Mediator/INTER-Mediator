<?php

namespace INTERMediator\DB\Support\ActionHandlers;


/**
 * Visitor class for handling no-operation (noop) in the Proxy pattern.
 * Implements methods that effectively bypass all authentication, authorization, data, and challenge operations.
 */
class NothingHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Always returns false (no authentication access required).
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation.
     * 
     * @return bool Always returns true (authentication always succeeds).
     */
    public function checkAuthentication(): bool
    {
        return true;
    }

    /** Visits the CheckAuthorization operation.
     * 
     * @return bool Always returns true (authorization always succeeds).
     */
    public function checkAuthorization(): bool
    {
        return true;
    }

    /** Visits the DataOperation operation. No operation was performed.
     * 
     * @return void
     */
    public function dataOperation(): void
    {
    }

    /** Visits the HandleChallenge operation. No operation was performed.
     * 
     * @return void
     */
    public function handleChallenge(): void
    {
    }

}