<?php

namespace INTERMediator\DB\Support\ActionHandlers;

class AuthPasskeyHandler extends ActionHandler
{
     /** Visits the IsAuthAccessing operation.
     * 
     * @return bool Result of the operation.
     */
     public function isAuthAccessing(): bool{
         return true;
     }

    /** Visits the CheckAuthentication operation.
     * 
     * @return bool Result of the operation.
     */
     public function checkAuthentication(): bool{
         return true;
     }

    /** Visits the CheckAuthorization operation.
     * 
     * @return bool Result of the operation.
     */
     public function checkAuthorization(): bool{
         return true;
     }

    /** Visits the DataOperation operation.
     * 
     * @return void
     */
     public function dataOperation(): void{}

    /** Visits the HandleChallenge operation.
     * 
     * @return void
     */
     public function handleChallenge(): void{}

 }
