<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Tests\Unit;

use VerifyNow\Laravel\Tests\TestCase;
use VerifyNow\Laravel\Services\VerifyNowService;
use VerifyNow\Laravel\Services\IDVService;
use VerifyNow\Laravel\Services\AuthenticationService;

/**
 * Services Unit Test
 *
 * Tests service classes functionality
 */
class ServicesTest extends TestCase
{
    /**
     * Test VerifyNowService can be instantiated
     *
     * @return void
     */
    public function test_verifinow_service_can_be_instantiated(): void
    {
        $service = app(VerifyNowService::class);

        $this->assertInstanceOf(VerifyNowService::class, $service);
    }

    /**
     * Test IDV service can be instantiated
     *
     * @return void
     */
    public function test_idv_service_can_be_instantiated(): void
    {
        $service = app(IDVService::class);

        $this->assertInstanceOf(IDVService::class, $service);
    }

    /**
     * Test Authentication service can be instantiated
     *
     * @return void
     */
    public function test_authentication_service_can_be_instantiated(): void
    {
        $service = app(AuthenticationService::class);

        $this->assertInstanceOf(AuthenticationService::class, $service);
    }

    /**
     * Test facades are available
     *
     * @return void
     */
    public function test_verifinow_facade_is_available(): void
    {
        $this->assertTrue(
            class_exists('VerifyNow\Laravel\Facades\VerifyNow')
        );
    }

    /**
     * Test verifinow manager facade is available
     *
     * @return void
     */
    public function test_verifinow_manager_facade_is_available(): void
    {
        $this->assertTrue(
            class_exists('VerifyNow\Laravel\Facades\VerifyNowManager')
        );
    }

    /**
     * Test helper functions are available
     *
     * @return void
     */
    public function test_helper_functions_exist(): void
    {
        $this->assertTrue(function_exists('verify_now'));
        $this->assertTrue(function_exists('verifinow_manager'));
        $this->assertTrue(function_exists('request_idv'));
        $this->assertTrue(function_exists('request_authentication'));
        $this->assertTrue(function_exists('check_verification_status'));
    }
}
