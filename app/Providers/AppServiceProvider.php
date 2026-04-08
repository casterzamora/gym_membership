<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\AuthManager;
use App\Auth\DualSanctumGuard;

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
        // TODO: Register custom Sanctum guard that supports both User and Member models
        // Temporarily disabled to debug issues
        // auth()->extend('sanctum', function ($app, $name, array $config) {
        //     $guard = new DualSanctumGuard(
        //         auth()->createUserProvider($config['provider'] ?? null),
        //         $app['request']
        //     );
        //     return $guard;
        // });
    }
}
