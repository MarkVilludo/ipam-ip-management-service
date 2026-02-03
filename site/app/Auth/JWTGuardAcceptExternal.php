<?php

namespace App\Auth;

use Tymon\JWTAuth\JWTGuard;

/**
 * JWT guard that accepts tokens issued by the auth service (cross-service).
 * Skips subject model (prv) validation so tokens from auth service are accepted
 * even if the issuing app uses a different model namespace.
 */
class JWTGuardAcceptExternal extends JWTGuard
{
    /**
     * Ensure the JWTSubject matches what is in the token.
     * We accept any valid token from the auth service (skip prv check).
     *
     * @return bool
     */
    protected function validateSubject()
    {
        return true;
    }
}
