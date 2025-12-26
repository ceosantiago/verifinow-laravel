<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use VerifyNow\Laravel\VerifyNowServiceProvider;

/**
 * Base Test Case
 *
 * Extends Orchestra Testbench for testing VerifyNow package
 * Provides common test setup and utilities
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * Get package providers
     *
     * @param mixed $app
     * @return array<int, string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            VerifyNowServiceProvider::class,
        ];
    }

    /**
     * Define environment setup
     *
     * @param mixed $app
     * @return void
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('verifinow.api_key', 'test_api_key');
        $app['config']->set('verifinow.base_url', 'https://7on7-backend.verifinow.io');
        $app['config']->set('verifinow.webhook_secret', 'test_webhook_secret');
        $app['config']->set('verifinow.timeout', 30);
        $app['config']->set('verifinow.register_routes', false);
        $app['config']->set('verifinow.log_channel', 'single');
    }

    /**
     * Define database migrations
     *
     * @param mixed $app
     * @return void
     */
    protected function defineDatabaseMigrations($app): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    /**
     * Setup the test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    /**
     * Create a mock verification response
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function getMockVerificationResponse(array $overrides = []): array
    {
        $defaults = [
            'verification_id' => 'ver_' . str_random(20),
            'type' => 'idv',
            'status' => 'pending',
            'result' => 'pending',
            'confidence_score' => 0,
            'created_at' => now()->toIso8601String(),
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create a mock authentication response
     *
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function getMockAuthenticationResponse(array $overrides = []): array
    {
        $defaults = [
            'authentication_id' => 'auth_' . str_random(20),
            'verification_id' => 'ver_' . str_random(20),
            'status' => 'pending',
            'result' => 'pending',
            'confidence_score' => 0,
            'liveness_score' => 0,
            'face_match_score' => 0,
            'created_at' => now()->toIso8601String(),
        ];

        return array_merge($defaults, $overrides);
    }

    /**
     * Create a completed verification response
     *
     * @return array<string, mixed>
     */
    protected function getCompletedVerificationResponse(): array
    {
        return $this->getMockVerificationResponse([
            'status' => 'completed',
            'result' => 'approved',
            'confidence_score' => 95.5,
            'completed_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Create a completed authentication response
     *
     * @return array<string, mixed>
     */
    protected function getCompletedAuthenticationResponse(): array
    {
        return $this->getMockAuthenticationResponse([
            'status' => 'completed',
            'result' => 'approved',
            'confidence_score' => 98.2,
            'liveness_score' => 92.5,
            'face_match_score' => 96.8,
            'authenticated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Create a failed verification response
     *
     * @return array<string, mixed>
     */
    protected function getFailedVerificationResponse(): array
    {
        return $this->getMockVerificationResponse([
            'status' => 'failed',
            'result' => 'rejected',
            'confidence_score' => 35.2,
        ]);
    }
}
