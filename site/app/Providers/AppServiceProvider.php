<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureJwtNumericConfig();
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
