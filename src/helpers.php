<?php

declare(strict_types=1);

use VerifyNow\Laravel\Facades\VerifyNow;
use VerifyNow\Laravel\Facades\VerifyNowManager;

if (!function_exists('verify_now')) {
    /**
     * Get the VerifyNow service
     *
     * @return \VerifyNow\Laravel\Services\VerifyNowService
     */
    function verify_now()
    {
        return VerifyNow::getFacadeRoot();
    }
}

if (!function_exists('verifinow_manager')) {
    /**
     * Get the VerifyNow Manager service
     *
     * @return object
     */
    function verifinow_manager()
    {
        return VerifyNowManager::getFacadeRoot();
    }
}

if (!function_exists('request_idv')) {
    /**
     * Request IDV verification
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    function request_idv(array $data): array
    {
        return VerifyNow::requestIDV($data);
    }
}

if (!function_exists('request_authentication')) {
    /**
     * Request authentication (facial recognition)
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    function request_authentication(array $data): array
    {
        return VerifyNow::requestAuthentication($data);
    }
}

if (!function_exists('check_verification_status')) {
    /**
     * Check verification status
     *
     * @param string $verificationId
     * @return array<string, mixed>
     */
    function check_verification_status(string $verificationId): array
    {
        return VerifyNow::checkVerificationStatus($verificationId);
    }
}
