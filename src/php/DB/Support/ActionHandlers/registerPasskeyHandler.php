<?php

namespace INTERMediator\DB\Support\ActionHandlers;

class RegisterPasskeyHandler extends ActionHandler
{
    /** Visits the IsAuthAccessing operation.
     *
     * @return bool Result of the operation.
     */
    public function isAuthAccessing(): bool
    {
        return false;
    }

    /** Visits the CheckAuthentication operation.
     *
     * @return bool Result of the operation.
     */
    public function checkAuthentication(): bool
    {
        return false;
    }

    /** Visits the CheckAuthorization operation.
     *
     * @return bool Result of the operation.
     */
    public function checkAuthorization(): bool
    {
        return false;
    }

    /** Visits the DataOperation operation.
     *
     * @return void
     */
    public function dataOperation(): void
    {
    }

    /** Visits the HandleChallenge operation.
     *
     * @return void
     */
    public function handleChallenge(): void
    {
    }

}
