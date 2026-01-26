<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\Exceptions\JWTException;

class SyncUserMiddleware
{
    protected $jwt;

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Handle an incoming request.
     * Syncs user data from JWT token to local database BEFORE authentication
     * This ensures the user exists when JWT auth tries to find them
     */
    public function handle(Request $request, Closure $next)
    {
        // Try to sync user from JWT token before authentication
        try {
            // Get token from Authorization header
            $authHeader = $request->header('Authorization');

            if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $tokenString = $matches[1];

                try {
                    // Set the token and decode it
                    $this->jwt->setToken($tokenString);

                    // Get the payload - this validates the signature but doesn't require user to exist
                    $payload = $this->jwt->getPayload();
                    $payloadArray = $payload->toArray();

                    // Extract user data from JWT payload
                    $userId = $payloadArray['sub'] ?? null;
                    $userEmail = $payloadArray['email'] ?? null;
                    $userName = $payloadArray['name'] ?? null;
                    $userRole = $payloadArray['role'] ?? 'user';

                    if ($userId) {
                        // Sync user to local database BEFORE authentication
                        User::updateOrCreate(
                            ['id' => $userId],
                            [
                                'email' => $userEmail,
                                'name' => $userName,
                                'role' => $userRole,
                            ]
                        );

                        \Log::info("Synced user {$userId} ({$userEmail}) to IP management service");
                    }
                } catch (JWTException $e) {
                    // JWT validation failed - token might be invalid, expired, or secret doesn't match
                    \Log::warning('JWT validation failed in SyncUserMiddleware: ' . $e->getMessage());
                } catch (\Exception $e) {
                    // Other errors
                    \Log::warning('Failed to parse JWT token in SyncUserMiddleware: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            // If sync fails, continue anyway - JWT validation will catch auth issues
            \Log::warning('SyncUserMiddleware error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
