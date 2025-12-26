<?php

declare(strict_types=1);

namespace VerifyNow\Laravel;

use Illuminate\Support\ServiceProvider;
use VerifyNow\Laravel\Facades\VerifyNow;
use VerifyNow\Laravel\Facades\VerifyNowManager;
use VerifyNow\Laravel\Services\VerifyNowService;
use VerifyNow\Laravel\Services\IDVService;
use VerifyNow\Laravel\Services\AuthenticationService;

/**
 * Service Provider for VerifyNow Laravel Package
 *
 * Registers all package services, facades, configuration, and publishable assets
 * for Laravel auto-discovery.
 */
class VerifyNowServiceProvider extends ServiceProvider
{
    /**
     * Register the package services
     *
     * @return void
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/Config/verifinow.php',
            'verifinow'
        );

        // Register core service
        $this->app->singleton(VerifyNowService::class, function ($app) {
            return new VerifyNowService(
                config('verifinow.api_key'),
                config('verifinow.base_url'),
                config('verifinow.timeout')
            );
        });

        // Register IDV service
        $this->app->singleton(IDVService::class, function ($app) {
            return new IDVService(
                $app->make(VerifyNowService::class)
            );
        });

        // Register Authentication service
        $this->app->singleton(AuthenticationService::class, function ($app) {
            return new AuthenticationService(
                $app->make(VerifyNowService::class)
            );
        });

        // Register facade accessor
        $this->app->bind('verifinow', function ($app) {
            return $app->make(VerifyNowService::class);
        });

        $this->app->bind('verifinow-manager', function ($app) {
            return new class($app->make(IDVService::class), $app->make(AuthenticationService::class)) {
                public function __construct(
                    private IDVService $idvService,
                    private AuthenticationService $authService
                ) {}

                public function idv(): IDVService
                {
                    return $this->idvService;
                }

                public function authentication(): AuthenticationService
                {
                    return $this->authService;
                }
            };
        });
    }

    /**
     * Bootstrap package services
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/Config/verifinow.php' => config_path('verifinow.php'),
        ], 'verifinow-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/Database/Migrations' => database_path('migrations'),
        ], 'verifinow-migrations');

        // Publish routes
        if ($this->app['config']['verifinow.register_routes']) {
            $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    /**
     * Get the services provided by the provider
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            VerifyNowService::class,
            IDVService::class,
            AuthenticationService::class,
            'verifinow',
            'verifinow-manager',
        ];
    }
}
