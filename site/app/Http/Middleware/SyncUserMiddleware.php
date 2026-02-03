<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

                    // Extract user data from JWT payload (role may be string or array from Spatie)
                    $userId = $payloadArray['sub'] ?? null;
                    $userEmail = $payloadArray['email'] ?? null;
                    $userName = $payloadArray['name'] ?? null;
                    $rawRole = $payloadArray['role'] ?? null;
                    $userRole = $this->normalizeRole($rawRole);
                    if ($userId && ($rawRole === null || $rawRole === '')) {
                        Log::debug('JWT payload missing role for user ' . $userId . ', defaulting to user');
                    }

                    if ($userId && $userEmail) {
                        $email = strtolower($userEmail);
                        $user = User::firstOrCreate(
                            ['email' => $email],
                            ['id' => $userId, 'name' => $userName, 'role' => $userRole]
                        );
                        $user->update(['id' => $userId, 'name' => $userName, 'role' => $userRole]);
                        Log::info("Synced user {$userId} ({$email}) role={$userRole} to IP management service");
                    }
                } catch (JWTException $e) {
                    // JWT validation failed - token might be invalid, expired, or secret doesn't match
                    Log::warning('JWT validation failed in SyncUserMiddleware: ' . $e->getMessage());
                } catch (\Exception $e) {
                    // Other errors
                    Log::warning('Failed to parse JWT token in SyncUserMiddleware: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            // If sync fails, continue anyway - JWT validation will catch auth issues
            Log::warning('SyncUserMiddleware error: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Normalize role from JWT payload (string or array from Spatie) to a single string.
     */
    private function normalizeRole(mixed $role): string
    {
        if ($role === null || $role === '') {
            return 'user';
        }
        if (is_string($role)) {
            return $role;
        }
        if (is_array($role)) {
            $first = reset($role);
            return is_string($first) ? $first : 'user';
        }
        if (is_object($role) && method_exists($role, 'first')) {
            $first = $role->first();
            return is_string($first) ? $first : 'user';
        }
        return 'user';
    }
}
