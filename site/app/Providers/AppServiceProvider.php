<?php

namespace App\Providers;

use App\Auth\JWTGuardAcceptExternal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Set JWT secret as early as possible so it's used when the JWT provider is first resolved.
        // Prefer runtime env (Docker) so auth and IP always use the same secret.
        $this->ensureJwtSecretFromEnvironment();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureJwtNumericConfig();
        $this->useCustomJwtGuard();
    }

    /**
     * Prefer JWT_SECRET from runtime environment (e.g. Docker) so auth and IP service always match.
     * Fall back to .env when running locally without Docker.
     */
    protected function ensureJwtSecretFromEnvironment(): void
    {
        $secret = getenv('JWT_SECRET');
        if ($secret !== false && $secret !== '') {
            Config::set('jwt.secret', $secret);
            return;
        }
        $fromEnv = env('JWT_SECRET');
        if ($fromEnv !== null && $fromEnv !== '') {
            Config::set('jwt.secret', $fromEnv);
        }
    }

    /**
     * Use a JWT guard that accepts tokens from the auth service (skips prv subject validation).
     */
    protected function useCustomJwtGuard(): void
    {
        Auth::extend('jwt', function ($app, $name, array $config) {
            $guard = new JWTGuardAcceptExternal(
                $app['tymon.jwt'],
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }

    /**
     * Force JWT time config to integers so Carbon never receives strings (env() returns strings).
     * Fixes: Carbon\Carbon::rawAddUnit() Argument #3 ($value) must be of type int|float, string given
     */
    protected function ensureJwtNumericConfig(): void
    {
        Config::set('jwt.ttl', (int) config('jwt.ttl', 60));
        Config::set('jwt.refresh_ttl', (int) config('jwt.refresh_ttl', 20160));
        Config::set('jwt.leeway', (int) config('jwt.leeway', 0));
        Config::set('jwt.blacklist_grace_period', (int) config('jwt.blacklist_grace_period', 0));
    }
}
